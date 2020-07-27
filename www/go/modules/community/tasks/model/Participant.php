<?php

namespace go\modules\community\tasks\model;

use go\core\orm\Property;

class Participant extends Property
{

    const Kinds = [
        1 => 'individual',
        2 => 'group',
        3 => 'location',
        4 => 'resource',
    ];

    const RoleOwner = 'owner';
    const RoleAttendee = 'attendee';
    const RoleOptional = 'optional';
    const RoleInformational = 'informational';
    const RoleChair = 'chair';
    const RoleContact = 'contact';

    /** @var string display name of participant */
    public $name;

    /** @var string email address for the participant */
    public $email;

    /** @var string description with for eample information about there role or how best to contact them. */
    public $description;

    /**
     * @var string[string] method => uri
     * method can either be 'imip' or 'other'
     * future methods may be specified
     * eg. ['imap'=>'mailto:michael@example.com']
     */
    public $sendTo;

    /** @var string What kind of entity this participant is: 'individuel', 'group', 'location', 'resource */
    protected $kind;
    public function getKind() { return self::Kinds[$this->kind];}
    public function setKind($value) { $this->kind = array_search($value,self::Kinds);}

    /** @var string[bool] 'owner','attendee','optional','informational','chair','contact' */
    public $roles;

    /** @var string An id from the CalendarObject its `locations` array Where this participant is expected to be attending */
    // public $locationId;

    /** @var string language tag the best describes the participant's preferred language */
    // public $language;

    /** @var string 'needs-action', 'accepted', 'declined', 'tentative', 'delegated' */
    public $participationStatus;

    /** @var string a note from the participant to explain there participation status */
    public $participationComment;

    /** @var bool true if organizer is expecting the participant to notify them of their participation status. */
    public $expectReply;

    /** @var string is the client, server or none responsible for sending imip invites */
    // public $scheduleAgent = 'server';

    /** @var uint The sequence number of the last response from the participant.  */
    public $scheduleSequence = 0;

    /** @var DateTime The timestamp for the most recent response from this participant. */
    public $scheduleUpdated;

    /** @var string The participant id of the participant who invited this one, if known */
    public $invitedBy;

    /** @var string[bool] participantIds A set of participant ids that this participant has delegated their participation to. */
    // public $delegatedTo;

    /** @var string[bool] participantIds A set of participant ids that this participant is acting as a delegate for */
    // public $delegatedFrom;

    /** @var string[bool] participantIds A set of group participants that were invited to this calendar
    object, which caused this participant to be invited due to their
    membership in the group(s). */
    // public $memberOf;

    // public $linkIds;

    /** @var Progress Represents the progress of the participant for this task. */
    public $progress = Progress::NeedsAction;

    /** @var DateTime Specifies the date-time the progress property was last set on this participant */
    public $progressUpdated;

    /** @var uint [0-100] Represents the percent completion of the participant for this task */
    public $percentComplete = 0;

    protected static function defineMapping() {
        return parent::defineMapping()->addTable("tasks_participant", "participant");
    }
}