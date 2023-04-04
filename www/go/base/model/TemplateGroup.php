<?php
	/*
	 * Copyright Intermesh BV.
	 *
	 * This file is part of Group-Office. You should have received a copy of the
	 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
	 *
	 * If you have questions write an e-mail to info@intermesh.nl
	 *
	 */
namespace GO\Base\Model;

/**
 * A group for a Email template
 *
 * @property string $name
 * @property int $id
 */
class TemplateGroup extends \GO\Base\Db\ActiveRecord
{
	public function tableName(){
		return "go_template_group";
	}

}