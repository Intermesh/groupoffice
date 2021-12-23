<?php

/**
 * @var Domain $domain
 * @property int $domain_id
 * @property string $go_installation_id
 * @property string $username
 * @property string $password
 * @property string $name
 * @property string $maildir
 * @property string $homedir
 * @property int $quota Quota in kilobytes
 * @property int $ctime
 * @property int $mtime
 * @property boolean $active
 * @property int $usage Usage in kilobytes
 */

namespace GO\Postfixadmin\Model;


class Mailbox extends \GO\Base\Db\ActiveRecord {

	/**
	 * Returns a static model of itself
	 * 
	 * @param String $className
	 * @return Mailbox 
	 */
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'pa_mailboxes';
	}

	public function relations() {
		return array(
			'domain' => array('type' => self::BELONGS_TO, 'model' => 'GO\Postfixadmin\Model\Domain', 'field' => 'domain_id')
		);
	}

	protected function init() {
		$this->columns['username']['unique'] = true;
		$this->columns['username']['required'] = true;
		$this->columns['password']['required'] = true;

		return parent::init();
	}
	
	public function getLogMessage($action) {		
		return $this->username;
	}
	
	public $skipPasswordEncryption = false;

	protected function beforeSave() {

		if (!$this->skipPasswordEncryption && $this->isModified("password")) {
			$this->password = '{CRYPT}' . @\crypt($this->password); //disabled depricated error for unsalted crypt
		}
		
		if($this->getIsNew()) {
			$parts = explode('@', $this->username);
			$this->homedir = $this->domain->domain . '/' . $parts[0] . '/';
			$this->maildir = $this->domain->domain . '/' . $parts[0] . '/Maildir/';
		}
		
		return parent::beforeSave();
	}
/* See ticket #201307437
	protected function afterSave($wasNew) {
		if (!empty($wasNew)) {
			// Create alias
			$aliasModel = Alias::model();
			$aliasModel->setAttributes(
							array(
									'goto' => $this->username,
									'domain_id' => $this->domain_id,
									'address' => $this->username,
									'active' => $this->active
							)
			);
			$aliasModel->save();
		}
		return parent::afterSave($wasNew);
	}
*/
//	public function defaultAttributes() {
//		$attr = parent::defaultAttributes();
//		$attr['quota']=$this->domain->default_quota;
//		return $attr;
//	}
	
	public function defaultAttributes() {
		$attr = parent::defaultAttributes();
		$attr['quota']=1024*1024*1;//10 GB of quota per domain by default.
		return $attr;
	}

	public function validate() {


		$this->_checkQuota();
		
		if (!empty($this->domain->max_mailboxes) && $this->isNew && $this->domain->getSumMailboxes() >= $this->domain->max_mailboxes)
						throw new \Exception('The maximum number of mailboxes for this domain has been reached.');

		return parent::validate();
	}
	
	/**
	 * Get the filesystem folder with mail data.
	 * 
	 * @return \GO\Base\Fs\Folder
	 */
	public function getMaildirFolder(){
		return new \GO\Base\Fs\Folder('/home/vmail/'.$this->maildir);
	}
	
	public function cacheUsage(){

		// to prevent lots of "du" calls
		if(!$this->enabled) {
			return true;
		}

		$this->usage = $this->getUsageFromDovecot();
		
		if($this->usage === false) {
			GO::debug("Getting usage from doveadm command failed!");
			$this->usage = $this->getMaildirFolder()->calculateSize()/1024;
		}
		
		return $this->save();
	}
	
	
	private function getUsageFromDovecot() {
		exec("doveadm quota get -u " . escapeshellarg($this->username), $output, $return);
		
		/**
		 * returns:
		 * Quota name Type      Value    Limit                                                                     %
User quota STORAGE 9547844 10240000                                                                    93
User quota MESSAGE   81592        -                                                                     0
		 */
		
		if($return !=0) {
			return false;
		}
	
		if(!isset($output[0])) {
			return false;
		}		
		
		if(!preg_match("/STORAGE +([0-9]*)/", $output[0], $matches)) {
			return false;
		}
		
		return (int) $matches[1];
	}

	private function _checkQuota() {
		$total_quota = $this->domain->total_quota;
		if (!empty($total_quota)) {
			if (empty($this->quota))
				$this->setValidationError('quota', 'You are not allowed to disable mailbox quota');

			if ($this->isNew || $this->isModified("quota")) {

				$existingQuota = $this->isNew ? 0 : $this->getOldAttributeValue("quota");

				$sumUsedQuotaOtherwise = $this->domain->getSumUsedQuota() - $existingQuota; // Domain's used quota w/o the current mailbox's quota.
				if ($sumUsedQuotaOtherwise + $this->quota > $total_quota) {
					$quotaLeft = $total_quota - $sumUsedQuotaOtherwise;
					throw new \Exception('The maximum quota has been reached. You have ' . \GO\Base\Util\Number::localize($quotaLeft / 1024) . 'MB left');
				}
			}
		}
	}

}
