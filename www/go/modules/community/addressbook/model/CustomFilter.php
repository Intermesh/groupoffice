<?php
namespace go\modules\community\addressbook\model;

use go\core\jmap\Entity;

class CustomFilter extends Entity
{
    /**
     *
     * @var int
     */
    public $id;

    /**
     *
     * @var int
     */
    public $addressBookId;

    /**
     *
     * @var int
     */
    public $contactId;
    protected static function defineMapping()
    {
        return parent::defineMapping()->addTable("addressbook_filter_contact_map");
    }
}