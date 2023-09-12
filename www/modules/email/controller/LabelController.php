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

}
