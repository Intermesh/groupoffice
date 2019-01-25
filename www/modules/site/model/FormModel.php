<?php

/*
 * Copyright Intermesh BV
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 */

/**
 * Models extender from this class have validation options.
 * This can be used to generate a form with validation for Frontend forms
 *
 * @package GO.modules.sites.components
 * @copyright Copyright Intermesh
 * @version $Id FormModel.php 2012-07-04 12:40:32 mdhart $ 
 * @author Michael de Hart <mdehart@intermesh.nl> 
 */

namespace GO\Site\Model;


abstract class FormModel extends \GO\Base\Model {

	protected $requiredAttributes = array();

	/**
	 * Returns the attribute labels.
	 * Attribute labels are mainly used in error messages of validation.
	 * By default an attribute label is generated using {@link generateAttributeLabel}.
	 * This method allows you to explicitly specify attribute labels.
	 *
	 * @return array attribute labels (name=>label)
	 * @see generateAttributeLabel
	 */
	public function attributeLabels() {
		return array();
	}

	/**
	 * Generates a user friendly attribute label.
	 * This is done by replacing underscores or dashes with blanks and
	 * changing the first letter of each word to upper case.
	 * For example, 'department_name' or 'DepartmentName' becomes 'Department Name'.
	 * @param StringHelper $name the column name
	 * @return StringHelper the attribute label
	 */
	public function generateAttributeLabel($name) {
		return ucwords(trim(strtolower(str_replace(array('-', '_', '.'), ' ', preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name)))));
	}

	/**
	 * Returns the text label for the specified attribute.
	 * @param StringHelper $attribute the attribute name
	 * @return StringHelper the attribute label
	 * @see generateAttributeLabel
	 * @see attributeLabels
	 */
	public function getAttributeLabel($attribute) {
		$labels = $this->attributeLabels();
		if (isset($labels[$attribute]))
			return $labels[$attribute];
		else
			return $this->generateAttributeLabel($attribute);
	}

	/**
	 * Returns a value indicating whether the attribute is required.
	 * This is determined by checking if the attribute is associated with a
	 * {@link CRequiredValidator} validation rule in the current {@link scenario}.
	 * @param StringHelper $attribute attribute name
	 * @return boolean whether the attribute is required
	 */
	public function isAttributeRequired($attribute) {
		if (!isset($this->$attribute))
			return false;
		return in_array($attribute, $this->requiredAttributes);
	}
	
	
	public function validate()
	{
		foreach($this->requiredAttributes as $attributeName){

			if(empty($this->$attributeName))
				$this->setValidationError($attributeName, sprintf(\GO::t("Field %s is required"),'"'.$this->getAttributeLabel($attributeName).'"'));
		}

		return !$this->hasValidationErrors();
	}
	
	public function setAttributes($attributes){
		
		foreach($attributes as $property=>$value){
			if(property_exists($this, $property)){
				$this->$property=$value;
			}
		}
	}

}
