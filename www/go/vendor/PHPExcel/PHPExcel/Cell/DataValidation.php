<?php
/**
 * PHPExcel
 *
 * Copyright (c) 2006 - 2013 PHPExcel
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPExcel
 * @package    PHPExcel_Cell
 * @copyright  Copyright (c) 2006 - 2013 PHPExcel (http://www.codeplex.com/PHPExcel)
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 * @version    1.7.9, 2013-06-02
 */


/**
 * PHPExcel_Cell_DataValidation
 *
 * @category   PHPExcel
 * @package    PHPExcel_Cell
 * @copyright  Copyright (c) 2006 - 2013 PHPExcel (http://www.codeplex.com/PHPExcel)
 */
class PHPExcel_Cell_DataValidation
{
    /* Data validation types */
    const TYPE_NONE        = 'none';
    const TYPE_CUSTOM      = 'custom';
    const TYPE_DATE        = 'date';
    const TYPE_DECIMAL     = 'decimal';
    const TYPE_LIST        = 'list';
    const TYPE_TEXTLENGTH  = 'textLength';
    const TYPE_TIME        = 'time';
    const TYPE_WHOLE       = 'whole';

    /* Data validation error styles */
    const STYLE_STOP         = 'stop';
    const STYLE_WARNING      = 'warning';
    const STYLE_INFORMATION  = 'information';

    /* Data validation operators */
    const OPERATOR_BETWEEN             = 'between';
    const OPERATOR_EQUAL               = 'equal';
    const OPERATOR_GREATERTHAN         = 'greaterThan';
    const OPERATOR_GREATERTHANOREQUAL  = 'greaterThanOrEqual';
    const OPERATOR_LESSTHAN            = 'lessThan';
    const OPERATOR_LESSTHANOREQUAL     = 'lessThanOrEqual';
    const OPERATOR_NOTBETWEEN          = 'notBetween';
    const OPERATOR_NOTEQUAL            = 'notEqual';

    /**
     * Formula 1
     *
     * @var StringHelper
     */
    private $_formula1;

    /**
     * Formula 2
     *
     * @var StringHelper
     */
    private $_formula2;

    /**
     * Type
     *
     * @var StringHelper
     */
    private $_type = PHPExcel_Cell_DataValidation::TYPE_NONE;

    /**
     * Error style
     *
     * @var StringHelper
     */
    private $_errorStyle = PHPExcel_Cell_DataValidation::STYLE_STOP;

    /**
     * Operator
     *
     * @var StringHelper
     */
    private $_operator;

    /**
     * Allow Blank
     *
     * @var boolean
     */
    private $_allowBlank;

    /**
     * Show DropDown
     *
     * @var boolean
     */
    private $_showDropDown;

    /**
     * Show InputMessage
     *
     * @var boolean
     */
    private $_showInputMessage;

    /**
     * Show ErrorMessage
     *
     * @var boolean
     */
    private $_showErrorMessage;

    /**
     * Error title
     *
     * @var StringHelper
     */
    private $_errorTitle;

    /**
     * Error
     *
     * @var StringHelper
     */
    private $_error;

    /**
     * Prompt title
     *
     * @var StringHelper
     */
    private $_promptTitle;

    /**
     * Prompt
     *
     * @var StringHelper
     */
    private $_prompt;

    /**
     * Create a new PHPExcel_Cell_DataValidation
     */
    public function __construct()
    {
        // Initialise member variables
        $this->_formula1          = '';
        $this->_formula2          = '';
        $this->_type              = PHPExcel_Cell_DataValidation::TYPE_NONE;
        $this->_errorStyle        = PHPExcel_Cell_DataValidation::STYLE_STOP;
        $this->_operator          = '';
        $this->_allowBlank        = FALSE;
        $this->_showDropDown      = FALSE;
        $this->_showInputMessage  = FALSE;
        $this->_showErrorMessage  = FALSE;
        $this->_errorTitle        = '';
        $this->_error             = '';
        $this->_promptTitle       = '';
        $this->_prompt            = '';
    }

    /**
     * Get Formula 1
     *
     * @return StringHelper
     */
    public function getFormula1() {
        return $this->_formula1;
    }

    /**
     * Set Formula 1
     *
     * @param  StringHelper    $value
     * @return PHPExcel_Cell_DataValidation
     */
    public function setFormula1($value = '') {
        $this->_formula1 = $value;
        return $this;
    }

    /**
     * Get Formula 2
     *
     * @return StringHelper
     */
    public function getFormula2() {
        return $this->_formula2;
    }

    /**
     * Set Formula 2
     *
     * @param  StringHelper    $value
     * @return PHPExcel_Cell_DataValidation
     */
    public function setFormula2($value = '') {
        $this->_formula2 = $value;
        return $this;
    }

    /**
     * Get Type
     *
     * @return StringHelper
     */
    public function getType() {
        return $this->_type;
    }

    /**
     * Set Type
     *
     * @param  StringHelper    $value
     * @return PHPExcel_Cell_DataValidation
     */
    public function setType($value = PHPExcel_Cell_DataValidation::TYPE_NONE) {
        $this->_type = $value;
        return $this;
    }

    /**
     * Get Error style
     *
     * @return StringHelper
     */
    public function getErrorStyle() {
        return $this->_errorStyle;
    }

    /**
     * Set Error style
     *
     * @param  StringHelper    $value
     * @return PHPExcel_Cell_DataValidation
     */
    public function setErrorStyle($value = PHPExcel_Cell_DataValidation::STYLE_STOP) {
        $this->_errorStyle = $value;
        return $this;
    }

    /**
     * Get Operator
     *
     * @return StringHelper
     */
    public function getOperator() {
        return $this->_operator;
    }

    /**
     * Set Operator
     *
     * @param  StringHelper    $value
     * @return PHPExcel_Cell_DataValidation
     */
    public function setOperator($value = '') {
        $this->_operator = $value;
        return $this;
    }

    /**
     * Get Allow Blank
     *
     * @return boolean
     */
    public function getAllowBlank() {
        return $this->_allowBlank;
    }

    /**
     * Set Allow Blank
     *
     * @param  boolean    $value
     * @return PHPExcel_Cell_DataValidation
     */
    public function setAllowBlank($value = false) {
        $this->_allowBlank = $value;
        return $this;
    }

    /**
     * Get Show DropDown
     *
     * @return boolean
     */
    public function getShowDropDown() {
        return $this->_showDropDown;
    }

    /**
     * Set Show DropDown
     *
     * @param  boolean    $value
     * @return PHPExcel_Cell_DataValidation
     */
    public function setShowDropDown($value = false) {
        $this->_showDropDown = $value;
        return $this;
    }

    /**
     * Get Show InputMessage
     *
     * @return boolean
     */
    public function getShowInputMessage() {
        return $this->_showInputMessage;
    }

    /**
     * Set Show InputMessage
     *
     * @param  boolean    $value
     * @return PHPExcel_Cell_DataValidation
     */
    public function setShowInputMessage($value = false) {
        $this->_showInputMessage = $value;
        return $this;
    }

    /**
     * Get Show ErrorMessage
     *
     * @return boolean
     */
    public function getShowErrorMessage() {
        return $this->_showErrorMessage;
    }

    /**
     * Set Show ErrorMessage
     *
     * @param  boolean    $value
     * @return PHPExcel_Cell_DataValidation
     */
    public function setShowErrorMessage($value = false) {
        $this->_showErrorMessage = $value;
        return $this;
    }

    /**
     * Get Error title
     *
     * @return StringHelper
     */
    public function getErrorTitle() {
        return $this->_errorTitle;
    }

    /**
     * Set Error title
     *
     * @param  StringHelper    $value
     * @return PHPExcel_Cell_DataValidation
     */
    public function setErrorTitle($value = '') {
        $this->_errorTitle = $value;
        return $this;
    }

    /**
     * Get Error
     *
     * @return StringHelper
     */
    public function getError() {
        return $this->_error;
    }

    /**
     * Set Error
     *
     * @param  StringHelper    $value
     * @return PHPExcel_Cell_DataValidation
     */
    public function setError($value = '') {
        $this->_error = $value;
        return $this;
    }

    /**
     * Get Prompt title
     *
     * @return StringHelper
     */
    public function getPromptTitle() {
        return $this->_promptTitle;
    }

    /**
     * Set Prompt title
     *
     * @param  StringHelper    $value
     * @return PHPExcel_Cell_DataValidation
     */
    public function setPromptTitle($value = '') {
        $this->_promptTitle = $value;
        return $this;
    }

    /**
     * Get Prompt
     *
     * @return StringHelper
     */
    public function getPrompt() {
        return $this->_prompt;
    }

    /**
     * Set Prompt
     *
     * @param  StringHelper    $value
     * @return PHPExcel_Cell_DataValidation
     */
    public function setPrompt($value = '') {
        $this->_prompt = $value;
        return $this;
    }

    /**
     * Get hash code
     *
     * @return StringHelper    Hash code
     */
    public function getHashCode() {
        return md5(
              $this->_formula1
            . $this->_formula2
            . $this->_type = PHPExcel_Cell_DataValidation::TYPE_NONE
            . $this->_errorStyle = PHPExcel_Cell_DataValidation::STYLE_STOP
            . $this->_operator
            . ($this->_allowBlank ? 't' : 'f')
            . ($this->_showDropDown ? 't' : 'f')
            . ($this->_showInputMessage ? 't' : 'f')
            . ($this->_showErrorMessage ? 't' : 'f')
            . $this->_errorTitle
            . $this->_error
            . $this->_promptTitle
            . $this->_prompt
            . __CLASS__
        );
    }

    /**
     * Implement PHP __clone to create a deep clone, not just a shallow copy.
     */
    public function __clone() {
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            if (is_object($value)) {
                $this->$key = clone $value;
            } else {
                $this->$key = $value;
            }
        }
    }
}
