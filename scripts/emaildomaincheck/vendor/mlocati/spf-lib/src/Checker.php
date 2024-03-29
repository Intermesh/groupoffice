<?php

declare(strict_types=1);

namespace SPFLib;

use IPLib\Address;
use IPLib\Factory;
use SPFLib\Check\DomainNameValidator;
use SPFLib\Check\Environment;
use SPFLib\Check\Result;
use SPFLib\Check\State;
use SPFLib\DNS\Resolver;
use SPFLib\DNS\StandardResolver;
use SPFLib\Macro\MacroString;
use SPFLib\Macro\MacroString\Chunk\Placeholder;
use SPFLib\Macro\MacroString\Expander;
use SPFLib\Semantic\Issue;
use SPFLib\Term\Mechanism;
use SPFLib\Term\Modifier;
use Throwable;

/**
 * Class that check an email environment against SPF DNS records.
 */
class Checker
{
    /**
     * Check flag: check the domain specified in the "HELO" (or EHLO) MTA command.
     *
     * @var int
     */
    public const FLAG_CHECK_HELODOMAIN = 0b0001;

    /**
     * Check flag: check the email address specified in the "MAIL FROM" MTA command.
     *
     * @var int
     */
    public const FLAG_CHECK_MAILFROADDRESS = 0b0010;

    /**
     * @var \SPFLib\DNS\Resolver
     */
    private $dnsResolver;

    /**
     * @var \SPFLib\Decoder
     */
    private $spfDecoder;

    /**
     * @var \SPFLib\SemanticValidator
     */
    private $semanticValidator;

    /**
     * @var \SPFLib\Macro\MacroString\Expander
     */
    private $macroStringExpander;

    /**
     * @var \SPFLib\Check\DomainNameValidator
     */
    private $domainNameValidator;

    /**
     * Initialize the instance.
     *
     * @param \SPFLib\DNS\Resolver|null $dnsResolver the DNS resolver to be used (we'll use the default one if NULL)
     * @param \SPFLib\Decoder|null $spfDecoder the SPF DNS record decoder to be used (we'll use the default one if NULL)
     * @param \SPFLib\SemanticValidator|null $semanticValidator the SPF term semantic validator to be used (we'll use the default one if NULL)
     * @param \SPFLib\Macro\MacroString\Expander|null $macroStringExpander the MacroString expander to be used (we'll use the default one if NULL)
     * @param \SPFLib\Check\DomainNameValidator|null $domainNameValidator the DomainNameValidator to be used (we'll use the default one if NULL)
     */
    public function __construct(?Resolver $dnsResolver = null, ?Decoder $spfDecoder = null, ?SemanticValidator $semanticValidator = null, ?Expander $macroStringExpander = null, ?DomainNameValidator $domainNameValidator = null)
    {
        $this->dnsResolver = $dnsResolver ?: ($spfDecoder === null ? new StandardResolver() : $spfDecoder->getDNSResolver());
        $this->spfDecoder = $spfDecoder ?: new Decoder($this->getDNSResolver());
        $this->semanticValidator = $semanticValidator ?: new SemanticValidator();
        $this->macroStringExpander = $macroStringExpander ?: new Expander();
        $this->domainNameValidator = $domainNameValidator ?: new DomainNameValidator();
    }

    /**
     * Check the the environment agains SPF records.
     *
     * @param \SPFLib\Check\Environment $environment the environment instance holding all the environment values
     *
     * @return \SPFLib\Check\Result
     *
     * @see https://tools.ietf.org/html/rfc7208#section-2.3
     */
    public function check(Environment $environment, int $flags = self::FLAG_CHECK_HELODOMAIN | self::FLAG_CHECK_MAILFROADDRESS): Result
    {
        if ($environment->getClientIP() === null) {
            return Result::create(Result::CODE_NONE)->addMessage('The IP address of the sender SMTP client is not speciified');
        }
        if ($flags & static::FLAG_CHECK_MAILFROADDRESS) {
            $result = $this->checkMailFrom($environment);
        } else {
            $result = null;
        }
        if ($flags & static::FLAG_CHECK_HELODOMAIN) {
            if ($result === null) {
                $result = $this->checkHeloDomain($environment);
            } else {
                switch ($result->getCode()) {
                    case Result::CODE_PASS:
                    case Result::CODE_FAIL:
                    case Result::CODE_ERROR_TEMPORARY:
                    case Result::CODE_ERROR_PERMANENT:
                        break;
                    default:
                        $heloDomain = $environment->getHeloDomain();
                        if ($heloDomain === '' || strcasecmp($heloDomain, $environment->getMailFromDomain()) !== 0) {
                            $result2 = $this->checkHeloDomain($environment);
                            switch ($result2->getCode()) {
                                case Result::CODE_NONE:
                                    break;
                                default:
                                    $result = $result2;
                                    break;
                            }
                        }
                        break;
                }
            }
        }
        if ($result === null) {
            return Result::create(Result::CODE_NONE)->addMessage('No check has been performed (as requested)');
        }

        return $result;
    }

    protected function checkHeloDomain(Environment $environment): Result
    {
        return $this->startValidation($environment, $this->createHeloDomainCheckState($environment));
    }

    protected function checkMailFrom(Environment $environment): Result
    {
        return $this->startValidation($environment, $this->createMailFromCheckState($environment));
    }

    protected function startValidation(Environment $environment, State $state): Result
    {
        $domain = $state->getSenderDomain();
        try {
            $domain = $this->getDomainNameValidator()->check($domain);
        } catch (Exception\InvalidDomainException $x) {
            return Result::create(Result::CODE_NONE)->addMessage($x->getMessage());
        }
        try {
            return $this->validate($state, $domain);
        } catch (Exception\TooManyDNSLookupsException $x) {
            return Result::create(Result::CODE_ERROR_PERMANENT)->addMessage($x->getMessage());
        } catch (Exception\TooManyDNSVoidLookupsException $x) {
            return Result::create(Result::CODE_ERROR_PERMANENT)->addMessage($x->getMessage());
        } catch (Exception\DNSResolutionException $x) {
            return Result::create(Result::CODE_ERROR_TEMPORARY)->addMessage($x->getMessage());
        } catch (Exception\IncludeMechanismException $x) {
            return Result::create($x->getFinalResultCode())->addMessages($x->getIncludeResult()->getMessages());
        } catch (Exception\InvalidDomainException $x) {
            return $this->buildInvalidDomainResult($state, $x);
        }
    }

    /**
     * @throws \SPFLib\Exception\TooManyDNSLookupsException
     * @throws \SPFLib\Exception\TooManyDNSVoidLookupsException
     * @throws \SPFLib\Exception\DNSResolutionException
     * @throws \SPFLib\Exception\IncludeMechanismException
     * @throws \SPFLib\Exception\InvalidDomainException
     */
    protected function validate(State $state, string $domain): Result
    {
        try {
            $record = $this->getSPFDecoder()->getRecordFromDomain($domain);
        } catch (Exception\DNSResolutionException $x) {
            return Result::create(Result::CODE_ERROR_TEMPORARY)->addMessage($x->getMessage());
        } catch (Exception $x) {
            return Result::create(Result::CODE_ERROR_PERMANENT)->addMessage($x->getMessage());
        }
        if ($record === null) {
            return Result::create(Result::CODE_NONE)->addMessage("No SPF DNS record found for domain '{$domain}'");
        }
        $issues = $this->getSemanticValidator()->validate($record, Issue::LEVEL_FATAL);
        if ($issues !== []) {
            $result = Result::create(Result::CODE_ERROR_PERMANENT);
            foreach ($issues as $issue) {
                $result->addMessage($issue->getDescription());
            }

            return $result;
        }
        foreach ($record->getMechanisms() as $mechanism) {
            if ($this->matchMechanism($state, $domain, $mechanism)) {
                switch ($mechanism->getQualifier()) {
                    case Mechanism::QUALIFIER_PASS:
                        return Result::create(Result::CODE_PASS, $mechanism);
                    case Mechanism::QUALIFIER_FAIL:
                        return $this->buildFailResult($state, $domain, $record, $mechanism, Result::CODE_FAIL);
                    case Mechanism::QUALIFIER_SOFTFAIL:
                        return $this->buildFailResult($state, $domain, $record, $mechanism, Result::CODE_SOFTFAIL);
                    case Mechanism::QUALIFIER_NEUTRAL:
                        return Result::create(Result::CODE_NEUTRAL, $mechanism);
                }
            }
        }
        foreach ($record->getModifiers() as $modifier) {
            if ($modifier instanceof Modifier\RedirectModifier) {
                $state->countDNSLookup();
                /** @see https://tools.ietf.org/html/rfc7208#section-6.1 */
                $targetDomain = $this->expandDomainSpec($state, $domain, $modifier->getDomainSpec(), false);
                $result = $this->validate($state, $targetDomain);
                if ($result->getCode() === Result::CODE_NONE) {
                    $result = Result::create(Result::CODE_ERROR_PERMANENT)->addMessage("The redirect SPF record didn't return a response code");
                }

                return $result;
            }
        }
        /** @see https://tools.ietf.org/html/rfc7208#section-4.7 */
        return Result::create(Result::CODE_NEUTRAL)->addMessage('No mechanism matched and no redirect modifier found.');
    }

    protected function createHeloDomainCheckState(Environment $environment): Check\State
    {
        return new Check\State\HeloDomainState($environment, $this->getDNSResolver());
    }

    protected function createMailFromCheckState(Environment $environment): Check\State
    {
        return new Check\State\MailFromState($environment, $this->getDNSResolver());
    }

    protected function getDNSResolver(): Resolver
    {
        return $this->dnsResolver;
    }

    protected function getSPFDecoder(): Decoder
    {
        return $this->spfDecoder;
    }

    protected function getSemanticValidator(): SemanticValidator
    {
        return $this->semanticValidator;
    }

    protected function getMacroStringExpander(): Expander
    {
        return $this->macroStringExpander;
    }

    protected function getDomainNameValidator(): DomainNameValidator
    {
        return $this->domainNameValidator;
    }

    /**
     * @throws \SPFLib\Exception\TooManyDNSLookupsException
     * @throws \SPFLib\Exception\DNSResolutionException
     * @throws \SPFLib\Exception\IncludeMechanismException
     * @throws \SPFLib\Exception\InvalidDomainException
     * @throws \SPFLib\Exception\TooManyDNSVoidLookupsException
     */
    protected function matchMechanism(State $state, string $domain, Mechanism $mechanism): bool
    {
        if ($mechanism instanceof Mechanism\AllMechanism) {
            return $this->matchMechanismAll($state, $domain, $mechanism);
        }
        if ($mechanism instanceof Mechanism\IncludeMechanism) {
            return $this->matchMechanismInclude($state, $domain, $mechanism);
        }
        if ($mechanism instanceof Mechanism\AMechanism) {
            return $this->matchMechanismA($state, $domain, $mechanism);
        }
        if ($mechanism instanceof Mechanism\MxMechanism) {
            return $this->matchMechanismMx($state, $domain, $mechanism);
        }
        if ($mechanism instanceof Mechanism\PtrMechanism) {
            return $this->matchMechanismPtr($state, $domain, $mechanism);
        }
        if ($mechanism instanceof Mechanism\Ip4Mechanism) {
            return $this->matchMechanismIp($state, $domain, $mechanism);
        }
        if ($mechanism instanceof Mechanism\Ip6Mechanism) {
            return $this->matchMechanismIp($state, $domain, $mechanism);
        }
        if ($mechanism instanceof Mechanism\ExistsMechanism) {
            return $this->matchMechanismExists($state, $domain, $mechanism);
        }
    }

    /**
     * @see https://tools.ietf.org/html/rfc7208#section-5.1
     */
    protected function matchMechanismAll(State $state, string $domain, Mechanism\AllMechanism $mechanism): bool
    {
        return true;
    }

    /**
     * @throws \SPFLib\Exception\TooManyDNSLookupsException
     * @throws \SPFLib\Exception\DNSResolutionException
     * @throws \SPFLib\Exception\IncludeMechanismException
     * @throws \SPFLib\Exception\InvalidDomainException
     *
     * @see https://tools.ietf.org/html/rfc7208#section-5.2
     */
    protected function matchMechanismInclude(State $state, string $domain, Mechanism\IncludeMechanism $mechanism): bool
    {
        $targetDomain = $this->expandDomainSpec($state, $domain, $mechanism->getDomainSpec(), false);
        $state->countDNSLookup();
        $includeResult = $this->validate($state, $targetDomain);
        switch ($includeResult->getCode()) {
            case Result::CODE_PASS:
                return true;
            case Result::CODE_FAIL:
            case Result::CODE_SOFTFAIL:
            case Result::CODE_NEUTRAL:
                return false;
            case Result::CODE_ERROR_TEMPORARY:
                throw new Exception\IncludeMechanismException(Result::CODE_ERROR_TEMPORARY, $domain, $mechanism, $includeResult);
            case Result::CODE_NONE:
            case Result::CODE_ERROR_PERMANENT:
                throw new Exception\IncludeMechanismException(Result::CODE_ERROR_PERMANENT, $domain, $mechanism, $includeResult);
        }
    }

    /**
     * @throws \SPFLib\Exception\TooManyDNSLookupsException
     * @throws \SPFLib\Exception\DNSResolutionException
     * @throws \SPFLib\Exception\InvalidDomainException
     * @throws \SPFLib\Exception\TooManyDNSVoidLookupsException
     *
     * @see https://tools.ietf.org/html/rfc7208#section-5.3
     */
    protected function matchMechanismA(State $state, string $domain, Mechanism\AMechanism $mechanism): bool
    {
        $targetDomain = $this->expandDomainSpec($state, $domain, $mechanism->getDomainSpec(), true);
        $state->countDNSLookup();

        return $state->matchDomainIPs($targetDomain, $mechanism->getIp4CidrLength(), $mechanism->getIp6CidrLength());
    }

    /**
     * @throws \SPFLib\Exception\TooManyDNSLookupsException
     * @throws \SPFLib\Exception\DNSResolutionException
     * @throws \SPFLib\Exception\InvalidDomainException
     * @throws \SPFLib\Exception\TooManyDNSVoidLookupsException
     *
     * @see https://tools.ietf.org/html/rfc7208#section-5.4
     */
    protected function matchMechanismMx(State $state, string $domain, Mechanism\MxMechanism $mechanism): bool
    {
        $targetDomain = $this->expandDomainSpec($state, $domain, $mechanism->getDomainSpec(), true);
        $state->countDNSLookup();
        $mxRecords = $this->getDNSResolver()->getMXRecords($targetDomain);
        if (count($mxRecords) > $state::MAX_DNS_LOOKUPS) {
            throw new Exception\TooManyDNSLookupsException($state::MAX_DNS_LOOKUPS);
        }
        foreach ($mxRecords as $mxRecord) {
            $mxRecordIP = Factory::addressFromString($mxRecord);
            if ($mxRecordIP !== null) {
                if ($state->matchIP($mxRecordIP, $mechanism->getIp4CidrLength(), $mechanism->getIp6CidrLength())) {
                    return true;
                }
            } else {
                if ($state->matchDomainIPs($mxRecord, $mechanism->getIp4CidrLength(), $mechanism->getIp6CidrLength())) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @throws \SPFLib\Exception\TooManyDNSLookupsException
     * @throws \SPFLib\Exception\DNSResolutionException
     * @throws \SPFLib\Exception\InvalidDomainException
     *
     * @see https://tools.ietf.org/html/rfc7208#section-5.5
     */
    protected function matchMechanismPtr(State $state, string $domain, Mechanism\PtrMechanism $mechanism): bool
    {
        $targetDomain = $this->expandDomainSpec($state, $domain, $mechanism->getDomainSpec(), true);

        return $state->getValidatedDomain($targetDomain, false) !== '';
    }

    /**
     * @param \SPFLib\Term\Mechanism\Ip4Mechanism|\SPFLib\Term\Mechanism\Ip6Mechanism $mechanism
     *
     * @see https://tools.ietf.org/html/rfc7208#section-5.6
     */
    protected function matchMechanismIp(State $state, string $domain, Mechanism $mechanism): bool
    {
        return $state->matchIP(
            $mechanism->getIP(),
            $mechanism instanceof Mechanism\Ip4Mechanism ? $mechanism->getCidrLength() : null,
            $mechanism instanceof Mechanism\Ip6Mechanism ? $mechanism->getCidrLength() : null
        );
    }

    /**
     * @throws \SPFLib\Exception\TooManyDNSLookupsException
     * @throws \SPFLib\Exception\DNSResolutionException
     * @throws \SPFLib\Exception\InvalidDomainException
     *
     * @see https://tools.ietf.org/html/rfc7208#section-5.7
     */
    protected function matchMechanismExists(State $state, string $domain, Mechanism\ExistsMechanism $mechanism): bool
    {
        $targetDomain = $this->expandDomainSpec($state, $domain, $mechanism->getDomainSpec(), false);
        $state->countDNSLookup();
        foreach ($this->getDNSResolver()->getIPAddressesFromDomainName($targetDomain) as $ip) {
            if ($state->getEnvironment()->getClientIP() instanceof Address\IPv4) {
                if ($ip instanceof Address\IPv4) {
                    return true;
                }
            } elseif ($state->getEnvironment()->getClientIP() instanceof Address\IPv6) {
                if ($ip instanceof Address\IPv4) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @see https://tools.ietf.org/html/rfc7208#section-6.2
     */
    protected function buildFailResult(State $state, string $domain, Record $record, Mechanism $matchedMechanism, string $failCode): Result
    {
        $result = Result::create($failCode, $matchedMechanism);
        foreach ($record->getModifiers() as $modifier) {
            if (!$modifier instanceof Modifier\ExpModifier) {
                break;
            }
            try {
                $targetDomain = $this->expandDomainSpec($state, $domain, $modifier->getDomainSpec(), false);
                $txtRecords = $this->getDNSResolver()->getTXTRecords($targetDomain);
                $numTxtRecords = count($txtRecords);
                switch ($numTxtRecords) {
                    case 0:
                        $result->addMessage("Failed to build the fail explanation string: no TXT records for '{$targetDomain}'");
                        break;
                    case 1:
                        $macroStringDecoder = $this->getSPFDecoder()->getMacroStringDecoder();
                        $macroString = $macroStringDecoder->decode($txtRecords[0], $macroStringDecoder::FLAG_EXP);
                        $string = $this->getMacroStringExpander()->expand($macroString, $targetDomain, $state);
                        if (!preg_match('/^[\x01-\x7f]*$/s', $string)) {
                            $result->addMessage("Failed to build the fail explanation string: non US-ASCII chars found in '{$string}'");
                        } else {
                            $result->setFailExplanation($string);
                        }
                        break;
                    default:
                        $result->addMessage("Failed to build the fail explanation string: more that one TXT records (exactly {$numTxtRecords}) for '{$targetDomain}'");
                        break;
                }
            } catch (Throwable $x) {
                $result->addMessage("Failed to build the fail explanation string: {$x->getMessage()}.");
            }
            break;
        }

        return $result;
    }

    /**
     * @throws \SPFLib\Exception\InvalidDomainException
     */
    protected function expandDomainSpec(State $state, string $currentDomain, MacroString $macroString, bool $useCurrentDomainIfEmpty): string
    {
        if ($useCurrentDomainIfEmpty && $macroString->isEmpty()) {
            return $currentDomain;
        }
        $targetDomain = $this->getMacroStringExpander()->expand($macroString, $currentDomain, $state);

        return $this->getDomainNameValidator()->check($targetDomain, $macroString);
    }

    protected function buildInvalidDomainResult(State $state, Exception\InvalidDomainException $exception): Result
    {
        $code = Result::CODE_ERROR_PERMANENT;
        $domainSpec = $exception->getDerivedFrom();
        if ($domainSpec !== null) {
            foreach ($domainSpec->getChunks() as $chunk) {
                if ($chunk instanceof Placeholder) {
                    $code = Result::CODE_FAIL;
                    break;
                }
            }
        }

        return Result::create($code)->addMessage($exception->getMessage());
    }
}
