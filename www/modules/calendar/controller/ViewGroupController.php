<?php
/*
 * Copyright Intermesh BV.
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * The View controller
 *
 * @package GO.modules.Calendar
 * @version $Id$
 * @copyright Copyright Intermesh BV.
 * @author Michael de Hart mdhart@intermesh.nl
 */


namespace GO\Calendar\Controller;


class ViewGroupController extends \GO\Base\Controller\AbstractMultiSelectModelController {

	//protected $model = 'GO\Calendar\Model\ViewGroup';
	
	//protected function formatColumns(\GO\Base\Data\ColumnModel $columnModel) {
	//	$columnModel->formatColumn('group_name','$model->group->name',array(),'group_id');
	//	return parent::formatColumns($columnModel);
	//}

  public function linkModelField() {
    return 'group_id';
  }

  public function linkModelName() {
    return 'GO\Calendar\Model\ViewGroup';
  }

  public function modelName() {
    return 'GO\Base\Model\Group';
  }
	
}
