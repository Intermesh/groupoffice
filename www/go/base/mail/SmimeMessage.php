<?php

namespace GO\Base\Mail;


class SmimeMessage extends Message
{
	
	 /**
   * Create a new Message.
   * @param string $subject
   * @param string $body
   * @param string $contentType
   * @param string $charset
   * @return SmimeMessage
   */
  public static function newInstance($subject = null, $body = null,
    $contentType = null, $charset = null)
  {
    return new static($subject, $body, $contentType, $charset);
  }

	/**
	 * Call this function to sign a message with a pkcs12 certificate.
	 * 
	 * @param type $pkcs12_data
	 * @param type $passphrase 
	 */
	
	public function setSignParams($pkcs12_data, $passphrase){

		openssl_pkcs12_read ($pkcs12_data, $certs, $passphrase);
		if(!is_array($certs)){

			//unfortunately exceptions are catched and it leads to an SMTP timeout somehow.
			trigger_error("Could not decrypt key. Invalid passphrase?", E_USER_ERROR);
		}

		$extraCerts = null;
		if(!empty($certs['extracerts'])){
			$extraCertsFile = \GO\Base\Fs\File::tempFile();
			foreach($certs['extracerts'] as $certData){
				$extraCertsFile->putContents($certData, FILE_APPEND);
			}
			$extraCerts = $extraCertsFile->path();
		}

		$this->smimeSign($certs['cert'], $certs['pkey'], $passphrase, $extraCerts);

	}
	
	public function setEncryptParams($recipcerts) {
		$this->smimeEncrypt($recipcerts);
	}
}

