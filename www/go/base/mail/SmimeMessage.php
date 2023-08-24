<?php

namespace GO\Base\Mail;


use GO\Smime\SmimeModule;

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

		$certs = SmimeModule::readPKCS12($pkcs12_data, $passphrase);

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

