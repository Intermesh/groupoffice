<?php

namespace go\core\orm;

use go\core\model\Group;
use go\core\model\User;
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

		$success = B::delete((new Query()));
		$this->assertEquals(true, $success);
		
		$stmt = B::find()->execute();
		$this->assertEquals(0, $stmt->rowCount());
	}

	public function testDefaultValueFromDB() {
		$entity = new B();

		$this->assertEquals("test", $entity->propC);
	}

	public function testCreate() {

		$entity = new B();
		$entity->propA = "string 1";
		$entity->propB = "string 2";
		$entity->createdAt = new DateTime();

		//Directly access by offset
		$entity->hasMany[0] = new AHasMany($entity);
		$entity->hasMany[0]->propOfHasManyA = "string 5";
		

		$aHasMany = new AHasMany($entity);
		$aHasMany->propOfHasManyA = "string 3";
		//No offset
		$entity->hasMany[] = $aHasMany;

		
		$entity->hasOne = new AHasOne($entity);
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

	public function testMap() {
		$this->internalTestMap();
		$this->internalTestMap();

		$count = go()->getDbConnection()->selectSingleValue('count(*)')->from('test_a_map')->single();

		$this->assertEquals(2, $count);
	}
	public function internalTestMap() {
		$a1 = new A();
		$a1->propA = 'map-' . uniqid();
		$success = $a1->save();

		$this->assertEquals(true, $success);

		$a2 = new A();
		$a2->propA = 'map-' . uniqid();
		$success = $a2->save();

		$this->assertEquals(true, $success);

		$a3 = new A();
		$a3->propA = 'map-' . uniqid();
		$success = $a3->save();

		$this->assertEquals(true, $success);

		$a1->setValues(['map' => [$a2->id => ['anotherAId' => $a2->id,'description' => 'link to map']]]);

		$success = $a1->save();

		$this->assertEquals(true, $success);

		$a1->setValues([
			'map' => [
				$a2->id => ['anotherAId' => $a2->id, 'description' => 'link to a2'],
				$a3->id => ['anotherAId' => $a3->id,'description' => 'link to a3']]
		]);

		$success = $a1->save();

		$this->assertEquals(true, $success);

		$arr = $a1->toArray();

		$a1 = A::findById($a1->id);
		$this->assertEquals($arr, $a1->toArray());

		$a1->setValues(['map' => [$a2->id => null, $a3->id => ['anotherAId' => $a3->id,'description' => 'link to a3']]]);
		$success = $a1->save();

		$this->assertEquals(true, $success);

		$this->assertEquals(1, count($a1->map));

		$a1 = A::findById($a1->id);

		$this->assertEquals(1, count($a1->map));


		$a1->map[$a3->id]['description'] = 'link to a3 (updated)';
		$success = $a1->save();

		$this->assertEquals(true, $success);

		$a1 = A::findById($a1->id);
		$this->assertEquals(1, count($a1->map));

		$this->assertEquals('link to a3 (updated)', $a1->map[$a3->id]['description']);


		return $a1;

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
		
		$entity = B::find($fetchProperties)->orderBy(['id' => 'DESC'])->single();


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


	public function testReplaceHasOne() {
		$entity = new B();
		$entity->propA = "string 1";
		$entity->propB = "string 2";

		$entity->hasOne = new AHasOne($entity);
		$entity->hasOne->propA = "string 4";

		$success = $entity->save();

		$this->assertEquals(true, $success);


		$entity->hasOne = new AHasOne($entity);
		$entity->hasOne->propA = "string 5";

		$success = $entity->save();

		$this->assertEquals(true, $success);

		$reloaded = B::findById($entity->id);

		$this->assertEquals("string 5", $reloaded->hasOne->propA);

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
		$a->dynamic = new \go\modules\community\test\model\ADynamic($a);
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


	public function testScalar() {
		

		$newGroup = new Group();
		$newGroup->name = uniqid();
		$success = $newGroup->save();

		$this->assertEquals(true, $success);

		$user = User::find(['id','groups'])->single();
		$count = count($user->groups);
		$user->groups[] = $newGroup->id;
		$success = $user->save();

		$this->assertEquals(true, $success);

		$user = User::find(['id','groups'])->single();

		$this->assertEquals($count + 1, count($user->groups));

		$user->groups = array_filter($user->groups, function($groupId) use($newGroup) { return $groupId != $newGroup->id;});
		$success = $user->save();

		$this->assertEquals(true, $success);

		$this->assertEquals($count, count($user->groups));

		$user = User::find(['id','groups'])->single();
		$this->assertEquals($count, count($user->groups));
	}



	public function testMerge() {
		$entity1 = new B();
		$entity1->propA = "string 1";
		$entity1->propB = "string 2";
		$entity1->createdAt = new DateTime();

		//Directly access by offset
		$entity1->hasMany[0] = new AHasMany($entity1);
		$entity1->hasMany[0]->propOfHasManyA = "string 5";


		$aHasMany = new AHasMany($entity1);
		$aHasMany->propOfHasManyA = "string 3";
		//No offset
		$entity1->hasMany[] = $aHasMany;


		$entity1->hasOne = new AHasOne($entity1);
		$entity1->hasOne->propA = "string 4";

		$success = $entity1->save();
		$this->assertEquals(true, $success);


		$entity2 = new B();
		$entity2->propA = "string 6";
		$entity2->propB = "string 7";
		$entity2->createdAt = new DateTime();

		//Directly access by offset
		$entity2->hasMany[0] = new AHasMany($entity2);
		$entity2->hasMany[0]->propOfHasManyA = "string 8";


		$aHasMany = new AHasMany($entity2);
		$aHasMany->propOfHasManyA = "string 9";
		//No offset
		$entity2->hasMany[] = $aHasMany;


		$entity2->hasOne = new AHasOne($entity2);
		$entity2->hasOne->propA = "string 10";

		$success = $entity2->save();
		$this->assertEquals(true, $success);

		$success = $entity1->merge($entity2);
		$this->assertEquals(true, $success);


		$this->assertEquals("string 6", $entity1->propA);
		$this->assertEquals("string 7", $entity1->propB);
		$this->assertEquals(4, count($entity1->hasMany));
		$this->assertEquals("string 10", $entity1->hasOne->propA);




		// again but with empty stuff on the target


		$entity1 = new B();
		$entity1->propA = "string 1";
		$entity1->propB = "string 2";

		$success = $entity1->save();
		$this->assertEquals(true, $success);


		$entity2 = new B();
		$entity2->propA = "string 6";
		$entity2->propB = "string 7";

		//Directly access by offset
		$entity2->hasMany[0] = new AHasMany($entity2);
		$entity2->hasMany[0]->propOfHasManyA = "string 8";


		$aHasMany = new AHasMany($entity2);
		$aHasMany->propOfHasManyA = "string 9";
		//No offset
		$entity2->hasMany[] = $aHasMany;


		$entity2->hasOne = new AHasOne($entity2);
		$entity2->hasOne->propA = "string 10";

		$success = $entity2->save();
		$this->assertEquals(true, $success);

		$success = $entity1->merge($entity2);
		$this->assertEquals(true, $success);


		$this->assertEquals("string 6", $entity1->propA);
		$this->assertEquals("string 7", $entity1->propB);
		$this->assertEquals(2, count($entity1->hasMany));
		$this->assertEquals("string 10", $entity1->hasOne->propA);



		// again with empty stuff on the source

		$entity1 = new B();
		$entity1->propA = "string 1";
		$entity1->propB = "string 2";
		$entity1->createdAt = new DateTime();

		//Directly access by offset
		$entity1->hasMany[0] = new AHasMany($entity1);
		$entity1->hasMany[0]->propOfHasManyA = "string 5";


		$aHasMany = new AHasMany($entity1);
		$aHasMany->propOfHasManyA = "string 3";
		//No offset
		$entity1->hasMany[] = $aHasMany;


		$entity1->hasOne = new AHasOne($entity1);
		$entity1->hasOne->propA = "string 4";

		$success = $entity1->save();
		$this->assertEquals(true, $success);


		$entity2 = new B();
		$entity2->propA = "string 6";
		$entity2->propB = "string 7";


		$success = $entity2->save();
		$this->assertEquals(true, $success);

		$success = $entity1->merge($entity2);
		$this->assertEquals(true, $success);


		$this->assertEquals("string 6", $entity1->propA);
		$this->assertEquals("string 7", $entity1->propB);
		$this->assertEquals(2, count($entity1->hasMany));
		$this->assertEquals("string 4", $entity1->hasOne->propA);



	}
}
