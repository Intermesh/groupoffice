<?php


namespace GO\Email\Controller;


class AliasController extends \GO\Base\Controller\AbstractModelController {

	protected $model = 'GO\Email\Model\Alias';

	protected function beforeStore(&$response, &$params, &$store) {

		$store->setDefaultSortOrder('name');

		return parent::beforeStore($response, $params, $store);
	}

	protected function getStoreParams($params) {
		
		if(empty($params['account_id'])){
			$findParams = \GO\Base\Db\FindParams::newInstance()
							->select('t.*')
							->joinModel(array(
									'model' => 'GO\Email\Model\AccountSort',
									'foreignField' => 'account_id', //defaults to primary key of the remote model
									'localField' => 'account_id', //defaults to primary key of the model
									'type' => 'LEFT',
									'tableAlias'=>'sor',
									"criteria"=>  \GO\Base\Db\FindCriteria::newInstance()->addCondition('user_id', \GO::user()->id,"=",'sor')
							))
							->ignoreAdminGroup()
							->permissionLevel(\GO\Base\Model\Acl::CREATE_PERMISSION)
							->order(array('order','default'), array('DESC','DESC'));
		}else
		{
			$findParams = \GO\Base\Db\FindParams::newInstance();
			$findParams->getCriteria()->addCondition("account_id", $params['account_id'])->addCondition("default", 1,'!=');
		}

		return $findParams;
	}

	public function formatStoreRecord($record, $model, $store) {
		
		$r = new \GO\Base\Mail\EmailRecipients();
		$r->addRecipient($model->email, $model->name);
		$record['from'] = (string) $r;
		$record['html_signature'] = \GO\Base\Util\StringHelper::text_to_html($model->signature);
		$record['plain_signature'] = $model->signature;
		$record['signature_below_reply'] = $model->account->signature_below_reply;
		$record['template_id']=0;
		
		unset($record['signature']);
		
		$defaultAccountTemplateModel = \GO\Email\Model\DefaultTemplateForAccount::model()->findByPk($model->account_id);
			if($defaultAccountTemplateModel && $defaultAccountTemplateModel->template_id){
				$record['template_id']=$defaultAccountTemplateModel->template_id;
			}else{
				$defaultUserTemplateModel = \GO\Email\Model\DefaultTemplate::model()->findByPk(\GO::user()->id);
				if(!$defaultUserTemplateModel){
					$defaultUserTemplateModel= new \GO\Email\Model\DefaultTemplateForAccount();
					$defaultUserTemplateModel->account_id = $model->account_id;
					$defaultUserTemplateModel->save();
				}
				$record['template_id']=$defaultUserTemplateModel->template_id;
			}
		

		return parent::formatStoreRecord($record, $model, $store);
	}

}
