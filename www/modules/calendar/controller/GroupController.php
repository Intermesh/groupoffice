<?php

namespace GO\Calendar\Controller;


class GroupController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Calendar\Model\Group';
	
	protected function getStoreParams($params) {
		$findParams = \GO\Base\Db\FindParams::newInstance();
		
		//don't show calendars group. First group is a special one for calendars.
		$findParams->getCriteria()->addCondition('id', 1,'>');
						
		return $findParams;
	}
	
	
	protected function actionGroupsWithResources($params){
		
		$stmt = \GO\Calendar\Model\Group::model()->find(\GO\Base\Db\FindParams::newInstance()
						->order('t.name')
						->criteria(\GO\Base\Db\FindCriteria::newInstance()
										->addCondition('id',1,'>')));
		
		$response['results']=array();
		$response['total']=0;
		while($group = $stmt->fetch()){
			$record = $group->getAttributes('formatted');
						
			$record['resources']=array();
			
			$calStmt = \GO\Calendar\Model\Calendar::model()->find(\GO\Base\Db\FindParams::newInstance()
							->permissionLevel(\GO\Base\Model\Acl::READ_PERMISSION)
							->joinCustomFields()
							->order('t.name')
							->criteria(\GO\Base\Db\FindCriteria::newInstance()
										->addCondition('group_id',$group->id)
										));
			
			while($resource = $calStmt->fetch()){				
				$resourceRecord = $resource->getAttributes('html');
				
				$record['resources'][]= $resourceRecord;
			}
			
			$num_resources = count($record['resources']);
			if($num_resources > 0) {
				$response['results'][] = $record;
				$response['total']+=$num_resources;
			}
		}		
		return $response;
		
	}	
}
