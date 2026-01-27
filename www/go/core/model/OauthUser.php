<?php
/**
 * @author      Alex Bilbie <hello@alexbilbie.com>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/thephpleague/oauth2-server
 */

namespace go\core\model;
;

use go\core\model;
use League\OAuth2\Server\Entities\UserEntityInterface;
use OpenIDConnectServer\Entities\ClaimSetInterface;

class OauthUser implements UserEntityInterface, ClaimSetInterface
{
	private $user;
	public function __construct(model\User $user)
	{
		$this->user = $user;
	}


	/**
	 * Return the user's identifier.
	 *
	 * @return mixed
	 */
	public function getIdentifier(): string
	{
		return $this->user->id();
	}

	public function getClaims(): array
	{
		$parts = explode(' ', $this->user->displayName);
		$firstName = array_shift($parts);
		$middleName = '';
		$lastName = implode(' ', $parts);

		$gender = '';
		$locale = go()->getSettings()->getLocale();
		$birthdate = '';
		$organization = '';
		$department = '';
		$address = [];
		$phoneNumber = '';

		$userProfile = $this->user->getProfile();
		if ($userProfile !== null) {
			//convert to match in 25.x
			switch ($userProfile->gender) {
				case 'M':
					$gender = 'M';
					break;
				case 'F':
					$gender = 'F';
					break;
				default:
					$gender = '?';
					break;
			}

			if ( $userProfile->language) {
				$locale =  $userProfile->language;
			}

			$userProfileBirthDay = $userProfile->getBirthday();
			if (!empty($userProfileBirthDay)) {
				$birthdate = $userProfileBirthDay->format('Y-m-d');
			}

			$department = $userProfile->department;
			$organizations = $userProfile->getOrganizations();
			if (count($organizations) > 0) {
				$organization = $organizations[0]->name;
			}

			if (count($userProfile->addresses)) {
				$userProfileAddress = $userProfile->addresses[0];
				$address = [
					'formatted' => $userProfileAddress->getFormatted(),
					'street_address' => $userProfileAddress->address,
					'locality' => $userProfileAddress->city,
					'region' => $userProfileAddress->state,
					'postal_code' => $userProfileAddress->zipCode,
					'country' => $userProfileAddress->country,
				];
			}

			if (count($userProfile->phoneNumbers)) {
				$phoneNumber = $userProfile->phoneNumbers[0]->number;
			}
		}

		return [
			"id" => $this->user->id(),
			"profile" => go()->getSettings()->URL,
			'name' => $this->user->displayName,
			'family_name' => $lastName,
			'given_name' => $firstName,
			'middle_name' => $middleName,
			'nickname' => $this->user->displayName,
			'preferred_username' => $this->user->username,
			'picture' => '',
			'website' => '',
			'gender' => $gender,
			'birthdate' => $birthdate,
			'zoneinfo' => $this->user->timezone ?? go()->getSettings()->defaultTimezone,
			'locale' => $locale,
			'updated_at' => $this->user->modifiedAt->format('c'),
			// email
			'email' => $this->user->email,
			'email_verified' => true,
			// phone
			'phone_number' => $phoneNumber,
			'phone_number_verified' => true,
			// address
			'address' => $address,
			'roles' => $this->user->isAdmin() ? ['admin'] : ['user'],
			// custom business claims
			'department' => $department,
			'organization' => $organization,
		];
	}
}
