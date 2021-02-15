<?php

namespace GO\Base\Mail;


class EmailRecipients{
	/**
	 * Pass a e-mail string like:
	 * 
	 * "Merijn Schering" <mschering@intermesh.nl>,someone@somedomain.com,Pete <pete@pete.com
	 * 
	 * @param StringHelper $emailRecipientList 
	 */
	public function __construct($emailRecipientList='', $strict=false){
		$this->strict=$strict;
		$this->addString($emailRecipientList);
	}
	
	/**
	 * Set to true if you want to throw an exception when an e-mail is invalid.
	 * @var boolean 
	 */
	public $strict=false;
	
	/**
	 * Create a list for a sinlge recipient.
	 * 
	 * Useful to format a single addres like this:
	 * 
	 * (string) EmailRecipients::createSingle("john@example.com", "John Smith");
	 * 
	 * @param StringHelper $email
	 * @param StringHelper $personal
	 * @return EmailRecipients 
	 */
	public static function createSingle($email, $personal){
		$l = new EmailRecipients();
		$l->addRecipient(strtolower($email), $personal);
		return $l;
	}
	
	/**
	 * Add a recipient to the list.
	 * 
	 * @param StringHelper $email
	 * @param StringHelper $personal 
	 */
	public function addRecipient($email, $personal=null){
		//echo $email.' '.$personal.'<br />';
		if(empty($email))
			return false;

		$this->_addresses[trim(strtolower($email))]=!empty($personal) ? trim($personal) : null;
	}
	
	/**
	 * Check if an e-mail address is in this list
	 * 
	 * @param StringHelper $email
	 * @return boolean 
	 */
	public function hasRecipient($email){
//		return isset($this->_addresses[$email]);
		return array_key_exists(strtolower($email), $this->_addresses);
	}

	
	/**
	 * Count the addresses
	 * 
	 * @return int 
	 */
	public function count(){
		return count($this->_addresses);
	}
	
	/**
	 * Remove a recipient from the list.
	 * 
	 * @param StringHelper $email 
	 */
	public function removeRecipient($email){
		unset($this->_addresses[trim(strtolower($email))]);
	}
	/**
	 * Get the addresses in an array('email@address.com'=>'Personal name'))
	 * 
	 * @return array  
	 */
	public function getAddresses(){
		return $this->_addresses;
	}
	
	/**
	 * Get the next address as array('email'=>'example@domain.com','personal'=>'Example name');
	 * 
	 * @return array  
	 */
	public function getAddress(){
    if(count($this->_addresses) == 0) {
      return false;
    }
		reset($this->_addresses);
    $email = key($this->_addresses);
    $personal = current($this->_addresses) ?? $email;
		return ['email'=>$email, 'personal'=>$personal];
	}
	
//	public function getEmail($index=0){
//		return isset($this->_addresses[$index]['email']) ? $this->_addresses[$index]['email'] : '';
//	}
//	
//	public function getPersonal($index=0){
//		return isset($this->_addresses[$index]['personal']) ? $this->_addresses[$index]['personal'] : '';
//	}
	
	public function __toString() {
		$str = '';
		foreach($this->_addresses as $email=>$personal){			
			$str .= $this->_formatAddress($email, $personal).', ';			
		}		
		return rtrim($str,', ');
	}
	
	/**
	 * Get an array of formatted address:
	 * 
	 * $a[] = '"John Doe" <john@domain.com>';
	 * 
	 * @return array
	 */
	public function getArray(){
		$array = array();
		foreach($this->_addresses as $email=>$personal){			
			$array[] = $this->_formatAddress($email, $personal);			
		}		
		return $array;
	}
	
	
	private function _formatAddress($email, $personal){
		if(!empty($personal)){
			return '"'.$personal.'" <'.$email.'>';
		}else
		{
			return $email;
		}
	}
	
	/**
	* The array of parsed addresses
	*
	* @var     array
	* @access  private
	*/
	private $_addresses = array();
	
	/**
	* Temporary storage of personal info of an e-mail address
	*
	* @var     StringHelper
	* @access  private
	*/
	private $_personal = false;
	
	/**
	* Temporary storage
	*
	* @var     StringHelper
	* @access  private
	*/
	private $_buffer = '';
	
	/**
	* Bool to check if a string is quoted or not
	*
	* @var     bool
	* @access  private
	*/
	private $_quote = false;
	
	/**
	* Bool to check if we found an e-mail address
	*
	* @var     bool
	* @access  private
	*/

	private $_emailFound=false;
	

	/**
	 * Pass a e-mail string like:
	 * 
	 * "Merijn Schering" <mschering@intermesh.nl>,someone@somedomain.com,Pete <pete@pete.com
	 * 
	 * @param StringHelper $emailRecipientList 
	 */
	public function addString($recipientListString)
	{
		//initiate addresses array
		//$this->_addresses = array();

		$recipientListString = trim($recipientListString,',; ');
		
		
		
		for($i=0;$i<strlen($recipientListString);$i++)
		{
			$char = $recipientListString[$i];	
			
			switch($char)
			{
				case '"':
					$this->_handleQuote($char);
				break;
				
				case "'":
					$this->_handleQuote($char);
				break;
				
				case '<':
					$this->_personal = trim($this->_buffer);
					$this->_buffer = '';
					$this->_emailFound=true;
				break;
				
				case '>':
					//do nothing		
				break;							
				
				case ',':
				case ';':
					if($this->_quote || (!$this->strict && !$this->_emailFound && !\GO\Base\Util\StringHelper::validate_email(trim($this->_buffer))))
					{
						$this->_buffer .= $char;				
					}else
					{
						$this->_addBuffer();
					}
				break;
			

				default:					
					$this->_buffer .= $char;
				break;
			}			
		}
		$this->_addBuffer();
		
		return $this->_addresses;	
	}

	
	/**
	* Adds the current buffers to the addresses array
	*
	* @access private
	* @return void
	*/
	private function _addBuffer()
	{
		$this->_buffer = trim($this->_buffer);
		if(!empty($this->_personal) && empty($this->_buffer)){
			$this->_buffer = 'noaddress';
		}
		
		if(!empty($this->_buffer))
		{
			if($this->strict && !\GO\Base\Util\StringHelper::validate_email($this->_buffer)){
				throw new \Exception("Address ".$this->_buffer." is not valid");
			}else
			{
				$email = $this->_buffer;
				if(!\GO\Base\Util\StringHelper::validate_email($email)) {
					$email = 'unknown@unknown.domain';
					$this->_personal = $this->_buffer;
				}
				$this->addRecipient($email, $this->_personal);
			}
		}
		$this->_buffer = '';
		$this->_personal = false;
		$this->_emailFound=false;
		$this->_quote=false;
	}
	
	/**
	* Hanldes a quote character (' or ")
	*
	* @access private
	* @return void
	*/
	private function _handleQuote($char)
	{
		if(!$this->_quote && trim($this->_buffer)=="")
		{
			$this->_quote = $char;
		}elseif($char == $this->_quote)
		{
			$this->_quote = false;
		}else
		{
			$this->_buffer .= $char;			
		}
	}
	
	/**
	 * Merge two address strings
	 * 
	 * @param EmailRecipients $recipients
	 * @return EmailRecipients 
	 */
	public function mergeWith(EmailRecipients $recipients){
		$this->_addresses = array_merge($this->_addresses, $recipients->getAddresses());
		
		return $this;
	}
}
