<?php

namespace go\core\db;

use go\core\App;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase {

	public function testDelete() {
		$success = go()
						->getDbConnection()
						->delete("test_a", ['id' => 1])
						->execute();

		$this->assertEquals(true, $success);
	}


	public function testSingle() {
		$result = go()->getDbConnection()->select('*')->from('test_a')->where('false')->single();

		$this->assertEquals(null, $result);

	}

	public function testAll() {
		$result = go()->getDbConnection()->select('*')->from('test_a')->where('false')->all();

		$this->assertEquals([], $result);

	}

	public function testInsert() {

		$now = new \DateTime();

		$data = [
				"id" => 1,
				"propA" => "string 1",
				"createdAt" => $now,
				"modifiedAt" => $now
		];

		$result = go()
						->getDbConnection()
						->insert("test_a", $data)
						->execute();

		$this->assertEquals(true, $result);

		$record = go()->getDbConnection()
			->select()
			->from("test_a")
			->where('id', '=', 1)
			->single();

		$this->assertEquals($data['id'], $record['id']);
		$this->assertEquals($data['propA'], $record['propA']);
		$this->assertEquals($data['createdAt']->format('Y-m-d H:i:s'), $record['createdAt']);
		$this->assertEquals($data['modifiedAt']->format('Y-m-d H:i:s'), $record['modifiedAt']);

		$data = [
				"id" => 1,
				"propB" => "string 2",
				"userId" => 1
		];

		$result = go()->getDbConnection()->replace("test_b", $data)->execute();

		$this->assertEquals(true, $result);
	}
	
	public function testUpdate() {
		$data = [
				"propA" => "string 3"
		];
		
		$stmt = go()->getDbConnection()->update("test_a", $data, ['id' => 1]);



		$stmt->execute();

		$this->assertEquals(1, $stmt->rowCount());
	}

	public function testSelect() {

		$query = (new Query())
						->select('*')
						->from('test_a')
						->where('id', '=', 1)
						->limit(1)
						->offset(0)
						->orderBy(['id' => 'ASC']);


		$record = $query->single();

		//Query should return typed data because of PDO::ATTR_EMULATE_PREPARES
		$this->assertIsInt($record['id']);
		$this->assertEquals(1, $record['id']);


		$query = (new Query())
						->select('*')
						->from('test_a')
						->where(['id' => 1]);

		$record = $query->single();

		$this->assertEquals(1, $record['id']);

		$query = (new Query())
										->select('*')
										->from('test_a')
										->where('id = :id')->bind(':id', 1);

		$record = $query->single();

		$this->assertEquals(1, $record['id']);
	}

	public function testJoin() {
		$query = (new Query())
						->select('*')
						->from('test_a', "a")
						->join("test_b", "b", "a.id = b.id")
						->where('id', '=', 1);

		$record = $query->single();

		$this->assertEquals("string 2", $record['propB']);
	}

	public function testSubQuery() {
		$query = (new Query())
						->select('*')
						->from('test_a', "a")
						->join("test_b", "b", "a.id = b.id")
						->where('id', 'IN', 
									(new Query)
										->select('id')
										->from("test_b", 'sub_b')
										->where('id = :id')
										->bind(':id', 1)
						)->andWhere('id', '=', 1);

		$record = $query->single();

		$this->assertEquals("string 2", $record['propB']);


		$query = (new Query())
						->select('*')
						->from('test_a', "a")
						->join("test_b", "b", "a.id = b.id")
						->join(
										(new Query)
										->select('id')
										->from("test_b", 'sub_b')
										->where('id = :id')
										->bind(':id', 1)

										, "subjoin", "subjoin.id = a.id"
						)
						->where('id', '=', 1);

		$record = $query->single();

		$this->assertEquals("string 2", $record['propB']);


		$query = (new Query())
						->select('*')
						->from('test_a', "a")
						->whereExists(
							(new Query)
							->select('id')
							->from("test_b", 'sub_b')
							->where("sub_b.id = a.id")
		);

		$record = $query->single();

		$this->assertEquals("string 3", $record['propA']);
	}

	public function testGrouping() {
		$query = (new Query())
						->select('*')
						->from('test_a')
						->where('id', '=', 1)
						->orWhere(
										(new Criteria())
										->where("id", "=", 2)
										->andWhere("id", '>', 1)
										);

		$record = $query->single();

		$this->assertEquals(1, $record['id']);
	}

}
