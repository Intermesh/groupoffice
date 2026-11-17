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
				"propE" => "string 2",
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

	private function optimizerSwitch(): string
	{
		return go()->getDbConnection()->query("SELECT @@SESSION.optimizer_switch")->fetchColumn();
	}

	public function testDisableSemiJoinOnlyWhileExecuting()
	{
		$before = $this->optimizerSwitch();

		$during = go()->getDbConnection()
			->selectSingleValue('@@SESSION.optimizer_switch')
			->from('core_module')
			->disableSemiJoin()
			->single();

		$this->assertStringContainsString('semijoin=off', $during);
		$this->assertEquals($before, $this->optimizerSwitch());
	}

	public function testDisableSemiJoinRestoredAfterError()
	{
		$before = $this->optimizerSwitch();

		try {
			go()->getDbConnection()
				->select('doesNotExist')
				->from('core_module')
				->disableSemiJoin()
				->single();
			$this->fail("Query should have failed");
		} catch (DbException $e) {
			// expected
		}

		$this->assertEquals($before, $this->optimizerSwitch());
	}

	private function maxExecutionTimeVar(): string
	{
		return go()->getDatabase()->isMariaDB() ? "max_statement_time" : "max_execution_time";
	}

	private function sessionMaxExecutionTime(): float
	{
		return (float) go()->getDbConnection()->query("SELECT @@SESSION." . $this->maxExecutionTimeVar())->fetchColumn();
	}

	public function testMaxExecutionTimeOnlyWhileExecuting()
	{
		$before = $this->sessionMaxExecutionTime();

		$during = go()->getDbConnection()
			->selectSingleValue('@@SESSION.' . $this->maxExecutionTimeVar())
			->from('core_module')
			->maxExecutionTime(5)
			->single();

		$this->assertEquals(go()->getDatabase()->isMariaDB() ? 5 : 5000, (float) $during);
		$this->assertEquals($before, $this->sessionMaxExecutionTime());
	}

	public function testMaxExecutionTimeAbortsQuery()
	{
		$before = $this->sessionMaxExecutionTime();

		try {
			go()->getDbConnection()
				->selectSingleValue('SLEEP(2)')
				->from('core_module')
				->maxExecutionTime(0.2)
				->single();
			$this->fail("Query should have been aborted");
		} catch (DbException $e) {
			$this->assertTrue($e->isMaxExecutionTimeExceeded(), $e->getMessage());
		}

		$this->assertEquals($before, $this->sessionMaxExecutionTime());
	}

}
