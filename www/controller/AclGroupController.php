<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * @author WilmarVB <wilmar@intermesh.nl>
 */

/**
 * Purpose of this class: handle client requests to collect and edit the set of
 * all user group records that have permissions in the ACL acl_id
 * (the latter is assumed to be user input $params['model_id']).
 */


namespace GO\Core\Controller;


class AclGroupController extends \GO\Base\Controller\AbstractMultiSelectModelController {
	
	/**
	 * The name of the model we are showing and adding to the other model.
	 * 
	 * eg. When selecting calendars for a user in the sync settings this is set to \GO\Calendar\Model\Calendar
	 */
	public function modelName() {
		return 'GO\Base\Model\Group';
	}
	
	/**
	 * Returns the name of the model that handles the MANY_MANY relation.
	 * @return String 
	 */
	public function linkModelName() {
		return 'GO\Base\Model\AclUsersGroups';
	}
	
	/**
	 * The name of the field in the linkModel where the key of the current model is defined.
	 * @return String
	 */
	public function linkModelField() {
		return 'group_id';
	}	
	
	protected function getRemoteKey() {
		return 'acl_id';
	}
	
	protected function getExtraDeletePks($params){
		return array($this->getRemoteKey()=>$params['model_id'], 'user_id'=>0);
	}
	
	protected function formatColumns(\GO\Base\Data\ColumnModel $cm) {
		$cm->formatColumn('manage_permission', 'isset($model->level) ? $model->level : ""');
		return parent::formatColumns($cm);
	}
	
	/**
	 * The client CAN use the information contained in
	 * $response['manage_permission'] to make decisions such as whether or not to
	 * allow the current user to edit the set of groups in the store.
	 * @param Array $params Client input parameters
	 * @return $response for the client. 
	 */
	protected function actionSelectedStore($params) {
		$currentPermissionLevel = \GO\Base\Model\Acl::getUserPermissionLevel($params['model_id'],\GO::user()->id);
		$response['manage_permission'] = $params['currentUserHasManagePermission'] = \GO\Base\Model\Acl::hasPermission($currentPermissionLevel,\GO\Base\Model\Acl::MANAGE_PERMISSION);
		$response = array_merge($response,parent::actionSelectedStore($params));
		return $response;
	}

	protected function beforeAdd(array $params) {
		$addKeys = !empty($params['add']) ? json_decode($params['add']) : array();
		if (!empty($addKeys)) {
			// Only admins may edit the set of linked groups.
			if(!$params['currentUserHasManagePermission'])
				throw new \GO\Base\Exception\AccessDenied();
		} else {
			return false;
		}
		return true;
	}
	
	protected function beforeDelete(array $params) {
		$delKeys = !empty($params['delete_keys']) ? json_decode($params['delete_keys']) : array();
		if (!empty($delKeys)) {
			// Only admins may edit the set of linked groups.
			if(!$params['currentUserHasManagePermission'])
					throw new \GO\Base\Exception\AccessDenied();
			foreach ($delKeys as $delKey) {
				if ($delKey==\GO::config()->group_root) {
					throw new \Exception(\GO::t('dontChangeAdminsPermissions'));
				}
			}
		} else {
			return false;
		}
		return true;
	}
	
	protected function beforeUpdateRecord($params, &$record, $model) {
		if ($record['id']==\GO::config()->group_root)
			throw new \GO\Base\Exception\AccessDenied();
		return true;
	}
}