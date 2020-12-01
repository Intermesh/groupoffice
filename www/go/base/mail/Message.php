<?php
/*
 * Copyright Intermesh
 * 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 * 
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */

namespace GO\Base\Mail;

use GO;
use GO\Base\Fs\Folder;

use Exception;
use go\core\ErrorHandler;
use go\core\fs\Blob;


//make sure temp dir exists
$cacheFolder = new Folder(GO::config()->tmpdir);
$cacheFolder->create();

/**
 * This class is used to parse and write RFC822 compliant recipient lists
 * 
 * @package GO.base.mail
 * @version $Id: RFC822.class.inc 7536 2011-05-31 08:37:36Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @copyright Copyright Intermesh BV.
 */




class Message extends \Swift_Message{
	
	private $_loadedBody;
	
	/**
	 * The path in where the temporary attachments are stored
	 * 
	 * @var boolean/string 
	 */
	private $_tmpDir = false;
	
	public function __construct($subject = null, $body = null, $contentType = null, $charset = null) {
		parent::__construct($subject, $body, $contentType, $charset);

    $headers = $this->getHeaders();
    $headers->addTextHeader("X-Group-Office-Title", go()->getSettings()->title);

		// See Mailer.php at line 105 for header encoding
		if(GO::config()->swift_email_body_force_to_base64) {
			//Override qupted-prinatble encdoding with base64 because it uses much less memory on larger bodies. See also:
			//https://github.com/swiftmailer/swiftmailer/issues/356
			$this->setEncoder(new \Swift_Mime_ContentEncoder_Base64ContentEncoder());
		}
	}
	
	public function setBody($body, $contentType = null, $charset = null) {
		
		$this->switchEncoder($body);		
		
		return parent::setBody($body, $contentType, $charset);
	}
	
	private function switchEncoder($body) {
		
		if(GO::config()->swift_email_body_force_to_base64) {
			return;
		}
		
		if(strlen($body) * 8 > 200 * 1024) {
			//Override quoted-prinatble encdoding with base64 because it uses much less memory on larger bodies. See also:
			//https://github.com/swiftmailer/swiftmailer/issues/356
			$this->setEncoder(new \Swift_Mime_ContentEncoder_Base64ContentEncoder());
		}
	}
	
	public function addPart($body, $contentType = null, $charset = null) {
		
		$this->switchEncoder($body);
		
		return parent::addPart($body, $contentType, $charset);		
	}
	
	/**
	 * Get the tmp directory in where the temporary attachments are stored
	 * 
	 * @return StringHelper The path to the tmp directory
	 */
	public function getTmpDir(){
		return $this->_tmpDir;
	}
	
	/**
   * Create a new Message.
   * @param StringHelper $subject
   * @param StringHelper $body
   * @param StringHelper $contentType
   * @param StringHelper $charset
   * @return Message
   */
  public static function newInstance($subject = null, $body = null,
    $contentType = null, $charset = null)
  {
    return new self($subject, $body, $contentType, $charset);
  }
	
	/**
	 * Load the message by mime data
	 * 
	 * @param String $mimeData
	 * @param array/string $replaceCallback A function that will be called with the body so you can replace tags in the body.
	 */
	public function loadMimeMessage($mimeData, $loadDate=false, $replaceCallback=false, $replaceCallbackArgs=array()){
		
		$decoder = new MimeDecode($mimeData);
		$structure = $decoder->decode(array(
				'include_bodies'=>true,
				'decode_headers'=>true,
				'decode_bodies'=>true,
		));
		
		if(!$structure)
			throw new \Exception("Could not decode mime data:\n\n $mimeData");

		if(!empty($structure->headers['subject'])){
			$this->setSubject($structure->headers['subject']);
		}
		
		if(isset($structure->headers['disposition-notification-to']))
		{
			//$mail->ConfirmReadingTo = $structure->headers['disposition-notification-to'];
		}
		
		
		//fix for [20150125 05:43:24] PHP Warning: strpos() expects parameter 1 to be string, array given in /usr/share/groupoffice/go/base/mail/Message.php on line 105
		if(isset($structure->headers['to']) && is_array($structure->headers['to'])){
			$structure->headers['to'] = implode(',', $structure->headers['to']);
		}
		
		if(isset($structure->headers['cc']) && is_array($structure->headers['cc'])){
			$structure->headers['cc'] = implode(',', $structure->headers['cc']);
		}
		
		if(isset($structure->headers['bcc']) && is_array($structure->headers['bcc'])){
			$structure->headers['bcc'] = implode(',', $structure->headers['bcc']);
		}

		$to = isset($structure->headers['to']) && strpos($structure->headers['to'],'undisclosed')===false ? $structure->headers['to'] : '';
		$cc = isset($structure->headers['cc']) && strpos($structure->headers['cc'],'undisclosed')===false ? $structure->headers['cc'] : '';
		$bcc = isset($structure->headers['bcc']) && strpos($structure->headers['bcc'],'undisclosed')===false ? $structure->headers['bcc'] : '';
		
		//workaround activesync problem where 'mailto:' is included in the mail address.		
		$to = str_replace('mailto:','', $to);
		$cc = str_replace('mailto:','', $cc);
		$bcc = str_replace('mailto:','', $bcc);
	
		$toList = new EmailRecipients($to);
		$to =$toList->getAddresses();
		foreach($to as $email=>$personal){
			try{
				$this->addTo($email, $personal);
			} catch (Exception $e){
				trigger_error('Failed to add receipient address: '.$e);
			}
		}
		
		$ccList = new EmailRecipients($cc);
		$cc =$ccList->getAddresses();
		foreach($cc as $email=>$personal){
			try{
				$this->addCc($email, $personal);
			} catch (Exception $e){
				trigger_error('Failed to add CC address: '.$e);
			}
		}
		
		$bccList = new EmailRecipients($bcc);
		$bcc =$bccList->getAddresses();
		foreach($bcc as $email=>$personal){
			try{
				$this->addBcc($email, $personal);
			} catch (Exception $e){
				trigger_error('Failed to add BCC address: '.$e);
			}
		}

		if(isset($structure->headers['from'])){
			
			$fromList = new EmailRecipients(str_replace('mailto:','',$structure->headers['from']));
			$from =$fromList->getAddress();
		
			if($from){
				try {
					$this->setFrom($from['email'], $from['personal']);
				} catch(Exception $e)  {
					\GO::debug('Failed to add from address: '.$e);
				}
			}
		}
		
		$this->_getParts($structure);
		
		if($replaceCallback){			
				
			
			$bodyStart = strpos($this->_loadedBody, '<body');
			
			if($bodyStart){		  
			
			  $body = substr($this->_loadedBody, $bodyStart);
			  array_unshift($replaceCallbackArgs, $body);
			  $body = call_user_func_array($replaceCallback, $replaceCallbackArgs);
			  
			  $this->_loadedBody = substr($this->_loadedBody,0,$bodyStart).$body;
			}else{
			  array_unshift($replaceCallbackArgs, $this->_loadedBody);
			  $this->_loadedBody = call_user_func_array($replaceCallback, $replaceCallbackArgs);
			}
		}
		
		if($loadDate){
			$date=isset($structure->headers['date']) ? preg_replace('/\([^\)]*\)/','', $structure->headers['date']) : date('c');
			try {
				$udate = new \DateTime($date);
			} catch(\Exception $e) {
				ErrorHandler::logException($e);
				$udate = new \DateTime();
			}

			$this->setDate($udate);
		}
		
		
		
		$this->setHtmlAlternateBody($this->_loadedBody);
		
		return $this;
	}
	
	/**
	 * Set the HTML body and automatically create an alternate text body
	 * 
	 * @param String $htmlBody 
	 * @return Message
	 */
	public function setHtmlAlternateBody($htmlBody){
	
		//add body
		$htmlBody = \GO\Base\Util\StringHelper::normalizeCrlf($htmlBody);
		$htmlBody = str_replace("\r\n\r\n", "\r\n", $htmlBody);
		
		$this->setBody($htmlBody, 'text/html','UTF-8');
			
		//add text version of the HTML body
		$htmlToText = new \GO\Base\Util\Html2Text($htmlBody);
		$part= $this->addPart($htmlToText->get_text(), 'text/plain','UTF-8');
		
		
		//Override qupted-prinatble encdoding with base64 because it uses much less memory on larger bodies. See also:
		//https://github.com/swiftmailer/swiftmailer/issues/356
		if(GO::config()->swift_email_body_force_to_base64) {
			$part->setEncoder(new \Swift_Mime_ContentEncoder_Base64ContentEncoder());
		}
			
//	Was testing this but didn't seem to work		
//			$plainTextPart = $this->findPlainTextBody();
//		if(!$plainTextPart) {
//			$part= $this->addPart($htmlToText->get_text(), 'text/plain','UTF-8');
//			//Override qupted-prinatble encdoding with base64 because it uses much less memory on larger bodies. See also:
//			//https://github.com/swiftmailer/swiftmailer/issues/356
//			$part->setEncoder(new \Swift_Mime_ContentEncoder_Base64ContentEncoder());
//		}else
//		{
//			$plainTextPart->setBody($htmlToText->get_text());
//		}
		
		return $this;
	}
	
	/**
	 * 
	 * @return \Swift_MimePart
	 */
	public function findPlainTextBody(){
		
		//the body was already set so find the text version and replace it.
		$children = (array) $this->getChildren();
		foreach($children as $child){

			if($child->getContentType()=='text/plain'){
				return $child;
			}					
		}
		return false;
	}
	
	/**
	 * Try to convert the encoding of the email to UTF-8
	 * 
	 * @param  stdClass $part
	 */
	private function _convertEncoding(&$part){
		$charset='UTF-8';
					
		if(isset($part->ctype_parameters['charset'])){
			$charset = strtoupper($part->ctype_parameters['charset']);
		}

		if($charset!='UTF-8'){
			$part->body = \GO\Base\Util\StringHelper::to_utf8($part->body, $charset);
			
			$part->body = str_ireplace($charset, 'UTF-8', $part->body);
			
		}
	}
	
	private function _getParts($structure, $part_number_prefix='')
	{
		if (isset($structure->parts))
		{
			//$part_number=0;
			foreach ($structure->parts as $part_number=>$part) {

				//text part and no attachment so it must be the body
				if($structure->ctype_primary=='multipart' && $structure->ctype_secondary=='alternative' &&
				$part->ctype_primary == 'text' && $part->ctype_secondary=='plain')
				{
					//check if html part is there					
					if($this->_hasHtmlPart($structure)){						
						continue;
					}
				}

				
				if ($part->ctype_primary == 'text' && ($part->ctype_secondary=='plain' || $part->ctype_secondary=='html') && (!isset($part->disposition) || $part->disposition != 'attachment') && empty($part->d_parameters['filename']))
				{
					$this->_convertEncoding($part);
					
					if (stripos($part->ctype_secondary,'plain')!==false)
					{
						$content_part = nl2br($part->body);
					}else
					{
						$content_part = $part->body;
					}
					$this->_loadedBody .= $content_part;
				}elseif($part->ctype_primary=='multipart')
				{

				}elseif(isset($part->body))
				{
					//attachment
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

					if(!isset($part->ctype_primary)) {
            $part->ctype_primary = 'text';
          }
          if(!isset($part->ctype_secondary)) {
            $part->ctype_secondary = 'plain';
          }

					$mime_type = $part->ctype_primary.'/'.$part->ctype_secondary;

          //only embed if we can find the content-id in the body
					if(isset($part->headers['content-id']) && ($content_id=trim($part->headers['content-id'],' <>')) && strpos($this->_loadedBody, $content_id) !== false)
					{
						$img = new \Swift_EmbeddedFile($part->body, $filename, $mime_type);
						$img->setContentType($mime_type);
						
						//Only set valid ID's. Iphone sends invalid content ID's sometimes.
						if (preg_match('/^.+@.+$/D',$content_id))
						{
							$img->setId($content_id);
						}
						$this->embed($img);
					}else
					{
						$attachment = new \Swift_Attachment($part->body, $filename,$mime_type);
						$this->attach($attachment);
					}
				}

				//$part_number++;
				if(isset($part->parts))
				{
					$this->_getParts($part, $part_number_prefix.$part_number.'.');
				}

			}
		}elseif(isset($structure->body))
		{
			
			$this->_convertEncoding($structure);
			//convert text to html
			if (stripos( $structure->ctype_secondary,'plain')!==false)
			{
				$text_part = nl2br($structure->body);
			}else
			{
				$text_part = $structure->body;
			}
			$this->_loadedBody .= $text_part;
		}
	}
	
	private function _hasHtmlPart($structure){
		if(isset($structure->parts)){
			foreach($structure->parts as $part){
				if($part->ctype_primary == 'text' && $part->ctype_secondary=='html')
					return true;
				else if($this->_hasHtmlPart($part)){
					return true;
				}
			}
		}
		return false;
	}
	
	/**
	 * Sometimes the browser changes absolute URL's into relative URL's when using
	 * wysiwyg html editors.
	 * 
	 * In outgoing messages we don't want them so we make them absolute again.
	 * 
	 * @param StringHelper $body
	 * @return type 
	 */
	private function _fixRelativeUrls($body){		
		return str_replace('href="?r=','href="'.\GO::config()->full_url, $body);
	}
	
	private function _embedPastedImages($body){
		$regex = '/src="data:image\/([^;]+);([^,]+),([^"]+)/';
			
		preg_match_all($regex, $body, $allMatches,PREG_SET_ORDER);
		foreach($allMatches as $matches){
			if($matches[2]=='base64'){
				$extension = $matches[1];
				$img = new \Swift_EmbeddedFile(base64_decode($matches[3]), uniqid() . '.'. $extension);
				$contentId = $this->embed($img);

				$body = str_replace($matches[0],'src="'.$contentId, $body);
			}
		}
		
		$blobIds = \go\core\fs\Blob::parseFromHtml($body);
		foreach($blobIds as $blobId) {
			$blob = \go\core\fs\Blob::findById($blobId);
			
			if($blob) {
				$img = \Swift_EmbeddedFile::fromPath($blob->getFile()->getPath());
				$img->setContentType($blob->type);
				$img->setFilename($blob->name);
				$contentId = $this->embed($img);
				$body = \go\core\fs\Blob::replaceSrcInHtml($body, $blobId, $contentId);
			}
		}
		
		return $body;
	}
	
	/**
	 * Check if either to,cc and bcc has at least one recipient.
	 * 
	 * @return boolean 
	 */
	public function hasRecipients(){
		return $this->countRecipients() > 0;
	}
	
	public function countRecipients(){
		return count($this->getTo() ?? []) + count($this->getCc() ?? []) + count($this->getBcc() ?? []);
	}
	
	/**
	 * handleEmailFormInput
	 * 

	 * This method can be used in Models and Controllers. It puts the email body
	 * and inline (image) attachments from the client in the message, which can
	 * then be used for storage in the database or sending emails.
	 * 
	 * @param Array $params Must contain elements: body (string) and
	 * 
	 * inlineAttachments (string).
	 */
	public function handleEmailFormInput($params){
		
		if(!empty($params['subject']))
			$this->setSubject($params['subject']);		
		
		if(!empty($params['to'])){		
			$to = new EmailRecipients($params['to']);
			foreach($to->getAddresses() as $email=>$personal)
				$this->addTo($email,$personal);
		}
		if(!empty($params['cc'])){		
			$cc = new EmailRecipients($params['cc']);
			foreach($cc->getAddresses() as $email=>$personal)
				$this->addCc($email,$personal);
		}
		if(!empty($params['bcc'])){		
			$bcc = new EmailRecipients($params['bcc']);
			foreach($bcc->getAddresses() as $email=>$personal)
				$this->addBcc($email,$personal);
		}
		
		if(isset($params['alias_id'])){
			$alias = \GO\Email\Model\Alias::model()->findByPk($params['alias_id']);	
			$this->setFrom($alias->email, $alias->name);
			
			if(!empty($params['notification']))
				$this->setReadReceiptTo(array($alias->email=>$alias->name));
		}
		
		if(isset($params['priority']) && $params['priority'] != 3)
			$this->setPriority ($params['priority']);
		
		
		if(isset($params['in_reply_to'])){
			$headers = $this->getHeaders();
			$headers->addTextHeader('In-Reply-To', $params['in_reply_to']);
			$headers->addTextHeader('References', $params['in_reply_to']);
		}	

		if($params['content_type']=='html'){
			
						
			$params['htmlbody'] = $this->_embedPastedImages($params['htmlbody']);
			
			//inlineAttachments is an array(array('url'=>'',tmp_file=>'relative/path/');
			if(!empty($params['inlineAttachments'])){
				$inlineAttachments = json_decode($params['inlineAttachments']);

				/* inline attachments must of course exist as a file, and also be used in
				 * the message body
				 */
				 if(count($inlineAttachments)){
					foreach ($inlineAttachments as $ia) {

						//$tmpFile = new \GO\Base\Fs\File(\GO::config()->tmpdir.$ia['tmp_file']);
						if(empty($ia->tmp_file)){
							continue; // Continue to the next inline attachment for processing.
							//throw new Exception("No temp file for inline attachment ".$ia->name);
						}
						if(in_array(substr($ia->tmp_file,0,14), ['saved_messages', 'imap_messages/'])) {
							$path = \GO::config()->tmpdir.$ia->tmp_file;
						} else {
							$path = empty($ia->from_file_storage) ? Blob::buildPath($ia->tmp_file) : \GO::config()->file_storage_path . $ia->tmp_file;
						}
						$tmpFile = new \GO\Base\Fs\File($path);

						if (!$tmpFile->exists()) {
							throw new \Exception("Error: inline attachment missing on server: ".$tmpFile->stripTempPath().
								".<br /><br />The temporary files folder is cleared on each login. Did you relogin?");
						}
						//Different browsers reformat URL's to absolute or relative. So a pattern match on the filename.
						//$filename = rawurlencode($tmpFile->name());
						$result = preg_match('/="([^"]*'.preg_quote($ia->token).'[^"]*)"/',$params['htmlbody'],$matches);
						if($result){
							$img = \Swift_EmbeddedFile::fromPath($tmpFile->path());
							$img->setContentType($tmpFile->mimeType());
							$contentId = $this->embed($img);

							//$tmpFile->delete();
							$params['htmlbody'] = \GO\Base\Util\StringHelper::replaceOnce($matches[1], $contentId, $params['htmlbody']);
						}
					}
				}
			}
			$params['htmlbody']=$this->_fixRelativeUrls($params['htmlbody']);
						
			$htmlTop = '<html>
<head>
<style type="text/css">
body,p,td,div,span{
	'.\GO::config()->html_editor_font.'
};
body p{
	margin:0px;
}
</style>
</head>
<body>';
			
			$htmlBottom = '</body></html>';
			
			$this->setHtmlAlternateBody($htmlTop.$params['htmlbody'].$htmlBottom);
		}else
		{
			$this->setBody($params['plainbody'], 'text/plain');
		}		
		
		if (!empty($params['attachments'])) {
			$attachments = json_decode($params['attachments']);
			foreach ($attachments as $att) {
				if(!empty($att->blobId)) {
					$path = Blob::buildPath($att->blobId);
				}else	if(!empty($att->tmp_file) && empty($att->from_file_storage)){
					$path = \GO::config()->tmpdir.$att->tmp_file;
				} else {
					$path = \GO::config()->file_storage_path . $att->tmp_file;
				}
				$tmpFile = new \GO\Base\Fs\File($path);
				if ($tmpFile->exists()) {
					$file = \Swift_Attachment::fromPath($tmpFile->path());
					$file->setContentType($tmpFile->mimeType());
					$file->setFilename($att->fileName);
					$this->attach($file);
					
					//$tmpFile->delete();
				}else
				{
					throw new \Exception("Error: attachment missing on server: ".$tmpFile->stripTempPath().".<br /><br />The temporary files folder is cleared on each login. Did you relogin?");
				}
			}
		}
	}
	
}
