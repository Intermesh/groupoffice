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
 * all user records that have permissions in the ACL acl_id (the latter is
 * assumed to be user input $params['model_id']).
 */


namespace GO\Core\Controller;


class AclUserController extends \GO\Base\Controller\AbstractMultiSelectModelController {
	
	protected function init() {
		
	}
	
	/**
	 * The name of the model from where the MANY_MANY relation is called
	 * @return String 
	 */
	public function modelName() {
		return 'GO\Base\Model\User';
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
		return 'user_id';
	}	
	
	protected function getRemoteKey() {
		return 'acl_id';
	}
	
	protected function getExtraDeletePks($params){
		return array($this->getRemoteKey()=>$params['model_id'], 'group_id'=>0);
	}
	
	protected function formatColumns(\GO\Base\Data\ColumnModel $cm) {
		$cm->formatColumn('manage_permission', 'isset($model->level) ? $model->level : ""');
		$cm->formatColumn('name', '$model->name', array(), array('first_name','last_name'));
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
		$response['manage_permission'] = $params['currentUserHasManagePermission'] =  \GO\Base\Model\Acl::hasPermission($currentPermissionLevel,\GO\Base\Model\Acl::MANAGE_PERMISSION);
		$response = array_merge($response,parent::actionSelectedStore($params));
		return $response;
	}
	
	protected function actionSelectNewStore($params) {
		if(\GO::user()->isAdmin())
			\GO::config()->limit_usersearch=0;
//		echo \GO::config()->limit_usersearch;
			// Check for the value "limit_usersearch" in the group-office config file and then add the limit.
		if(!empty(\GO::config()->limit_usersearch)){
			if($params['limit']>\GO::config()->limit_usersearch)
				$params['limit'] = \GO::config()->limit_usersearch;			
			
			$params['start']=isset($params['start']) ? $params['start'] : 0;
			
			if($params['start']+$params['limit']>\GO::config()->limit_usersearch)
				$params['start']=0;
		}
		
		$response = parent::actionSelectNewStore($params);
		
		if(!empty(\GO::config()->limit_usersearch) && $response['total']>\GO::config()->limit_usersearch)
			$response['total']=\GO::config()->limit_usersearch;	
		
		return $response;
	}
	
	protected function beforeAdd(array $params) {
		$addKeys = !empty($params['add']) ? json_decode($params['add']) : array();
		if (!empty($addKeys)) {
			// Only admins may edit the set of linked users.
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
			// Only admins may edit the set of linked users.
			if(!$params['currentUserHasManagePermission'])
					throw new \GO\Base\Exception\AccessDenied();
			
			foreach ($delKeys as $delKey) {
//				if ($delKey==1)
//					throw new \Exception(\GO::t('dontChangeAdminPermissions'));
				
				$aclItem = \GO\Base\Model\Acl::model()->findByPk($params['model_id']);
				if ($aclItem->user_id == $delKey) {
					// Situation: user with id $delKey is owner of ACL with id $params['model_id']
					if(\GO::user()->isAdmin()){
						// Situation: Current user is in root group. Action: set current
						// user as owner of the ACL
						$aclItem->user_id = \GO::user()->id;
						$aclItem->save();
					}else
					{
						throw new \Exception(\GO::t('dontChangeOwnersPermissions'));
					}
				}
			}
		} else {
			return false;
		}
		return true;
	}
	
	protected function beforeUpdateRecord($params, &$record, $model) {
		
		if($record['id']==\GO::user()->id && !\GO::user()->isAdmin()){
			throw new \Exception(\GO::t('dontChangeOwnersPermissions'));
		}
		
		if($model->aclItem->user_id==$record['id']){
			throw new \Exception(\GO::t('dontChangeOwnersPermissions'));
		}
		return true;
	}
}