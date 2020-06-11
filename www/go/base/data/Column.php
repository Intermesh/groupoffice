<?php
/*
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */

/**
 * Class for the column object
 * 
 * 
 * @package GO.base.data
 * @version $Id: Column.php 7607 2011-11-16 09:17:42Z wsmits $
 * @copyright Copyright Intermesh BV.
 * @author Wesley Smits <wsmits@intermesh.nl> 
 * 
 */


namespace GO\Base\Data;


class Column {
	
	/**
	 * The dataindex name of this column
	 *
	 * @var String 
	 */
	private $_dataindex;
	
	/**
	 * The label for this column (Column Header)
	 *
	 * @var String 
	 */
	private $_label;
	
	/**
	 * The index on which the column will be sorted
	 * 
	 * @var int 
	 */
	private $_sortIndex;
	
	/**
	 * The string with the information how this column will show the data.
	 * 
	 * @var String 
	 */
	private $_format;
	
	/**
	 * Extra options that can be passed to this column
	 * 
	 * @var Array 
	 */
	private $_extraVars = array();
	
	/**
	 * The sortAlias for this column
	 * 
	 * @var String 
	 */
	private $_sortAlias;
	
	/**
	 * TODO: Create this description
	 * @var type 
	 */
	private $_modelFormatType;
	
	/**
	 * Returns a new instance of a column with the given values
	 * 
	 * @param StringHelper $dataindex
	 * @param StringHelper $label
	 * @param int $sortIndex
	 * @return Column 
	 */
	public static function newInstance($dataindex, $label='', $sortIndex=0){
		return new self($dataindex, $label, $sortIndex);
	}
	
	/**
	 * Constructor for this class
	 * 
	 * @param StringHelper $dataindex
	 * @param StringHelper $label
	 * @param int $sortIndex 
	 */
	public function __construct($dataindex, $label='', $sortIndex=0, $sortAlias=false){
		$this->_dataindex = $dataindex;
		$this->_label = !empty($label) ? $label : $dataindex;
		$this->_sortIndex = $sortIndex;
		
		if($sortAlias)
			$this->_sortAlias=$sortAlias;
	}
	
	/**
	 * TODO: Create this description
	 * 
	 * @param type $type 
	 */
	public function setModelFormatType($type){
		$this->_modelFormatType=$type;
	}
	
	public function getModelFormatType() {
		return $this->_modelFormatType;
	}
	
	/**
	 * Returns the sort index of this column
	 * 
	 * @return int 
	 */
	public function getSortIndex(){
		return $this->_sortIndex;
	}
	
	/**
	 * Set the sort index of this column
	 * 
	 * @param int $index  
	 */
	public function setSortIndex($index){
		$this->_sortIndex=$index;
	}
	
	/**
	 * Returns the sortAlias for this column, of no sortAlias is provided the 
	 * Dataindex will be returned.
	 * 
	 * @return String 
	 */
	public function getSortColumn(){
		return isset($this->_sortAlias) ? $this->_sortAlias : $this->getDataIndex();
	}
	
	/**
	 * Give the format for this column to display it's data.
	 * 
	 * @param String $format
	 * @param Array $extraVars
	 * @return Column 
	 */
	public function setFormat($format, $extraVars=array()){
		$this->_format = $format;
		$this->_extraVars=$extraVars;
		return $this;
	}
	
	/**
	 * Give this column a sortalias.
	 * Returns the whole column object
	 * 
	 * @param String $sortAlias
	 * @return Column 
	 */
	public function setSortAlias($sortAlias){
		$this->_sortAlias = $sortAlias;
		return $this;
	}
	
	/**
	 * Get the current label for this column
	 * 
	 * @return StringHelper 
	 */
	public function getLabel(){
		return $this->_label;
	}
	
	/**
	 * Get the dataindex for this column
	 * 
	 * @return StringHelper 
	 */
	public function getDataIndex(){
		return $this->_dataindex;
	}
	
	/**
	 * TODO: Create this description
	 * 
	 * @param type $model
	 * @return StringHelper 
	 */
	public function render($model) {

		//$array = $model->getAttributes($this->_modelFormatType);

		/**
		 * The extract function makes the array keys available as variables in the current function scope.
		 * we need this for the eval functoion.
		 * 
		 * example $array = array('name'=>'Pete'); becomes $name='Pete';
		 * 
		 * In the column definition we can supply a format like this:
		 * 
		 * 'format'=>'$name'
		 */
		//extract($array);

		extract($this->_extraVars);

		if (isset($this->_format)) {
			
			if(is_string($this->_format)) {

				$result = '';
				if($this->_format!='')
					eval('try{$result=' . $this->_format . ';}catch(Exception $e){};');

				if($this->_modelFormatType == 'html'){
					$result = \GO\Base\Util\StringHelper::encodeHtml($result);
				}

				return $result;
			} else {
				return call_user_func_array($this->_format, [$model, $this->_extraVars]);
			}
		} elseif (isset($model->{$this->_dataindex})) {
		  if($model instanceof \GO\Base\Db\ActiveRecord){
				return $model->getAttribute($this->_dataindex,$this->_modelFormatType);
			}
		  return $model->{$this->_dataindex};
		} else {
			return "";
		}
	}
	
}
