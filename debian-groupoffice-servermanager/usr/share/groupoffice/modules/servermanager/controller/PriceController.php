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
 * @version $Id PricesController.php 2012-08-29 15:17:36 mdhart $
 * @author Michael de Hart <mdehart@intermesh.nl> 
 * @package GO.ServerManager.Controllers
 */
/**
 * This controller handles saving the table for user/prices rate and space usage 
 *
 * @package GO.servermanager.controller
 * @copyright Copyright Intermesh
 * @version $Id PricesController.php 2012-08-29 15:17:36 mdhart $
 * @author Michael de Hart <mdehart@intermesh.nl> 
 */

namespace GO\Servermanager\Controller;


class PriceController extends \GO\Base\Controller\AbstractController
{

	/**
	 * UNTESTED: Loading the go_settings mbs_include and extra_mbs for prices config
	 * @return array json response 
	 */
	protected function actionLoad($params)
	{
		//\GO::config()->save_setting('sm_price_extra_gb', 2.5);
		return array(
			'success'=>true,
			'data' => array(
				'mbs_included'=>\GO::config()->get_setting('sm_mbs_included'), 
				'price_extra_gb'=>\GO::config()->get_setting('sm_price_extra_gb'),
			),
		);
	}
	
	/**
	 * UNTESTED: submit the userpirces, moduleprices, and quota mbs included and extra costs
	 * @param array $params the $_REQUEST object
	 */
	protected function actionSubmit($params)
	{
		if(isset($params['mbs_included']) && isset($params['price_extra_gb']))
		{
			\GO::config()->save_setting('sm_mbs_included', $params['mbs_included']);
			\GO::config()->save_setting('sm_price_extra_gb', $params['price_extra_gb']);
			
			return array('success'=>true);
		}
		else
			return array('success'=>false, 'feedback'=>'Posted wrong parameters');
		
	}
}

