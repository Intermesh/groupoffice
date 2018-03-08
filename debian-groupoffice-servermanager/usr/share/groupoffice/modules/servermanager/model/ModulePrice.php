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
 * @version $Id ModulePrice.php 2012-10-09 11:31:31 mdhart $
 * @author Michael de Hart <mdehart@intermesh.nl> 
 * @package GO.servermanager.model
 */
/**
 * This model serves for loading and saving data from sm_module_prices table
 *
 * @package GO.servermanger.model
 * @copyright Copyright Intermesh
 * @version $Id ModulePrice.php 2012-10-09 11:31:31 mdhart $ 
 * @author Michael de Hart <mdehart@intermesh.nl> 
 * 
 * @property string $module_name the directory name of the module
 * @property double $price_per_month the price the module costs per month
 */

namespace GO\ServerManager\Model;


class ModulePrice extends \GO\Base\Db\ActiveRecord
{
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'sm_module_prices';
	}
	
	public function primaryKey()
	{
		return 'module_name';
	}
}