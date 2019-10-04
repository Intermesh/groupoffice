<?php
namespace go\core\jmap;

use go\modules\community\test\controller\B as BController;
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
        ]
      ]
    ]);
   

    $this->assertEquals(2, count($result['created']));

    $createdIds = array_map(function($mod){return $mod['id'];}, $result['created']);
    sort($createdIds);

    $result = $bController->changes([
      "sinceState" => $result['oldState']
    ]);

    sort($result['changed']);

    $this->assertEquals(2, count($result['changed']));
    $this->assertEquals($createdIds, $result['changed']);

  }
}