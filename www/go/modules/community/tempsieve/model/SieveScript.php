<?php

namespace go\modules\community\tempsieve\Model;


use go\core\jmap\Entity;

final class SieveScript extends Entity
{

	public string $id;
	public string $blobId;
	public bool $isActive;
	public string $name;
	public string $script;
	public array $extensions = [];
//	public string $parsedScript;

}