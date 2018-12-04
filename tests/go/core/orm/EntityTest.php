<?php

namespace go\core\orm;

use go\core\util\DateTime;
use go\modules\community\test\model\A;
use go\modules\community\test\model\AHasMany;
use go\modules\community\test\model\AHasOne;
use go\modules\community\test\model\B;
use go\modules\community\test\model\C;
use PHPUnit\Framework\TestCase;
use function json_encode;

class EntityTest extends TestCase {	
		
	public function testDelete() {
		$entities = B::find();
		
		foreach($entities as $entity) {
			$success = $entity->delete();
			
			$this->assertEquals(true, $success);
		}
	}

	public function testCreate() {

		$entity = new B();
		$entity->propA = "string 1";
		$entity->propB = "string 2";
		$entity->createdAt = new DateTime();

		//Directly access by offset
		$entity->hasMany[0] = new AHasMany();
		$entity->hasMany[0]->propOfHasManyA = "string 5";
		

		$aHasMany = new AHasMany();
		$aHasMany->propOfHasManyA = "string 3";
		//No offset
		$entity->hasMany[] = $aHasMany;

		
		$entity->hasOne = new AHasOne();
		$entity->hasOne->propA = "string 4";
		
		
		$success = $entity->save();
		
	

		$this->assertEquals(true, $success);
		
		$array = $entity->toArray(['propA','propB','hasMany', 'createdAt']);
		
		$string = json_encode($array);
		$string = str_replace("string ", "copy ", $string);
		$array = json_decode($string, true);
		
		
		$copy = new B();
		$copy->setValues($array);
		$success = $copy->save();		

		$this->assertEquals(true, $success);
	}
	
	
	public function testCopy() {
		$source = B::find()->single();
		$copy = $source->copy();		
	
		$copy->propA .= " (copy of " . $source->id . ")";		
		$success = $copy->save();		

		$this->assertEquals(true, $success);
	}

	public function testLoad() {
		
		$fetchProperties = ['id', 'createdAt', 'propA', 'propB', 'hasMany', 'hasOne', 'sumOfTableBIds'];
		
		$entity = B::find($fetchProperties)->single();


		$this->assertInstanceOf(Entity::class, $entity);
		
		$this->assertInstanceOf(DateTime::class, $entity->createdAt);
		
		$this->assertEquals(2, count($entity->hasMany));

//		echo json_encode($entity, JSON_PRETTY_PRINT);
		
		$this->assertEquals($fetchProperties, array_keys($entity->toArray()));

//		var_dump($entity->toArray(['id','propA','propB','hasMany', 'createdAt', 'sumOfTableBIds']));
	}

	public function testUpdate() {
		$entities = B::find();

		$entity = $entities->execute()->fetch();

//		var_dump($entity->toArray(['id','propA','propB','hasMany', 'createdAt', 'sumOfTableBIds']));


		$entity->setValues([
				"propA" => uniqid(),
				"hasMany" => [
						[
								"propOfHasManyA" => uniqid()
						]
				],
				"hasOne" => [
						"propA" => "test2"
				]
		]);

		$success = $entity->save();
		

		$this->assertEquals(true, $success);
		
		$this->assertEquals("test2", $entity->hasOne->propA);

		$this->assertEquals(1, count($entity->hasMany));
	}
	
	
	public function testDeleteHasOne() {
		$entities = B::find();

		$entity = $entities->execute()->fetch();		
		$entity->hasOne = null;
		$success = $entity->save();

		$this->assertEquals(true, $success);
		
		$this->assertEquals(null, $entity->hasOne);
		
	}
	
	
	public function testSetEntityRelation() {
		
		$c = new C();
		$c->name = "Test name";
		$success = $c->save();
		
		if(!$success) {
			var_dump($c->getValidationErrors());
		}
		
		$this->assertEquals(true, $success);
		
		
		
		$entities = B::find();
		$entity = $entities->execute()->fetch();	
		
		$entity->cId = $c->id;
		
		$success = $entity->save();
		
		$this->assertEquals(true, $success);		
		
		$this->assertEquals($c->name, $entity->getC()->name);		
	}
//	
//	public function testFilter() {
//
//		$filters = [
//				[
//						"propA" => "copy",
//				]
//		];
//		
//		$stmt = B::find(10, 0, ['id' => 'ASC'], [], $filters)->execute();
//		
//		$this->assertEquals(1, $stmt->rowCount());
//		
//		$this->assertEquals("copy 1", $stmt->fetch()->propA);
//		
//		
//		
//		
//	}
//	
//	
//	public function testFilterHasMany() {
//		$filters = [
//				[
//						"hasHasMany" => "copy",
//				]
//		];
//		
//		$ctrl = new \go\modules\community\test\controller\B();
//		$ctrl->getList([
//				"limit" => 10,
//				"sort" => ['id' => 'ASC'],
//				"filter" => $filters
//		]);
//		
//		$data = \go\core\jmap\Response::get()->getData();
//		
//		$stmt = B::find(10, 0, ['id' => 'ASC'], [], $filters)->execute();
//		
//		$this->assertEquals(1, $stmt->rowCount());
//		
//		$this->assertEquals("copy 1", $stmt->fetch()->propA);
//	}
	

	public function testFindByProperties() {
		$b = B::find()->where(['propB' => 'string 2'])->single();
		
		$this->assertEquals("string 2", $b->propB);
	}
	
	
	public function testDynamicProperties() {
		$a = new B();
		$a->propA = "string 1";
		$a->propB = "string 2";
		$a->dynamic = new \go\modules\community\test\model\ADynamic();
		$a->dynamic->propA = "123";
		$a->propD = "string 3";
		$success = $a->save();
	
		
		$this->assertEquals(true, $success);
		
		$b = A::findById($a->id);
		
		$this->assertInstanceOf(A::class, $b);
		
		$this->assertEquals($a->dynamic->propA, $b->dynamic->propA);
		
		$this->assertEquals($a->propD, $b->propD);
	}
	
	
	
	
	public function testSetInvalidPropery(){
		
		$this->expectException(\Exception::class);
		
		$a = new A();
		$a->thisPropDoesNotExist = true;
		
		$test = $a->thisPropDoesNotExist;
	}
	
	public function testGetInvalidPropery(){
		
		$this->expectException(\Exception::class);
		
		$a = new A();
		
		$test = $a->thisPropDoesNotExist;
	}
}
