<?php

namespace GO\Email\Controller;

use GO;
use GO\Email\Model\Label;

use GO\Base\Db\FindCriteria;
use GO\Base\Db\FindParams;

use GO\Base\Controller\AbstractModelController;

class LabelController extends AbstractModelController
{
    protected $model = "GO\\Email\\Model\\Label";

    /**
     * Get store params
     *
     * @param array $params
     * @return \GO\Base\Db\FindParams
     */
    protected function getStoreParams($params)
    {
        $criteria = FindCriteria::newInstance()->addCondition('account_id', (isset($params['account_id'])?$params['account_id']:0));
        return FindParams::newInstance()->criteria($criteria);
    }

    /**
     * Format store record
     *
     * @param $record
     * @param $model
     * @param $store
     * @return mixed
     */
    public function formatStoreRecord($record, $model, $store)
    {
        if (!empty($_POST['forContextMenu'])) {
            $record['text'] = $record['name'];
            $record['xtype'] = 'menucheckitem';
            unset($record['id']);
        }
        return $record;
    }

    /**
     * processStoreDelete
     *
     * @param $store
     * @param $params
     */
    protected function processStoreDelete($store, &$params)
    {
        if (isset($params['delete_keys'])) {

            $deleteRecords = json_decode($params['delete_keys'], true);
            $deleteRecords = array_filter($deleteRecords, 'intval');

            $criteria = FindCriteria::newInstance();
            $criteria->addCondition('default', 0);
            $criteria->addInCondition('id', $deleteRecords);

            $findParams = FindParams::newInstance()->criteria($criteria);
            $stmt = Label::model()->find($findParams);

            $deleteRecords = [];
            while ($label = $stmt->fetch()) {
                $deleteRecords[] = $label->getPk();
            }

            if (!count($deleteRecords)) {
                $params['delete_keys'] = '[]';
            } else {
                $params['delete_keys'] = json_encode($deleteRecords);
            }
        }

        $store->processDeleteActions($params, $this->model);

		// The code below will trigger an error if both conditions are true, since response is protected
	    // TODO: Remove entirely after a few months. The deleteSuccess is set anyway in the Store class
//        if (isset($params['delete_keys']) && !count(json_decode($params['delete_keys']))) {
//            $store->response['deleteSuccess'] = true;
//        }
    }
}
