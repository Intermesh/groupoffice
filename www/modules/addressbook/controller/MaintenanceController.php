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
 * The Portlet controller
 *
 * @package GO.modules.Addressbook.controller
 * @version $Id: PortletController.php 16757 2014-01-30 10:54:43Z mschering $
 * @copyright Copyright Intermesh BV.
 * @author Michael de Hart <mdhart@intermesh.nl>
 */
namespace GO\Addressbook\Controller;

class MaintenanceController extends \GO\Base\Controller\AbstractController{
	
	public function actionIsoCountryToText() {
		$countries = \GO::language()->getCountries();
		
		$iso = null;
		$text = null;
		
		$sql1 = "UPDATE ab_contacts set country=:text where country=:iso";
		$stmt1 = \GO::getDbConnection()->prepare($sql1);
		$stmt1->bindParam(':text', $text);
		$stmt1->bindParam(':iso', $iso);
		
		$sql2 = "UPDATE ab_companies set country=:text where country=:iso";
		$stmt2 = \GO::getDbConnection()->prepare($sql2);
		$stmt2->bindParam(':text', $text);
		$stmt2->bindParam(':iso', $iso);
		
		$sql3 = "UPDATE ab_companies set post_country=:text where post_country=:iso";
		$stmt3 = \GO::getDbConnection()->prepare($sql3);
		$stmt3->bindParam(':text', $text);
		$stmt3->bindParam(':iso', $iso);
		
		foreach($countries as $iso => $text) {
			$stmt1->execute();
			$stmt2->execute();
			$stmt3->execute();				
		}
		
		echo "DONE";
	}
	
}
