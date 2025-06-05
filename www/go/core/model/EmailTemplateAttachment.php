<?php
namespace go\core\model;
						
use go\core\orm\Mapping;
use go\core\orm\Property;
						
/**
 * NewsletterAttachment model
 *
 * @copyright (c) 2019, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */

class EmailTemplateAttachment extends Property {

  /**
   * Primary key
   */
  protected int $id;
	
	/**
	 * 
	 * @var int
	 */							
	protected int $emailTemplateId;

	/**
	 *  The blob ID
   * 
	 * @var string
	 */							
  public string $blobId;
  
  /**
   * File name
   * 
   * @var string
   */
  public string $name;

  /**
   * True if it's inline
   * 
   * @var bool
   */
  public bool $inline = false;

  /**
   * True if it's attached
   * 
   * @var bool
   */
  public bool $attachment = true;

	protected static function defineMapping(): Mapping
	{
		return parent::defineMapping()
						->addTable("core_email_template_attachment", "attachment");
	}

}