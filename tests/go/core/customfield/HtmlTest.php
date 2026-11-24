<?php

namespace go\core\customfield;

use go\core\model\Field;
use go\core\model\FieldSet;
use go\modules\community\test\model\A;
use PHPUnit\Framework\TestCase;

/**
 * HTML custom fields are shown as HTML in the detail view, so active content must be removed on save
 */
class HtmlTest extends TestCase
{
	private static FieldSet $fieldSet;
	private static Field $field;

	public static function setUpBeforeClass(): void
	{
		try {
			self::$fieldSet = new FieldSet();
			self::$fieldSet->setEntity("A");
			self::$fieldSet->name = "HtmlTest " . uniqid();
			if (!self::$fieldSet->save()) {
				throw new \Exception("Could not save field set");
			}

			self::$field = new Field();
			self::$field->fieldSetId = self::$fieldSet->id;
			self::$field->name = "Rich note";
			self::$field->databaseName = "htmlTest" . uniqid();
			self::$field->type = "Html";
			if (!self::$field->save()) {
				throw new \Exception("Could not save field: " . var_export(self::$field->getValidationErrors(), true));
			}
		} catch (\Throwable $e) {
			self::tearDownAfterClass();
			throw $e;
		}
	}

	public static function tearDownAfterClass(): void
	{
		if (isset(self::$fieldSet->id)) {
			FieldSet::delete(['id' => self::$fieldSet->id]);
		}
	}

	public static function payloadProvider(): array
	{
		return [
			'greater than in double quoted value' => ['<p>x</p><img alt=">" onerror="alert(1)" src="x">'],
			'greater than in single quoted value' => ["<img src=x alt='>'onerror=alert(1)>"],
			'script' => ['<p>x</p><script>alert(1)</script>'],
			'javascript url' => ['<a href="javascript:alert(1)">x</a>'],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('payloadProvider')]
	public function testActiveContentIsRemovedOnSave(string $html): void
	{
		$a = new A();
		$a->propA = 'html-' . uniqid();
		$a->setCustomFields([self::$field->databaseName => $html]);
		$this->assertTrue($a->save(), var_export($a->getValidationErrors(), true));

		try {
			$stored = A::findById($a->id)->getCustomFields()[self::$field->databaseName];
			$this->assertIsString($stored);
			$this->assertDoesNotMatchRegularExpression('/onerror|<script|javascript:/i', $stored);
		} finally {
			A::delete(['id' => $a->id]);
		}
	}

	public function testSafeHtmlIsKept(): void
	{
		$html = '<p>Hello <b>world</b> <a href="https://www.group-office.com">link</a></p>';

		$a = new A();
		$a->propA = 'html-' . uniqid();
		$a->setCustomFields([self::$field->databaseName => $html]);
		$this->assertTrue($a->save(), var_export($a->getValidationErrors(), true));

		try {
			$this->assertSame($html, A::findById($a->id)->getCustomFields()[self::$field->databaseName]);
		} finally {
			A::delete(['id' => $a->id]);
		}
	}
}
