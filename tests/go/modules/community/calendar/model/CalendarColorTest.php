<?php

namespace go\modules\community\calendar\model;

use go\core\auth\TemporaryState;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Colors are rendered in HTML and style attributes so only hexadecimal values may be saved
 */
class CalendarColorTest extends TestCase
{
	private array $calendarIds = [];
	private array $categoryIds = [];

	protected function setUp(): void
	{
		go()->setAuthState(new TemporaryState(1));
	}

	protected function tearDown(): void
	{
		if (!empty($this->categoryIds)) {
			Category::delete(['id' => $this->categoryIds]);
		}
		if (!empty($this->calendarIds)) {
			Calendar::delete(['id' => $this->calendarIds]);
		}
	}

	/**
	 * Values are short enough for the 21 character column so only the color validation can reject them
	 */
	public static function invalidColors(): array
	{
		return [
			['0"><svg/onload=f()>'],
			['red;background:url(x)'],
			['red'],
		];
	}

	#[DataProvider('invalidColors')]
	public function testCalendarColor(string $color): void
	{
		$calendar = new Calendar();
		$calendar->name = 'Color test';
		$calendar->setColor($color);
		$saved = $calendar->save();
		if ($saved) {
			$this->calendarIds[] = $calendar->id;
		}
		$this->assertFalse($saved);
		$this->assertArrayHasKey('color', $calendar->getValidationErrors());

		$calendar = new Calendar();
		$calendar->name = 'Color test';
		$calendar->setColor('FF0000');
		$this->assertTrue($calendar->save(), json_encode($calendar->getValidationErrors()));
		$this->calendarIds[] = $calendar->id;

		$calendar = Calendar::findById($calendar->id);
		$calendar->setColor($color);
		$this->assertFalse($calendar->save());
		$this->assertArrayHasKey('color', $calendar->getValidationErrors());
	}

	#[DataProvider('invalidColors')]
	public function testCategoryColor(string $color): void
	{
		$category = new Category();
		$category->name = 'Color test';
		$category->color = $color;
		$saved = $category->save();
		if ($saved) {
			$this->categoryIds[] = $category->id;
		}
		$this->assertFalse($saved);
		$this->assertArrayHasKey('color', $category->getValidationErrors());

		$category = new Category();
		$category->name = 'Color test';
		$category->color = '00FF00';
		$this->assertTrue($category->save(), json_encode($category->getValidationErrors()));
		$this->categoryIds[] = $category->id;
	}
}
