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
use go\core\mail\Address;
use go\core\mail\AddressList;
use go\core\mail\Attachment;
use go\core\webclient\Extjs3;


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

class Message extends \go\core\mail\Message {
	
	private $_loadedBody;
	
	/**
	 * The path in where the temporary attachments are stored
	 * 
	 * @var boolean/string 
	 */
	private $_tmpDir = false;
	
	public function __construct($subject = "", $body = "", $contentType = 'text/plain') {
		parent::__construct();

		$this->setSubject($subject);
		$this->setBody($body, $contentType);
	}
	
	/**
	 * Get the tmp directory in where the temporary attachments are stored
	 * 
	 * @return string The path to the tmp directory
	 */
	public function getTmpDir(){
		return $this->_tmpDir;
	}
	
	/**
   * Create a new Message.
   * @param string $subject
   * @param string $body
   * @param string $contentType
   * @param string $charset
   * @return Message
   */
  public static function newInstance($subject = "", $body = "",
    $contentType = 'text/plain')
  {
    return new self($subject, $body, $contentType);
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

		$to = isset($structure->headers['to']) && strpos($structure->headers['to'],'undisclosed')===false ? $structure->headers['to'] : '';
		$cc = isset($structure->headers['cc']) && strpos($structure->headers['cc'],'undisclosed')===false ? $structure->headers['cc'] : '';
		$bcc = isset($structure->headers['bcc']) && strpos($structure->headers['bcc'],'undisclosed')===false ? $structure->headers['bcc'] : '';
		
		//workaround activesync problem where 'mailto:' is included in the mail address.		
		$to = str_replace('mailto:','', $to);
		$cc = str_replace('mailto:','', $cc);
		$bcc = str_replace('mailto:','', $bcc);

		$toList = new AddressList($to);
		$this->addTo(...$toList->toArray());

		$ccList = new AddressList($cc);
		$this->addCc(...$ccList->toArray());

		$bccList = new AddressList($bcc);
		$this->addBcc(...$bccList->toArray());


		if(isset($structure->headers['from'])) {

			$fromList = new AddressList(str_replace('mailto:','',$structure->headers['from']));
			if(isset($fromList[0])){
				$from = $fromList[0];
				try {
					$this->setFrom($from->getEmail(), $from->getName());
				} catch(Exception $e)  {
					\GO::debug('Failed to add from address: '.$e);
				}
			}
		}

		if(isset($structure->headers['message-id'])) {
			//Microsoft had ID Message-ID: <[132345@microsoft.com]>
			$this->setId(trim($structure->headers['message-id'], ' <>[]'));
		}

		if(isset($structure->headers['in-reply-to'])) {
			$this->setInReplyTo(trim($structure->headers['in-reply-to'], '<>'));
		}

		if(!empty($structure->headers['references'])) {
			$refs = explode(" ", $structure->headers['references']);
			$refs = array_map(function($ref) {
				return trim($ref, '<>');
			}, $refs);

			$this->setReferences(...$refs);
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
				$msg = $this->getSubject() . isset($from) ? ' <'. $from->getEmail() . '>' : '';
				ErrorHandler::log($msg);
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

		if(empty($htmlBody)) {
			return "";
		}
	
		//add body
		$htmlBody = \GO\Base\Util\StringHelper::normalizeCrlf($htmlBody);
		$htmlBody = str_replace("\r\n\r\n", "\r\n", $htmlBody);
		
		$this->setBody($htmlBody, 'text/html');
			
		//add text version of the HTML body
		$htmlToText = new \GO\Base\Util\Html2Text(str_replace('<div><br></div>', '<br>', $htmlBody));
		$plainText = $htmlToText->get_text();
		$this->setAlternateBody($plainText);

		return $this;
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
			$part->body = str_ireplace($charset, 'UTF-8', $part->body);
		}
		$part->body = \GO\Base\Util\StringHelper::clean_utf8($part->body, $charset);
	}
	
	private function _getParts($structure, $part_number_prefix='')
	{
		// Apple sends contentID's that don't comply. So we replace them with new onces but we have to replace
		// this in the body too.

		$cidReplacements = [];
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

					$mime_type = $this->buildContentType($part);

          //only embed if we can find the content-id in the body
					if(isset($this->_loadedBody) && isset($part->headers['content-id']) && ($content_id=trim($part->headers['content-id'],' <>')) && strpos($this->_loadedBody, $content_id) !== false)
					{
						$img = Attachment::fromString ($part->body, $filename, $mime_type);

						//Only set valid ID's. Iphone sends invalid content ID's sometimes.
						if (preg_match('/^.+@.+$/D',$content_id))
						{
							$img->setId($content_id);
							$this->embed($img);
						} else{
							$this->embed($img);
							$cidReplacements[$content_id] = $img->getId();
						}

					}else
					{
						$attachment = Attachment::fromString ($part->body, $filename,$mime_type);
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

		foreach($cidReplacements as $old => $new) {
			$this->_loadedBody = str_replace($old, $new, $this->_loadedBody);
		}
	}

	private function buildContentType($part): string
	{
		$mime_type = $part->ctype_primary.'/'.$part->ctype_secondary;
		if(!empty($part->ctype_parameters)) {
			foreach ($part->ctype_parameters as $name => $value) {
				if ($name == 'name') {
					continue;
				}
				$mime_type .= ';' . $name . '=' . $value;
			}
		}

		return $mime_type;
	}
	
	private function _hasHtmlPart($structure): bool
	{
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
	 * @param string $body
	 */
	private function _fixRelativeUrls(string $body) : string{
		return str_replace('href="?r=','href="'.\GO::config()->full_url, $body);
	}
	
	private function _embedPastedImages($body){
		$regex = '/src="data:image\/([^;]+);([^,]+),([^"]+)/';
			
		preg_match_all($regex, $body, $allMatches,PREG_SET_ORDER);
		foreach($allMatches as $matches){
			if($matches[2]=='base64'){
				$extension = $matches[1];
				$img = Attachment::fromString(base64_decode($matches[3]), uniqid() . '.'. $extension);
				$contentId = $this->embed($img);

				$body = str_replace($matches[0],'src="'.$contentId, $body);
			}
		}
		
		$blobIds = Blob::parseFromHtml($body);
		foreach($blobIds as $blobId) {
			$blob = Blob::findById($blobId);
			
			if($blob) {
				$img = Attachment::fromBlob($blob);

				$contentId = $this->embed($img);
				$body = Blob::replaceSrcInHtml($body, $blobId, $contentId);
			}
		}
		
		return $body;
	}
	
	/**
	 * Check if either to,cc and bcc has at least one recipient.
	 * 
	 * @return boolean 
	 */
	public function hasRecipients(): bool
	{
		return $this->countRecipients() > 0;
	}
	
	public function countRecipients(): int
	{
		return count($this->getTo() ?? []) + count($this->getCc() ?? []) + count($this->getBcc() ?? []);
	}
	
	/**
	 * handleEmailFormInput
	 * 

	 * This method can be used in Models and Controllers. It puts the email body
	 * and inline (image) attachments from the client in the message, which can
	 * then be used for storage in the database or sending emails.
	 * 
	 * @param array $params Must contain elements: body (string) and
	 * 
	 * inlineAttachments (string).
	 */
	public function handleEmailFormInput(array $params){
		
		if(!empty($params['subject']))
			$this->setSubject($params['subject']);		
		
		if(!empty($params['to'])){		
			$to = new AddressList($params['to']);
			$this->addTo(...$to->toArray());
		}
		if(!empty($params['cc'])){
			$cc = new AddressList($params['cc']);
			$this->addCc(...$cc->toArray());
		}
		if(!empty($params['bcc'])){
			$bcc = new AddressList($params['bcc']);
			$this->addBcc(...$bcc->toArray());
		}
		
		if(isset($params['alias_id'])){
			$alias = \GO\Email\Model\Alias::model()->findByPk($params['alias_id']);	
			$this->setFrom($alias->email, $alias->name);
			
			if(!empty($params['notification']))
				$this->setReadReceiptTo(new Address($alias->email, $alias->name));
		}
		
		if(isset($params['priority']) && $params['priority'] != 3)
			$this->setPriority ($params['priority']);
		
		
		if(!empty($params['in_reply_to'])){
			$this->setInReplyTo($params['in_reply_to']);
			$this->setReferences($params['in_reply_to']);
		}

		if(!empty($params['references'])) {
			$this->setReferences($params['references']);
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
						} elseif($ia->blobId) {
							$path = Blob::buildPath($ia->blobId);
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
							$img = Attachment::fromPath($tmpFile->path());
							$img->setContentType($tmpFile->mimeType());
							$contentId = $this->embed($img);

							//$tmpFile->delete();
							$params['htmlbody'] = \GO\Base\Util\StringHelper::replaceOnce($matches[1], $contentId, $params['htmlbody']);
						}
					}
				}
			}
			$params['htmlbody']=$this->_fixRelativeUrls($params['htmlbody']);

			if(file_exists(Extjs3::get()->getThemePath() . 'htmleditor.css')) {
				$style = preg_replace("'/\*.*\*/'", "", file_get_contents(Extjs3::get()->getThemePath() . 'htmleditor.css'));
			} else {
				$style = preg_replace("'/\*.*\*/'", "", file_get_contents(Extjs3::get()->getBasePath() . '/views/Extjs3/themes/Paper/htmleditor.css'));
			}

			$htmlTop = '<html>
<head>
<style type="text/css" id="groupoffice-email-style">
'.$style.'
</style>
</head>
<body>';
			
			$htmlBottom = '</body></html>';
			
			$this->setHtmlAlternateBody($htmlTop.$params['htmlbody'].$htmlBottom);
		} else {
			$this->setBody($params['plainbody'], 'text/plain');
		}

		if (!empty($params['attachments'])) {
			$attachments = json_decode($params['attachments']);
			foreach ($attachments as $att) {
				if (!empty($att->blobId)) {
					$path = Blob::buildPath($att->blobId);
				} else if (!empty($att->tmp_file) && empty($att->from_file_storage)) {
					$path = \GO::config()->tmpdir . $att->tmp_file;
				} else {
					$path = \GO::config()->file_storage_path . $att->tmp_file;
				}
				$path = html_entity_decode($path);
				$tmpFile = new \GO\Base\Fs\File($path);
				if ($tmpFile->exists()) {
					$file = Attachment::fromPath($tmpFile->path());
					$file->setFilename($att->fileName);
					$this->attach($file);
				} else {
					throw new \Exception("Error: attachment missing on server: " . $tmpFile->stripTempPath() . ".\n\nThe temporary files folder is cleared on each login. Did you relogin?");
				}
			}
		}
	}


	public function addFrom(string $address, ?string $name = null): \go\core\mail\Message
	{
		return \go\core\mail\Message::setFrom($address, $name); // TODO: Change the autogenerated stub
	}

}
