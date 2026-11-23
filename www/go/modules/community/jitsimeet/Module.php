<?php

namespace go\modules\community\jitsimeet;

use go\core;
use go\modules\community\calendar\model\CalendarEvent;
use go\modules\community\jitsimeet\model\Settings;

/**
 * @copyright (c) 2025, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Module extends core\Module
{
	public function getStatus() : string
	{
		return self::STATUS_STABLE;
	}

	public function getAuthor(): string
	{
		return "Intermesh BV <info@intermesh.nl>";
	}

	public function getDependencies(): array
	{
		return ["community/calendar"];
	}

	public function getSettings()
	{
		return Settings::get();
	}

	public function autoInstall(): bool
	{
		return true;
	}

	public function defineListeners()
	{
		parent::defineListeners();
		CalendarEvent::on(CalendarEvent::EVENT_ONLINE_MEETING, static::class, 'onOnlineMeeting');
	}

	/**
	 * Create a Jitsi Meet link for an event saved with "onlineMeeting": "jitsi"
	 *
	 * Only for users with access to this module, so others can't get a JWT signed by the server.
	 *
	 * @return string|null The link, or null for another service or a user without access
	 */
	public static function onOnlineMeeting(CalendarEvent $event, string $service): ?string
	{
		if (strtolower($service) !== 'jitsi' || !core\model\Module::isAvailableFor('community', 'jitsimeet')) {
			return null;
		}
		return Settings::get()->createMeetingUri();
	}
}
