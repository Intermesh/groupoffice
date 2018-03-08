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
 * @version $Id AutomaticInvoice.php 2012-09-06 14:29:45 mdhart $
 * @author Michael de Hart <mdhart@intermesh.nl> 
 * @package GO.servermanager.models
 */
/**
 * This objec tis responsible for creating invoices in the billing module based
 * on specifications in the servermanagers Installations
 *
 * @package GO.servermanager.models
 * @copyright Copyright Intermesh
 * @version $Id AutomaticInvoice.php 2012-09-06 14:29:45 mdhart $ 
 * @author Michael de Hart <mdhart@intermesh.nl> 
 * 
 * @property integer $id
 * @property boolean $enable_invoicing
 * @property double $discount_price
 * @property string $discount_description
 * @property double $discount_percentage
 * @property integer $invoice_timespan amount of months to pass before next invoice
 * @property integer $next_invoice_time unixtimestamp when next invoice needs to be created
 * @property integer $trial_days amount of days how long the customer can try out new users or modules
 * @property string $customer_name invoicing name
 * @property string $customer_address invoicing address
 * @property string $customer_address_no customers housenumber
 * @property string $customer_zip invoiceing zip
 * @property string $customer_state invoicing state
 * @property string $customer_country invoicing country
 * @property string $customer_vat invoice VAT
 * @property string $customer_city The customers city
 * @property integer $installation_id foreingkey of installation
 */

namespace GO\ServerManager\Model;


class AutomaticInvoice extends \GO\Base\Db\ActiveRecord {
	
	public function tableName()
	{
		return 'sm_automatic_invoices';
	}
	
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function init()
	{
		$this->columns['next_invoice_time']['gotype'] = 'unixtimestamp';
	}
	
	public function beforeSave()
	{
		if($this->isNew) //set next_invoice time of an new object to end of trial period
		{
			$this->next_invoice_time = \GO\Base\Util\Date::dateTime_add(strtotime('today'), 0, 0, 0, $this->installation->trial_days);
		}
		return true;
	}
	
	public function relations()
	{
		return array(
				'installation'=>array('type'=>self::BELONGS_TO, 'model'=>'GO\Servermanager\Model\Installation', 'field'=>'installation_id'),
		);
	}
	
	/**
	 * See if there is login information to connect to a groupoffice server where the
	 * Billing module is installed and see if remote invoicing is possible
	 * @return boolean true is remote invoicing if possible on the provided information 
	 */
	public static function canConnect()
	{
		//echo \GO::config()->servermanager_billing_bookid;
		$host = \GO::config()->servermanager_billing_host;
		$username = \GO::config()->servermanager_billing_user;
		$password = \GO::config()->servermanager_billing_pass;
		if(!isset($host) || !isset($username) || !isset($password))
			return false;
		
		//connection with login
		$c = new \GO\Base\Util\HttpClient();
		$c->groupofficeLogin($host, $username, $password);
		$response = $c->request($host.'?r=billing/order/remoteAutoInvoice', array(
				'data'=>json_encode(array('test'=>true))
		));
		
		$result = json_decode($response,true);
		return $result['success'];
	}
	
	/**
	 * get the cost for the amount of users in the installation based in staffel prices
	 * @return double price for amount of users
	 */
	protected function _getUserPrice()
	{
		$params = \GO\Base\Db\FindParams::newInstance()->order('max_users','ASC');
		$staffelprices = UserPrice::model()->find($params);
		$uprice = 0;
		foreach($staffelprices as $price)
		{
			if($price->max_users <= count($this->installation->getPayedUsers()))
				$uprice = $price->price_per_month;
			else
				break;
		}
		return $uprice;
	}
	
	private $_module_prices;
	/**
	 * Get the price of a module from the sm_module_prices table
	 * @param StringHelper $module_name the name of the module directory is found in the database
	 * @return double price_per_month as specified in the database 
	 */
	protected function _getModulePrice($module_name)
	{
		if(!isset($this->_module_prices))
			$this->_module_prices = ModulePrice::model()->find()->fetchAll();

		foreach($this->_module_prices as $modulePrice)
		{
			if($modulePrice->module_name == $module_name)
				return $modulePrice->price_per_month;
		}
		return 0;
	}
	
	/**
	 * Return data in MBs of extra data use 
	 * Returns 0 if there is no installation or nu current usage for an installation
	 * @return integer extra mbs used
	 */
	protected function _getExtraMbsUsed()
	{
		$extra_mbs = 0;
		if(isset($this->installation) && isset($this->installation->currentusage))
		{
			$mbs_used = $this->installation->currentusage->getTotalUsage()/1024/1024;
			$extra_mbs = $mbs_used - (\GO::config()->get_setting('sm_mbs_included') * $this->installation->currentusage->count_users);
		}
		return $extra_mbs;
	}
	
	/**
	 * Convert MBs to GBs and ceil up to hole numbers
	 * @return type 
	 */
	protected function _getExtraGbsUsed()
	{
		return ceil($this->_getExtraMbsUsed()/1024);
	}
	
	/**
	 * Return the prices of the extra MBs that are used
	 * The price is the config is per gigabyte so we need to devide by 1024
	 * @return double price
	 */
	protected function _getExtraGbsUsedPrice()
	{
		return $this->_getExtraGbsUsed() * \GO::config()->get_setting('sm_price_extra_gb');
	}
	
	/**
	 * This method is responsabgle for creating an new invoice in the billin module
	 * We send JSONdata to antoher server using CURL
	 * @return boolean true if an invoice was created successfull
	 */
	public function createOrderData()
	{
			$userCountPrice = $this->_getUserPrice();

			//create jsondata to send
			$order = array();
			$order['book_id'] = isset(\GO::config()->servermanger_billing_bookid) ? \GO::config()->servermanger_billing_bookid : 2;
			$order['customer_address'] = $this->customer_address;
			$order['customer_address_no'] = $this->customer_address_no;
			$order['customer_city'] = $this->customer_city;
			$order['customer_contact_name'] = $this->customer_name;
			$order['customer_contact_to'] = $this->customer_name;
			$order['customer_country'] = $this->customer_country;
			$order['customer_email'] = $this->installation->admin_email;
			$order['customer_vat_no'] = $this->customer_vat;
			$order['customer_state'] = $this->customer_state;
			$order['customer_zip'] = $this->customer_zip;
			$order['customer_name'] = $this->customer_name;
			$order['reference'] = 'GroupOffice hosted server';
			$order['items'] = array();
			// Add amount of Users price
			$order['items'][] = array(
					'description'=>'Hosted Groupoffice ('.count($this->installation->getPayedUsers()).' users)', 
					'unit_price'=>$userCountPrice, 
					'amount'=>$this->invoice_timespan,
					'discount'=>$this->discount_percentage,
			);
			// Add payed modules prices to order
			foreach($this->installation->modules as $module)
			{
				$price = $this->_getModulePrice($module->name);
				if($price > 0 && !$module->isTrial())
				{
					$order['items'][] = array(
							'description'=>\GO::t('name', $module->name). " Module",
							'unit_price'=>$price, 
							'amount'=>$this->invoice_timespan,
							'discount'=>$this->discount_percentage,
					);
				}
			}
			// Add discount to order
			if(!empty($this->discount_price) && $this->discount_price > 0)
			{
				$order['items'][] = array(
						'description'=>$this->discount_description, 
						'unit_price'=>0-$this->discount_price, 
						'amount'=>$this->invoice_timespan,
						'discount'=>$this->discount_percentage,
						);
			}
			// Add extra GBs price
			if($this->_getExtraGbsUsed() > 0)
			{
				$order['items'][] = array(
						'description'=>'Extra diskspace used ('.$this->_getExtraGbsUsed().'GB)',
						'unit_price'=>$this->_getExtraGbsUsedPrice(),
						'amount'=>$this->invoice_timespan,
						'discount'=>$this->discount_percentage,
				);
			}

			return $order;
	}
	
	/**
	 * This will post a new order to a billing module
	 * @return boolean true when every went well
	 * @throws Exception when we cant connect to billing or when we cant save new invoice time
	 */
	public function sendOrder()
	{
		//if we can connect to the billing module
		if(self::canConnect())
		{
			$orderData = $this->createOrderData();
			$host = \GO::config()->servermanager_billing_host;
			$username = \GO::config()->servermanager_billing_user;
			$password = \GO::config()->servermanager_billing_pass;
		  //send the data to billing module using curl
			$c = new \GO\Base\Util\HttpClient();
			$c->groupofficeLogin($host, $username, $password);
			$response = $c->request($host.'?r=billing/order/remoteAutoInvoice', array(
					'data'=>json_encode($orderData)
			));
			//return the response status curl returns (true if invoice was created)
			$result = json_decode($response,true);

			if($result['success'])
			{
				$this->next_invoice_time = $this->calcNextInvoiceTime();
				if(!$this->save())
					throw new Exception('Could not save last invoice time');
				return true;
			}
			else
				return false;
		} else
			throw new Exception('Could not connect to the billing host');
	}
		
	/**
	 * send a partial order for new users/modules when trial periode is over 
	 */
	public function sendPartialOrder()
	{
		//TODO
	}
	
	/**
	 * Add the invoice timespan to the next_invoice_time
	 * @return int unixtimestamp with next_invoice_time
	 */
	protected function calcNextInvoiceTime()
	{
		return \GO\Base\Util\Date::date_add($this->next_invoice_time, 0, $this->invoice_timespan);
		//$timestring = "+".$this->invoice_timespan." month";
		//return strtotime($timestring,$this->next_invoice_time);
	}
	
	/**
	 * A new order should be created when:
	 * - next_invoice_time has been passed
	 */
	public function shouldCreateOrder()
	{
		return time() >= $this->next_invoice_time;
	}
}
?>