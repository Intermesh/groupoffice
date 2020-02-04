<?php

namespace GO\Email\Model;

use GO;
use GO\Base\Db\ActiveRecord;
use go\core\util\StringUtil;

/**
 * Class Label
 *
 * @property int id
 * @property string name
 * @property string flag
 * @property string color
 * @property int account_id
 * @property boolean default
 */
class Label extends ActiveRecord
{

    /**
     * Returns a static model of itself
     *
     * @param String $className
     *
     * @return Label
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    /**
     * Returns the table name
     */
    public function tableName()
    {
        return 'em_labels';
    }

    /**
     * Get count of account labels
     *
     * @param int $account_id Account ID
     *
     * @return int
     */
    public function getLabelsCount($account_id)
    {
        $account_id = (int)$account_id;

        if (!$account_id) {
            return 0;
        }

        $sql = "SELECT count(*) FROM `{$this->tableName()}` WHERE account_id = " . $account_id;
        $stmt = $this->getDbConnection()->query($sql);
        return (int)($stmt->fetchColumn(0));
    }

    /**
     * Delete account labels
     *
     * @param int $account_id Account ID
     *
     * @return bool
     */
    public function deleteAccountLabels($account_id)
    {
        $account_id = (int)$account_id;

        if (!$account_id) {
            return 0;
        }

        $sql = "DELETE FROM `{$this->tableName()}` WHERE account_id = " . $account_id;
        $stmt = $this->getDbConnection()->query($sql);
        return $stmt->execute();
    }

    /**
     * Create default account labels
     *
     * @param int $account_id Account ID
     *
     * @return bool
     * @throws GO\Base\Exception\AccessDenied
     */
    public function createDefaultLabels($account_id)
    {
        $labelsCount = $this->getLabelsCount($account_id);

        if ($labelsCount >= 5) {
            return false;
        }

        if ($labelsCount > 0 && $labelsCount < 5) {
            $this->deleteAccountLabels($account_id);
        }

        $colors = array(
            1 => '7A7AFF',
            2 => '59BD59',
            3 => 'FFBD59',
            4 => 'FF5959',
            5 => 'BD7ABD'
        );

        for ($i = 1; $i < 6; $i++) {
            $label = new Label;
            $label->account_id = $account_id;
            $label->name = 'Label ' . $i;
            $label->flag = '$label' . $i;
            $label->color = $colors[$i];
            $label->default = true;
            $label->save();
        }

        return true;
    }

    protected function init()
    {
        //$this->columns['name']['unique'] = true;
        parent::init();
    }

    protected function beforeSave()
    {
		$maxLabels = isset(\GO::config()->email_max_labels) ? (int)\GO::config()->email_max_labels : 10;
        if ($this->isNew && $this->getLabelsCount($this->account_id) >= $maxLabels) {
            throw new \Exception(sprintf(GO::t("Label's limit reached. The maximum number of labels is %d", "email"), $maxLabels));
        }

        if (!$this->default && $this->isNew) {
            $flag = preg_replace('~[^\\pL0-9_]+~u', '-', $this->name);
            $flag = trim($flag, '-');
            $flag = StringUtil::toAscii($flag);
            $flag = strtolower($flag);
            $this->flag = preg_replace('~[^-a-z0-9_]+~', '', $flag);
        }
        return true;
    }

    /**
     * @param $account_id
     * @return array
     */
    public function getAccountLabels($account_id)
    {
        $labels = array();

        $stmt = Label::model()->findByAttribute('account_id', (int)$account_id);
        while ($label = $stmt->fetch()) {
            $labels[$label->flag] = $label;
        }

        return $labels;
    }
}
