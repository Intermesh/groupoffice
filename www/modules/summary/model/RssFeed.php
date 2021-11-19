<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @property int $user_id
 * @property string $title
 * @property string $url
 * @property boolean $summary
 */


namespace GO\Summary\Model;


class RssFeed extends \GO\Base\Db\ActiveRecord {
	
	public static function model($className=__CLASS__)
	{	
		return parent::model($className);
	}
	
	public function tableName(){
		return 'su_rss_feeds';
	}
	
	protected function init() {
		
		$this->columns['url']['gotype']='html';
		return parent::init();
	}

	public function validate()
	{
		$parsed = parse_url($this->url, PHP_URL_HOST);
		$address = gethostbyname($parsed);

		if(!filter_var($address, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
			$this->setValidationError('url', go()->t("Private URL's are not allowed"));
			return parent::validate();
		}

		if (function_exists('curl_init')) {
			$httpclient = new \GO\Base\Util\HttpClient();
			$xml = $httpclient->request($this->url);
		} else {
			if (!\GO\Base\Fs\File::checkPathInput($this->url))
				throw new \Exception("Invalid request");

			$xml = @file_get_contents($this->url);
		}

		if (!$xml || !self::isRSS($xml)){

			$this->setValidationError('url', go()->t('The supplied URL is not an RSS feed'));
		}

		return parent::validate();
	}

	public static function isRSS($string) {
		return preg_match('/<rss.*<\/rss>/i', str_replace(["\r","\n"],'', $string)) ||
		preg_match('/<rdf:RDF.*<\/rdf:RDF>/i', str_replace(["\r","\n"],'', $string));
	}

}
