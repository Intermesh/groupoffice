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
 * @version $Id: Go2Mime.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.mail
 * @uses Swift
 */

/**
 * Require all mail classes that are used by this class
 */
require_once $GO_CONFIG->class_path.'mail/RFC822.class.inc';
require_once $GO_CONFIG->class_path.'mail/swift/lib/swift_required.php';
require_once($GO_CONFIG->class_path."html2text.class.inc");
require_once($GO_CONFIG->class_path."mail/mimeDecode.class.inc");
require_once($GO_CONFIG->class_path.'filesystem.class.inc');


/**
 * This class is used to convert mime objects to an array and vice versa
 *
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: Go2Mime.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @copyright Copyright Intermesh
 * @license Affero General Public License
 * @package go.mail
 * @uses Swift
 * @since Group-Office 3.0
 */


class Go2Mime
{
	var $body='';
	var $attachments=array();
	var $inline_attachments=array();
	var $notification=false;

	public function set_body($body)
	{
		$this->body=$body;
	}

	public function set_attachments($attachments)
	{
		$this->attachments=$attachments;
	}

	public function set_notification($email)
	{
		$this->notification=$email;
	}

	public function set_inline_attachments($attachments)
	{
		$this->inline_attachments=$attachments;
	}

	public function build_mime()
	{
		global $GO_CONFIG;

		/*
		 * Make sure temp dir exists
		 */
		File::mkdir($GO_CONFIG->tmpdir);

		$message = Swift_Message::newInstance();
		if($this->notification)
		{
			$message->setReadReceiptTo($this->notification);
		}

		//var_dump($this->inline_attachments);
		foreach($this->inline_attachments as $inline_attachment)
		{
			//go_debug($inline_attachment);
			if(isset($inline_attachment['data']))
			{
				$img = Swift_EmbeddedFile::newInstance($inline_attachment['data'],
				$inline_attachment['filename'],
				$inline_attachment['content_type']);								
				
			}else
			{
				$img = Swift_EmbeddedFile::fromPath($inline_attachment['tmp_file']);
				$img->setContentType(File::get_mime($inline_attachment['tmp_file']));				
			}
			$src_id = $message->embed($img);
				
			//Browsers reformat URL's so a pattern match
			//$this->body = str_replace($inline_attachment['url'], $src_id, $this->body);
			$just_filename = utf8_basename($inline_attachment['url']);
			$this->body = preg_replace('/="[^"]*'.preg_quote($just_filename).'"/', '="'.$src_id.'"', $this->body);
		}

		$message->setBody($this->body, "text/html");

		return $message->toString();
	}


	public function mime2GO($mime, $inline_attachments_url='', $create_tmp_attachments=false, $create_tmp_inline_attachments=false, $part_number=''){

		global $lang, $GO_LANGUAGE;

		//fix for strange Microsoft exports
		$mime = str_replace("------=_NextPart", "\r\n------=_NextPart", $mime);
		
		require_once($GO_LANGUAGE->get_language_file('email'));
		
		$this->replacements = array();
		$this->inline_attachments_url=$inline_attachments_url;


		$params['include_bodies'] = true;
		$params['decode_bodies'] = true;
		$params['decode_headers'] = true;
		$params['input'] = $mime;

		$structure = Mail_mimeDecode::decode($params);

		if($part_number!='')
		{
			$parts_arr = explode('.',$part_number);
			for($i=0;$i<count($parts_arr);$i++)
			{
				$structure = $structure->parts[$parts_arr[$i]];
			}
		}
		
		$RFC822 = new RFC822();

		$from = isset($structure->headers['from']) ? $structure->headers['from'] : '';
		$addresses = $RFC822->parse_address_list($from);

		$this->response['notification'] = isset($structure->headers['disposition-notification-to']) ? true : false;
		$this->response['subject']= empty($structure->headers['subject']) ? '' : $structure->headers['subject'];
		$this->response['from'] = isset($addresses[0]['personal']) ? htmlspecialchars($addresses[0]['personal'],ENT_QUOTES, 'UTF-8') : '';
		$this->response['sender']= isset($addresses[0]['email']) ? htmlspecialchars($addresses[0]['email'],ENT_QUOTES, 'UTF-8') : '';
		$this->response['to'] = isset($structure->headers['to']) ? $structure->headers['to'] : '';
		$this->response['cc'] = isset($structure->headers['cc']) ? $structure->headers['cc'] : '';
		$this->response['bcc'] = isset($structure->headers['bcc']) ? $structure->headers['bcc'] : '';
		$this->response['reply-to']=isset($structure->headers['reply-to']) ? $structure->headers['reply-to'] : $this->response['sender'];
		
		$this->response['message-id']=isset($structure->headers['message-id']) ? $structure->headers['message-id'] : "";

		//in some cases decoding lead to
		if(is_array($this->response['subject']))
			$this->response['subject']=$this->response['subject'][0];
		if(is_array($this->response['to'])){
			$this->response['to']=implode(',', $this->response['to']);
		}
		if(is_array($this->response['cc'])){
			$this->response['cc']=implode(',', $this->response['cc']);
		}
		if(is_array($this->response['bcc'])){
			$this->response['bcc']=implode(',', $this->response['bcc']);
		}

		if(isset($structure->headers['date']) && is_array($structure->headers['date']))
				$structure->headers['date']=$structure->headers['date'][0];
		
		$this->response['to_string']='';
		if(!empty($this->response['to']))
		{
			
			//exit();
			$addresses=$RFC822->parse_address_list($this->response['to']);
			$to=array();
			foreach($addresses as $address)
			{
				$this->response['to_string'].= $RFC822->write_address($address['personal'], $address['email']).', ';
				$to[] = array('email'=>htmlspecialchars($address['email'], ENT_QUOTES, 'UTF-8'),
						'name'=>htmlspecialchars($address['personal'], ENT_QUOTES, 'UTF-8'));
			}
			$this->response['to']=$to;
			$this->response['to_string']=substr($this->response['to_string'],0,-2);
		}else
		{
			$this->response['to'][]=array('email'=>'', 'name'=> '');
		}

		$cc=array();
		$this->response['cc_string']='';
		if(!empty($this->response['cc']))
		{
			$addresses=$RFC822->parse_address_list($this->response['cc']);
			foreach($addresses as $address)
			{
				$this->response['cc_string'].= $RFC822->write_address($address['personal'], $address['email']).', ';
				$cc[] = array('email'=>htmlspecialchars($address['email'], ENT_QUOTES, 'UTF-8'),
						'name'=>htmlspecialchars($address['personal'], ENT_QUOTES, 'UTF-8'));
			}
			$this->response['cc_string']=substr($this->response['cc_string'],0,-2);
		}
		$this->response['cc']=$cc;

		$bcc=array();
		$this->response['bcc_string']='';
		if(!empty($this->response['bcc']))
		{
			$addresses=$RFC822->parse_address_list($this->response['bcc']);
			foreach($addresses as $address)
			{
				$this->response['bcc_string'].= $RFC822->write_address($address['personal'], $address['email']).', ';
				$bcc[] = array('email'=>htmlspecialchars($address['email'], ENT_QUOTES, 'UTF-8'),
						'name'=>htmlspecialchars($address['personal'], ENT_QUOTES, 'UTF-8'));
			}
			$this->response['bcc_string']=substr($this->response['bcc_string'],0,-2);
		}
		$this->response['bcc']=$bcc;

		$this->response['full_from']=$this->response['from'];
		$this->response['priority']=3;


		if(isset($structure->headers['date'])){
			$this->response['udate']=strtotime($structure->headers['date']);
		}else
		{
			$this->response['udate']=time();
		}
		$this->response['date']=date($_SESSION['GO_SESSION']['date_format'].' '.$_SESSION['GO_SESSION']['time_format'], $this->response['udate']);
			
		$this->response['size']=strlen($params['input']);

		$this->response['attachments']=array();
		//$this->response['inline_attachments']=array();
		$this->response['body']='';
		

		$this->get_parts($structure, '', $create_tmp_attachments, $create_tmp_inline_attachments);

		$a = $this->response['attachments'];
		$this->response['attachments']=array();
		
		$this->response['smime_signed']=false;

		for ($i=0;$i<count($a);$i++)
		{
			$count=0;

			//go_debug($a[$i]);
			if(!empty($a[$i]['replacement_url'])){				
				$this->response['body'] = str_replace('cid:'.$a[$i]['id'], $a[$i]['replacement_url'], $this->response['body'], $count);
			}

			if(!$count)
				unset($a[$i]['replacement_url']);	
			
			
			if($a[$i]['name']=='smime.p7s'){					
				$this->response['smime_signed']=true;
				continue;
			}


			$this->response['attachments'][]=$a[$i];
		}

		//for compatibility with IMAP get_message_with_body
		//$this->response['url_replacements']=$this->response['inline_attachments'];

		return $this->response;
	}

	public function remove_inline_images($attachments){
		$removed = array();

		for($i=0;$i<count($attachments);$i++) {
			if(empty($attachments[$i]['replacement_url'])){
				$removed[]=$attachments[$i];
			}
		}
		return $removed;
	}

	private function get_parts($structure, $part_number_prefix='', $create_tmp_attachments=false, $create_tmp_inline_attachments=false)
	{
		global $GO_CONFIG;

		if (isset($structure->parts))
		{
			$part_number=0;
			foreach ($structure->parts as $part_number=>$part) {
				//text part and no attachment so it must be the body
				if($structure->ctype_primary=='multipart' && $structure->ctype_secondary=='alternative' &&
				$part->ctype_primary == 'text' && $part->ctype_secondary=='plain')
				{
					continue;
				}

				if(!isset($part->ctype_primary))
					continue;

				if ($part->ctype_primary == 'text' && (!isset($part->disposition) || $part->disposition != 'attachment') && empty($part->d_parameters['filename']))
				{
					$part->ctype_parameters['charset']=isset($part->ctype_parameters['charset']) ? $part->ctype_parameters['charset'] : 'UTF-8';
					$content_part = String::clean_utf8($part->body, $part->ctype_parameters['charset']);
					if (stripos($part->ctype_secondary,'plain')!==false)
					{
						$content_part = String::text_to_html($content_part);
					}else
					{
						$content_part = String::convert_html($content_part);
					}

					$this->response['body'] .= $content_part;
				}
				//store attachments in the attachments array

				$filename = '';
				if(!empty($part->ctype_parameters['name']))
				{
					$filename = $part->ctype_parameters['name'];
				}elseif(!empty($part->d_parameters['filename']) )
				{
					$filename = $part->d_parameters['filename'];
				}elseif(!empty($part->d_parameters['filename*']))
				{
					$filename=$part->d_parameters['filename*'];
				}
				
				if (!empty($part->body) && (!empty($filename) || !empty($part->headers['content-id'])))
				{
					$mime_attachment['tmp_file']=false; //for compatibility with IMAP attachments which use this property.
					$mime_attachment['index']=count($this->response['attachments']);
					$mime_attachment['size'] = isset($part->body) ? strlen($part->body) : 0;
					$mime_attachment['human_size'] = Number::format_size($mime_attachment['size']);
					$mime_attachment['name'] = $filename;
					$mime_attachment['extension'] = File::get_extension($filename);
					$mime_attachment['type'] = $part->ctype_primary;
					$mime_attachment['subtype'] = $part->ctype_secondary;
					$mime_attachment['encoding'] = isset($part->headers['content-transfer-encoding']) ? $part->headers['content-transfer-encoding'] : '';
					$mime_attachment['imap_id'] = $part_number_prefix.$part_number;
					$mime_attachment['disposition'] = isset($part->disposition) ? $part->disposition : '';
					$mime_attachment['id'] = '';//isset($part->headers['content-id']) ? $part->headers['content-id'] : '';

					if($create_tmp_attachments)
					{
						if(empty($filename))$filename=uniqid(time());

						$mime_attachment['tmp_file']=$GO_CONFIG->tmpdir.'attachments/'.uniqid(date('is'),true).'/'.$filename;
						filesystem::mkdir_recursive(dirname($mime_attachment['tmp_file']));

						file_put_contents($mime_attachment['tmp_file'], $part->body);
					}

					if(!empty($part->headers['content-id']))
					{
						$content_id = trim($part->headers['content-id']);
						if ($content_id != '')
						{
							if (strpos($content_id,'>'))
							{
								$content_id = substr($part->headers['content-id'], 1,strlen($part->headers['content-id'])-2);
							}
							$mime_attachment['id'] = $content_id;
							
							//$filename=

							//$path = 'mimepart.php?path='.urlencode($path).'&part_number='.$part_number;
							//replace inline images identified by a content id with the url to display the part by Group-Office
							$mime_attachment['replacement_url']=String::add_params_to_url($this->inline_attachments_url, 'part_number='.$part_number_prefix.$part_number.'&amp;time='.time().'&amp;name='.urlencode($filename));

							if($create_tmp_inline_attachments)
							{
								if(empty($filename)) $filename=uniqid(time());
								$mime_attachment['tmp_file']=$GO_CONFIG->tmpdir.'attachments/'.uniqid(date('is'),true).'/'.$filename;
								filesystem::mkdir_recursive(dirname($mime_attachment['tmp_file']));

								file_put_contents($mime_attachment['tmp_file'], $part->body);
							}
						}
					}
					//go_debug($mime_attachment);

					$this->response['attachments'][] = $mime_attachment;
				}

				if(isset($part->parts))
				{
					$this->get_parts($part, $part_number_prefix.$part_number.'.',$create_tmp_attachments, $create_tmp_inline_attachments);
				}
				$part_number++;
			}
		}elseif(isset($structure->body))
		{
			$structure->ctype_parameters['charset']=isset($structure->ctype_parameters['charset']) ? $structure->ctype_parameters['charset'] : 'UTF-8';
			$text_part = String::clean_utf8($structure->body, $structure->ctype_parameters['charset']);
			//convert text to html
			if (stripos($structure->ctype_secondary,'plain')!==false)
			{
				$text_part = String::text_to_html($text_part);
			}else
			{
				$text_part = String::convert_html($text_part);
			}
			$this->response['body'] .= $text_part;
		}
	}
}
?>