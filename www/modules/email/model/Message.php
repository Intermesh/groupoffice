<?php

/**
 * A message from the imap server
 *
 * @package GO.modules.email
 *
 * @property \GO\Base\Mail\EmailRecipients $to
 * @property \GO\Base\Mail\EmailRecipients $cc
 * @property \GO\Base\Mail\EmailRecipients $bcc
 * @property \GO\Base\Mail\EmailRecipients $from
 * @property \GO\Base\Mail\EmailRecipients $reply_to
 * @property string $subject
 * @property int $uid
 * @property int $size
 * @property string $internal_date Date received
 * @property string $date Date sent
 * @property int $udate Unix time stamp sent
 * @property int $internal_udate Unix time stamp received
 * @property string $x_priority
 * @property string $message_id
 * @property string $content_type
 * @property array $content_typeattributes
 * @property string $disposition_notification_to
 * @property string $content_transfer_encoding
 * @property string $charset
 * @property bool $seen
 * @property bool $flagged
 * @property bool $answered
 * @property bool $forwarded
 * @property Account $account
 * @property String $mailbox
 * @property array $labels
 */

namespace GO\Email\Model;

use GO;


abstract class Message extends \GO\Base\Model {

	protected $attributes = array(
			'to' => '',
			'cc' => '',
			'bcc' => '',
			'from' => '',
			'subject' => '',
			'uid' => '',
			'size' => '',
			'internal_date' => '',
			'date' => '',
			'udate' => '',
			'internal_udate' => '',
			'x_priority' => 3,
			'reply_to' => '',
			'message_id' => '',
			'content_type' => '',
			'content_typeattributes' => array(),
			'disposition_notification_to' => '',
			'content_transfer_encoding' => '',
			'charset' => '',
			'seen' => 0,
			'flagged' => 0,
			'answered' => 0,
			'forwarded' => 0,
			'smime_signed'=>false
	);

	protected $attachments=array();

	protected $defaultCharset='UTF-8';

	/**
	 * True iff the actual message's body is larger than the maximum allowed. See
	 * also how \GO\Base\Mail\Imap::max_read is used.
	 * @var boolean
	 */
	protected $_bodyTruncated;

	public function __construct() {
		$this->attributes['to'] = new \GO\Base\Mail\EmailRecipients($this->attributes['to']);
		$this->attributes['cc'] = new \GO\Base\Mail\EmailRecipients($this->attributes['cc']);
		$this->attributes['bcc'] = new \GO\Base\Mail\EmailRecipients($this->attributes['bcc']);
		$this->attributes['from'] = new \GO\Base\Mail\EmailRecipients($this->attributes['from']);
		$this->attributes['reply_to'] = new \GO\Base\Mail\EmailRecipients($this->attributes['reply_to']);
	}

	/**
	 * PHP getter magic method.
	 * This method is overridden so that AR attributes can be accessed like properties.
	 * @param StringHelper $name property name
	 * @return mixed property value
	 * @see getAttribute
	 */
	public function __get($name) {

		$getter = 'get'.$name;
		if(method_exists($this, $getter))
			return $this->$getter();
		else	if (isset($this->attributes[$name])) {
			return $this->attributes[$name];
		}
	}

	public function __set($name, $value){
		$setter = 'set'.$name;
		if(method_exists($this, $setter))
			return $this->$setter($name, $value);
		else
			$this->attributes[$name]=$value;
	}

	public function __isset($name) {
		$value = $this->__get($name);
		return isset($value);
	}

	public function __unset($name) {
		unset($this->attributes[$name]);
	}

	/**
	 * Returns a static model of itself
	 *
	 * @param String $className
	 * @return ImapMessage
	 */
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public function setAttributes($attributes) {

		$this->attributes = array_merge($this->attributes, $attributes);
		
		$this->attributes['to'] = new \GO\Base\Mail\EmailRecipients(\GO\Base\Util\StringHelper::clean_utf8($this->attributes['to']));
		$this->attributes['cc'] = new \GO\Base\Mail\EmailRecipients(\GO\Base\Util\StringHelper::clean_utf8($this->attributes['cc']));
		$this->attributes['bcc'] = new \GO\Base\Mail\EmailRecipients(\GO\Base\Util\StringHelper::clean_utf8($this->attributes['bcc']));
		$this->attributes['from'] = new \GO\Base\Mail\EmailRecipients(\GO\Base\Util\StringHelper::clean_utf8($this->attributes['from']));

		//workaround for invalid from
		if(!$this->attributes['from']->getAddress()) {
			$this->attributes['from'] = 	new \GO\Base\Mail\EmailRecipients("unknown@unknown.domain");
		}
		$this->attributes['reply_to'] = new \GO\Base\Mail\EmailRecipients(\GO\Base\Util\StringHelper::clean_utf8($this->attributes['reply_to']));

		$this->attributes['x_priority']= isset($this->attributes['x_priority']) ? strtolower($this->attributes['x_priority']) : 3;
		
		switch($this->attributes['x_priority']){
			case 'high':
				$this->attributes['x_priority']=1;
				break;

			case 'low':
				$this->attributes['x_priority']=5;
				break;

			case 'normal':
				$this->attributes['x_priority']=3;
				break;

			default:
				$this->attributes['x_priority']= intval($this->attributes['x_priority']);
				break;
		}
	}

	/**
	 * Get the body in HTML format. If no HTML body was found the text version will
	 * be converted to HTML.
	 *
	 * @return StringHelper
	 */
	abstract public function getHtmlBody();

	/**
	 * Get the body in plain text format. If no plain text body was found the HTML version will
	 * be converted to plain text.
	 *
	 * @return StringHelper
	 */
	abstract public function getPlainBody();

	/**
	 * Return the raw MIME source as string
	 *
	 * @return StringHelper
	 */
	abstract public function getSource();

	/**
	 * Get an array of attachments in this message.
	 *
	 * @return array MessageAttachment
	 *
	 */

	public function &getAttachments() {
		return $this->attachments;
	}

	public function addAttachment(MessageAttachment $a){
		$this->attachments[$a->number]=$a;
	}

	public function isAttachment($number){
		$att = $this->getAttachments();
		return isset($att[$number]);
	}

	/**
	 * Get an attachment by MIME partnumber.
	 * eg. 1.1 or 2
	 *
	 * @param StringHelper $number
	 * @return array See getAttachments
	 */
	public function getAttachment($number){
		$att = $this->getAttachments();
		if(!isset($att[$number]))
			return false;
		else
			return $att[$number];
//			false}else{
//			//throw new \Exception("Attachment number $number not found");
//			return ;
//		}
	}


	protected function extractUuencodedAttachments(&$body)
 {
//		$body = str_replace("\r", '', $body);

		if (($pos = strpos($body, "\nbegin ")) === false)
			return;

//		$regex = "/(begin ([0-7]{3}) (.+))\n/";
		$regex = "/(begin ([0-7]{1,3}) (.+))\n/";
//		$regex = "/(begin ([0-7]+) (.+))\n/";



		if (preg_match_all($regex, $body, $matches, PREG_OFFSET_CAPTURE)) {

//		throw new E$attxception(var_export($matches, true));
//			$body = substr($body, 0, $pos);

			for ($i = 0, $count = count($matches[3]); $i < $count; $i++) {
//			$boundary	= $matches[1][$i];
//			$fileperm	= $matches[2][$i];
				$filename = trim($matches[3][$i][0]);
				$offset = $matches[3][$i][1] + strlen($matches[3][$i][0]) + 1;

				$endpos = strpos($body, 'end', $offset) - $offset - 1;


				if($endpos){

					if(!isset($startPosAtts))
						$startPosAtts= $matches[0][$i][1];

					$att = str_replace(array("\r"), "", substr($body, $offset, $endpos));

					//$size = strlen($matches[4][$i]);

					$file = \GO\Base\Fs\File::tempFile($filename);
					$file->putContents(convert_uudecode($att));
	//			$file->putContents($att);

					$a = MessageAttachment::model()->createFromTempFile($file);
					$a->number = "UU" . $i;
					$this->addAttachment($a);
				}
			}

			$body = substr($body, 0, $startPosAtts);
		}
		\GO::debug($matches);
	}

	private function _convertRecipientArray($r){
		$new = array();
		foreach($r as $email=>$personal)
			$new[]=array('email'=>$email, 'personal'=>(string) $personal);

		return $new;
	}

	public function getZipOfAttachmentsUrl(){
		return '';
	}

	/**
	 * Returns MIME fields contained in this class's instance as an associative
	 * array.
	 *
	 * @param boolean $html Whether or not to return the HTML body. The alternative is
	 * plain text. Defaults to true.
	 *
	 * @return Array
	 */
	public function toOutputArray($html=true, $recipientsAsString=false, $noMaxBodySize=false,$useHtmlSpecialChars=true) {

		$from = $this->from->getAddresses();

		$response['notification'] = $this->disposition_notification_to;

		//seen is expensive because it can't be recovered from cache.
		// We'll use the grid to check if a message was seen or not.
		//$response['seen']=$this->seen;

		$from = $this->from->getAddress();
		
		$response['seen']=$this->seen;		
		$response['forwarded']=$this->forwarded;
		$response['flagged']=$this->flagged;
		$response['answered']=$this->answered;
		
		$response['from'] = $from ? $from['personal'] : "";
		$response['sender'] = $from ? $from['email']: "";
		$response['to'] = $recipientsAsString ? (string) $this->to : $this->_convertRecipientArray($this->to->getAddresses());
		$response['cc'] = $recipientsAsString ? (string) $this->cc : $this->_convertRecipientArray($this->cc->getAddresses());
		$response['bcc'] = $recipientsAsString ? (string) $this->bcc :  $this->_convertRecipientArray($this->bcc->getAddresses());
		$response['reply_to'] = (string) $this->reply_to;
		$response['message_id'] = $this->message_id;
		$response['date'] = $this->date;

		$response['to_string'] = (string) $this->to;

		if (!$recipientsAsString && empty($response['to']))
			$response['to'][] = array('email' => '', 'personal' => \GO::t("Undisclosed recipients", "email"));

		$response['full_from'] = (string) $this->from;
		$response['priority'] = intval($this->x_priority);
		$response['udate'] = $this->udate;
		$response['date'] = \GO\Base\Util\Date::get_timestamp($this->udate);
		$response['size'] = $this->size;

		$labels = array();
		if (property_exists($this, 'account')) {
			$labels = \GO\Email\Model\Label::model()->getAccountLabels($this->account->id);
		}

		$response['labels'] = array();
		if(!empty($this->labels)){
			foreach ($this->labels as $label) {
				if (isset($labels[$label])) {
					$response['labels'][] = array(
						'name' => $labels[$label]->name,
						'color' => $labels[$label]->color
					);
				}
			}
		}

		$response['attachments'] = array();
		$response['zip_of_attachments_url']=$this->getZipOfAttachmentsUrl();

		$response['inlineAttachments'] = array();

		if($html) {
			$response['htmlbody'] = $this->getHtmlBody(false,$noMaxBodySize);
		} else {
			$response['plainbody'] =$this->getPlainBody(false,$noMaxBodySize);
		}

		if($useHtmlSpecialChars){
			$response['subject'] = htmlspecialchars($this->subject,ENT_COMPAT,'UTF-8');
		} else {
			$response['subject'] = $this->subject;
		}


		$response['body_truncated'] = $this->bodyIsTruncated();

		$response['smime_signed'] = isset($this->content_type_attributes['smime-type']) && $this->content_type_attributes['smime-type']=='signed-data';

		$attachments = $this->getAttachments();

		foreach($attachments as $att){
			
			if($html && $att->disposition != 'attachment') {				
				if($att->mime == 'text/html') {
					$htmlPartStr = $att->getData();
					$htmlPartStr = \GO\Base\Util\StringHelper::convertLinks($htmlPartStr);
					$htmlPartStr = \GO\Base\Util\StringHelper::sanitizeHtml($htmlPartStr);

					$response['htmlbody'] .= '<hr />'.$htmlPartStr;
					continue;

				}else if($att->mime == 'text/plain') {
					$htmlPartStr = $att->getData();
					$htmlPartStr = \GO\Base\Util\StringHelper::text_to_html($htmlPartStr);

					$response['htmlbody'] .= '<hr />'.$htmlPartStr;
				}
			}
				
			
			
			$replaceCount = 0;

			$a = $att->getAttributes();

			//add unique token for detecting precense of inline attachment when we submit the message in handleFormInput
			if(isset($a['tmp_file']) && $a['tmp_file']) {
				$a['token']=md5($a['tmp_file']);
			} else {
				// Sometimes $a['tmp_file'] is empty. In the case of multiple attachments, only the last attachment will be available
				$a['token'] = md5($a['content_id']);
			}
			$a['url'] .= '&amp;token='.$a['token'];
			
			if ($html && !empty($a['content_id']))
				$response['htmlbody'] = str_replace('cid:' . $a['content_id'], $a['url'], $response['htmlbody'], $replaceCount);

			if ($a['name'] == 'smime.p7s') {
				$response['smime_signed'] = true;
				continue;
			}

			if(!$replaceCount || $a['disposition'] == 'attachment')
				$response['attachments'][] = $a;
			else
				$response['inlineAttachments'][]=$a;

		}

		
		$response['contact_name']="";			
		$response['contact_thumb_url']=null; //GO::config()->host.'modules/addressbook/themes/Default/images/unknown-person.png';
		
		$response['blocked_images']=0;
		$response['xssDetected']=false;
		
		
		$response['links'] = [];
		
		
		$this->fireEvent('tooutputarray', array(&$response, $this, $html));

		return $response;
	}

	/**
	 * Returns true iff message body has exceeded maximum size.
	 * @return boolean
	 */
	public function bodyIsTruncated() {
		return $this->_bodyTruncated;
	}

}
