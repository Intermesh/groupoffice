<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: ImapSearchQuery.php 7607 2011-09-01 15:38:01Z wsmits $
 * @copyright Copyright Intermesh
 * @author wsmits@intermesh.nl
 */

/**
 * IMAP search query builder
 */


namespace GO\Email\Model;


class ImapSearchQuery {
						
	const TO = "TO";								// - match messages with "string" in the To:
	const BCC  = "BCC";							// - match messages with "string" in the Bcc: field
	const CC = "CC";								// - match messages with "string" in the Cc: field
	const FROM  = "FROM";						// - match messages with "string" in the From: field
	const BODY = "BODY";						// - match messages with "string" in the body of the message
	const SUBJECT = "SUBJECT";			// - match messages with "string" in the Subject:
	const TEXT = "TEXT";						// - match messages with text "string"
	const KEYWORD	= "KEYWORD";			// - match messages with "string" as a keyword
	const UNKEYWORD = "UNKEYWORD";	// - match messages that do not have the keyword "string"
	
	const BEFORE = "BEFORE";				// - match messages with Date: before "date"
	const ON = "ON";								// - match messages with Date: matching "date"
	const SINCE = "SINCE";					// - match messages with Date: after "date"
	
	const FLAG_ALL = "ALL";
	const FLAG_NEW = "NEW";
	const FLAG_RECENT = "RECENT";
	const FLAG_OLD = "OLD";
	const FLAG_SEEN = "SEEN";
	const FLAG_UNSEEN = "UNSEEN";
	const FLAG_DELETED = "DELETED";
	const FLAG_UNDELETED = "UNDELETED";
	const FLAG_ANSWERED = "ANSWERED";
	const FLAG_UNANSWERED = "UNANSWERED";
	const FLAG_FLAGGED = "FLAGGED";
	const FLAG_UNFLAGGED = "UNFLAGGED";
	
	private $_bcc = array();
	private $_cc = array();
	private $_body = array();
	private $_from = array();
	private $_subject = array();
	private $_text = array();
	private $_keyword = array();
	private $_unkeyword = array();
	private $_to = array();
	
	private $_before = false;
	private $_on = false;
	private $_since = false;
			
	/**
	 * ALL - return all messages matching the rest of the criteria
	 * 
	 * @var boolean
	 */
	private $_all = false;
	
	/**
	 * FLAGGED - match messages with the \\FLAGGED (sometimes referred to as Important or Urgent) flag set
	 * 
	 * @var boolean
	 */
	private $_flagged = false;
	
	/**
	 * UNFLAGGED - match messages that are not flagged
	 * 
	 * @var boolean
	 */
	private $_unflagged = false;
	
	/**
	 * UNDELETED - match messages that are not deleted
	 * 
	 * @var boolean
	 */
	private $_undeleted = false;
	
	/**
	 * DELETED - match deleted messages
	 * 
	 * @var boolean
	 */
	private $_deleted = false;
	
	/**
	 * SEEN - match messages that have been read (the \\SEEN flag is set)
	 * 
	 * @var boolean
	 */
	private $_seen = false;
	
	/**
	 * UNSEEN
	 * 
	 * @var boolean
	 */
	private $_unseen = false;
	
	/**
	 * NEW - match new messages
	 * 
	 * @var boolean
	 */
	private $_new = false;
	
	/**
	 * UNANSWERED - match messages that have not been answered
	 * 
	 * @var boolean
	 */
	private $_unanswered = false;
	
	/**
	 * ANSWERED - match messages with the \\ANSWERED flag set
	 * 
	 * @var boolean
	 */
	private $_answered = false;
	
	/**
	 * OLD - match old messages
	 * 
	 * @var boolean
	 */
	private $_old = false;
	
	/**
	 * RECENT - match messages with the \\RECENT flag set
	 *
	 * @var boolean
	 */
	private $_recent = false;

	
	private $_query;
	/**
	 * Add a word to search for in the specific section
	 * 
	 * Possible $section:
	 *	TO				: ImapSearchQuery::TO
	 *	BCC				: ImapSearchQuery::BCC
	 *	CC				: ImapSearchQuery::CC
	 *	FROM			: ImapSearchQuery::FROM
	 *	BODY			: ImapSearchQuery::BODY
	 *	TEXT			: ImapSearchQuery::TEXT
	 *	KEYWORD		: ImapSearchQuery::KEYWORD
	 *	UNKEYWORD : ImapSearchQuery::UNKEYWORD
	 *	SUBJECT		: ImapSearchQuery::SUBJECT		*default value
	 *  
	 * @param StringHelper $word
	 * @param StringHelper $section
	 * @param boolean $useAnd TODO: THIS WILL PROBABLY NOT WORK CORRECTLY YET
	 * @return null
	 */
	public function addSearchWord($word, $section=self::ALL, $useAnd=false){
		if(empty($word))
			return;
		
		$operator = 'OR';
		
		if($useAnd)
			$operator = 'AND';
		
		switch($section){
			case self::TO:
				$this->_to[$word] = $operator;
				break;
			case self::BCC:
				$this->_bcc[$word] = $operator;
				break;
			case self::CC:
				$this->_cc[$word] = $operator;
				break;
			case self::FROM:
				$this->_from[$word] = $operator;
				break;
			case self::BODY:
				$this->_body[$word] = $operator;
				break;
			case self::TEXT:
				$this->_text[$word] = $operator;
				break;
			case self::KEYWORD:
				$this->_keyword[$word] = $operator;
				break;
			case self::UNKEYWORD:
				$this->_unkeyword[$word] = $operator;
				break;
			case self::SUBJECT:
			default:
				$this->_subject[$word] = $operator;
				break;
		}
	}
	
	/**
	 * Search before the given date
	 * 
	 * @param unixtimestamp $date
	 */
	public function searchBefore($date){
		if(empty($date))
			$this->_before = false;
		else	
			$this->_before = date('d-M-Y',$date);
	}
	
	/**
	 * Search on the given date
	 * 
	 * @param unixtimestamp $date
	 */
	public function searchOn($date){
		if(empty($date))
			$this->_on = false;
		else	
			$this->_on = date('d-M-Y',$date);
	}
	
	/**
	 * Search after the given date
	 * 
	 * @param unixtimestamp $date
	 */
	public function searchSince($date){
		if(empty($date))
			$this->_since = false;
		else	
			$this->_since = date('d-M-Y',$date);
	}

	/**
	 * 
	 * @param boolean $bool
	 */
	public function searchAll($bool=true){
			$this->_all = $bool;
	}
	
	/**
	 * 
	 * @param boolean $bool
	 */
	public function searchUnFlagged($bool=true){
			$this->_unflagged = $bool;
	}
	
	/**
	 * 
	 * @param boolean $bool
	 */
	public function searchFlagged($bool=true){
			$this->_flagged = $bool;
	}
	
	/**
	 * 
	 * @param boolean $bool
	 */
	public function searchUnDeleted($bool=true){
			$this->_undeleted = $bool;
	}
	
	/**
	 * 
	 * @param boolean $bool
	 */
	public function searchDeleted($bool=true){
			$this->_deleted = $bool;
	}
	
	/**
	 * 
	 * @param boolean $bool
	 */
	public function searchUnSeen($bool=true){
			$this->_unseen = $bool;
	}
	
	/**
	 * 
	 * @param boolean $bool
	 */
	public function searchSeen($bool=true){
			$this->_seen = $bool;
	}
	
	/**
	 * 
	 * @param boolean $bool
	 */
	public function searchNew($bool=true){
			$this->_new = $bool;
	}
	
	/**
	 * 
	 * @param boolean $bool
	 */
	public function searchOld($bool=true){
			$this->_old = $bool;
	}
	
	/**
	 * 
	 * @param boolean $bool
	 */
	public function searchAnswered($bool=true){
			$this->_answered = $bool;
	}
	
	/**
	 * 
	 * @param boolean $bool
	 */
	public function searchUnanswered($bool=true){
			$this->_unanswered = $bool;
	}
	
	/**
	 * 
	 * @param boolean $bool
	 */
	public function searchRecent($bool=true){
			$this->_recent = $bool;
	}
	
	/**
	 * Create a part of the query
	 * 
	 * @param array $searchwords
	 * @param StringHelper $section
	 * @return StringHelper
	 */
	private function _prepareQueryPart($searchwords,$section){
		
		$wordString = '';
		
		foreach($searchwords as $word=>$operator){
			if(!empty($this->_query))
				$wordString .= $operator.' ';
			
			$wordString .= $section.' "'.$word.'" ';
		}
		
		return ' '.trim($wordString,' ').' ';
	}
	
	/**
	 * Get the full query for the IMAP server command
	 * 
	 * @return StringHelper
	 */
	public function getImapSearchQuery(){

		$this->_query = '';

		if(!empty($this->_all))
			$this->_query .= self::FLAG_ALL.' ';
		
		if(!empty($this->_from))
			$this->_query .= $this->_prepareQueryPart($this->_from, self::FROM);
		
		if(!empty($this->_to))
			$this->_query .= $this->_prepareQueryPart($this->_to, self::TO);
		
		if(!empty($this->_bcc))
			$this->_query .= $this->_prepareQueryPart($this->_bcc, self::BCC);
		
		if(!empty($this->_cc))
			$this->_query .= $this->_prepareQueryPart($this->_cc, self::CC);
			
		if(!empty($this->_body))
			$this->_query .= $this->_prepareQueryPart($this->_body, self::BODY);
		
		if(!empty($this->_subject))
			$this->_query .= $this->_prepareQueryPart($this->_subject, self::SUBJECT);
		
		if(!empty($this->_text))
			$this->_query .= $this->_prepareQueryPart($this->_text, self::TEXT);
		
		if(!empty($this->_keyword))
			$this->_query .= $this->_prepareQueryPart($this->_keyword, self::KEYWORD);
		
		if(!empty($this->_unkeyword))
			$this->_query .= $this->_prepareQueryPart($this->_unkeyword, self::UNKEYWORD);
		
		if(!empty($this->_before))
			$this->_query .= self::BEFORE.' '.$this->_before.' ';
		
		if(!empty($this->_since))
			$this->_query .= self::SINCE.' '.$this->_since.' ';
		
		if(!empty($this->_on))
			$this->_query .= self::ON.' '.$this->_on.' ';
			
		if($this->_new)
			$this->_query .= self::FLAG_NEW.' ';
		
		if($this->_recent)
			$this->_query .= self::FLAG_RECENT.' ';
		
		if($this->_old)
			$this->_query .= self::FLAG_OLD.' ';
		
		if($this->_seen)
			$this->_query .= self::FLAG_SEEN.' ';
		
		if($this->_unseen)
			$this->_query .= self::FLAG_UNSEEN.' ';
		
		if($this->_deleted)
			$this->_query .= self::FLAG_DELETED.' ';
		
		if($this->_undeleted)
			$this->_query .= self::FLAG_UNDELETED.' ';
		
		if($this->_answered)
			$this->_query .= self::FLAG_ANSWERED.' ';
		
		if($this->_unanswered)
			$this->_query .= self::FLAG_UNANSWERED.' ';
		
		if($this->_flagged)
			$this->_query .= self::FLAG_FLAGGED.' ';
		
		if($this->_unflagged)
			$this->_query .= self::FLAG_UNFLAGGED.' ';
				
		return $this->_query;
	}
}
