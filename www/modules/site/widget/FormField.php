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
 * Field widget can render a label input error and fint at once
 * 
 * Use like this:
 * echo $form->field($model, $attribute)->textField();
 *
 * @package GO.site.widget
 * @copyright Copyright Intermesh
 * @version $Id: FormField.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @author Michael de Hart <mdehart@intermesh.nl> 
 */

namespace GO\Site\Widget;


class FormField extends \GO\Site\Components\Widget {

	/**
	 * @var Form this fields form
	 */
	public $form;

	/**
	 * @var \GO\Base\Model the datamodel this field represents
	 */
	public $model;

	/**
	 * @var StringHelper the attribute of the model this field represents
	 */
	public $attribute;

	/**
	 * @var StringHelper the template that is used to arrange the label, the input field, the error message and the hint text.
	 * The following tokens will be replaced when [[render()]] is called: `{label}`, `{input}`, `{error}` and `{hint}`.
	 */
	public $template = "{label}\n{input}\n{error}\n{hint}";
	public $parts = array();
	public $options = array('class'=>'row');

	/**
	 * Render the form field
	 */
	public function render() {

		if (!isset($this->parts['{input}'])) {
			$this->parts['{input}'] = $this->form->textField($this->model, $this->attribute);
		}
		if (!isset($this->parts['{label}'])) {
			$this->parts['{label}'] = $this->form->label($this->model, $this->attribute);
		}
		if (!isset($this->parts['{error}'])) {
			$this->parts['{error}'] = $this->form->error($this->model, $this->attribute);
		}
		if (!isset($this->parts['{hint}'])) {
			$this->parts['{hint}'] = '';
		}
		$content = strtr($this->template, $this->parts);
		return $this->begin() . "\n" . $content . "\n" . $this->end();
	}

	/**
	 * Renders the open tag of the form field.
	 * @return string the rendering result.
	 */
	public function begin() {
		list($tag, $options) = $this->stripTag();
		return $this->form->tag($tag, $options, false, false);
	}
	private function stripTag() {
		$options = $this->options;
		if (isset($options['tag'])) {
			$tag = $options['tag'];
			unset($options['tag']);
		} else
			$tag = 'div';
		return array($tag, $options);
	}

	/**
	 * Renders the closing tag of the form field.
	 * @return string the rendering result.
	 */
	public function end() {
		list($tag, $options) = $this->stripTag();
		return "</$tag>";
	}

	/**
	 * Generates a label tag
	 * @param string $label the label to use. If null, it will be generated with the models:getAttributeLabel().
	 * @param array $options the tag options in terms of name-value pairs.
	 * @return static the field object itself
	 */
	public function label($label = null, $options = array()) {
		if ($label !== null) {
			$options['label'] = $label;
		}
		$this->parts['{label}'] = $this->form->label($this->model, $this->attribute, $options);
		return $this;
	}

	/**
	 * Renders the hint tag.
	 * @param string $content the hint content. It will NOT be HTML-encoded.
	 * @param array $options the tag options in terms of name-value pairs.tag: 
	 * the 'tag' option specifies the tag name. If not set, "div" will be used.
	 *
	 * @return static the field object itself
	 */
	public function hint($content, $options = array()) {
		$options = array_merge($this->hintOptions, $options);
		if (isset($options['tag'])) {
			$tag = $options['tag'];
			unset($options['tag']);
		} else
			$tag = 'div';
		$this->parts['{hint}'] = $this->form->tag($tag, $content, $options);
		return $this;
	}

	/**
	 * Renders an input tag.
	 * @param string $type the input type (e.g. 'text', 'password')
	 * @param array $options the tag options in terms of name-value pairs.
	 * @return static the field object itself
	 */
	public function input($content) {
		$this->parts['{input}'] = $content;
		return $this;
	}

	public function text($options = array()) {
		$this->parts['{input}'] = $this->form->textField($this->model, $this->attribute, $options);
		return $this;
	}

	public function password($options = array()) {
		$this->parts['{input}'] = $this->form->passwordField($this->model, $this->attribute, $options);
		return $this;
	}
	
	/**
	 * Hidden field is ntop chainable but only renders a field.
	 * It is here for completion
	 * @param extra html option in input field $options
	 * @return string the rendered output
	 */
	public function hidden($options = array()) {
		return $this->form->hiddenField($this->model, $this->attribute, $options);
	}

	public function email($options = array()) {
		$this->parts['{input}'] = $this->form->emailField($this->model, $this->attribute, $options);
		return $this;
	}

	public function dropDown($data, $options = array()) {
		$this->parts['{input}'] = $this->form->dropDownList($this->model, $this->attribute, $data, $options);
		return $this;
	}

	public function checkbox($options = array()) {
		$this->parts['{input}'] = $this->form->checkbox($this->model, $this->attribute, $options);
		return $this;
	}

	public function radioList($data, $options = array()) {
		$this->parts['{input}'] = $this->form->radioButtonList($this->model, $this->attribute, $data, $options);
		return $this;
	}

	public function textArea($options = array()) {
		$this->parts['{input}'] = $this->form->textArea($this->model, $this->attribute, $options);
		return $this;
	}

	public function date($htmlOptions = array(),$datePickerOptions=array()) {
		$this->parts['{input}'] = $this->form->dateField($this->model,$this->attribute,$htmlOptions,$datePickerOptions);
		return $this;
	}
	
	public function setTemplate($templateString) {
		$this->template = $templateString;
		return $this;
	}
	
}
