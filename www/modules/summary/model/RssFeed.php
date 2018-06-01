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
	
}
