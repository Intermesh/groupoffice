<?php

namespace go\modules\community\calendar\model;

use go\core\orm\Mapping;
use go\core\orm\Property;
use go\core\util\DateTime;

/**
 * Class Participant
 *
 * This is unused, originally written for the task module but we decided to take
 * a simpler approach for task participants.
 */
class Participant extends Property
{
	/* ParticipationStatus */
	const NeedsAction = 'needs-action'; // not responded
	const Tentative = 'tentative'; // Maybe
	const Accepted = 'accepted'; // Yes
	const Declined = 'declined'; // No
	const Delegated = 'delegated'; // Someone else is going in my place (not supported yet)

	/* Kinds */
	const Individual = 'individual';
	const Group = 'group';
	const Location = 'location';
	const Resource = 'resource';

	/* Roles */
	const ValidRoles = [
		'owner'=>0, // REQ-PARTICIPANT + ORGANIZER
		'attendee'=>1, // REQ-PARTICIPANT
		'optional'=>2, // OPT-PARTICIPANT
		'informational'=>3, // NON-PARTICIPANT
		'chair'=>4, // CHAIR
		'contact'=>5
	];

	protected $id;
	protected $eventId;
	/** @var string display name of participant */
	public $name;

	/** @var string email address for the participant */
	public $email;

	/** @var string description with for example information about there role or how best to contact them. */
	public $description;

	/**
	* @var string[string] method => uri
	* method can either be 'imip' or 'other'
	* future methods may be specified
	* eg. ['imap'=>'mailto:michael@example.com']
	*/
	public $sendTo;

	/** @var string What kind of entity this participant is: 'individuel', 'group', 'location', 'resource */
	public $kind;

	/** @var int mask to be converted to string[bool] 'owner','attendee','optional','informational','chair','contact' */
	protected $rolesMask;

	/** @var string An id from the CalendarObject its `locations` array Where this participant is expected to be attending */
	//public $locationId;

	/** @var string language tag the best describes the participant's preferred language */
	//public $language;

	/** @var string 'needs-action', 'accepted', 'declined', 'tentative', 'delegated' */
	public $participationStatus = self::NeedsAction;

	/** @var string a note from the participant to explain there participation status */
	public $participationComment;

	/** @var bool true if organizer is expecting the participant to notify them of their participation status. */
	public $expectReply;

	/** @var string is the 'client', 'server' or 'none' responsible for sending imip invites */
	public $scheduleAgent;

	/** @var int The sequence number of the last response from the participant.  */
	public $scheduleSequence = 0;

	/** @var string[] A list of status codes, returned from the precessing of the most recent scheduling messages */
	//public $scheduleStatus = [];

	/** @var DateTime The timestamp for the most recent response from this participant. */
	public $scheduleUpdated;

	/** @var string The requestStatus received when the participant sends an REPLY iTip */
	public $scheduleStatus;

	/** @var string The participant id of the participant who invited this one, if known */
	public $invitedBy;

	/** @var string[bool] participantIds A set of participant ids that this participant has delegated their participation to. */
	//public $delegatedTo;

	/** @var string[bool] participantIds A set of participant ids that this participant is acting as a delegate for */
	//public $delegatedFrom;

	/** @var string[bool] participantIds A set of group participants that were invited to this calendar
	object, which caused this participant to be invited due to their
	membership in the group(s). */
	//public $memberOf;

	protected static function defineMapping(): Mapping
	{
	  return parent::defineMapping()->addTable("calendar_participant", "participant");
	}

	public function getRoles() {
		$roles = [];
		foreach (self::ValidRoles as $item => $bitPosition) {
			// Check if the bit at the current position is set to 1
			if (($this->rolesMask & (1 << $bitPosition)) !== 0) {
				// Set the corresponding item to true in the decoded array
				$roles[$item] = true;
			}
		}
		return $roles;
	}

	/**
	 * A client may set the property on a participant to true to request that the server send a scheduling message to
	 * the participant when it would not normally do so (e.g., if no significant change is made the object or the
	 * scheduleAgent is set to client). The property MUST NOT be stored in the JSCalendar object on the server or appear
	 * in a scheduling message.
	 */
	public function setScheduleForceSend($val) {
		$this->_sendTheSchedulingMessageAnyway = $val;
	}

	public function isOwner() {
		return $this->hasRole('owner');
	}

	public function hasRole($name) {
		$bitPosition = self::ValidRoles[$name];
		return ($this->rolesMask & (1 << $bitPosition)) !== 0;
	}

	public function setRoles($roles) {
		if(empty($roles)) {
			$this->rolesMask = 0;
			return;
		}
		foreach ($roles as $item => $value) {
			if ($value === true) {
				// Set the corresponding bit to 1
				$this->rolesMask |= (1 << self::ValidRoles[$item]);
			} elseif ($value === null) {
				// Clear the corresponding bit to 0
				$this->rolesMask &= ~(1 << self::ValidRoles[$item]);
			}
			// If the item is not in the input array, leave the bit unchanged
		}

	}
}