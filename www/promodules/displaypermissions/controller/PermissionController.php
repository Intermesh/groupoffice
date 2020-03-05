<?php
namespace GO\Displaypermissions\Controller;

use AccessDeniedException;
use GO;
use GO\Addressbook\Model\Addressbook;
use GO\Base\Controller\AbstractJsonController;
use GO\Base\Db\FindCriteria;
use GO\Base\Db\FindParams;
use GO\Base\Model\Acl;
use GO\Base\Model\User;
use GO\Calendar\Model\Calendar;
use GO\Email\Model\Account;
use GO\Files\Model\Folder;
use GO\Tasks\Model\Tasklist;
use GO\Tickets\Model\Type;


class PermissionController extends AbstractJsonController{
	
	private static $_storeSort = 'model_name';
	private static $_storeDir = 'ASC';
	
	protected function actionStore($params)	{
		
		if ( GO::user()->getModulePermissionLevel('displaypermissions') < Acl::MANAGE_PERMISSION ) {
			throw new AccessDeniedException();
		}
		
		$oldIgnoreAcl = GO::setIgnoreAclPermissions();
		
		$response = array(
			'total'	=> 0,
			'success' => true,
			'results' => array()
		);
		
		self::$_storeSort = isset($params['sort']) ? $params['sort'] : 'model_name';
		self::$_storeDir = isset($params['dir']) ? $params['dir'] : 'ASC';
		$userId = $params['user_id'];


		if (self::$_storeSort=='permission_level')
			self::$_storeSort='a.level';
		
		$calendarsStmt = Calendar::model()->find(
				FindParams::newInstance()
					->select('t.acl_id, t.id AS model_id, t.name AS model_name, \''.GO::t("Calendar", "calendar").'\' AS model_type_name, a.level AS permission_level')
					->join(
						'core_acl_group',
						FindCriteria::newInstance()->addRawCondition('a.aclId', 't.acl_id'),
						'a'
						)
					->join(
						'core_user_group',
						FindCriteria::newInstance()->addRawCondition('a.groupId', 'ug.groupId'),
						'ug',
						'LEFT'
					)
					->criteria(
						FindCriteria::newInstance()
							//->addCondition('user_id', $userId, '=', 'a', false)
							->addCondition('userId', $userId, '=', 'ug', false)
					)
					->order(self::$_storeSort,self::$_storeDir)
			);
		foreach ($calendarsStmt as $calendarModel) {
			if (!isset($response['results'][$calendarModel->acl_id]) || $response['results'][$calendarModel->acl_id]['permission_level']<$calendarModel->permission_level) {
				if (!isset($response['results'][$calendarModel->acl_id]))
					$response['total']++;
				$response['results'][$calendarModel->acl_id] = array(
					'model_id' => $calendarModel->model_id,
					'model_name' => $calendarModel->model_name,
					'model_type_name' => $calendarModel->model_type_name,
					'permission_level' => $calendarModel->permission_level
				);
			}
		}

		$addressbooksStmt = Addressbook::model()->find(
				FindParams::newInstance()->debugSql()
					->select('t.acl_id, t.id AS model_id, t.name AS model_name, \''.GO::t("Address book", "addressbook").'\' AS model_type_name, a.level AS permission_level')
					->join(
						'core_acl_group',
						FindCriteria::newInstance()->addRawCondition('a.aclId', 't.acl_id'),
						'a'
						)
					->join(
						'core_user_group',
						FindCriteria::newInstance()->addRawCondition('a.groupId', 'ug.groupId'),
						'ug',
						'LEFT'
					)
					->criteria(
						FindCriteria::newInstance()
							->addCondition('userId', $userId, '=', 'ug', false)
					)
					->order(self::$_storeSort,self::$_storeDir)
			);

		foreach ($addressbooksStmt as $addressbookModel) {
			if (!isset($response['results'][$addressbookModel->acl_id]) || $response['results'][$addressbookModel->acl_id]['permission_level']<$addressbookModel->permission_level) {
				if (!isset($response['results'][$addressbookModel->acl_id]))
					$response['total']++;
				$response['results'][$addressbookModel->acl_id] = array(
					'model_id' => $addressbookModel->model_id,
					'model_name' => $addressbookModel->model_name,
					'model_type_name' => $addressbookModel->model_type_name,
					'permission_level' => $addressbookModel->permission_level
				);
			}
		}

		$tasklistsStmt = Tasklist::model()->find(
				FindParams::newInstance()
					->select('t.acl_id, t.id AS model_id, t.name AS model_name, \''.GO::t("Tasklist", "tasks").'\' AS model_type_name, a.level AS permission_level')
					->join(
						'core_acl_group',
						FindCriteria::newInstance()->addRawCondition('a.aclId', 't.acl_id'),
						'a'
						)
					->join(
						'core_user_group',
						FindCriteria::newInstance()->addRawCondition('a.groupId', 'ug.groupId'),
						'ug',
						'LEFT'
					)
					->criteria(
						FindCriteria::newInstance()
							->addCondition('userId', $userId, '=', 'ug', false)
					)
					->order(self::$_storeSort,self::$_storeDir)
			);
		foreach ($tasklistsStmt as $tasklistModel) {
			if (!isset($response['results'][$tasklistModel->acl_id]) || $response['results'][$tasklistModel->acl_id]['permission_level']<$tasklistModel->permission_level) {
				if (!isset($response['results'][$tasklistModel->acl_id]))
					$response['total']++;
				$response['results'][$tasklistModel->acl_id] = array(
					'model_id' => $tasklistModel->model_id,
					'model_name' => $tasklistModel->model_name,
					'model_type_name' => $tasklistModel->model_type_name,
					'permission_level' => $tasklistModel->permission_level
				);
			}
		}

		$emailAccountsStmt = Account::model()->find(
				FindParams::newInstance()
					->select('t.acl_id, t.id AS model_id, t.username AS model_name, \''.GO::t("E-mail Account", "email").'\' AS model_type_name, a.level AS permission_level')
					->join(
						'core_acl_group',
						FindCriteria::newInstance()->addRawCondition('a.aclId', 't.acl_id'),
						'a'
						)
					->join(
						'core_user_group',
						FindCriteria::newInstance()->addRawCondition('a.groupId', 'ug.groupId'),
						'ug',
						'LEFT'
					)
					->criteria(
						FindCriteria::newInstance()
							->addCondition('userId', $userId, '=', 'ug', false)
					)
					->order(self::$_storeSort,self::$_storeDir)
			);
		foreach ($emailAccountsStmt as $accountModel) {
			if (!isset($response['results'][$accountModel->acl_id]) || $response['results'][$accountModel->acl_id]['permission_level']<$accountModel->permission_level) {
				if (!isset($response['results'][$accountModel->acl_id]))
					$response['total']++;
				$response['results'][$accountModel->acl_id] = array(
					'model_id' => $accountModel->model_id,
					'model_name' => $accountModel->model_name,
					'model_type_name' => $accountModel->model_type_name,
					'permission_level' => $accountModel->permission_level
				);
			}
		}

		$ticketTypesStmt = Type::model()->find(
				FindParams::newInstance()
					->select('t.acl_id, t.id AS model_id, t.name AS model_name, \''.GO::t("Ticket type", "displaypermissions").'\' AS model_type_name, a.level AS permission_level')
					->join(
						'core_acl_group',
						FindCriteria::newInstance()->addRawCondition('a.aclId', 't.acl_id'),
						'a'
						)
					->join(
						'core_user_group',
						FindCriteria::newInstance()->addRawCondition('a.groupId', 'ug.groupId'),
						'ug',
						'LEFT'
					)
					->criteria(
						FindCriteria::newInstance()
							->addCondition('userId', $userId, '=', 'ug', false)
					)
					->order(self::$_storeSort,self::$_storeDir)
			);
		foreach ($ticketTypesStmt as $typeModel) {
			if (!isset($response['results'][$typeModel->acl_id]) || $response['results'][$typeModel->acl_id]['permission_level']<$typeModel->permission_level) {
				if (!isset($response['results'][$typeModel->acl_id]))
					$response['total']++;
				$response['results'][$typeModel->acl_id] = array(
					'model_id' => $typeModel->model_id,
					'model_name' => !empty($typeModel->group_name) ? $typeModel->group_name.': '.$typeModel->model_name : $typeModel->model_name,
					'model_type_name' => $typeModel->model_type_name,
					'permission_level' => $typeModel->permission_level
				);
			}
		}

		$foldersStmt = \GO\Files\Model\Folder::model()->find(
				FindParams::newInstance()
					->select('t.*, t.name AS model_name, \''.GO::t("Folder", "files").'\' AS model_type_name, a.level AS permission_level')
					->join(
						'core_acl_group',
						FindCriteria::newInstance()->addRawCondition('a.aclId', 't.acl_id'),
						'a'
						)
					->join(
						'core_user_group',
						FindCriteria::newInstance()->addRawCondition('a.groupId', 'ug.groupId'),
						'ug',
						'LEFT'
					)
					->criteria(
						FindCriteria::newInstance()
							->addCondition('userId', $userId, '=', 'ug', false)
					)
					->order(self::$_storeSort,self::$_storeDir)
			);
		$foldersArray = array();
		foreach ($foldersStmt as $folderModel) {

			$fullPath = $folderModel->getFullPath();
			if (!empty(GO::config()->displaypermissions_show_all_folders) || strpos($fullPath,'users')===0) {
				if (!isset($response['results'][$folderModel->acl_id]) || $response['results'][$folderModel->acl_id]['permission_level']<$folderModel->permission_level) {
					if (!isset($response['results'][$folderModel->acl_id]))
						$response['total']++;
					$foldersArray[$folderModel->acl_id] = array(
						'model_id' => $folderModel->id,
						'model_name' => $fullPath,
						'model_type_name' => $folderModel->model_type_name,
						'permission_level' => $folderModel->permission_level
					);
				}
			}
		}

		usort($foldersArray,array('\GO\Displaypermissions\Controller\PermissionController','sortByModelName'));

		foreach ($foldersArray as $folderRecord) {
			$response['results'][] = $folderRecord;
			$response['total']++;
		}

		$i = 0;
		foreach ($response['results'] as $aclId => $result) {

			$response['results'][$i++] = $result;
			if ($i!=$aclId)
				unset($response['results'][$aclId]);

		}

		$response['results'] = array_values($response['results']);
		GO::setIgnoreAclPermissions($oldIgnoreAcl);
		echo json_encode($response);
		
	}
	
	public static function sortByModelName($a, $b) {
		if ($a['model_name']==$b['model_name'])
			return 0;
		if (self::$_storeSort=='model_name' && self::$_storeDir=='DESC')
			return $a['model_name']<$b['model_name'];

		return $a['model_name']>$b['model_name'];
	}
	
}
