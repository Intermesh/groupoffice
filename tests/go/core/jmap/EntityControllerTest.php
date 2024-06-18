<?php
namespace go\core\jmap;

use go\modules\community\test\controller\B as BController;
use go\modules\community\test\model\B;
use PHPUnit\Framework\TestCase;

class EntityControllerTest extends TestCase {	

  public function testOtherModelSave() {

    $bController = new BController();

    $result = $bController->set([
      "create" => [
        "client-id" => [
          'propA' => 'testOtherA',
          'propB' => 'testOtherB',
          'testSaveOtherModel' => true //this will make it create a second model
        ],
	      "client-id2" => [
		      'propA' => 'testOtherA',
		      'propB' => 'testOtherB',
		      'testSaveOtherModel' => true //this will make it create a second model
	      ]
      ]
    ]);
   

    $this->assertEquals(4, count($result['created']));

    $createdIds = array_map(function($mod){return $mod['id'];}, $result['created']);
    sort($createdIds);

    $result = $bController->changes([
      "sinceState" => $result['oldState']
    ]);

    sort($result['changed']);

    $this->assertEquals(4, count($result['changed']));
    $this->assertEquals($createdIds, $result['changed']);


	  $bController = new BController();

		$id = $createdIds[0];
	  $result = $bController->set([
		  "update" => [
			  "$id" => [
				  'map' => [
						$createdIds[1] => ['description' => 'link'],
				    $createdIds[2] => ['description' => 'link']
				  ]
			  ]
		  ]
	  ]);

		$this->assertArrayHasKey($id, $result['updated']);


		// With JSON Patch object
	  $result = $bController->set([
		  "update" => [
			  "$id" => [
				  '/map/' .  $createdIds[2] . '/description' => 'patched link'
				  ]
			  ]
	  ]);

	  $this->assertArrayHasKey($id, $result['updated']);


		$model = B::findById($id);

		$this->assertEquals('patched link', $model->map[$createdIds[2]]->description);
		$this->assertCount(2, $model->map);
  }

	public function testResultReference() {

		$router = new Router();
		$router->run([
			['B/query', ["limit" => 3], "r1"],
			['B/get', ["#ids" => ["resultOf"=> "r1", "path" => "/ids"]], "r2"]
		], false);

		$data = Response::get()->getData();

		$this->assertEquals("B/get", $data[1][0]);
		$this->assertIsArray($data[1][1]['list']);

	}

}