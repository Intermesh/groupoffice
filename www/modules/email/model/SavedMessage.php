<?php

namespace GO\Email\Model;


use GO\Base\Fs\File;

class SavedMessage extends ComposerMessage {
	
	private $_loadedBody;
	
	private $_tmpDir;
	/**
	 * Returns the static model of the specified AR class.
	 * Every child of this class must override it.
	 * 
	 * @return SavedMessage the static model class
	 */
	public static function model($className=__CLASS__)
	{		
		return parent::model($className);
	}

	/**
	 * Get a model instance loaded from  MIME data string.
	 * 
	 * @param StringHelper $mimeData MIME data string
	 * @return SavedMessage 
	 */
	public function createFromMimeData($mimeData, $preserveHtmlStyle = true) {
		$m = new SavedMessage();		
		$m->setMimeData($mimeData, $preserveHtmlStyle);
		return $m;
	}

	/**
	 * Reads a MIME file and creates a SavedMessage model from it.
	 * 
	 * @param StringHelper $path Relative path from file_storage_path or tmpdir where the MIME file is stored
	 * @param bookean $isTempFile Indicates if path it relative from tmpdir or file_storage_path
	 * @return SavedMessage
	 */
	public function createFromMimeFile($path, $isTempFile=false, $preserveHtmlStyle = true) {
		
		$fullPath = $isTempFile ? \GO::config()->tmpdir.$path : \GO::config()->file_storage_path.$path;
		
		$file = new \GO\Base\Fs\File($fullPath);
		
		if(!$file->exists()){
			return null; //throw new \Exception("E-mail message file does not exist!");
		}
		
		$mimeData = $file->contents();
		
		return $this->createFromMimeData($mimeData, $preserveHtmlStyle);
	}
	
	/**
	 * Reads MIME data and creates a SavedMessage model from it.
	 * @param StringHelper $mimeData The MIME data string.
	 * @return SavedMessage 
	 */
	public function setMimeData($mimeData, $preserveHtmlStyle = true) {
	
//		if (!empty($path))
//			$attributes['path'] = $path;
		
		$decoder = new \GO\Base\Mail\MimeDecode($mimeData);
		$structure = $decoder->decode(array(
				'include_bodies' => true,
				'decode_headers' => true,
				'decode_bodies' => true
						));
		
		if (!$structure)
			throw new \Exception("Could not decode mime data:\n\n $mimeData");

		$attributes=array();
		
		if (!empty($structure->headers['subject'])) {
			$attributes['subject'] = $structure->headers['subject'];
		}

		if (isset($structure->headers['disposition-notification-to'])) {
			//$mail->ConfirmReadingTo = $structure->headers['disposition-notification-to'];
		}

		$attributes['to']=isset($structure->headers['to']) && strpos($structure->headers['to'], 'undisclosed') === false ? $structure->headers['to'] : '';
		$attributes['cc'] = isset($structure->headers['cc']) && strpos($structure->headers['cc'], 'undisclosed') === false ? $structure->headers['cc'] : '';
		$attributes['bcc'] = isset($structure->headers['bcc']) && strpos($structure->headers['bcc'], 'undisclosed') === false ? $structure->headers['bcc'] : '';		
		$attributes['from'] = isset($structure->headers['from']) ? $structure->headers['from'] : '';
		

		$attributes['date']=isset($structure->headers['date']) ? $structure->headers['date'] : null;		
		$attributes['udate']=isset($structure->headers['date']) ? strtotime($attributes['date']) : null;
		$attributes['size']=strlen($mimeData);
		
		$attributes['message_id']=isset($structure->headers['message-id']) ? $structure->headers['message-id'] : "";
		
		
//		
//		\GO::debug($structure->headers);
//		
		if(isset($structure->headers['content-type']) && preg_match("/([^\/]*\/[^;]*)(.*)/", $structure->headers['content-type'], $matches)){
			$attributes['content_type_attributes']=array();
			$attributes['content_type']=$matches[1];
			$atts = trim($matches[2], ' ;');							
			$atts=explode(';', $atts);

			for($i=0;$i<count($atts);$i++){
				$keyvalue=explode('=', $atts[$i]);
				if(isset($keyvalue[1]) && $keyvalue[0]!='boundary')
					$attributes['content_type_attributes'][trim($keyvalue[0])]=trim($keyvalue[1],' "');
			}
		}
		
		
		$this->setAttributes($attributes);
		
		$this->_getParts($structure, "", $preserveHtmlStyle);

		$this->_loadedBody = $this->_loadedBody;
	}
	
	private function _getTempDir(){
		
		if(!isset($this->_tmpDir)) {
			$this->_tmpDir=\GO::config()->tmpdir.'saved_messages/'.md5(serialize($this->attributes)).'/';

			$dir = new \GO\Base\Fs\Folder($this->_tmpDir);
			$dir->delete();
			$dir->create();
		}
		
		return $this->_tmpDir;
	}
	
	public function getHtmlBody(){
		return $this->_loadedBody;
	}
	
	public function getPlainBody() {
		return \GO\Base\Util\StringHelper::html_to_text($this->_loadedBody);
	}
	
	public function getSource(){
		return '';
	}
	
	public function getZipOfAttachmentsUrl(){
		return \GO::url("savemailas/linkedEmail/zipOfAttachments", array("tmpdir"=>str_replace(\GO::config()->tmpdir, '', $this->_getTempDir())));
	}
	
	
//	protected function getAttachmentUrl($attachment) {
//		
//		$file = new \GO\Base\Fs\File($attachment['name']);
//		
//		if($file->extension()=='dat'){			
//			return \GO::url('email/message/tnefAttachmentFromTempFile', array('tmp_file'=>$attachment['tmp_file']));
//		}else
//		{		
//			return \GO::url('core/downloadTempFile', array('path'=>$attachment['tmp_file']));
//		}
//	}

	private function _getParts($structure, $part_number_prefix='', $preserveHtmlStyle = true) {
				
		if (isset($structure->parts)) {
			$structure->ctype_primary = strtolower($structure->ctype_primary);
			$structure->ctype_secondary = strtolower($structure->ctype_secondary);
			//$part_number=0;
			foreach ($structure->parts as $part_number => $part) {
			
				$part->ctype_primary = !empty($part->ctype_primary) ? strtolower($part->ctype_primary) : "text";
				$part->ctype_secondary = !empty($part->ctype_secondary) ? strtolower($part->ctype_secondary) : "plain";
				
				//text part and no attachment so it must be the body
				if ($structure->ctype_primary == 'multipart' && $structure->ctype_secondary == 'alternative' &&
								$part->ctype_primary == 'text' && $part->ctype_secondary == 'plain') {
					//check if html part is there					
					if ($this->_hasHtmlPart($structure)) {
						continue;
					}
				}

				if ($part->ctype_primary == 'text' && ($part->ctype_secondary == 'plain' || $part->ctype_secondary == 'html') && (!isset($part->disposition) || $part->disposition != 'attachment') && empty($part->d_parameters['filename'])) {
					$charset = isset($part->ctype_parameters['charset']) ? $part->ctype_parameters['charset'] : 'UTF-8';
					$body = \GO\Base\Util\StringHelper::clean_utf8($part->body, $charset);
					
					if (stripos($part->ctype_secondary, 'plain') !== false) {
						$body = $preserveHtmlStyle ? '<div class="msg">' . nl2br($body) . '</div>' : nl2br($body);
					} else {
						$body = \GO\Base\Util\StringHelper::convertLinks($body);
						$body = \GO\Base\Util\StringHelper::sanitizeHtml($body, $preserveHtmlStyle);
					}
					$this->_loadedBody .= $body;
				} elseif ($part->ctype_primary == 'multipart') {
					
				} else {
					//attachment

					if (!empty($part->ctype_parameters['name'])) {
						$filename = $part->ctype_parameters['name'];
					} elseif (!empty($part->d_parameters['filename'])) {
						$filename = $part->d_parameters['filename'];
					} elseif (!empty($part->d_parameters['filename*'])) {
						$filename = $part->d_parameters['filename*'];
					} else {
						$filename = uniqid(time());
					}

					$mime_type = $part->ctype_primary . '/' . $part->ctype_secondary;

					if (isset($part->headers['content-id'])) {
						$content_id = trim($part->headers['content-id']);
						if (strpos($content_id, '>')) {
							$content_id = substr($part->headers['content-id'], 1, strlen($part->headers['content-id']) - 2);
						}
					} else {
						$content_id='';						
					}

					$filename = File::stripInvalidChars($filename);
					
					$f = new \GO\Base\Fs\File($filename);
					
					$a = new MessageAttachment();										
					$a->name=$filename;
					$a->number=$part_number_prefix.$part_number;
					$a->content_id=$content_id;
					$a->mime=$mime_type;
					
					//$tmp_file = new \GO\Base\Fs\File($this->_getTempDir().$filename);
					if(!empty($part->body)){
						$tmp_file = new \GO\Base\Fs\File($this->_getTempDir(). \GO\Base\Fs\File::stripInvalidChars($filename));
						$tmp_file->appendNumberToNameIfExists();						
						$tmp_file->putContents($part->body);
						
						$a->setTempFile($tmp_file);
					}					

					$a->index=count($this->attachments);
					$a->size=isset($part->body) ? strlen($part->body) : 0;
					$a->encoding = isset($part->headers['content-transfer-encoding']) ? $part->headers['content-transfer-encoding'] : '';
					$a->disposition = isset($part->disposition) ? $part->disposition : '';
		
					$this->addAttachment($a);
					
				}

				//$part_number++;
				if (isset($part->parts)) {
					$this->_getParts($part, $part_number_prefix . $part_number . '.', $preserveHtmlStyle);
				}
			}
		} elseif (isset($structure->body)) {			
			$charset = isset($structure->ctype_parameters['charset']) ? $structure->ctype_parameters['charset'] : 'UTF-8';
			$text_part = \GO\Base\Util\StringHelper::clean_utf8( $structure->body,$charset);
			//convert text to html
			if (stripos($structure->ctype_secondary, 'plain') !== false) {
				$this->extractUuencodedAttachments($text_part);
				$text_part = nl2br($text_part);
			}else{
				$text_part = \GO\Base\Util\StringHelper::convertLinks($text_part);
				$text_part = \GO\Base\Util\StringHelper::sanitizeHtml($text_part, $preserveHtmlStyle);
			}
			
			$this->_loadedBody .= $text_part;
		}
	}

	private function _hasHtmlPart($structure) {
		if (isset($structure->parts)) {
			foreach ($structure->parts as $part) {
				if ($part->ctype_primary == 'text' && $part->ctype_secondary == 'html')
					return true;
				else if ($this->_hasHtmlPart($part)) {
					return true;
				}
			}
		}
		return false;
	}	

//	protected function _getAttachedImages($mimeNode) {
//		$imageInfos = array();
//
//		if (!empty($mimeNode->ctype_primary) && $mimeNode->ctype_primary=='image') {
//			$imageInfos[] = array(
//					'filename' => $mimeNode->d_parameters['filename'],
//					'image' => $mimeNode->body
//			);
//		}
//		if (!empty($mimeNode->parts) && is_array($mimeNode->parts)) {
//			foreach ($mimeNode->parts as $part) {
//				$imageInfos = array_merge($imageInfos,$this->_getAttachedImages($part));
//			}
//		}
//		
//		return $imageInfos;
//	}
//	
//	/**
//	 * Returns information of the images, if any.
//	 * @return Array Array of elements of type array("url"=>img src tag,
//	 * "path"=>image location on server)
//	 */
//	public function getEmbeddedImages() {
//		$imagePaths = array();
////		preg_match_all('!<[\s]*img[\s][.]*src[\s]*="([^"]*)"!',$this->getHtmlBody(),$matches);
////		foreach ($matches[1] as $src) {
////			$pathArr = explode('&amp;path=',$src);
////			$imagePaths[] = urldecode($pathArr[1]);
////		}
//		
//		return $imagePaths;
//	}
//
//	/**
//	 * 
//	 */
//	public function toOutputArray($html=true) {
//		$response = parent::toOutputArray();
//		$response['inlineImages'] = $this->inlineImages;
//		return $response;
//	}
}
