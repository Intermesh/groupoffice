<?php
namespace go\core\customfields;

use go\core\customfield\MultiSelect;
use go\core\model\FieldSet;
use go\modules\community\test\model\A;
use go\modules\community\test\model\B;

class Field extends \PHPUnit\Framework\TestCase {
	public function testCreate() {

		FieldSet::delete(FieldSet::findByEntity("A")->removeJoin("core_entity"));
		FieldSet::delete(FieldSet::findByEntity("B")->removeJoin("core_entity"));

		$fieldSet = new FieldSet();
		$fieldSet->setEntity("A");
		$fieldSet->name = uniqid();
		$this->assertTrue($fieldSet->save());


		$field = new \go\core\model\Field();
		$field->fieldSetId = $fieldSet->id;
		$field->name = "Multiselect";
		$field->type = "MultiSelect";
		$ms = $field->getDataType();
		/** @var MultiSelect $ms */
		$ms->setOptions([
			['text' => 'Red'],
			['text' => 'Green'],
			['text' => 'Blue']
		]);

		$this->assertTrue($field->save());


		FieldSet::migrateCustomFields(A::class, B::class);

		$bFieldSet = FieldSet::findByEntity("B")->where(['name' => $fieldSet->name])->single();

		$this->assertInstanceOf(FieldSet::class, $bFieldSet);

		$bField = \go\core\model\Field::find()
			->where('fieldSetId', '=', $bFieldSet->id)
			->andWhere('name', '=', 'Multiselect')
			->single();

		$this->assertInstanceOf(\go\core\model\Field::class, $bField);

		$dataType = $bField->getDataType();
		/** @var MultiSelect $dataType */

		$options = $dataType->getOptions();

		$this->assertCount(3, $options);

		$this->assertEquals($ms->getOptions()[0]['text'], $options[0]['text']);
		$this->assertNotEquals($ms->getOptions()[0]['id'], $options[0]['id']);



	}
}