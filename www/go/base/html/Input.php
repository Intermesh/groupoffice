<?php


namespace GO\Base\Html;


class Input {
	
	public static $errors;
	
	public static $id=1;

	protected $attributes;
	
	protected $isPosted=false;
	
	/**
	 * Set error message for a form input field
	 */
	public static function setError($inputName, $errorMsg){
		\GO::session()->values['formErrors'][$inputName]=$errorMsg;
	}
	
	/**
	 * Check if any input has errors.
	 * 
	 * @return boolean 
	 */
	public static function hasErrors(){
		return !empty(\GO::session()->values['formErrors']);
	}

	/**
	 *
	 * @param type $inputName
	 * @return string 
	 */
	public static function getErrorMsg($inputName){
		return isset(\GO::session()->values['formErrors'][$inputName]) ? \GO::session()->values['formErrors'][$inputName] : false;
	}
	
	public static function getError($inputName){
		$errorMsg = self::getErrorMsg($inputName);
		if($errorMsg){
			return '<div class="errortext">'.$errorMsg.'</div>';
		}else
		{
			return '';
		}
	}
	
	public static function printError($inputName){
		echo self::getError($inputName);
	}
	
	public static function render($attributes,$echo=true) {
		$i = new self($attributes);
		if($echo)
			echo $i->getHtml();
		else
			return $i->getHtml();
	}

	public function __construct($attributes) {
		$this->attributes = $attributes;
		
		if(!empty($this->attributes['model'])){
			// Set the model properties
			$columns = $this->attributes['model']->getColumns();
			
			if(!empty($columns[$this->attributes['name']]['customfield']))
				$m = $columns[$this->attributes['name']]['customfield'];
			else
				$m =  $columns[$this->attributes['name']];
			
			if(!isset($this->attributes['required']))
				$this->attributes['required'] = !empty($m->required)?true:false;
			
			if(!isset($this->attributes['label']))
				$this->attributes['label'] = $this->attributes['model']->getAttributeLabel($this->attributes['name']);
			
			if(!isset($this->attributes['value']))
				$this->attributes['value'] = $this->attributes['model']->getAttribute($this->attributes['name']);
		}
		
		if(!empty($this->attributes['label'])){
			if(!empty($this->attributes['required'])){
				if (!empty($this->attributes['requiredClass']))
					$reqClassString = ' '.$this->attributes['requiredClass'];
				else
					$reqClassString = '';
				$this->attributes['label'] .= '<span class="required'.$reqClassString.'">*</span>';
			}		
		}
		
		if (!isset($this->attributes['value']))
			$this->attributes['value'] = '';

		if (!isset($this->attributes['extra']))
			$this->attributes['extra'] = '';

		if (!isset($this->attributes['class']))
			$this->attributes['class'] = 'input';

		if (!isset($this->attributes['renderContainer']))
			$this->attributes['renderContainer'] = true;

		$this->attributes['required'] = empty($this->attributes['required']) ? false : true;
		
		$this->init();
		
		if (empty($this->attributes['forget_value'])) {
			if ($pos = strpos($this->attributes['name'], '[')) {
				$key1 = substr($this->attributes['name'], 0, $pos);
				$key2 = substr($this->attributes['name'], $pos + 1, -1);

				$this->isPosted = isset($_POST[$key1][$key2]);
				$this->attributes['value'] = isset($_POST[$key1][$key2]) ? $_POST[$key1][$key2] : $this->attributes['value'];
			} else {
				
				if(empty($this->attributes['type']) || $this->attributes['type'] != 'checkbox'){
					$this->attributes['value'] = isset($_POST[$this->attributes['name']]) ? $_POST[$this->attributes['name']] : $this->attributes['value'];
				}
				$this->isPosted = isset($_POST[$this->attributes['name']]);
			}
		}
		
//		if($this->isPosted && empty($this->attributes['value']) && $this->attributes['required'])
//			self::setError ($this->attributes['name'], "This field is required");
		
		if(empty($this->attributes['value']) && !empty($this->attributes['empty_text'])){
			$this->attributes['value'] = $this->attributes['empty_text'];
		}
		if(empty($this->attributes['type'])){
			$this->attributes['type']='text';
		}
		
		if(!isset($this->attributes['id'])){
			self::$id++;
			$this->attributes['id']='form_'.self::$id;
		}
		
		//$this->init();
		
	}
	
	protected function init(){
		return true;
	}
	
	protected function renderHidden(){
		
		$html = '<input id="'.$this->attributes['id'].'" class="'.$this->attributes['class'].'" type="'.$this->attributes['type'].'" name="'.$this->attributes['name'].'" value="'.htmlspecialchars($this->attributes['value'],ENT_COMPAT,'UTF-8').'" '.$this->attributes['extra'];
		$html .= ' />';
		return $html;
	}
	
	protected function renderNormalInput(){
		
		$html = '';
		
		if (!empty($this->attributes['inputInDiv'])) {
			$inputDivClassString = "input-container";
			if (!empty($this->attributes['inputDivClass']))
				$inputDivClassString .= " ".$this->attributes['inputDivClass'];
			$html .= '<div class="'.$inputDivClassString.'">';
		}
		
		$html .= '<input id="'.$this->attributes['id'].'" class="'.$this->attributes['class'].'" type="'.$this->attributes['type'].'" name="'.$this->attributes['name'].'" value="'.htmlspecialchars($this->attributes['value'],ENT_COMPAT,'UTF-8').'" '.$this->attributes['extra'];

		if (!empty($this->attributes['size']))
			$html .= ' size="'.$this->attributes['size'].'" ';
		
		if (!empty($this->attributes['maxlength']))
			$html .= ' maxlength="'.$this->attributes['maxlength'].'" ';
		
		if (!empty($this->attributes['empty_text'])) {
			$html .= ' onfocus="if(this.value==\'' . $this->attributes['empty_text'] . '\'){this.value=\'\';';

			if (!empty($this->attributes['empty_text_active_class'])) {
				$html .= 'this.className+=\' ' . $this->attributes['empty_text_active_class'] . '\'};"';
			} else {
				$html .= '}"';
			}

			$html .= ' onblur="if(this.value==\'\'){this.value=\'' . $this->attributes['empty_text'] . '\';';
			if (!empty($this->attributes['empty_text_active_class'])) {
				$html .= 'this.className=this.className.replace(\' ' . $this->attributes['empty_text_active_class'] . '\',\'\');';
			}
			$html .= '}"';
		}

		$html .= ' />';
		
		if (!empty($this->attributes['inputInDiv'])) {
			if (!empty($this->attributes['afterInput']))
				$html .= $this->attributes['afterInput'];
			$html .= '</div>';
		}
		
		if (!empty($this->attributes['empty_text'])) {
			$html .= '<input type="hidden" name="empty_texts[]" value="' . $this->attributes['name'] . ':' . $this->attributes['empty_text'] . '" />';
		}
		
		return $html;
	}
	
	
	protected function renderCheckbox(){
		
		$html = '';
//		$checked = !empty($this->attributes['checked']);
//		unset($this->attributes['checked']);
		
		if (isset($this->attributes['empty_value'])) {
			$html .= '<input type="hidden" name="'.$this->attributes['name'].'" value="'.$this->attributes['empty_value'].'"';
			$html .= ' />';
		}
		
		$html .= '<input id="'.$this->attributes['id'].'" class="'.$this->attributes['class'].'" type="'.$this->attributes['type'].'" name="'.$this->attributes['name'].'" value="'.$this->attributes['value'].'" '.$this->attributes['extra'];

		//if(!empty($this->attributes['value']) && $this->attributes['value']==1 )

		if(!empty($this->attributes['checked']) || ($this->isPosted && !empty($_POST[$this->attributes['name']])))
			$html .= 'checked';	
		
		$html .= ' />';
		
		return $html;
	}
	
	protected function renderTextarea(){
		$html = '<textarea id="'.$this->attributes['id'].'" class="'.$this->attributes['class'].'" name="'.$this->attributes['name'].'" '.$this->attributes['extra'].'>';

		$html .=  htmlspecialchars($this->attributes['value'],ENT_COMPAT,'UTF-8');
		
		if (!empty($this->attributes['empty_text']) && empty($this->attributes['value'])) {
			$html .= $this->attributes['empty_text'];
		}

		$html .= '</textarea>';
		
		if (!empty($this->attributes['empty_text'])) {
			$html .= '<input type="hidden" name="empty_texts[]" value="' . $this->attributes['name'] . ':' . $this->attributes['empty_text'] . '" />';
		}
		
		return $html;
	}
	
	protected function renderMultiInput(){
		
		if(!empty($this->attributes['options']) && (!isset($this->attributes['options'][0]) || !is_array($this->attributes['options'][0]))){
			$oldoptions = $this->attributes['options'];
			$this->attributes['options'] = array();
			foreach($oldoptions as $value=>$label){
				
				$option = array();
				$option['label']= $label;
				$option['value']= $value;
				
				$this->attributes['options'][] = $option;
			}
		}
		
		
		$html = '';
		
		if($this->attributes['type'] == 'select')
			$html .= '<select class="'.$this->attributes['class'].'" name="'.$this->attributes['name'].'" value="'.$option['value'].'">';
		
		foreach($this->attributes['options'] as $option){
			if($this->attributes['type'] == 'select'){
				
				$html .= '<option';
				
				$html .= ' value="'.$option['value'].'"';
				if($this->attributes['value']==$option['value'])
					$html .= ' selected';
			
				$html .='>';
				$html .= $option['label'];
				$html .= '</option>';
			} else {
				$html .= '<label>';
				$html .= '<input class="'.$this->attributes['class'].'" type="'.$this->attributes['type'].'" name="'.$this->attributes['name'].'" value="'.$option['value'].'" ';
				if(!empty($option['extra']))
					$html .= $option['extra'];

				if(!empty($this->attributes['value'])){
					if($this->attributes['value']==$option['value']){
						if($this->attributes['type'] == 'checkbox' || $this->attributes['type'] == 'radio')
							$html .= 'checked';						
					}
				}

				$html .= '/>';
				$html .= $option['label'];
				$html .= '</label>';
			}
		}
		
		if($this->attributes['type'] == 'select')
			$html .= '</select>';
		
		return $html;
	}

	public function getHtml() {
		
		// Check for errors
		if(self::getErrorMsg($this->attributes['name']))
			$this->attributes['class'].=' error';
		
		$html = '';
		
		// The opening div for the row
		if(!empty($this->attributes['renderContainer']))
		{
			$html .= '<div class="formrow '.$this->attributes['id'];
			if(!empty($this->attributes['rowClass']))
				$html .= ' '.$this->attributes['rowClass'];
			$html .= '">';
		}
		// The label div
		if(!empty($this->attributes['label'])){
			$html .= '<div class="formlabel';
			if(!empty($this->attributes['labelClass']))
				$html .= ' '.$this->attributes['labelClass'];
			$html .= '"';
			if(!empty($this->attributes['labelStyle']))
				$html .= 'style="'.$this->attributes['labelStyle'].'"';
			$html .= '>';
			
			if($this->attributes['type']=='checkbox')
				$html .= '<label for="'.$this->attributes['id'].'">';
			
			$html .= $this->attributes['label'].' :';
			
			if($this->attributes['type']=='checkbox')
				$html .= '</label>';
			
			$html .= '</div>';
		}
		
		// Check for multiple input fields or not
		if(!empty($this->attributes['options'])){
			$html .= $this->renderMultiInput();
		} elseif($this->attributes['type'] == 'textarea'){
			$html .= $this->renderTextarea();
		} elseif($this->attributes['type'] == 'checkbox') {
			$html .= $this->renderCheckbox();
		} elseif($this->attributes['type'] == 'hidden') {
			$html .= $this->renderHidden();
		}else{
			$html .= $this->renderNormalInput();
		}
		
		// The error div is created when there is an error.
			$html .= $this->getError($this->attributes['name']);

		if($this->attributes['required'])
			$html .= '<input type="hidden" name="required[]" value="'.$this->attributes['name'].'" />';
				
		// Close the row div
		if(!empty($this->attributes['renderContainer'])) {
			$html .= '<div style="clear:both;"></div>';
			$html .= '</div>';
		}
		
		
		unset(\GO::session()->values['formErrors'][$this->attributes['name']]);

		return $html;
	}

}
