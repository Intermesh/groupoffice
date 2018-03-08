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
 * @version $Id: TemplateDecorator.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.mail
 */

/**
 * Require all mail classes that are used by this class
 */
require_once($GLOBALS['GO_MODULES']->modules['mailings']['class_path'].'templates.class.inc.php');

/**
 * This class can be used to replace fields in a batch mail operation.
 * Swift documentation can be found here:
 *
 * {@link http://www.swiftmailer.org/wikidocs/"target="_blank Documentation}
 *
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: TemplateDecorator.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @license AGPL Affero General Public License
 * @package go.mail
 * @uses Swift
 * @since Group-Office 3.0
 */

class Swift_Plugins_TemplateDecoratorPlugin
	implements Swift_Events_SendListener
{
  /**
   * The replacement object.
   * @var array
   * @access private
   */
  private $_replacements;

  /**
   * The body as it was before replacements.
   * @var StringHelper
   * @access private
   */
  private $_orginalBody;

  /**
   * The original subject of the message, before replacements.
   * @var StringHelper
   * @access private
   */
  private $_originalSubject;

  /**
   * Bodies of children before they are replaced.
   * @var array
   * @access private
   */
  private $_originalChildBodies = array();

  /**
   * The Message which was last replaced.
   * @var Swift_Mime_Message
   * @access private
   */
  private $_lastMessage;

  /**
   * Create a new DecoratorPlugin with $replacements.
   * @param array $replacments
   */
  public function __construct($replacements = array())
  {
    $this->_replacements=$replacements;
    
    $this->tp = new templates();
  }

  /**
   * Invoked immediately before the Message is sent.
   * @param Swift_Events_SendEvent $evt
   */
  public function beforeSendPerformed(Swift_Events_SendEvent $evt)
  {
    $message = $evt->getMessage();
    $this->_restoreMessage($message);
    $to = array_keys($message->getTo());
    $address = array_shift($to);

    $replacements = isset($this->_replacements[$address])
        ? $this->_replacements[$address]
        : array();

    if (count($replacements))
    {
      
      $body = $message->getBody();
      //$search = array_keys($replacements);
      //$replace = array_values($replacements);
      $bodyReplaced = $this->replace($replacements, $body); 
      
      if ($body != $bodyReplaced)
      {
        $this->_originalBody = $body;
        $message->setBody($bodyReplaced);
      }
      $subject = $message->getSubject();
      $subjectReplaced = $this->replace($replacements, $subject);
      
      if ($subject != $subjectReplaced)
      {
        $this->_originalSubject = $subject;
        $message->setSubject($subjectReplaced);
      }
      $children = (array) $message->getChildren();
      foreach ($children as $child)
      {
        list($type, ) = sscanf($child->getContentType(), '%[^/]/%s');
        if ('text' == $type)
        {
          $body = $child->getBody();
          $bodyReplaced =  $this->replace($replacements, $body);
          if ($body != $bodyReplaced)
          {
            $child->setBody($bodyReplaced);
            $this->_originalChildBodies[$child->getId()] = $body;
          }
        }
      }
      $this->_lastMessage = $message;
    }
  }

  /**
   * Invoked immediately after the Message is sent.
   * @param Swift_Events_SendEvent $evt
   */
  public function sendPerformed(Swift_Events_SendEvent $evt)
  {
    $this->_restoreMessage($evt->getMessage());
  }

  // -- Private methods

  /**
   * Restore a changed message back to its original state.
   * @param Swift_Mime_Message $message
   * @access private
   */
  private function _restoreMessage(Swift_Mime_Message $message)
  {
    if ($this->_lastMessage === $message)
    {
      if (isset($this->_originalBody))
      {
        $message->setBody($this->_originalBody);
        $this->_originalBody = null;
      }
      if (isset($this->_originalSubject))
      {
        $message->setSubject($this->_originalSubject);
        $this->_originalSubject = null;
      }
      if (!empty($this->_originalChildBodies))
      {
        $children = (array) $message->getChildren();
        foreach ($children as $child)
        {
          $id = $child->getId();
          if (array_key_exists($id, $this->_originalChildBodies))
          {
            $child->setBody($this->_originalChildBodies[$id]);
          }
        }
        $this->_originalChildBodies = array();
      }
      $this->_lastMessage = null;
    }
  }
  
  /**
   * Perform a str_replace() over the given value.
   * @param array The list of replacements as (search => replacement)
   * @param StringHelper The string to replace
   * @return StringHelper
   */
  protected function replace($replacements, $value)
  {
  	
  	$this->tp->replace_fields($value, $replacements);
    return $value;
  }
}
