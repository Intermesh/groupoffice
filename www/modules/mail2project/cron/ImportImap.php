<?php

namespace GO\Mail2project\Cron;

use Exception;
use GO;
use GO\Addressbook\Model\Company;
use GO\Addressbook\Model\Contact;
use GO\Base\Cron\AbstractCron;
use GO\Base\Cron\CronJob;
use GO\Base\Db\FindParams;
use GO\Base\Exception\AccessDenied;
use GO\Base\Fs\File;
use GO\Base\Fs\Folder;
use GO\Base\Mail\SystemMessage;
use GO\Base\Model\User;
use GO\Email\Model\Account;
use GO\Email\Model\ImapMessage;
use GO\Projects2\Model\Project;
use Swift_Attachment;

class ImportImap extends AbstractCron
{
    public $emailAccount;

    /**
     * @return bool
     */
    public function enableUserAndGroupSupport()
    {
        return false;
    }

    /**
     * @return String
     */
    public function getLabel()
    {
        return 'Import projects from IMAP';
    }

    /**
     * @return String
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * @param CronJob $cronJob
     * @param User|null $user
     * @return bool
     * @throws Exception
     */
    public function run(CronJob $cronJob,User $user = null)
    {

		$account = Account::model()->findSingleByAttribute("id",$this->emailAccount);
        if (!$account) {
            return false;
        }

        /** @var ImapMessage[] $messages */
        $messages = ImapMessage::model()->find(
            $account,
            'INBOX',
            0,
            1);

		echo 'Messages count: ' . count($messages);
        GO::debug('Messages count: ' . count($messages));

        foreach ($messages as $message) {
            try {
                $this->processMessage($message);
                if (GO::config()->mail2project['deleteMessage']) {
                    $message->delete();
                }
            } catch (Exception $e) {
                trigger_error($e, E_USER_WARNING);
                GO::debug($this->getLabel() . ' IS CAUSING AN ERROR: ' . $e->getMessage());
                GO::debug($this->getLabel() . $e->getTraceAsString());
            }
        }
        return true;
    }

    /**
     * @param ImapMessage|null $message
     * @throws Exception
     */
    public function processMessage(ImapMessage $message)
    {
        $project = $this->_createProject($message);
        $emailAttachments = $message->getAttachments();
        if (count($emailAttachments)) {
            GO::debug('Found attachments ...');
            foreach ($emailAttachments as $_attachment) {
                $_folder = new Folder(GO::config()->file_storage_path . '/' . $project->buildFilesPath() . '/Schadensakte/');
                GO::debug($_folder->path());
                $_attachment->saveToFile($_folder);
                GO::debug('Saving attachment ' . $_attachment->name);
            }
        }
    }

    /**
     * @param ImapMessage $message
     * @return Project
     * @throws AccessDenied
     * @throws Exception
     */
    private function _createProject(ImapMessage $message)
    {
        $configuration = GO::config()->mail2project;

        $project = new Project();
        $project->parent_project_id = $configuration['project']['parent_project_id'];
        $project->company_id = null;
        $project->contact_id = null;
        $project->template_id = $configuration['project']['template_id'];
        $project->start_time = time();
        // name
        $project->name = $this->_getProjectName();
        if ($configuration['useCustomNameGenerator']) {
            $project->name = $this->_getProjectName();
        }
        $project->type_id = $configuration['project']['type_id'];
        $project->status_id = $configuration['project']['status_id'];

        $attributes = $this->_parseMessage($message);
        if (empty($attributes['schadennummer'])) {
            $sn = $this->_parseSubject($message);
            if ($sn) {
                $attributes['schadennummer'] = $sn;
            }
        }

        if (!empty($attributes['schadennummer'])) {
            $project->reference_no = $attributes['schadennummer'];
        }

        $contactFound = true;

        if (GO::modules()->customfields) {
            $cfRecord = $project->getCustomfieldsRecord();

            //schadennummer
            if (!empty($attributes['schadennummer']) && isset($configuration['schadennummer'])) {
                $cfRecord->{$configuration['schadennummer']} = $attributes['schadennummer'];
            }

            //vn
            if (!empty($attributes['vn']) && isset($configuration['vn'])) {
                /** @var Contact $vnContact */
                $vnContact = Contact::model()->findSingleByEmail($attributes['vn']);
                if ($vnContact && isset($configuration['vn']['contact'])) {
                    GO::debug('Found VN contact ' . $vnContact->getName());
                    $cfRecord->{$configuration['vn']['contact']} = $vnContact->id . ':' . $vnContact->getName();
                }

                /** @var Company $vnCompany */
                $vnCompany = Company::model()->findSingleByAttribute('email', $attributes['vn']);
                if ($vnCompany && isset($configuration['vn']['company'])) {
                    GO::debug('Found VN company ' . $vnCompany->name);
                    $cfRecord->{$configuration['vn']['company']} = $vnCompany->id . ':' . $vnCompany->name;
                }
                if (!$vnContact && !$vnCompany) {
                    GO::debug('VN contact not found. Creating new');

                    $contactFound = false;

                    $vnContact = new Contact();
                    $vnContact->last_name = $attributes['vn'];
                    $vnContact->email = $attributes['vn'];
                    $vnContact->addressbook_id = $configuration['vn']['defaultAddressbook'];
                    $vnContact->save();

                    $cfRecord->{$configuration['vn']['contact']} = $vnContact->id . ':' . $vnContact->getName();
                    $this->_sendNewContactMessage($vnContact, $configuration['vn']['mailTo']);
                }
            }

            //vu
            if (!empty($attributes['vu']) && isset($configuration['vu'])) {
                /** @var Company $vuCompany */
                $vuCompany = Company::model()->findSingleByAttribute('email', $attributes['vu']);
                if ($vuCompany && isset($configuration['vu']['company'])) {
                    GO::debug('Found VU company ' . $vuCompany->name);
                    $cfRecord->{$configuration['vu']['company']} =  $vuCompany->id . ':' . $vuCompany->name;
                }
                if (!$vuCompany) {
                    GO::debug('VU company not found. Creating new');

                    $contactFound = false;

                    $vuCompany = new Company();
                    $vuCompany->name = $attributes['vu'];
                    $vuCompany->email = $attributes['vu'];
                    $vuCompany->addressbook_id = $configuration['vu']['defaultAddressbook'];
                    $vuCompany->save();

                    $cfRecord->{$configuration['vu']['company']} = $vuCompany->id . ':' . $vuCompany->name;
                    $this->_sendNewCompanyMessage($vuCompany, $configuration['vu']['mailTo']);
                }
            }

            //mak
            if (!empty($attributes['mak']) && isset($configuration['mak'])) {
                /** @var Contact $makContact */
                $makContact = Contact::model()->findSingleByEmail($attributes['mak']);
                if ($makContact && isset($configuration['mak']['contact'])) {
                    $cfRecord->{$configuration['mak']['contact']} = $makContact->id . ':' . $makContact->getName();
                }

                /** @var Company $makCompany */
                $makCompany = Company::model()->findSingleByAttribute('email', $attributes['mak']);
                if ($makCompany && isset($configuration['mak']['company'])) {
                    $cfRecord->{$configuration['mak']['company']} = $makCompany->id . ':' . $makCompany->name;
                }
                if (!$makContact && !$makCompany) {

                    $contactFound = false;

                    $makContact = new Contact();
                    $makContact->last_name = $attributes['mak'];
                    $makContact->email = $attributes['mak'];
                    $makContact->addressbook_id = $configuration['mak']['defaultAddressbook'];
                    $makContact->save();

                    $cfRecord->{$configuration['mak']['contact']} = $makContact->id . ':' . $makContact->getName();
                    $this->_sendNewContactMessage($makContact, $configuration['mak']['mailTo']);
                }
            }

            if (!empty($attributes['sv']) && isset($configuration['sv'])) {
                $cfRecord->{$configuration['sv']} = $attributes['sv'];
            }

            if (!empty($attributes['ast']) && isset($configuration['ast'])) {
                $cfRecord->{$configuration['ast']} = $attributes['ast'];
            }

            if (!empty($configuration['emailDate'])) {
                $cfRecord->{$configuration['emailDate']} = date('Y-m-d', strtotime($message->date));
            }
        }

        if (!empty($attributes['vu']) && !empty($attributes['sv'])) {
            $this->_sendReplyMessage($attributes, $attributes['vu']);
        }

        if (!empty($attributes['mak'])) {
            $this->_sendAttachmentMessage($attributes, $attributes['mak']);
        }
        if (!empty($attributes['vn'])) {
            $this->_sendAttachmentMessage($attributes, $attributes['vn']);
        }
		$project->setCustomFields(["schadennummer" => $attributes['schadennummer']
								,"vn" => $attributes['vn']
								,"vu" => $attributes['vu']
								,"mak" => $attributes['mak']
								,"sv" => $attributes['sv']
								,"ast" => $attributes['ast']]);
        $project->save();

        if ($contactFound) {
            $this->_sendExistingContactMessage($project);
        }

        GO::debug('Project ' . $project->name . ' created');
        return $project;
    }

    /**
     * @return string
     * @throws Exception
     */
    private function _getProjectName()
    {
        $currentYear = date('y');
        $suffix = static::_getSuffix(0);

        $mask = '6-' . $currentYear . '-';
        $project = $this->_tryFindProjectByName($mask);
        if (!$project) {
            $mask = '6-' . ($currentYear - 1) . '-';
            $project = $this->_tryFindProjectByName($mask);
        }

        if ($project) {
            $suffix = str_replace($mask, '', $project->name);
            $suffix = (int)$suffix;
            ++$suffix;
            $suffix = static::_getSuffix($suffix);
        }

        return '6-' . $currentYear . '-' . $suffix;
    }

    /**
     * @param $input
     * @return string
     */
    private static function _getSuffix($input)
    {
        return sprintf('%05u', $input);
    }

    /**
     * @param ImapMessage $message
     * @return bool|mixed
     */
    private function _parseSubject(ImapMessage $message)
    {
        preg_match_all('/Schadennummer:( )?#(.*)#/', $message->subject, $matches);
        if (!count($matches)) {
            return null;
        }

        if(isset($matches[2][0])) {
        	return $matches[2][0];
		}

        return "";
    }

    /**
     * @param ImapMessage $message
     * @return array
     */
    private function _parseMessage(ImapMessage $message)
    {
        $body = $message->getPlainBody();

        $attributes = [
            'schadennummer' => null,
            'vu' => null,
            'vn' => null,
            'mak' => null,
            'sv' => null,
            'ast' => null,
        ];

        preg_match_all('/Schadennummer:( )?#(.*)#/', $body, $matches);
        if (count($matches) && isset($matches[2][0])) {
            $attributes['schadennummer'] = $matches[2][0];
        }

        preg_match_all('/VU:( )?#(.*)#/', $body, $matches);
        if (count($matches) && isset($matches[2][0])) {
            $attributes['vu'] = static::_cleanUpEmail($matches[2][0]);
        }

        preg_match_all('/VN:( )?#(.*)#/', $body, $matches);
        if (count($matches) && isset($matches[2][0])) {
            $attributes['vn'] = static::_cleanUpEmail($matches[2][0]);
        }

        preg_match_all('/MAK:( )?#(.*)#/', $body, $matches);
        if (count($matches) && isset($matches[2][0])) {
            $attributes['mak'] = static::_cleanUpEmail($matches[2][0]);
        }

        preg_match_all('/SV:( )?#(.*)#/', $body, $matches);
        if (count($matches) && isset($matches[2][0])) {
            $attributes['sv'] = trim($matches[2][0]);
        }

        preg_match_all('/AST:( )?#(.*)#/', $body, $matches);
        if (count($matches) && isset($matches[2][0])) {
            $attributes['ast'] = trim($matches[2][0]);
        }

        return $attributes;
    }

    private static function _cleanUpEmail($email)
    {
        $email = trim($email);
        $email = strip_tags($email);

        $urlEncodedWhiteSpaceChars = array('%81','%7F','%C5%8D','%8D','%8F','%C2%90','%C2','%90','%9D','%C2%A0','%A0','%C2%AD','%AD','%08','%09','%0A','%0D');
        $email_address = urlencode($email);
        foreach($urlEncodedWhiteSpaceChars as $v){
            $email_address  = str_replace($v, '', $email_address);
        }
        return urldecode($email_address);
    }

    /**
     * @param $name
     * @return Project
     * @throws Exception
     */
    public function _tryFindProjectByName($name)
    {
        $findParams = FindParams::newInstance();
        $findParams->getCriteria()->addCondition('name', $name . '%', 'LIKE');
        $findParams->order('name', 'DESC');

        return Project::model()->findSingle($findParams);
    }

    /**
     * @param Project $project
     * @return bool
     */
    private function _sendExistingContactMessage(Project $project)
    {
        $template = GO::config()->mail2project['existingContactTpl'];
        if (!$template) {
            return false;
        }

        $template = str_replace('{PROJECT_NAME}', $project->name, $template);

        $subject = GO::config()->mail2project['existingContactSubject'];
        $subject = str_replace('{PROJECT_NAME}', $project->name, $subject);

        $mailTo = GO::config()->mail2project['existingContactMailTo'];

        $message = new SystemMessage();
        $message->setSubject($subject);
        $message->setBody($template);
        $message->addTo($mailTo, $mailTo);
        return $message->send();
    }

    /**
     * @param Contact $contact
     * @param $mailTo
     * @return bool
     */
    private function _sendNewContactMessage(Contact $contact, $mailTo)
    {
        $template = GO::config()->mail2project['newContactTpl'];
        if (!$template) {
            return false;
        }

        $template = str_replace('{CONTACT}', $contact->getName(), $template);
        $template = str_replace('{ADDRESSBOOK}', $contact->addressbook->name, $template);

        $message = new SystemMessage();
        $message->setSubject('New Contact');
        $message->setBody($template);
        $message->addTo($mailTo, $mailTo);
        return $message->send();
    }

    /**
     * @param Company $company
     * @param $mailTo
     * @return bool
     */
    private function _sendNewCompanyMessage(Company $company, $mailTo)
    {
        $template = GO::config()->mail2project['newContactTpl'];
        if (!$template) {
            return false;
        }

        $template = str_replace('{CONTACT}', $company->name, $template);
        $template = str_replace('{ADDRESSBOOK}', $company->addressbook->name, $template);

        $message = new SystemMessage();
        $message->setSubject('New Company');
        $message->setBody($template);
        $message->addTo($mailTo, $mailTo);
        return $message->send();
    }

    /**
     * @param $attributes
     * @param $mailTo
     * @return bool
     */
    private function _sendReplyMessage($attributes, $mailTo)
    {
        $template = GO::config()->mail2project['replyTpl'];
        if (!$template) {
            return false;
        }

        $template = str_replace('{VU}', $attributes['vu'], $template);
        $template = str_replace('{VN}', $attributes['vn'], $template);
        $template = str_replace('{MAK}', $attributes['mak'], $template);
        $template = str_replace('{SV}', $attributes['sv'], $template);
        $template = str_replace('{AST}', $attributes['ast'], $template);
        $template = str_replace('{SCHADENNUMMER}', $attributes['schadennummer'], $template);

        $message = new SystemMessage();
        $message->setSubject($attributes['schadennummer']);
        $message->setBody($template);
        $message->addTo($mailTo, $mailTo);
        return $message->send();
    }

    /**
     * @param $attributes
     * @param $mailTo
     * @return bool
     */
    private function _sendAttachmentMessage($attributes, $mailTo)
    {
        $configuration = GO::config()->mail2project['pdfMail'];
        if (!$configuration) {
            return false;
        }

        if (empty($configuration['tpl'])) {
            return false;
        }

        /** @var File $attachment */
        $attachment = new File(GO::config()->file_storage_path . '/' . $configuration['attachment']);
        if (!$attachment->exists()) {
            return false;
        }

        $template = $configuration['tpl'];

        $message = new SystemMessage();
        $message->setSubject($configuration['subject']);
        $message->setBody($template);
        $message->addTo($mailTo, $mailTo);
        $message->attach(Swift_Attachment::fromPath($attachment->path()));
        return $message->send();
    }
}
