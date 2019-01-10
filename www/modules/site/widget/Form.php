<?php

namespace GO\Site\Widget;


class Form extends \GO\Site\Components\Widget {
	
	public $errorCss='error';
	public $requiredCss='required';
	public $beforeRequiredLabel='';
	public $afterRequiredLabel=' <span class="required">*</span>';
	
	protected $action = ''; //form action [use createUrl()]
	protected $method = 'POST'; //form method [POST or GET] defaults to post
	protected $htmlAttributes = array(); //extra html attributes for form tag
	
	//Extra html attributes for the field tags
	public $inputOptions = array();
	public $labelOptions = array();
	public $errorOptions = array('class'=>'error');
	
	public $defaultTemplate = '';
	public $placeholderLabels=false;
	
	/**
	 * The number of date fields used in this form.
	 * @var integer
	 */
	private $_nDateFields = 0;
	
	/**
	 * Should not be used for rendering a form
	 * Use beginForm() to render the starting tag instead
	 * @deprecated use beginForm() instead
	 * @return StringHelper with detailed error if used anyway
	 */
	public function render() { return "use beginForm() instead of render()"; }
	
	public function field($model, $attribute, $options = array()){
		$template=null;
		if (isset($options['template'])) {
			$template = $options['template'];
			unset($options['tag']);
		}
		
		$field = new FormField($options);
		
		if (!empty($this->defaultTemplate))
			$field->template = $this->defaultTemplate;
		if(!empty($template))
			$field->template = $template;
		$field->form = $this;
		$field->model = $model;
		$field->attribute = $attribute;
		return $field;
	}
	
	/**
	 * Generates a label tag for a model attribute.
	 * The label text is the attribute label and the label is associated with
	 * the input for the attribute (see {@link CModel::getAttributeLabel}.
	 * If the attribute has input error, the label's CSS class will be appended with {@link errorCss}.
	 * @param \GO\Base\Model $model the data model
	 * @param StringHelper $attribute the attribute
	 * @param array $htmlAttributes additional HTML attributes. The following special options are recognized:
	 * @return StringHelper the generated label tag
	 */
	public function label($model,$attribute,$htmlAttributes=array())
	{
		if(isset($htmlAttributes['for']))
		{
			$for=$htmlAttributes['for'];
			unset($htmlAttributes['for']);
		}
		else
			$for=$this->_getIdByName($this->_resolveName($model,$attribute));
		if(isset($htmlAttributes['label']))
		{
			if(($label=$htmlAttributes['label'])===false)
				return '';
			unset($htmlAttributes['label']);
		}
		else{
			if($model instanceof \GO\Customfields\Model\AbstractCustomFieldsRecord){
				$label=$model->getAttributeLabelWithoutCategoryName($attribute);
			} else {
				$label=$model->getAttributeLabel($attribute);
			}
		}
		if($model->hasValidationErrors($attribute))
			$htmlAttributes = $this->_addErrorCss($htmlAttributes);
		
		$htmlAttributes = $this->_resolveDefaultAttributes($model, $attribute, $htmlAttributes);
		
		$htmlAttributes = array_merge($this->labelOptions, $htmlAttributes);
		
		return $this->staticLabel($label,$for,$htmlAttributes);
	}
	
	public function passwordField($model,$attribute,$htmlAttributes=array()){
		return $this->_inputField('password',$model,$attribute,$htmlAttributes);
	}
	
	public function emailField($model,$attribute,$htmlAttributes=array()){
		return $this->_inputField('email',$model,$attribute,$htmlAttributes);
	}
	
	public function checkBox($model,$attribute,$htmlAttributes=array()){
		
		if(!isset($htmlAttributes['value']))
			$htmlAttributes['value']=1;
		if(!isset($htmlAttributes['checked']) && $this->_resolveValue($model,$attribute)==$htmlAttributes['value'])
			$htmlAttributes['checked']='checked';
		$hidden = $this->_inputField('hidden',$model,$attribute,array('value'=>0,'id'=>''));
		
		return $hidden.$this->_inputField('checkbox',$model,$attribute,$htmlAttributes);
	}
	
	/**
	 * Render a list of radio buttons same as a dropdown list
	 * @param \GO\Base\Model $model
	 * @param StringHelper $attribute a propertyname of the model
	 * @param array $data the keys en values of the button as key=>value of the array
	 * @param array $htmlOptions extra html attributes
	 * special values are 
	 * template : {input} {label}, 
	 * separator: <br\n, 
	 * uncheckValue  ''
	 * labelOption array
	 * @return StringHelper the rendered radio buttons
	 */
	public function radioButtonList($model,$attribute,$data,$htmlOptions=array())
	{
		$htmlOptions = $this->_resolveNameID($model,$attribute,$htmlOptions);
		$selection=$this->_resolveValue($model,$attribute);
		if($model->hasValidationErrors($attribute))
			$this->_addErrorCss($htmlOptions);
		$name=$htmlOptions['name'];
		unset($htmlOptions['name']);

		if(array_key_exists('uncheckValue',$htmlOptions))
		{
			$uncheck=$htmlOptions['uncheckValue'];
			unset($htmlOptions['uncheckValue']);
		}
		else
			$uncheck='';

		$hiddenOptions=isset($htmlOptions['id']) ? array('id'=>$htmlOptions['id']) : array('id'=>false);
		$hidden=$uncheck!==null ? $this->staticHiddenField($name,$uncheck,$hiddenOptions) : '';
		
		$template=isset($htmlOptions['template'])?$htmlOptions['template']:'{input} {label}';
		$separator=isset($htmlOptions['separator'])?$htmlOptions['separator']:"\n";
		unset($htmlOptions['template'],$htmlOptions['separator']);

		$labelOptions=isset($htmlOptions['labelOptions'])?$htmlOptions['labelOptions']:array();
		unset($htmlOptions['labelOptions']);

		if (!empty($htmlOptions['horizontal'])) {
			if (!empty($labelOptions['style']))
				$labelOptions['style'] .= ';float:left;';
			else
				$labelOptions['style'] = 'float:left;';
			
			if (!empty($htmlOptions['style']))
				$htmlOptions['style'] .= ';float:left;';
			else
				$htmlOptions['style'] = 'float:left;';
		}
		
		$items=array();
		$baseID=$this->_getIdByName($name);
		$id=0;
		foreach($data as $value=>$label)
		{
			$checked=!strcmp($value,$selection);
			$htmlOptions['value']=$value;
			$htmlOptions['id']=$baseID.'_'.$id++;
			$option=$this->staticRadioButton($name,$checked,$htmlOptions);
			$label=$this->staticLabel($label,$htmlOptions['id'],$labelOptions);
			$items[]=strtr($template,array('{input}'=>$option,'{label}'=>$label));
		}
		$tag = !empty($htmlOptions['horizontal']) ? 'span' : 'ul';
		return $hidden . $this->_tag($tag,array('id'=>$baseID),implode($separator,$items));
	}
	
	/**
	 * Render a static radio button field
	 * @param StringHelper $name html name attribute
	 * @param boolean $checked html checked attribute
	 * @param array $htmlOptions other ghtml attributes
	 * @return StringHelper the rendered output
	 */
	protected function staticRadioButton($name,$checked=false,$htmlOptions=array())
	{
		if($checked)
			$htmlOptions['checked']='checked';
		else
			unset($htmlOptions['checked']);
		$value=isset($htmlOptions['value']) ? $htmlOptions['value'] : 1;

		if(array_key_exists('uncheckValue',$htmlOptions))
		{
			$uncheck=$htmlOptions['uncheckValue'];
			unset($htmlOptions['uncheckValue']);
		}
		else
			$uncheck=null;

		if($uncheck!==null)
		{
			// add a hidden field so that if the radio button is not selected, it still submits a value
			if(isset($htmlOptions['id']) && $htmlOptions['id']!==false)
				$uncheckOptions=array('id'=>self::ID_PREFIX.$htmlOptions['id']);
			else
				$uncheckOptions=array('id'=>false);
			$hidden=$this->staticHiddenField($name,$uncheck,$uncheckOptions);
		}
		else
			$hidden='';

		// add a hidden field so that if the radio button is not selected, it still submits a value
		return $hidden . $this->staticInputField('radio',$name,$value,$htmlOptions);
	}
	
	public function dateField($model,$attribute,$htmlAttributes=array(),$datePickerOptions=array()){
		
		$this->_nDateFields++;
		
		$currentUser = \GO::user();		
		$dateFormatArr = array();
		$goDateFormat = !empty($currentUser) ? $currentUser->date_format : \GO::config()->default_date_format;
		$goDateSeparator = !empty($currentUser) ? $currentUser->date_separator : \GO::config()->default_date_separator;
		
		for ($i=0;$i<3;$i++) {
			if ($goDateFormat[$i]=='Y')
				$dateFormatArr[] = 'yy';
			else
				$dateFormatArr[] = $goDateFormat[$i].$goDateFormat[$i];
		}
		
		$datePickerOptionsString = '';
		$allowedWeekdaysArray = '[0,1,2,3,4,5,6]';
		foreach ($datePickerOptions as $name=>$value) {
			if ($name=='allowedWeekDays') {
				$allowedWeekdaysArray = json_encode($value);
			} else {
				$datePickerOptionsString .= ','.$name.': '.var_export($value,true).'';
			}
		}
		
		\Site::scripts()->registerGapiScript('jquery');
		\Site::scripts()->registerGapiScript('jquery-ui');
		\Site::scripts()->registerCssFile('http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css');
		\Site::scripts()->registerScript('datepicker'.$this->_nDateFields, '
$(function() {
	$( "#datepicker'.$this->_nDateFields.'" ).datepicker({ dateFormat: "'.implode($goDateSeparator,$dateFormatArr).'" '.$datePickerOptionsString.'
//		,beforeShow:function(input) {
//        $(input).css({
//            "position": "relative",
//            "z-index": 999999
//        });
//    }
	});
});
');
		$htmlAttributes['id'] = 'datepicker'.$this->_nDateFields;
		return $this->_inputField('text',$model,$attribute,$htmlAttributes);
	}
	
	public function textField($model,$attribute,$htmlAttributes=array()){
		return $this->_inputField('text',$model,$attribute,$htmlAttributes);
	}
	
	public function hiddenField($model,$attribute,$htmlAttributes=array()){
		return $this->_inputField('hidden',$model,$attribute,$htmlAttributes);
	}
	
	public function textArea($model,$attribute,$htmlAttributes=array()){
		
		if(isset($htmlAttributes['label']))
		{
			if(($label=$htmlAttributes['label'])===false)
				return '';
			unset($htmlAttributes['label']);
		}
		else
			$label=$model->getAttributeLabel($attribute);
		
		$htmlAttributes = $this->_resolveDefaultAttributes($model, $attribute, $htmlAttributes);
		$htmlAttributes['name'] = $this->_resolveName($model, $attribute, $htmlAttributes);
		
		return $this->staticTextArea($htmlAttributes['name'],$model->{$attribute},$htmlAttributes);
	}
	
	/**
	 * Generates a label tag.
	 * @param StringHelper $label label text. Note, you should HTML-encode the text if needed.
	 * @param StringHelper $for the ID of the HTML element that this label is associated with.
	 * If this is false, the 'for' attribute for the label tag will not be rendered.
	 * @param array $htmlAttributes additional HTML attributes.
	 * The following HTML option is recognized:
	 * <ul>
	 * <li>required: if this is set and is true, the label will be styled
	 * with CSS class 'required' (customizable with CHtml::$requiredCss),
	 * and be decorated with {@link CHtml::beforeRequiredLabel} and
	 * {@link CHtml::afterRequiredLabel}.</li>
	 * </ul>
	 * @return StringHelper the generated label tag
	 */
	public function staticLabel($label,$for,$htmlAttributes=array())
	{
		if($for===false)
			unset($htmlAttributes['for']);
		else
			$htmlAttributes['for']=$for;
		
		if(isset($htmlAttributes['required']))
		{
			if($htmlAttributes['required'])
			{
				if(isset($htmlAttributes['class']))
					$htmlAttributes['class'].=' '.$this->requiredCss;
				else
					$htmlAttributes['class']=$this->requiredCss;
				$label=$this->beforeRequiredLabel.$label.$this->afterRequiredLabel;
			}
			unset($htmlAttributes['required']);
		}
		return $this->_tag('label',$htmlAttributes,$label.':');
	}
	
	/**
	 * Generates a submit button.
	 * @param StringHelper $label the button label
	 * @param array $htmlAttributes additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return StringHelper the generated button tag
	 * @see clientChange
	 */
	public function submitButton($label='submit',$htmlAttributes=array())
	{
		$htmlAttributes['type']='submit';
		return $this->button($label,$htmlAttributes);
	}
	
	public function resetButton($label='submit',$htmlAttributes=array())
	{
		$htmlAttributes['type']='reset';
		return $this->button($label,$htmlAttributes);
	}
	
	
	/**
	 * Displays the first validation error for a model attribute.
	 * @param CModel $model the data model
	 * @param StringHelper $attribute the attribute name
	 * @param array $htmlAttributes additional HTML attributes to be rendered in the container div tag.
	 * @return StringHelper the error display. Empty if no errors are found.
	 * @see CModel::getErrors
	 * @see errorMessageCss
	 */
	public function error($model,$attribute,$htmlAttributes=array())
	{
		$this->_resolveName($model,$attribute); // turn [a][b]attr into attr
		$error=$model->getValidationError($attribute);
		if($error!='')
		{
			$htmlAttributes = array_merge($this->errorOptions, $htmlAttributes);
			return $this->_tag('div',$htmlAttributes,$error);
		}
		else
			return '';
	}
	
	/**
	 * Creat a from field by customfield propertys
	 * 
	 * 
	 * @param CModel $model the data model
	 * @param string $attribute the attribute name
	 * @param array $htmlAttributes additional HTML attributes to be rendered in the container div tag.
	 * @return string the error display. Empty if no errors are found.
	 * @see CModel::getErrors
	 * @see errorMessageCss
	 * @throws Exception
	 */
	public function customfield($model,$attribute,$htmlAttributes=array()) {
		
		switch ($model->columns[$attribute]['customfield']->datatype) {
			case "GO\Customfields\Customfieldtype\Select":
				$data = array();
				foreach ($model->columns[$attribute]['customfield']->selectOptions as $value) {
					$data[$value->text] = $value->text;
				}
				
				return $this->dropDownList($model, $attribute, $data, $htmlAttributes);
				break;
			case "GO\Customfields\Customfieldtype\Text":
				
				return $this->textField($model,$attribute,$htmlAttributes);
				break;
			case "GO\Customfields\Customfieldtype\Textarea":
				
				return $this->textArea($model,$attribute,$htmlAttributes);
				break;
			case "GO\Customfields\Customfieldtype\Date":
				
				return $this->dateField($model, $attribute, $htmlAttributes);
				break;

			default:
				
				throw new Exception('This custom field type is not implemented yet: '.$model->columns[$attribute]['customfield']->datatype);
				break;
		}
		
		
	}
	
	
	
	/**
	 * Generates a drop down list for a model attribute.
	 * If the attribute has input error, the input field's CSS class will
	 * be appended with {@link errorCss}.
	 * @param CModel $model the data model
	 * @param StringHelper $attribute the attribute
	 * @param array $data data for generating the list options (value=>display)
	 * You may use {@link listData} to generate this data.
	 * Please refer to {@link listOptions} on how this data is used to generate the list options.
	 * Note, the values and labels will be automatically HTML-encoded by this method.
	 * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are recognized. See {@link clientChange} and {@link tag} for more details.
	 * In addition, the following options are also supported:
	 * <ul>
	 * <li>encode: boolean, specifies whether to encode the values. Defaults to true.</li>
	 * <li>prompt: string, specifies the prompt text shown as the first list option. Its value is empty.  Note, the prompt text will NOT be HTML-encoded.</li>
	 * <li>empty: string, specifies the text corresponding to empty selection. Its value is empty.
	 * The 'empty' option can also be an array of value-label pairs.
	 * Each pair will be used to render a list option at the beginning. Note, the text label will NOT be HTML-encoded.</li>
	 * <li>options: array, specifies additional attributes for each OPTION tag.
	 *     The array keys must be the option values, and the array values are the extra
	 *     OPTION tag attributes in the name-value pairs. For example,
	 * <pre>
	 *     array(
	 *         'value1'=>array('disabled'=>true, 'label'=>'value 1'),
	 *         'value2'=>array('label'=>'value 2'),
	 *     );
	 * </pre>
	 * </li>
	 * </ul>
	 * @return StringHelper the generated drop down list
	 * @see clientChange
	 * @see listData
	 */
	public function dropDownList($model,$attribute,$data,$htmlAttributes=array())
	{
		$htmlAttributes = $this->_resolveNameID($model,$attribute,$htmlAttributes);
		$selection=$this->_resolveValue($model,$attribute);
		$options="\n".$this->_listOptions($selection,$data,$htmlAttributes);
		if (isset($htmlAttributes['placeholder'])){
			$placeholderSelected=$selection=="" ? 'selected' : '';
			$options = "\n<option value=\"\" disabled $placeholderSelected style=\"color:lightgray;display:none;\">".$htmlAttributes['placeholder']."</option>".$options;
		}
		//self::clientChange('change',$htmlOptions);
		if($model->hasValidationErrors($attribute))
			$this->_addErrorCss($htmlAttributes);
		if(isset($htmlAttributes['multiple']))
		{
			if(substr($htmlAttributes['name'],-2)!=='[]')
				$htmlAttributes['name'].='[]';
		}
		return $this->_tag('select',$htmlAttributes,$options);
	}
	
	/**
	 * Generates a button.
	 * @param StringHelper $label the button label
	 * @param array $htmlAttributes additional HTML attributes. Besides normal HTML attributes, a few special
	 * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
	 * @return StringHelper the generated button tag
	 * @see clientChange
	 */
	public function button($label='button',$htmlAttributes=array())
	{
		if(!isset($htmlAttributes['name']))
		{
			if(!array_key_exists('name',$htmlAttributes))
				$htmlAttributes['name']=$this->id;
		}
		if(!isset($htmlAttributes['type']))
			$htmlAttributes['type']='button';
		if(!isset($htmlAttributes['value']))
			$htmlAttributes['value']=$label;
		return $this->_tag('input',$htmlAttributes);
	}
	
	public function staticHiddenField($name,$value='',$htmlAttributes=array())
	{
		return $this->staticInputField('hidden',$name,$value,$htmlAttributes);
	}
	
	public function staticPasswordField($name,$value='',$htmlAttributes=array())
	{
		return $this->staticInputField('password',$name,$value,$htmlAttributes);
	}
	
	public function staticFileField($name,$value='',$htmlAttributes=array())
	{
		return $this->staticInputField('file',$name,$value,$htmlAttributes);
	}
	
	public function staticTextArea($name,$value='',$htmlAttributes=array())
	{
		if(empty($htmlAttributes['name']))
			$htmlAttributes['name']=$name;
		
		if(!isset($htmlAttributes['id']))
			$htmlAttributes['id']=$this->_getIdByName($htmlAttributes['name']);
		else if($htmlAttributes['id']===false)
			unset($htmlAttributes['id']);
		
		return $this->_tag('textarea',$htmlAttributes,isset($htmlAttributes['encode']) && !$htmlAttributes['encode'] ? $value : $this->_encode($value));
	}
	
	/**
	 * Generates an opening form tag.
	 * Note, only the open tag is generated. A close tag should be placed manually
	 * at the end of the form.
	 * @param mixed $action the form action URL (see {@link normalizeUrl} for details about this parameter.)
	 * @param StringHelper $method form method (e.g. post, get)
	 * @param array $htmlAttributes additional HTML attributes (see {@link tag}).
	 * @return StringHelper the generated form tag.
	 * @see endForm
	 */
	public function beginForm($action=false, $method=false, $htmlAttributes=false)
	{
		if(empty($action)) {
			$action = $_SERVER['REQUEST_URI'];
		}
		$this->action = $action;
		
		if(!empty($method))
			$this->method = $method;
		
		if(!empty($htmlAttributes))
			$this->htmlAttributes = $htmlAttributes;
		
		if(!empty($this->action))
			$htmlAttributes['action']=$url=$this->action;
		
		$htmlAttributes['method']=$this->method;
		$form=$this->_tag('form',$htmlAttributes,false,false);
		$hiddens=array();
		if(!strcasecmp($method,'get') && ($pos=strpos($url,'?'))!==false)
		{
			foreach(explode('&',substr($url,$pos+1)) as $pair)
			{
				if(($pos=strpos($pair,'='))!==false)
					$hiddens[]=$this->hiddenField(urldecode(substr($pair,0,$pos)),urldecode(substr($pair,$pos+1)),array('id'=>false));
			}
		}
		//$request=Yii::app()->request;
		//if($request->enableCsrfValidation && !strcasecmp($method,'post'))
		//	$hiddens[]=self::hiddenField($request->csrfTokenName,$request->getCsrfToken(),array('id'=>false));
		if($hiddens!==array())
			$form.="\n".$this->_tag('div',array('style'=>'display:none'),implode("\n",$hiddens));
		return $form;
	}
	
	/**
	 * Generates a closing form tag.
	 * @return StringHelper the generated tag
	 * @see beginForm
	 */
	public function endForm()
	{
		return '</form>';
	}
	
	
	
	/**
	 * Resolve the name and the ID from 
	 * 
	 * @param \GO\Base\Model $model
	 * @param StringHelper $attribute
	 * @param array $htmlAttributes
	 * 
	 * @return array $htmlAttributes
	 */
	private function _resolveNameID($model,$attribute,$htmlAttributes){
		if(!isset($htmlAttributes['name']))
			$htmlAttributes['name']=$this->_resolveName($model,$attribute);
		if(!isset($htmlAttributes['id']))
			$htmlAttributes['id']=$this->_getIdByName($htmlAttributes['name']);
		else if($htmlAttributes['id']===false)
			unset($htmlAttributes['id']);
		
		return $htmlAttributes;
	}
	
	private function _resolveDefaultAttributes($model,$attribute,$htmlAttributes){

		if(isset($htmlAttributes['required']) && $htmlAttributes['required']==false) {
			unset($htmlAttributes['required']);
		} else if(isset($model->columns) && isset($model->columns[$attribute])){
			if($model->columns[$attribute]['required'] && !isset($htmlAttributes['required']))
				$htmlAttributes['required'] = true;
			else if(isset($htmlAttributes['required']) && !$htmlAttributes['required'])
				unset($htmlAttributes['required']);
		}
		
		if($this->placeholderLabels===true && !isset($htmlAttributes['placeholder'])) {
			$htmlAttributes['placeholder']=$model->getAttributeLabel($attribute);
		}
		
		return $htmlAttributes;
	}
	
	private function _inputField($type,$model,$attribute,$htmlAttributes){
		
		$htmlAttributes = $this->_resolveNameID($model, $attribute, $htmlAttributes);
		$htmlAttributes = $this->_resolveDefaultAttributes($model, $attribute, $htmlAttributes);
		
		$htmlAttributes['type']=$type;
		
		
		if($type==='file')
			unset($htmlAttributes['value']);
		else if(!isset($htmlAttributes['value']))
			$htmlAttributes['value']=$this->_resolveValue($model,$attribute);
		
		if($model->hasValidationErrors($attribute))
			$htmlAttributes = $this->_addErrorCss($htmlAttributes);
		$htmlAttributes = array_merge($this->inputOptions, $htmlAttributes);
		return $this->_tag('input',$htmlAttributes);
	}
	
	/**
	 * Renders a static inputfield without a model
	 * @param StringHelper $type html type attribute
	 * @param StringHelper $name html name attribute
	 * @param StringHelper $value html value attribute
	 * @param array $htmlOptions other html attributes
	 * @return StringHelper the rendered output
	 */
	protected function staticInputField($type,$name,$value,$htmlOptions)
	{
		$htmlOptions['type']=$type;
		$htmlOptions['value']=$value;
		$htmlOptions['name']=$name;
		if(!isset($htmlOptions['id']))
			$htmlOptions['id']=$this->_getIdByName($name);
		else if($htmlOptions['id']===false)
			unset($htmlOptions['id']);
		return $this->_tag('input',$htmlOptions);
	}

	/**
	 * Generates input name for a model attribute.
	 * Note, the attribute name may be modified after calling this method if the name
	 * contains square brackets (mainly used in tabular input) before the real attribute name.
	 * @param \GO\Base\Model $model the data model
	 * @param StringHelper $attribute the attribute
	 * @return StringHelper the input name
	 */
	private function _resolveName($model,$attribute)
	{
		if(($pos=strpos($attribute,'['))!==false)
		{
			if($pos!==0)  // e.g. name[a][b]
				return $this->modelName($model).'['.substr($attribute,0,$pos).']'.substr($attribute,$pos);
			if(($pos=strrpos($attribute,']'))!==false && $pos!==strlen($attribute)-1)  // e.g. [a][b]name
			{
				$sub=substr($attribute,0,$pos+1);
				$attribute=substr($attribute,$pos+1);
				return $this->modelName($model).$sub.'['.$attribute.']';
			}
			if(preg_match('/\](\w+\[.*)$/',$attribute,$matches))
			{
				$name=$this->modelName($model).'['.str_replace(']','][',trim(strtr($attribute,array(']['=>']','['=>']')),']')).']';
				$attribute=$matches[1];
				return $name;
			}
		}
		return $this->modelName($model).'['.$attribute.']';
	}
	
	private function modelName($class) {
		$parts = explode('\\',get_class($class));
		return array_pop($parts);
	}
	
	/**
	 * Generates a valid HTML ID based on name.
	 * @param StringHelper $name name from which to generate HTML ID
	 * @return StringHelper the ID generated based on name.
	 */
	private function _getIdByName($name)
	{
		return str_replace(array('[]', '][', '[', ']'), array('', '_', '_', ''), $name);
	}
	
	/**
	 * Evaluates the attribute value of the model.
	 * This method can recognize the attribute name written in array format.
	 * For example, if the attribute name is 'name[a][b]', the value "$model->name['a']['b']" will be returned.
	 * @param \GO\Base\Model $model the data model
	 * @param StringHelper $attribute the attribute name
	 * @return mixed the attribute value
	 */
	private function _resolveValue($model,$attribute)
	{
		if(($pos=strpos($attribute,'['))!==false)
		{
			if($pos===0)  // [a]name[b][c], should ignore [a]
			{
				if(preg_match('/\](\w+)/',$attribute,$matches))
					$attribute=$matches[1];
				if(($pos=strpos($attribute,'['))===false)
					return $model->$attribute;
			}
			$name=substr($attribute,0,$pos);			
//			$value = method_exists($model, 'getAttribute') && $model->has ? $model->getAttribute($attribute,'formatted') : $model->$attribute;
			$value = $model->$attribute;
			foreach(explode('][',rtrim(substr($attribute,$pos+1),']')) as $id)
			{
				if(is_array($value) && isset($value[$id]))
					$value=$value[$id];
				else
					return null;
			}
			return $value;
		}
		else {
//			$value = method_exists($model, 'getAttribute') ? $model->getAttribute($attribute,'formatted') : $model->$attribute;
			$value = $model->$attribute;
			return $value;
		}
	}
	
	/**
	 * Appends {@link errorCss} to the 'class' attribute.
	 * @param array $htmlAttributes HTML options to be modified
	 * 
	 * @return array $htmlAttributes
	 */
	private function _addErrorCss($htmlAttributes)
	{
		if(isset($htmlAttributes['class']))
			$htmlAttributes['class'].=' '.$this->errorCss;
		else
			$htmlAttributes['class']=$this->errorCss;
		
		return $htmlAttributes;
	}
	
	/**
	 * Generates an HTML element.
	 * @param StringHelper $tag the tag name
	 * @param array $htmlAttributes the element attributes. The values will be HTML-encoded using {@link encode()}.
	 * If an 'encode' attribute is given and its value is false,
	 * the rest of the attribute values will NOT be HTML-encoded.
	 * Since version 1.1.5, attributes whose value is null will not be rendered.
	 * @param mixed $content the content to be enclosed between open and close element tags. It will not be HTML-encoded.
	 * If false, it means there is no body content.
	 * @param boolean $closeTag whether to generate the close tag.
	 * @return StringHelper the generated HTML element tag
	 */
	private function _tag($tag,$htmlAttributes=array(),$content=false,$closeTag=true)
	{
		$html='<' . $tag . $this->_renderAttributes($htmlAttributes);
		if($content===false)
			return $closeTag ? $html.' />' : $html.'>';
		else
			return $closeTag ? $html.'>'.$content.'</'.$tag.'>' : $html.'>'.$content;
	}
	
	public function tag($tag,$htmlAttributes=array(),$content=false,$closeTag=true) {
		return $this->_tag($tag, $htmlAttributes,$content,$closeTag);
	}
	
	/**
	 * Renders the HTML tag attributes.
	 * Since version 1.1.5, attributes whose value is null will not be rendered.
	 * Special attributes, such as 'checked', 'disabled', 'readonly', will be rendered
	 * properly based on their corresponding boolean value.
	 * @param array $htmlAttributes attributes to be rendered
	 * @return StringHelper the rendering result
	 */
	private function _renderAttributes($htmlAttributes)
	{
		static $specialAttributes=array(
			'checked'=>1,
			'declare'=>1,
			'defer'=>1,
			'disabled'=>1,
			'ismap'=>1,
			'multiple'=>1,
			'nohref'=>1,
			'noresize'=>1,
			'readonly'=>1,
			'selected'=>1,
		);

		if($htmlAttributes===array())
			return '';

		$html='';

			$raw=false;

		if($raw)
		{
			foreach($htmlAttributes as $name=>$value)
			{
				if(isset($specialAttributes[$name]))
				{
					if($value)
						$html .= ' ' . $name . '="' . $name . '"';
				}
				else if($value!==null)
					$html .= ' ' . $name . '="' . $value . '"';
			}
		}
		else
		{
			foreach($htmlAttributes as $name=>$value)
			{
				if(isset($specialAttributes[$name]))
				{
					if($value)
						$html .= ' ' . $name . '="' . $name . '"';
				}
				else if($value!==null)
					$html .= ' ' . $name . '="' . $this->_encode($value) . '"';
			}
		}
		return $html;
	}
	
	
	/**
	 * Encodes special characters into HTML entities.
	 * The {@link CApplication::charset application charset} will be used for encoding.
	 * @param StringHelper $text data to be encoded
	 * @return StringHelper the encoded data
	 * @see http://www.php.net/manual/en/function.htmlspecialchars.php
	 */
	private function _encode($text)
	{
		return htmlspecialchars($text,ENT_QUOTES,'UTF-8');
	}
	
	/**
	 * Generates the list options.
	 * @param mixed $selection the selected value(s). This can be either a string for single selection or an array for multiple selections.
	 * @param array $listData the option data (see {@link listData})
	 * @param array $htmlOptions additional HTML attributes. The following two special attributes are recognized:
	 * <ul>
	 * <li>encode: boolean, specifies whether to encode the values. Defaults to true.</li>
	 * <li>prompt: string, specifies the prompt text shown as the first list option. Its value is empty. Note, the prompt text will NOT be HTML-encoded.</li>
	 * <li>empty: string, specifies the text corresponding to empty selection. Its value is empty.
	 * The 'empty' option can also be an array of value-label pairs.
	 * Each pair will be used to render a list option at the beginning. Note, the text label will NOT be HTML-encoded.</li>
	 * <li>options: array, specifies additional attributes for each OPTION tag.
	 *     The array keys must be the option values, and the array values are the extra
	 *     OPTION tag attributes in the name-value pairs. For example,
	 * <pre>
	 *     array(
	 *         'value1'=>array('disabled'=>true, 'label'=>'value 1'),
	 *         'value2'=>array('label'=>'value 2'),
	 *     );
	 * </pre>
	 * </li>
	 * <li>key: string, specifies the name of key attribute of the selection object(s).
	 * This is used when the selection is represented in terms of objects. In this case,
	 * the property named by the key option of the objects will be treated as the actual selection value.
	 * This option defaults to 'primaryKey', meaning using the 'primaryKey' property value of the objects in the selection.
	 * This option has been available since version 1.1.3.</li>
	 * </ul>
	 * @return StringHelper the generated list options
	 */
	private function _listOptions($selection,$listData,&$htmlAttributes)
	{
		$raw=isset($htmlAttributes['encode']) && !$htmlAttributes['encode'];
		$content='';
		if(isset($htmlAttributes['prompt']))
		{
			$content.='<option value="">'.strtr($htmlAttributes['prompt'],array('<'=>'&lt;', '>'=>'&gt;'))."</option>\n";
			unset($htmlAttributes['prompt']);
		}
		if(isset($htmlAttributes['empty']))
		{
			if(!is_array($htmlAttributes['empty']))
				$htmlAttributes['empty']=array(''=>$htmlAttributes['empty']);
			foreach($htmlAttributes['empty'] as $value=>$label)
				$content.='<option value="'.$this->_encode($value).'">'.strtr($label,array('<'=>'&lt;', '>'=>'&gt;'))."</option>\n";
			unset($htmlAttributes['empty']);
		}

		if(isset($htmlAttributes['options']))
		{
			$options=$htmlAttributes['options'];
			unset($htmlAttributes['options']);
		}
		else
			$options=array();

		$key=isset($htmlAttributes['key']) ? $htmlAttributes['key'] : 'primaryKey';
		if(is_array($selection))
		{
			foreach($selection as $i=>$item)
			{
				if(is_object($item))
					$selection[$i]=$item->$key;
			}
		}
		else if(is_object($selection))
			$selection=$selection->$key;

		foreach($listData as $key=>$value)
		{
			if(is_array($value))
			{
				$content.='<optgroup label="'.($raw?$key : $this->_encode($key))."\">\n";
				$dummy=array('options'=>$options);
				if(isset($htmlAttributes['encode']))
					$dummy['encode']=$htmlAttributes['encode'];
				$content.=$this->_listOptions($selection,$value,$dummy);
				$content.='</optgroup>'."\n";
			}
			else
			{
				$attributes=array('value'=>(string)$key, 'encode'=>!$raw);
				if(!is_array($selection) && !strcmp($key,$selection) || is_array($selection) && in_array($key,$selection))
					$attributes['selected']='selected';
				if(isset($options[$key]))
					$attributes=array_merge($attributes,$options[$key]);
								
				$content.=$this->_tag('option',$attributes,$raw?(string)$value : $this->_encode((string)$value))."\n";
			}
		}

		unset($htmlAttributes['key']);

		return $content;
	}
	
	/**
	 * Generates the data suitable for list-based HTML elements.
	 * The generated data can be used in {@link dropDownList}, {@link listBox}, {@link checkBoxList},
	 * {@link radioButtonList}, and their active-versions (such as {@link activeDropDownList}).
	 * Note, this method does not HTML-encode the generated data. You may call {@link encodeArray} to
	 * encode it if needed.
	 * Please refer to the {@link value} method on how to specify value field, text field and group field.
	 * @param array $models a list of model objects. This parameter
	 * can also be an array of associative arrays (e.g. results of {@link CDbCommand::queryAll}).
	 * @param StringHelper $valueField the attribute name for list option values
	 * @param StringHelper $textField the attribute name for list option texts
	 * @param StringHelper $groupField the attribute name for list option group names. If empty, no group will be generated.
	 * @return array the list data that can be used in {@link dropDownList}, {@link listBox}, etc.
	 */
	public function listData($models,$valueField,$textField,$groupField='')
	{
		$listData=array();
		if($groupField==='')
		{
			foreach($models as $model)
			{
				$value=$this->_value($model,$valueField);
				$text=$this->_value($model,$textField);
				$listData[$value]=$text;
			}
		}
		else
		{
			foreach($models as $model)
			{
				$group=$this->_value($model,$groupField);
				$value=$this->_value($model,$valueField);
				$text=$this->_value($model,$textField);
				$listData[$group][$value]=$text;
			}
		}
		return $listData;
	}
	
	/**
	 * Used by listdata
	 * @param mixed $model the model. This can be either an object or an array.
	 * @param StringHelper $attribute the attribute name (use dot to concatenate multiple attributes)
	 * @param mixed $defaultValue the default value to return when the attribute does not exist
	 * @return mixed the attribute value
	 */
	private function _value($model,$attribute,$defaultValue=null)
	{
		foreach(explode('.',$attribute) as $name)
		{
			if(is_object($model))
				$model=$model->$name;
			else if(is_array($model) && isset($model[$name]))
				$model=$model[$name];
			else
				return $defaultValue;
		}
		return $model;
	}
}
