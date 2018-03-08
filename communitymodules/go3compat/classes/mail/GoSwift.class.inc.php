<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: GoSwift.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.mail
 * @uses Swift
 * @since Group-Office 3.0
 */

/**
 * Require all mail classes that are used by this class
 */

require_once($GLOBALS['GO_CONFIG']->class_path."html2text.class.inc");
require_once $GLOBALS['GO_CONFIG']->class_path.'mail/RFC822.class.inc';
require_once $GLOBALS['GO_CONFIG']->class_path.'mail/mimeDecode.class.inc';
require_once $GLOBALS['GO_CONFIG']->class_path.'mail/swift/lib/swift_required.php';
require_once($GLOBALS['GO_CONFIG']->class_path.'mail/swift/lib/classes/Swift/Mime/ContentEncoder/RawContentEncoder.php');
require_once $GLOBALS['GO_CONFIG']->class_path.'mail/smtp_restrict.class.inc.php';

//HOWTO DO THIS WITH 4?
//You change the cache class using this call...
//Swift_CacheFactory::setClassName("Swift_Cache_Disk");

//Then you set up the disk cache to write to a writable folder...
//Swift_Cache_Disk::setSavePath($GLOBALS['GO_CONFIG']->tmpdir);


/**
 * This class can be used to send an e-mail. It extends the 3rd party Swift class.
 * Swift documentation can be found here:
 *
 * {@link http://www.swiftmailer.org/wikidocs/"target="_blank Documentation}
 *
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: GoSwift.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @copyright Copyright Intermesh
 * @license GNU General Public License
 * @package go.mail
 * @uses Swift
 * @since Group-Office 3.0
 */

class GoSwift extends Swift_Mailer{

	/**
	 * The Swift message to send
	 *
	 * @var Swift_Message
	 */
	public $message;


	/**
	 * Array with failed e-mail addresses after send.
	 * @var Array
	 */
	public $failed_recipients = array();

	/**
	 * The raw message data to store in the sent folder or for a linked message
	 *
	 * @var String
	 * @access private
	 */
	private $data;

	/**
	 * When repied to a message it flags the orignal message with ANSWERED
	 *
	 * @var String
	 * @access private
	 */
	private $reply_mailbox;

	/**
	 * When repied to a message it flags the orignal message with ANSWERED
	 *
	 * @var String
	 * @access private
	 */
	private $reply_uid;

	/**
	 * When repied to a message it flags the orignal message with ANSWERED
	 *
	 * @var String
	 * @access private
	 */
	private $draft_uid;


	/**
	 * The account record as an array. see table em_accounts
	 *
	 * @var array
	 */
	public $account;

	private $smtp_host;

	public $log;

	private $subject;


	/**
	 * Constructor. This will create a Swift instance and a Swift message public property.
	 *
	 * @param String $email_to The reciepents in a comma separated string
	 * @param String $subject The subject of the e-mail
	 * @param Int $account_id The account id from the em_accounts table. Used for smtp server and sent items
	 * @param String $priority The priority can be 3 for normal, 1 for high or 5 for low.
	 */
	function __construct($email_to, $subject, $account_id=0, $alias_id=0, $priority = '3', $plain_text_body=null, $transport=null)
	{
		global $GO_CONFIG, $GO_MODULES;

		/*
		 * Make sure temp dir exists
		 */
		File::mkdir($GLOBALS['GO_CONFIG']->tmpdir);


		if($account_id>0 || $alias_id>0)
		{
			require_once ($GLOBALS['GO_MODULES']->modules['email']['class_path']."email.class.inc.php");
			$email = new email();

			$this->account = $email->get_account($account_id, $alias_id);

			//autolinking
			$subject = str_replace('[id:', '['.$this->account['id'].':', $subject);
			$this->subject = $subject;

			

			//end autolinking

			if(empty($transport))
			{
				$encryption = empty($this->account['smtp_encryption']) ? null : $this->account['smtp_encryption'];
				$transport = new Swift_SmtpTransport($this->account['smtp_host'], $this->account['smtp_port'], $encryption);
				if(!empty($this->account['smtp_username']))
				{
					$transport->setUsername($this->account['smtp_username'])
						->setPassword($this->account['smtp_password'])
						;
				}
			}
		}else
		{
			$this->account=false;

			if(empty($transport))
			{
				$encryption = empty($GLOBALS['GO_CONFIG']->smtp_encryption) ? null : $GLOBALS['GO_CONFIG']->smtp_encryption;
				$transport=new Swift_SmtpTransport($GLOBALS['GO_CONFIG']->smtp_server, $GLOBALS['GO_CONFIG']->smtp_port, $encryption);
				if(!empty($GLOBALS['GO_CONFIG']->smtp_username))
				{
					$transport->setUsername($GLOBALS['GO_CONFIG']->smtp_username)
						->setPassword($GLOBALS['GO_CONFIG']->smtp_password);
				}
			}
		}
		
		if(!empty($GLOBALS['GO_CONFIG']->smtp_local_domain))
			$transport->setLocalDomain($GLOBALS['GO_CONFIG']->smtp_local_domain);

		$this->smtp_host=$transport->getHost();
		parent::__construct($transport);

		$this->log = new Swift_Plugins_LoggerPlugin(new Swift_Plugins_Loggers_ArrayLogger());
		$this->registerPlugin($this->log);


		//$this->message =  $pgp ? Swift_Pgp_Message::newInstance($subject, $plain_text_body) :  Swift_Message::newInstance($subject, $plain_text_body);
		$this->message = Swift_Smime_Message::newInstance($subject, $plain_text_body);
		$this->message->setPriority($priority);

		if($this->account)
		{
			$this->message->setFrom(array($this->account['email']=>$this->account['name']));
		}


		$headers = $this->message->getHeaders();

		$headers->addTextHeader("X-Mailer", "Group-Office ".$GLOBALS['GO_CONFIG']->version);
		$headers->addTextHeader("X-MimeOLE", "Produced by Group-Office ".$GLOBALS['GO_CONFIG']->version);

		if(!empty($email_to))
			$this->set_to($email_to);
	}

	function set_to($email_to)
	{
		$RFC822 = new RFC822();
		$to_addresses = $RFC822->parse_address_list($email_to);

		//throw new Exception(var_export($to_addresses, true));

		$recipients=array();
		foreach($to_addresses as $address)
		{
			$recipients[$address['email']]=$address['personal'];
		}

		$this->message->setTo($recipients);
	}

	function &get_message(){
		return $this->message;
	}

	/**
	 * Sets the message body
	 *
	 * @param String $body The message body in HTML or text
	 * @param String $type Can be html or text
	 */



	function set_body($body,$type='html', $add_text_version=true)
	{
		global $GO_CONFIG;

		if($type=='html')
		{
			//replace URL's with anchor tags
			//$body = preg_replace('/[\s\n;]{1}http(s?):\/\/([^\b<\n]*)/', "<a href=\"http$1://$2\">http$1://$2</a>", $body);
		}
		//add body
		$this->message->setBody($body, 'text/'.$type);

		if($type=='html' && $add_text_version)
		{
			
			//add text version of the HTML body
			$htmlToText = new Html2Text ($body);

			if(isset($this->text_part_body)){
				//the body was already set so find the text version and replace it.
				$children = (array) $this->message->getChildren();
				foreach($children as $child){
					
					if($child->getBody()==$this->text_part_body){
						go_debug('Replaced');
						$this->text_part_body = $htmlToText->get_text();
						$child->setBody($this->text_part_body);
						break;
					}					
				}
				//$this->text_body->setBody($htmlToText->get_text());
			}else
			{
				$this->text_part_body =$htmlToText->get_text();
				$this->message->addPart($this->text_part_body, 'text/plain','UTF-8');
			}
		}
	}

	/**
	 * If this message is a reply to another message then you must supply the UID and the mailbox
	 * of the original message. The account id must be passed to the constructor for this to work.
	 *
	 * @param String $reply_uid
	 * @param String $reply_mailbox
	 */

	function set_reply_to($reply_uid, $reply_mailbox, $in_reply_to='')
	{
		//$this->in_reply_to=$in_reply_to;
		$this->reply_uid=$reply_uid;
		$this->reply_mailbox=$reply_mailbox;

		if(!empty($in_reply_to)){
			$headers = $this->message->getHeaders();
			$headers->addTextHeader('In-Reply-To', $in_reply_to);
			$headers->addTextHeader('References', $in_reply_to);
		}
	}

	function set_forward_uid($forward_uid, $forward_mailbox)
	{
		$this->forward_uid=$forward_uid;
		$this->forward_mailbox=$forward_mailbox;
	}

	function set_draft($draft_uid)
	{
		$this->draft_uid=$draft_uid;
	}

	function set_from($email_from,$name_from)
	{
		$this->message->setFrom(array($email_from=>$name_from));
	}

	/**
	 * Sends the email.
	 *
	 * @param String $email_from The from e-mail address. If you don't supply this then you must supply the account_id to the constructor
	 * @param String $name_from The from name. If you don't supply this then you must supply the account_id to the constructor
	 * @param boolean $batch If you set this to true it will use the Swift batchSend method. See the swift docs.
	 * @throws Swift_ConnectionException If sending fails for any reason.
	 * @return int The number of successful recipients
	 */


	function sendmail($batch=false, $dont_save_in_sent_items=false)
	{
		$smtp_restrict = new smtp_restrict();

		if(!$smtp_restrict->is_allowed($this->smtp_host))
		{
			global $lang;
			$msg = sprintf($lang['common']['max_emails_reached'], $this->smtp_host, $smtp_restrict->hosts[gethostbyname($this->smtp_host)]);
			throw new Exception($msg);
		}

		$this->failed_recipients=array();
		
		if($batch)
		{
			//$send_success = parent::batchSend($this->message,$this->recipients, new Swift_Address($email_from, $name_from));

			/*$this->batch =& new Swift_BatchMailer($this);
			$this->batch->setSleepTime(10);
			$this->batch->setMaxTries(2);
			$this->batch->setMaxSuccessiveFailures(10);
			$send_success=$this->batch->send($this->message, $this->recipients, new Swift_Address($email_from, $name_from));*/

			$send_success = parent::batchSend($this->message,$this->failed_recipients);

		}else
		{
			$send_success = parent::send($this->message,$this->failed_recipients);
		}

		if(!$send_success){
			$log_str = $this->log->dump();

			$error = preg_match('/<< 550.*>>/s', $log_str,$matches);

			if(isset($matches[0])){
				$log_str=trim(substr($matches[0],2,-2));
			}
			throw new Exception($log_str);
		}

		if(!$dont_save_in_sent_items && $send_success && $this->account && $this->account['type']=='imap' && !empty($this->account['sent']))
		{
			global $GO_CONFIG, $GO_MODULES;

			require_once ($GLOBALS['GO_MODULES']->modules['email']['class_path']."cached_imap.class.inc.php");
			$imap = new cached_imap();

			$mailbox = empty($this->reply_mailbox) ? 'INBOX' : $this->reply_mailbox;

			if ($imap->open($this->account,$mailbox)) {

				$this->data=$this->message->toString();

				$appended = $imap->append_message($this->account['sent'], $this->data,"\Seen");
				if ($appended)
				{
					if (!empty($this->reply_uid) && !empty($this->reply_mailbox))
					{
						$flag="\Answered";
						$flag_uid=$this->reply_uid;
						$flag_mailbox=$this->reply_mailbox;
					}elseif(!empty($this->forward_uid) && !empty($this->forward_mailbox)){
						$flag='$Forwarded';
						$flag_uid=$this->forward_uid;
						$flag_mailbox=$this->forward_mailbox;
					}

					if(isset($flag)){
						$uid_arr = array($flag_uid);
						$imap->select_mailbox($flag_mailbox);
						$imap->set_message_flag($uid_arr, $flag);

						$folder = $imap->email->get_folder($this->account['id'],$flag_mailbox);

						$cached_message['folder_id']=$folder['id'];
						$cached_message['uid']=$flag_uid;
						$cached_message[strtolower(substr($flag,1))]='1';
						$imap->update_cached_message($cached_message);
					}

					if(!empty($this->draft_uid))
					{
						$imap->select_mailbox($this->account['drafts']);
						$imap->delete(array($this->draft_uid));
					}

					$imap->disconnect();
				}
			}
		}

		//auto link with tag in subject
		preg_match('/\[([0-9]+):([0-9]+):([0-9]+)\]/', $this->subject,$matches);

		if(isset($matches[1]) && $matches[1]==$this->account['id']){
			$this->link_to(array(array("link_id"=>$matches[3],"link_type"=>$matches[2])));
		}

		return $send_success;
	}

	function implodeSwiftAddressArray($swiftArr)
	{
		$fromArr=array();
		foreach($swiftArr as $address=>$personal)
		{
			if(empty($personal))
			{
				$fromArr[]=$address;
			}else
			{
				$fromArr[]=RFC822::write_address($address, $personal);
			}
		}

		return implode(',',$fromArr);
	}

	/**
	 * Links the message to items in Group-Office. Must be called after send()
	 *
	 * @param array $links Format Array(Array(link_id=>1, link_type=>1));
	 * @return void
	 */
	function link_to($model_name, $model_id)
	{
		global $GO_CONFIG;

		require_once($GLOBALS['GO_CONFIG']->class_path.'base/links.class.inc.php');
		$GO_LINKS = new GO_LINKS();


		$link_message['path']='email/'.date('mY').'/sent_'.time().'.eml';

		require_once($GLOBALS['GO_CONFIG']->class_path.'filesystem.class.inc');
		$fs = new filesystem();
		$fs->mkdir_recursive($GLOBALS['GO_CONFIG']->file_storage_path.dirname($link_message['path']));

		if(empty($this->data))
		{
			$this->data = $this->message->toString();
		}

		$fp = fopen($GLOBALS['GO_CONFIG']->file_storage_path.$link_message['path'],"w+");
		fputs ($fp, $this->data, strlen($this->data));
		fclose($fp);

		$email = new email();

//		require_once($GLOBALS['GO_CONFIG']->class_path.'base/search.class.inc.php');
//		$search = new search();
//
//		$link_message['from']=$this->implodeSwiftAddressArray($this->message->getFrom());
//		$link_message['to']=$this->implodeSwiftAddressArray($this->message->getTo());
//		$link_message['subject']=$this->message->getSubject();
//		$link_message['ctime']=$link_message['time']=time();
		
		require_once($GO_CONFIG->root_path.'GO.php');
		
		$model = GO::getModel($model_name)->findByPk($model_id);
		
		$linkedEmail = new GO_Savemailas_Model_LinkedEmail();
		$linkedEmail->from = $this->implodeSwiftAddressArray($this->message->getFrom());
		$linkedEmail->to = $this->implodeSwiftAddressArray($this->message->getTo());
		$linkedEmail->subject = $this->message->getSubject();
		$linkedEmail->acl_id = $model->findAclId();
		$linkedEmail->path=$link_message['path'];
		$linkedEmail->save();			


		
		$linkedEmail->link($model);

		//$email->link_message($link_message, 0, $links, '');	
	}
}


class GoSwiftImport extends GoSwift{

	var $body='';

	public function __construct($mime, $add_body=true, $alias_id=0, $transport=null)
	{
		$RFC822 = new RFC822();

		$params['include_bodies'] = true;
		$params['decode_bodies'] = true;
		$params['decode_headers'] = true;
		$params['input'] = $mime;

		$structure = Mail_mimeDecode::decode($params);

		$subject = isset($structure->headers['subject']) ? $structure->headers['subject'] : '';

		if(isset($structure->headers['disposition-notification-to']))
		{
			//$mail->ConfirmReadingTo = $structure->headers['disposition-notification-to'];
		}

		$to = isset($structure->headers['to']) && strpos($structure->headers['to'],'undisclosed')===false ? $structure->headers['to'] : '';
		$cc = isset($structure->headers['cc']) && strpos($structure->headers['cc'],'undisclosed')===false ? $structure->headers['cc'] : '';
		$bcc = isset($structure->headers['bcc']) && strpos($structure->headers['bcc'],'undisclosed')===false ? $structure->headers['bcc'] : '';


		parent::__construct($to, $subject,0,$alias_id,'3',null, $transport);
		
		
		$RFC822 = new RFC822();
		$cc_addresses = $RFC822->parse_address_list($cc);
		$recipients=array();
		foreach($cc_addresses as $address)
		{
			$recipients[$address['email']]=$address['personal'];
		}

		$this->message->setCc($recipients);
		
		
		$bcc_addresses = $RFC822->parse_address_list($bcc);
		$recipients=array();
		foreach($bcc_addresses as $address)
		{
			$recipients[$address['email']]=$address['personal'];
		}

		$this->message->setBcc($recipients);


		if(empty($alias_id) && isset($structure->headers['from']) )
		{
			$addresses=$RFC822->parse_address_list($structure->headers['from']);
			if(isset($addresses[0]))
			{
				$this->set_from($addresses[0]['email'], $addresses[0]['personal']);
			}
		}

		//go_debug($structure);

		$this->replaceIds=array();
		$this->get_parts($structure);
		
		foreach($this->replaceIds as $oldId=>$newId)
			$this->body=str_replace($oldId, $newId, $this->body);

		if($add_body)
			$this->set_body($this->body);

	}

	private function has_html_part($structure){
		if(isset($structure->parts)){
			foreach($structure->parts as $part){
				go_debug($part->ctype_primary.'/'.$part->ctype_secondary);
				if($part->ctype_primary == 'text' && $part->ctype_secondary=='html')
					return true;
				else if($this->has_html_part($part)){
					return true;
				}
			}
		}
		return false;
	}


	private function get_parts($structure, $part_number_prefix='')
	{
		global $GO_CONFIG, $GO_MODULES;

		if (isset($structure->parts))
		{
			//$part_number=0;
			foreach ($structure->parts as $part_number=>$part) {

				//text part and no attachment so it must be the body
				if($structure->ctype_primary=='multipart' && $structure->ctype_secondary=='alternative' &&
				$part->ctype_primary == 'text' && $part->ctype_secondary=='plain')
				{
					//check if html part is there					
					if($this->has_html_part($structure)){						
						continue;
					}
				}


				if ($part->ctype_primary == 'text' && ($part->ctype_secondary=='plain' || $part->ctype_secondary=='html') && (!isset($part->disposition) || $part->disposition != 'attachment') && empty($part->d_parameters['filename']))
				{
					if (stripos($part->ctype_secondary,'plain')!==false)
					{
						$content_part = nl2br($part->body);
					}else
					{
						$content_part = $part->body;
					}
					$this->body .= $content_part;
				}elseif($part->ctype_primary=='multipart')
				{

				}else
				{
					//attachment

					$dir=$GLOBALS['GO_CONFIG']->tmpdir.'attachments/';

					if(!is_dir($dir))
						mkdir($dir, 0755, true);

					//unset($part->body);
					//var_dump($part);
					//exit();

					if(!empty($part->ctype_parameters['name']))
					{
						$filename = $part->ctype_parameters['name'];
					}elseif(!empty($part->d_parameters['filename']) )
					{
						$filename = $part->d_parameters['filename'];
					}elseif(!empty($part->d_parameters['filename*']))
					{
						$filename=$part->d_parameters['filename*'];
					}else
					{
						$filename=uniqid(time());
					}

					$tmp_file = $dir.$filename;
					file_put_contents($tmp_file, $part->body);

					$mime_type = $part->ctype_primary.'/'.$part->ctype_secondary;

					if(isset($part->headers['content-id']))
					{
						$content_id=trim($part->headers['content-id']);
						if (strpos($content_id,'>'))
						{
							$content_id = substr($part->headers['content-id'], 1,strlen($part->headers['content-id'])-2);
						}
						$img = Swift_EmbeddedFile::fromPath($tmp_file);
						$img->setContentType(File::get_mime($mime_type));
						try{
							$img->setId($content_id);
						}
						catch(Swift_RfcComplianceException $e){
							
						}
						$id = $this->message->embed($img);
						
						if($id != $content_id){
							$this->replaceIds[$content_id]=$id;
						}
					}else
					{
					//echo $tmp_file."\n";
						$attachment = Swift_Attachment::fromPath($tmp_file,File::get_mime($tmp_file));
						$this->message->attach($attachment);
					}
				}

				//$part_number++;
				if(isset($part->parts))
				{
					$this->get_parts($part, $part_number_prefix.$part_number.'.');
				}

			}
		}elseif(isset($structure->body))
		{
			//convert text to html
			if (stripos( $structure->ctype_secondary,'plain')!==false)
			{
				$text_part = nl2br($structure->body);
			}else
			{
				$text_part = $structure->body;
			}
			$this->body .= $text_part;
		}
	}
}
