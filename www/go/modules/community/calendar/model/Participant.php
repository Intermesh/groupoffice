<?php

namespace go\modules\community\calendar\model;

use DateTimeInterface;
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

	protected ?string $id;
	protected int $eventId;
	/** display name of participant */
	public ?string $name;

	/** @var string email address for the participant */
	public string $email;

	/** @var ?string description with for example information about there role or how best to contact them. */
	public ?string $description;

	/**
	* @var string[string] method => uri
	* method can either be 'imip' or 'other'
	* future methods may be specified
	* eg. ['imap'=>'mailto:michael@example.com']
	*/
	public ?string $sendTo;

	/** @var string What kind of entity this participant is: 'individuel', 'group', 'location', 'resource */
	public string $kind = 'individual';

	/** @var int mask to be converted to string[bool] 'owner','attendee','optional','informational','chair','contact' */
	protected int $rolesMask = 0;

	/** @var string 'needs-action', 'accepted', 'declined', 'tentative', 'delegated' */
	public string $participationStatus = self::NeedsAction;

	/** @var ?string a note from the participant to explain there participation status */
	public ?string $participationComment = null;

	/** @var bool true if organizer is expecting the participant to notify them of their participation status. */
	public bool $expectReply = false;

	/** @var int The sequence number of the last response from the participant.  */
	public int $scheduleSequence = 0;

	/** @var ?DateTimeInterface The timestamp for the most recent response from this participant. */
	public ?DateTimeInterface $scheduleUpdated = null;

	/** @var ?string The requestStatus received when the participant sends an REPLY iTip */
	public ?string  $scheduleStatus = null;
	/** @var ?string used for access to the invite page to accept/decline */
	protected ?string $scheduleSecret = null;

	/** @var ?string The participant id of the participant who invited this one, if known */
	public ?string $invitedBy = null;


	protected static function defineMapping(): Mapping
	{
	  return parent::defineMapping()->addTable("calendar_participant", "participant");
	}

	public function init() {
		if($this->isNew()) {
			$this->kind = 'individual'; // default
		}
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
		return (object)$roles;
	}
	public function pid() {
		// scheduler needs this after finding participant by scheduleId
		return $this->id;
	}

	public function expectReply(bool $v) {
		$this->expectReply = $v;
		if($v) {
			$this->scheduleSecret = $this->generateSecret();
		}
		return $this->scheduleSecret;
	}

	private function generateSecret() {
		$bits = openssl_random_pseudo_bytes(12); // 6bits per char, 96bits = 16 chars
		return strtr(base64_encode($bits), '+/', '-_'); // translate to make url-safe
	}

	public function checkSecret($s) {
		return $this->scheduleSecret === $s;
	}

	public function isOwner() {
		return $this->hasRole('owner');
	}

	public function hasRole($name) {
		$bitPosition = self::ValidRoles[$name];
		return ($this->rolesMask & (1 << $bitPosition)) !== 0;
	}

	public function setRoles(array|\stdClass $roles) {
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