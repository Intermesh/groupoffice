<?php

/**
 * Group-Office
 * 
 * Copyright Intermesh BV. 
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @license AGPL/Proprietary http://www.group-office.com/LICENSE.TXT
 * @link http://www.group-office.com
 * @copyright Copyright Intermesh BV
 * @version $Id ModulePriceController.php 2012-10-15 10:09:46 mdhart $
 * @author Michael de Hart <mdehart@intermesh.nl> 
 * @package GO.servermanager.controller
 */
/**
 * Controller for loading/saving/deleting Module prices
 *
 * @package GO.servermanager.controller
 * @copyright Copyright Intermesh
 * @version $Id ModulePriceController.php 2012-10-15 10:09:46 mdhart $ 
 * @author Michael de Hart <mdehart@intermesh.nl> 
 */

namespace GO\ServerManager\Controller;


class ModulePriceController extends \GO\Base\Controller\AbstractModelController{
	
	protected $model = 'GO\ServerManager\Model\ModulePrice';

	protected function beforeSubmit(&$response, &$model, &$params)
	{
		$model = \GO\ServerManager\Model\ModulePrice::model()->findByPk($params['module_name']);
		if($model == null)
			$model = new \GO\ServerManager\Model\ModulePrice();
	}
}
?>
