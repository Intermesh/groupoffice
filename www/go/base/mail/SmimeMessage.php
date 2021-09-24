<?php

/*
tempout Message for SwiftMailer
This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

/**
* tempout Message Special Message where we can apply signatures
* @package Swift
* @subpackage Signatures
* @author Xavier De Cock <xdecock@gmail.com>
*/

namespace GO\Base\Mail;


class SmimeMessage extends Message
{ 
	protected $tempout;
	protected $tempin;
	protected $pkcs12_data;
	protected $passphrase;
	//protected $extra_certs=array();
	
	protected $recipcerts;
	
	private $encrypted=false;
	private $signed=false;
	private $saved_headers=false;
	
	 /**
   * Create a new Message.
   * @param StringHelper $subject
   * @param StringHelper $body
   * @param StringHelper $contentType
   * @param StringHelper $charset
   * @return \Swift_Mime_Message
   */
  public static function newInstance($subject = null, $body = null,
    $contentType = null, $charset = null)
  {
    return new static($subject, $body, $contentType, $charset);
  }
	
	private $bcc;
	/**
	 * Call this function to sign a message with a pkcs12 certificate.
	 * 
	 * @param type $pkcs12_data
	 * @param type $passphrase 
	 */
	
	public function setSignParams($pkcs12_data, $passphrase){		
		$this->pkcs12_data=$pkcs12_data;
		$this->passphrase=$passphrase;
		
		//store bcc as the smtp transeport will remove it on send. We want to put it in the sent item
		$bccHeader = $this->getHeaders()->get('Bcc');
		if($bccHeader){
			$this->bcc = $bccHeader->getFieldBody();
		}
		//$this->extra_certs=$extra_certs;
	}
	
	public function setEncryptParams($recipcerts){
		
		$this->recipcerts=$recipcerts;	
	}

	private function _restoreHeaders() {
		$headers = $this->getHeaders();

		foreach($this->restore_headers as $h) {
			$headers->set($h);
		}

		$this->restore_headers = [];
	}

	private $restore_headers = [];
	
	private function _saveHeaders(){	
		if(!$this->saved_headers){		
			
			$tempInFile = new \GO\Base\Fs\File(\GO::config()->tmpdir."smime_tempin.txt");
			$tempInFile->parent()->create();
			$tempInFile->delete();
			
			$tempOutFile = new \GO\Base\Fs\File(\GO::config()->tmpdir."smime_tempout.txt");
			$tempOutFile->delete();			
			
			$this->tempin = $tempInFile->path();
			$this->tempout=$tempOutFile->path();
			

			/*
			 * Store the headers of the current message because the PHP function
			 * openssl_pkcs7_sign will rebuilt the MIME structure and will put the main
			 * headers in a nested mimepart. We don't want that so we remove them now 
			 * and add them to the new structure later.
			 */
			$headers = $this->getHeaders();


			$headers->removeAll('MIME-Version');
	//		$headers->removeAll('Content-Type');

			$this->saved_headers = array();//$headers->toString();

			$ignored_headers = array('Content-Transfer-Encoding','Content-Type');

			$h= $headers->getAll();
			foreach($h as $header){
				$name = $header->getFieldName();

				if(!in_array($name, $ignored_headers)){
					$this->saved_headers[$name]=$header->getFieldBody();
					$this->restore_headers[] = $header;
					$headers->removeAll($name);
				}
			}
			
			if(!empty($this->bcc)) {
				$this->saved_headers['Bcc'] = $this->bcc;
			}

			/*
			 * This class will stream the MIME structure to the tempin text file in 
			 * a memory efficient way.
			 */
			$fbs = new \Swift_ByteStream_FileByteStream($this->tempin, true);		
			parent::toByteStream($fbs);

			if(!file_exists($this->tempin))
				throw new \Exception('Could not write temporary message for signing');
		}	
	}
	
	private function _doSign(){		
		
		if(!$this->signed){					
			openssl_pkcs12_read ($this->pkcs12_data, $certs, $this->passphrase);
			if(!is_array($certs)){
				
				//unfortunately exceptions are catched by swift and it leads to an SMTP timeout somehow.
				trigger_error("Could not decrypt key. Invalid passphrase?", E_USER_ERROR);
			}
			

			if(!empty($certs['extracerts'])){
				$extraCertsFile = \GO\Base\Fs\File::tempFile();
				foreach($certs['extracerts'] as $certData){
					$extraCertsFile->putContents($certData, FILE_APPEND);
				}
			}
				//$this->extra_certs=array_merge($this->extra_certs,$certs['extracerts']);
			
			if(!file_exists($this->tempin))
				trigger_error('Failed to sign. Temp file disappeared', E_USER_ERROR);
			
			if(!isset($extraCertsFile)){
				openssl_pkcs7_sign($this->tempin, $this->tempout,$certs['cert'], array($certs['pkey'], $this->passphrase), $this->saved_headers, PKCS7_DETACHED);
			}else
			{
				openssl_pkcs7_sign($this->tempin, $this->tempout,$certs['cert'], array($certs['pkey'], $this->passphrase), $this->saved_headers, PKCS7_DETACHED, $extraCertsFile->path());
				$extraCertsFile->delete();
			}
			$this->signed=true;
		}
	}
	
	private function _doEncrypt(){						
		if(!$this->encrypted){
			if(file_exists($this->tempout)){
				//message was signed. Create new input file.

				file_put_contents($this->tempin, $this->saved_headers);

				$fp = fopen($this->tempout, 'r');
				if(!$fp)
					trigger_error('Could not read tempout file', E_USER_ERROR);

				while($line = fgets($fp)){			
					//fix header name bug in php
					$line = str_replace('application/x-pkcs7','application/pkcs7',$line);
					
					file_put_contents($this->tempin, $line, FILE_APPEND);
				}
				fclose($fp);			
				unlink($this->tempout);
			}

			openssl_pkcs7_encrypt($this->tempin, $this->tempout,$this->recipcerts, $this->saved_headers, 0, OPENSSL_CIPHER_AES_256_CBC);
			$this->encrypted=true;
		}
	}
  
	
	public function toString(){
		
		if(empty($this->pkcs12_data) && empty($this->recipcerts)){
			//no sign or encrypt parameters. Do parent method.
			return parent::toString();
		}
		
		$this->_saveHeaders();
		
		if(!empty($this->pkcs12_data)){
			$this->_doSign();
		}
		
		if(!empty($this->recipcerts)){
			$this->_doEncrypt();
		}

		$this->_restoreHeaders();

		return \GO\Base\Util\StringHelper::normalizeCrlf(file_get_contents($this->tempout));
	}
	
  /**
* Write this message to a {@link \Swift_InputByteStream}.
* @param \Swift_InputByteStream $is
*/
  public function toByteStream(\Swift_InputByteStream $is)
  {		
		if(empty($this->pkcs12_data) && empty($this->recipcerts)){
			//no sign or encrypt parameters. Do parent method.
			return parent::toByteStream($is);
		}
		
		$this->_saveHeaders();
		
		if(!empty($this->pkcs12_data)){
			$this->_doSign();
		}
		
		if(!empty($this->recipcerts)){
			$this->_doEncrypt();
		}
		
		//$is->write($this->saved_headers);
		
		$fp = fopen($this->tempout, 'r');
		if(!$fp)
			trigger_error('Could not read tempout file', E_USER_ERROR);
			
		while($line = fgets($fp)){			
			$line = \GO\Base\Util\StringHelper::normalizeCrlf($line);
			
			$is->write($line);
		}
		fclose($fp);

		$this->_restoreHeaders();
		
    return;
  }
	
	public function __destruct(){
		parent::__destruct();
		
		if(file_exists($this->tempout))
			unlink($this->tempout);
		
		if(file_exists($this->tempin))
			unlink($this->tempin);
	} 
}

