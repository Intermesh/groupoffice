<?php
return [
		"name" => "Address Book",
		"description" => "Store contacts and organizations",

		'mayChangeAddressbooks' => 'Change address books',

		"emailTypes" => [
				"work" => "Work",
				"home" => "Home",
				"billing" => "Billing"
		],
		"phoneTypes" => [
				"work" => "Work",
				"home" => "Home",
				"mobile" => "Mobile",
				"workmobile" => "Work Mobile",
				"fax" => "Fax",
				"workfax" => "Work fax"
		],
		"addressTypes" => [
				"visit" => "Visit",
				"postal" => "Postal",
				"work" => "Work",
				"home" => "Home",
				"delivery" => 'Delivery'
		],
		"dateTypes" => [
				"birthday" => "Birthday",
				"anniversary" => "Anniversary",
				"action" => "Action"
		],
		"urlTypes" => [
				"homepage" => "Homepage",
				"facebook" => "Facebook",
				"twitter" => "Twitter",
				"linkedin" => "LinkedIn"
		],

		"salutationTemplate" => 'Dear [if {{contact.prefixes}}]{{contact.prefixes}}[else][if !{{contact.gender}}]Ms./Mr.[else][if {{contact.gender}}=="M"]Mr.[else]Ms.[/if][/if][/if][if {{contact.middleName}}] {{contact.middleName}}[/if] {{contact.lastName}}'
];
