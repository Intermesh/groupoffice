<?php
namespace go\modules\community\tempsieve\Model;
use go\core\jmap\Entity;

final class Rule extends Entity
{
	protected string $name;
	protected int $index;
	protected string $scriptName;

	protected bool $active;
}