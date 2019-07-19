<?php

namespace go\core\mail;

use ArrayAccess;
use Exception;
use go\core\imap\Utils;
use go\core\util\StringUtil;
use go\core\validate\ValidateEmail;
use Countable;

/**
 * A list of e-mail recipients
 * 
 * example:
 * 
 * "Merijn Schering" <mschering@intermesh.nl>,someone@somedomain.com,Pete <pete@pete.com>
 * 
 */
class RecipientList implements ArrayAccess, Countable {

	/**
	 * Pass a e-mail string like:
	 * 
	 * "Merijn Schering" <mschering@intermesh.nl>,someone@somedomain.com,Pete <pete@pete.com>
	 * 
	 * @param string $emailRecipientList 
	 */
	public function __construct($emailRecipientList = '', $strict = false) {
		$this->strict = $strict;
		$this->addString($emailRecipientList);
	}

	/**
	 * Set to true if you want to throw an exception when an e-mail is invalid.
	 * @var boolean 
	 */
	public $strict = false;

	/**
	 * Check if an e-mail address is in this list
	 * 
	 * @param string $email
	 * @return int|false index of the array
	 */
	public function hasRecipient($email) {
//		return isset($this->_addresses[$email]);

		for ($i = 0, $c = count($this->recipients); $i < $c; $i++) {
			if ($this->recipients[$i]->getEmail() == $email) {
				return $i;
			}
		}

		return false;
	}

	/**
	 * Remove a recipient from the list.
	 * 
	 * @param string $email 
	 */
	public function removeRecipient($email) {

		$index = $this->hasRecipient($email);

		if ($index !== false) {
			unset($this->recipients[$index]);
		}
	}

	public function __toString() {
		$str = '';
		foreach ($this->recipients as $recipient) {
			$str .= $recipient . ', ';
		}
		return rtrim($str, ', ');
	}

	/**
	 * Get an array of formatted address:
	 * 
	 * $a[] = '"John Doe" <john@domain.com>';
	 * 
	 * @return Recipient[]
	 */
	public function toArray() {
		return $this->recipients;
	}

	/**
	 * The array of parsed addresses
	 *
	 * @var     array
	 * @access  private
	 */
	private $recipients = array();

	/**
	 * Temporary storage of personal info of an e-mail address
	 *
	 * @var     StringUtil
	 * @access  private
	 */
	private $personal = null;

	/**
	 * Temporary storage
	 *
	 * @var     StringUtil
	 * @access  private
	 */
	private $buffer = '';

	/**
	 * Bool to check if a string is quoted or not
	 *
	 * @var     bool
	 * @access  private
	 */
	private $inQuotedString = false;

	/**
	 * Bool to check if we found an e-mail address
	 *
	 * @var     bool
	 * @access  private
	 */
	private $emailFound = false;

	/**
	 * Pass a e-mail string like:
	 * 
	 * "Merijn Schering" <mschering@intermesh.nl>,someone@somedomain.com,Pete <pete@pete.com
	 * 
	 * @param string $emailRecipientList 
	 */
	public function addString($recipientListString) {
		//initiate addresses array
		//$this->_addresses = array();

		$recipientListString = trim($recipientListString, ',; ');



		for ($i = 0; $i < strlen($recipientListString); $i++) {
			$char = $recipientListString[$i];

			switch ($char) {
				case '"':
					$this->handleQuote($char);
					break;

				case "'":
					$this->handleQuote($char);
					break;

				case '<':
					$this->personal = trim($this->buffer);
					$this->buffer = '';
					$this->emailFound = true;
					break;

				case '>':
					//do nothing
					if ($this->inQuotedString) {
						$this->buffer .= $char;
					}
					break;

//				case ' ':
//					if($this->_inQuotedString){
//						$this->_buffer .= $char;
//					}else
//					{
//						$this->_addBuffer();
//					}
//						
//					break;

				case ',':
				case ';':
					if ($this->inQuotedString) {// || (!$this->strict && !$this->_emailFound && !ValidateEmail::check(trim($this->_buffer)))) {
						$this->buffer .= $char;
					} else {
						$this->addBuffer();
					}
					break;


				default:
					$this->buffer .= $char;
					break;
			}
		}
		$this->addBuffer();

		return $this->recipients;
	}

	/**
	 * Adds the current buffers to the addresses array
	 *
	 * @access private
	 * @return void
	 */
	private function addBuffer() {
		$this->buffer = trim($this->buffer);
		if (!empty($this->personal) && empty($this->buffer)) {
			$this->buffer = 'noaddress';
		}

		if (!empty($this->buffer)) {
			if ($this->strict && !Util::validateEmail($this->buffer)) {
				throw new Exception("Address " . $this->buffer . " is not valid");
			} else {
				$this->recipients[] = new Recipient($this->buffer, Utils::mimeHeaderDecode($this->personal));
			}
		}
		$this->buffer = '';
		$this->personal = null;
		$this->emailFound = false;
		$this->inQuotedString = false;
	}

	/**
	 * Hanldes a quote character (' or ")
	 *
	 * @access private
	 * @return void
	 */
	private function handleQuote($char) {
		if (!$this->inQuotedString && trim($this->buffer) == "") {
			$this->inQuotedString = $char;
		} elseif ($char == $this->inQuotedString) {
			$this->inQuotedString = false;
		} else {
			$this->buffer .= $char;
		}
	}

//	/**
//	 * Merge two address strings
//	 * 
//	 * @param RecipientList $recipients
//	 * @return RecipientList 
//	 */
//	public function mergeWith(RecipientList $recipients) {
//		$this->_addresses = array_merge($this->_addresses, $recipients->getAddresses());
//
//		return $this;
//	}

	public function offsetExists($offset) {
		return array_key_exists($offset, $this->recipients);
	}

	public function offsetGet($offset) {
		return $this->recipients[$offset];
	}

	public function offsetSet($offset, $value) {

		if (!is_string($value)) {
			return false;
		}

		$recipients = new RecipientList($value);

		$this->recipients[$offset] = $recipients[0];
	}

	public function offsetUnset($offset) {
		unset($this->recipients[$offset]);
	}

	public function count ( )
	{
		return count($this->recipients);
	}
}
