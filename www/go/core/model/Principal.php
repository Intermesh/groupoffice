<?php

namespace go\core\model;

use go\core\db\Criteria;
use go\core\db\Expression;
use go\core\jmap\Entity;
use go\core\orm\Filters;
use go\core\orm\Query;

/**
 * A Principal represents an individual, group, location (e.g. a room), resource (e.g. a projector) or other entity in a collaborative environment.
 */
class Principal extends Entity
{
	const Individual = 'individual'; // This represents a single person.
	const Group = 'group'; // This represents a group of people.
	const Resource = 'resource'; // This represents some resource, e.g. a projector.
	const Location = 'location'; // This represents a location (that needs scheduling).
	const Other = 'other'; // This represents some other undefined principal.


	/** @var string The id of the principal. */
	public $id;

	/** @var string One of the type constants */
	public $type;

	/** @var string The name of the principal, e.g. “Jane Doe”, or “Room 4B”. */
	public $name;

	/** @var ?string 40char hex hash of file */
	public $avatarId;

	/** @var ?string A longer description of the principal, for example details about the facilities of a resource, or null if no description available. */
	public $description;

	/** @var ?string An email address for the principal, or null if no email is available */
	public $email;

	/** @var ?string The time zone for this principal, if known. If not null, the value MUST be a time zone id from the IANA Time Zone Database */
	public $timeZone;

	public function getCapabilities() {
		return [
			'mayGetAvailability' => true, // met the user call Prinicpal/getAvailability
			'mayShareWith' => true, // may the principal be added to the shareWith of a calendar.
		];
	}

	protected static function textFilterColumns(): array
	{
		return ['name', 'description', 'email'];
	}

	protected static function defineFilters(): Filters
	{
		return parent::defineFilters()
			->add('type', function(Criteria $criteria, $value) {
				$criteria->andWhere('type', '=', $value);
			});
	}
}