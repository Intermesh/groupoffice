<?php
namespace go;

use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * Sets the PHP time zone back to UTC after every test
 *
 * GroupOffice works in UTC, like the database connection. GO\Base\Session::setCurrentUser() switches PHP to the time
 * zone of the user, so after a test that acts as another user, later tests wrote local times into UTC columns and
 * failed depending on the order the tests ran in.
 */
final class ResetTimeZoneExtension implements Extension
{
	public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
	{
		$facade->registerSubscriber(new class implements FinishedSubscriber {
			public function notify(Finished $event): void
			{
				date_default_timezone_set('UTC');
			}
		});
	}
}
