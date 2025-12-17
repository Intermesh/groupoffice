<?php

namespace go\modules\community\tempsieve\Model;


use go\core\jmap\Entity;

final class SieveScript extends Entity
{

	private string $id;
	private string $blobId;
	private bool $isActive;
	private string $name;

}