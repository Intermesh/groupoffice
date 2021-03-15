<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: config.class.inc.php 7687 2011-06-23 12:00:34Z mschering $
 * @copyright Copyright Intermesh BV.
 * @package GO.base.util 
 */

namespace GO\Base\Util;

use GO;
	
/**
 * Common utilities
 * 
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: config.class.inc.php 7687 2011-06-23 12:00:34Z mschering $
 * @copyright Copyright Intermesh BV.
 * @package GO.base.util 
 */


class Common {
	/**
	 * Check if this is a windows server
	 * 
	 * @return boolean
	 */
	public static function isWindows(){
		return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
	}

	/**
	 * Get a link to Google maps for a given address
	 * 
	 * @param StringHelper $address
	 * @param StringHelper $address_no
	 * @param StringHelper $city
	 * @param StringHelper $country
	 * @return StringHelper 
	 */
	public static function googleMapsLink($address, $address_no, $city, $country) {
		$l = '';

		if (!empty($address) && !empty($city)) {
			$l .= $address;
			if (!empty($address_no)) {
				$l .= ' ' . $address_no . ', ' . $city;
			} else {
				$l .= ', ' . $city;
			}

			if (!empty($country)) {
				$l .= ', ' . $country;
			}

			return 'http://maps.google.com/maps?q=' . urlencode($l);
		} else {
			return '';
		}
	}

	/**
	 * Format an address in the format that belongs to the give country ISO code.
	 * 
	 * @param StringHelper $isoCountry
	 * @param StringHelper $address
	 * @param StringHelper $address_no
	 * @param StringHelper $zip
	 * @param StringHelper $city
	 * @param StringHelper $state
	 * @return StringHelper 
	 */
	public static function formatAddress($isoCountry, $address, $address_no,$zip,$city, $state) {
		
		if(empty($address) && empty($city) && empty($state)){
			return "";
		}

		$countries = \GO::t('countries');
		$strCountry = isset($countries[$isoCountry]) ? $countries[$isoCountry] : $isoCountry;

		require(GO::config()->root_path . 'language/addressformats.php');

		$format = isset($af[$isoCountry]) ? $af[$isoCountry] : $af['default'];

		$format= str_replace('{address}', $address, $format);
		$format= str_replace('{address_no}', $address_no, $format);
		$format= str_replace('{city}', $city, $format);
		$format= str_replace('{zip}', $zip, $format);
		$format= str_replace('{state}', $state, $format);
		$format= str_replace('{country}', $strCountry, $format);

		return preg_replace("/(\r\n)+|(\n|\r)+/", "\n", $format);
	}

	
	public static function countUpgradeQueries($updatesFile){
		$count=0;
		if(file_exists($updatesFile))
		{
			require($updatesFile);
			
			if(isset($updates)){
				foreach($updates as $timestamp=>$queries)
					$count+=count($queries);
			}
		}
		
		return $count;		
	}
}
