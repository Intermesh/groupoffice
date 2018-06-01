<?php
/***********************************************
* File      :   syncobjects.php
* Project   :   Z-Push
* Descr     :   Defines general behavoir of sub-WBXML
*               entities (Sync* objects) that can be parsed
*               directly (as a stream) from WBXML.
*               They are automatically decoded
*               according to $mapping by the Streamer,
*               and the Sync WBXML mappings.
*
* Created   :   01.10.2007
*
* Copyright 2007 - 2016 Zarafa Deutschland GmbH
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License, version 3,
* as published by the Free Software Foundation.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU Affero General Public License for more details.
*
* You should have received a copy of the GNU Affero General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* Consult LICENSE file for details
************************************************/

abstract class SyncObject extends Streamer {
    const STREAMER_CHECKS = 6;
    const STREAMER_CHECK_REQUIRED = 7;
    const STREAMER_CHECK_ZEROORONE = 8;
    const STREAMER_CHECK_NOTALLOWED = 9;
    const STREAMER_CHECK_ONEVALUEOF = 10;
    const STREAMER_CHECK_SETZERO = "setToValue0";
    const STREAMER_CHECK_SETONE = "setToValue1";
    const STREAMER_CHECK_SETTWO = "setToValue2";
    const STREAMER_CHECK_SETEMPTY = "setToValueEmpty";
    const STREAMER_CHECK_CMPLOWER = 13;
    const STREAMER_CHECK_CMPHIGHER = 14;
    const STREAMER_CHECK_LENGTHMAX = 15;
    const STREAMER_CHECK_EMAIL   = 16;

    protected $unsetVars;
    protected $supportsPrivateStripping;


    public function __construct($mapping) {
        $this->unsetVars = array();
        $this->supportsPrivateStripping = false;
        parent::__construct($mapping);
    }

    /**
     * Sets all supported but not transmitted variables
     * of this SyncObject to an "empty" value, so they are deleted when being saved
     *
     * @param array     $supportedFields        array with all supported fields, if available
     *
     * @access public
     * @return boolean
     */
    public function emptySupported($supportedFields) {
        // Some devices do not send supported tag. In such a case remove all not set properties.
        if (($supportedFields === false || !is_array($supportedFields) || (empty($supportedFields)))) {
            if (defined('UNSET_UNDEFINED_PROPERTIES') && UNSET_UNDEFINED_PROPERTIES && ($this instanceof SyncContact || $this instanceof SyncAppointment || $this instanceof SyncTask)) {
                ZLog::Write(LOGLEVEL_INFO, sprintf("%s->emptySupported(): no supported list available, emptying all not set parameters", get_class($this)));
                $supportedFields = array_keys($this->mapping);
            }
            else {
                return false;
            }
        }

        foreach ($supportedFields as $field) {
            if (!isset($this->mapping[$field])) {
                ZLog::Write(LOGLEVEL_WARN, sprintf("Field '%s' is supposed to be emptied but is not defined for '%s'", $field, get_class($this)));
                continue;
            }
            $var = $this->mapping[$field][self::STREAMER_VAR];
            // add var to $this->unsetVars if $var is not set
            if (!isset($this->$var))
                $this->unsetVars[] = $var;
        }
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("Supported variables to be unset: %s", implode(',', $this->unsetVars)));
        return true;
    }


    /**
     * Compares this a SyncObject to another.
     * In case that all available mapped fields are exactly EQUAL, it returns true
     *
     * @see SyncObject
     * @param SyncObject $odo other SyncObject
     * @param boolean $log flag to turn on logging
     * @param boolean $strictTypeCompare to enforce type matching
     * @return boolean
     */
    public function equals($odo, $log = false, $strictTypeCompare = false) {
        if ($odo === false)
            return false;

        // check objecttype
        if (! ($odo instanceof SyncObject)) {
            ZLog::Write(LOGLEVEL_DEBUG, "SyncObject->equals() the target object is not a SyncObject");
            return false;
        }

        // check for mapped fields
        foreach ($this->mapping as $v) {
            $val = $v[self::STREAMER_VAR];
            // array of values?
            if (isset($v[self::STREAMER_ARRAY])) {
                // if neither array is created then don't fail the comparison
                if (!isset($this->$val) && !isset($odo->$val)) {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("SyncObject->equals() array '%s' is NOT SET in either object", $val));
                    continue;
                }
                elseif (is_array($this->$val) && is_array($odo->$val)) {
                    // if both arrays exist then seek for differences in the arrays
                    if (count(array_diff($this->$val, $odo->$val)) + count(array_diff($odo->$val, $this->$val)) > 0) {
                        ZLog::Write(LOGLEVEL_DEBUG, sprintf("SyncObject->equals() items in array '%s' differ", $val));
                        return false;
                    }
                }
                else {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("SyncObject->equals() array '%s' is set in one but not the other object", $val));
                    return false;
                }
            }
            else {
                if (isset($this->$val) && isset($odo->$val)) {
                    if ($strictTypeCompare){
                        if ($this->$val !== $odo->$val){
                            ZLog::Write(LOGLEVEL_DEBUG, sprintf("SyncObject->equals() false on field '%s': '%s' != '%s' using strictTypeCompare", $val, Utils::PrintAsString($this->$val), Utils::PrintAsString($odo->$val)));
                            return false;
                        }
                    } else {
                        if ($this->$val != $odo->$val){
                            ZLog::Write(LOGLEVEL_DEBUG, sprintf("SyncObject->equals() false on field '%s': '%s' != '%s'", $val, Utils::PrintAsString($this->$val), Utils::PrintAsString($odo->$val)));
                            return false;
                        }
                    }
                }
                else if (!isset($this->$val) && !isset($odo->$val)) {
                    continue;
                }
                else {
                    ZLog::Write(LOGLEVEL_DEBUG, sprintf("SyncObject->equals() false because field '%s' is only defined at one obj: '%s' != '%s'", $val, Utils::PrintAsString(isset($this->$val)), Utils::PrintAsString(isset($odo->$val))));
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Compares this a SyncObject to another, while printing out all properties and showing where they differ.
     *
     * @see SyncObject
     * @param SyncObject    $odo                other SyncObject
     * @param string        $odoName            how different data should be named
     * @param array         $supportedFields    the list of the supported fields of the device
     * @param int           $recCount           recursion counter
     *
     * @access public
     * @return array with one property per line, key being the property instance variable name
     */
    public function EvaluateAndCompare($odo, $odoName = "", $supportedFields, $keyprefix = "", $recCount = 0) {
        if ($odo === false)
            return false;

        // check objecttype
        if (! ($odo instanceof SyncObject) || get_class($this) != get_class($odo)) {
            ZLog::Write(LOGLEVEL_DEBUG, "SyncObject->EvaluateAndCompare() the target object is not a SyncObject or the objects are different SyncObjects: '%s' and '%s'", get_class($this), get_class($odo));
            return false;
        }

        // If $supportedFields is false, it means that the device doesn't have any supported fields.
        // Set it to an empty array in order to avoid warnings.
        if ($supportedFields == false) {
            $supportedFields = array();
        }

        $out = array();
        if ($keyprefix)
            $keyprefix = $keyprefix . $recCount;

        // check for mapped fields
        foreach ($this->mapping as $k=>$v) {
            // Do not bother with the properties for which notifications aren't required
            // or if they are not set
            if (!isset($v[self::STREAMER_RONOTIFY]) || !$v[self::STREAMER_RONOTIFY] || (!isset($this->{$v[self::STREAMER_VAR]}) && !isset($odo->{$v[self::STREAMER_VAR]}))) {
                continue;
            }
            $val = $v[self::STREAMER_VAR];
            // both values are set case
            if (isset($this->$val) && isset($odo->$val)) {
                if (isset($v[self::STREAMER_TYPE])) {
                    // Do the recursive compare of sub SyncObject
                    if ($this->$val instanceof SyncObject) {
                        $out += $this->$val->EvaluateAndCompare($odo->$val, $odoName, $supportedFields, substr(get_class($this->$val), 4), $recCount++);
                    }
                    // array of values?
                    else if (isset($v[self::STREAMER_ARRAY])) {
                        // if both arrays exist then seek for differences in the arrays
                        if (count(array_diff($this->$val, $odo->$val)) + count(array_diff($odo->$val, $this->$val)) > 0) {
                            if ($v[self::STREAMER_TYPE] == "SyncAppointmentException") {
                                $out[$keyprefix.$val] = "An exception was changed.";
                            }
                            else {
                                $out[$keyprefix.$val] = implode(", ", $this->$val) ." - ". $odoName .": ". implode(", ", $odo->$val);
                            }
                        }
                    }
                    // if they are streams, compare the streams
                    else if ($v[self::STREAMER_TYPE] == self::STREAMER_TYPE_STREAM_ASPLAIN || $v[self::STREAMER_TYPE] == self::STREAMER_TYPE_STREAM_ASBASE64) {
                        // Remove the \r as it seems to be only in one of the streams
                        $t = str_replace("\r", "", stream_get_contents($this->$val));
                        $o = str_replace("\r", "", stream_get_contents($odo->$val));
                        if ($this instanceof SyncBaseBody) {
                            $out["Body/Description"] = (trim($t) == trim($o)) ? "No changes made" : $t." - ". $odoName .": ".$o;
                        }
                        else if($v[self::STREAMER_TYPE] == self::STREAMER_TYPE_STREAM_ASPLAIN) {
                            $out[$keyprefix.$val] = (trim($t) == trim($o)) ? $t : $t." - ". $odoName .": ".$o;
                        }
                        else {
                            $out[$keyprefix.$val] = "Binary data changed";
                        }
                    }
                    // do the nice date formatting
                    else if ($v[self::STREAMER_TYPE] == self::STREAMER_TYPE_DATE || $v[self::STREAMER_TYPE] == self::STREAMER_TYPE_DATE_DASHES) {
                        if($this->$val == $odo->$val) {
                            $out[$keyprefix.$val] = Utils::GetFormattedTime($this->$val);
                        }
                        else {
                            $out[$keyprefix.$val] = (strlen($this->$val) ?
                                    Utils::GetFormattedTime($this->$val):"undefined") ." - ". $odoName .": ".
                                    (strlen($odo->$val) ? Utils::GetFormattedTime($odo->$val) : "undefined");
                        }
                    }
                    // else just compare their values and print human friendly if necessary
                    else {
                        if($this->$val == $odo->$val) {
                            $out[$keyprefix.$val] = $this->GetNameFromPropertyValue($v, $this->$val);
                        }
                        else {
                            $out[$keyprefix.$val] = (strlen($this->$val) ? $this->GetNameFromPropertyValue($v, $this->$val) : "undefined") .
                                    " - ". $odoName .": ".
                                    (strlen($odo->$val) ? $odo->GetNameFromPropertyValue($v, $odo->$val) : "undefined");
                        }
                    }
                }
                // array of values?
                else if (isset($v[self::STREAMER_ARRAY])) {
                    // if both arrays exist then seek for differences in the arrays
                    if (count(array_diff($this->$val, $odo->$val)) + count(array_diff($odo->$val, $this->$val)) > 0) {
                        $out[$keyprefix.$val] = implode(", ", $this->$val) ." - ". $odoName .": ". implode(", ", $odo->$val);
                    }
                }
                // else just compare their values
                else {
                    if($this->$val == $odo->$val) {
                        if (! ($this instanceof SyncRecurrence)) {
                            $out[$keyprefix.$val] = ($this->GetNameFromPropertyValue($v, $this->$val));
                        }
                    }
                    else {
                        if ($this instanceof SyncRecurrence) {
                            $out["Recurrence"] = "Recurrence changed";
                        }
                        else {
                            $out[$keyprefix.$val] = (strlen($this->$val) ? $this->GetNameFromPropertyValue($v, $this->$val) : "undefined") .
                                    " - ". $odoName .": ".
                                    (strlen($odo->$val) ? ($odo->GetNameFromPropertyValue($v, $odo->$val)) : "undefined");
                        }
                    }
                }
            }
            // a value removed in $odo case
            elseif (isset($this->$val)) {
                // If it's a supported property and it's not set, it was removed.
                // Otherwise it's a ghosted property and the device didn't send it, so we don't have to care about that case.
                if (in_array($k, $supportedFields)) {
                    if ((is_scalar($this->$val) && strlen($this->$val)) || (!is_scalar($this->$val) && !empty($this->$val))) {
                        $out[$keyprefix.$val] = (is_array($this->$val) ? implode(",", $this->$val) : $this->GetNameFromPropertyValue($v, $this->$val)) .
                        " - " . $odoName .": value completely removed";
                    }
                }
                // there is no data sent for SyncMail, so just output its values
                else if ($this instanceof SyncMail) {
                    if (isset($v[self::STREAMER_TYPE]) && ($v[self::STREAMER_TYPE] == self::STREAMER_TYPE_DATE || $v[self::STREAMER_TYPE] == self::STREAMER_TYPE_DATE_DASHES)) {
                        $out[$keyprefix.$val] = Utils::GetFormattedTime($this->$val);
                    }
                    else {
                        $out[$keyprefix.$val] = $this->GetNameFromPropertyValue($v, $this->$val);
                    }
                }
            }
            // a value added to $odo case
            elseif (isset($odo->$val)) {
                if (stripos($keyprefix, "MailFlags") !== false) {
                    $out["Flags"] = "To-do flags were added";
                }
                else if (isset($v[self::STREAMER_TYPE])) {
                    if($v[self::STREAMER_TYPE] == "SyncAppointmentException") {
                        $out[$keyprefix.$val] = "Not set - " . $odoName . ": an exception was added";
                    }
                    else {
                        $out[$keyprefix.$val] = "Not set - " . $odoName . ": " . $odo->GetNameFromPropertyValue($v, $odo->$val) . " (value added)";
                    }
                }
                else if (isset($v[self::STREAMER_ARRAY])) {
                    // if both arrays exist then seek for differences in the arrays
                    $out[$keyprefix.$val] = "Not set - ". $odoName .": ". implode(", ", $odo->$val) . " (value added)";
                }
                else {
                    $out[$keyprefix.$val] = "Not set - " . $odoName . ": " . $odo->GetNameFromPropertyValue($v, $odo->$val) . " (value added)";
                }
            }
        }

        return $out;
    }

    /**
     * String representation of the object
     *
     * @return String
     */
    public function __toString() {
        $str = get_class($this) . " (\n";

        $streamerVars = array();
        foreach ($this->mapping as $k=>$v)
            $streamerVars[$v[self::STREAMER_VAR]] = (isset($v[self::STREAMER_TYPE]))?$v[self::STREAMER_TYPE]:false;

        foreach (get_object_vars($this) as $k=>$v) {
            if ($k == "mapping") continue;

            if (array_key_exists($k, $streamerVars))
                $strV = "(S) ";
            else
                $strV = "";

            // self::STREAMER_ARRAY ?
            if (is_array($v)) {
                $str .= "\t". $strV . $k ."(Array) size: " . count($v) ."\n";
                foreach ($v as $value) $str .= "\t\t". Utils::PrintAsString($value) ."\n";
            }
            else if ($v instanceof SyncObject) {
                $str .= "\t". $strV .$k ." => ". str_replace("\n", "\n\t\t\t", $v->__toString()) . "\n";
            }
            else
                $str .= "\t". $strV .$k ." => " . (isset($this->$k)? Utils::PrintAsString($this->$k) :"null") . "\n";
        }
        $str .= ")";

        return $str;
    }

    /**
     * Returns the properties which have to be unset on the server
     *
     * @access public
     * @return array
     */
    public function getUnsetVars() {
        return $this->unsetVars;
    }

    /**
     * Removes not necessary data from the object
     *
     * @access public
     * @return boolean
     */
    public function StripData($flags = 0) {
        if ($flags === 0 && isset($this->unsetVars)) {
            unset($this->unsetVars);
        }
        return parent::StripData($flags);
    }

    /**
     * Indicates if a SyncObject supports the private flag and stripping of private data.
     * If an object does not support it, it will not be sent to the client but permanently be excluded from the sync.
     *
     * @access public
     * @return boolean - default false defined in constructor - overwritten by implementation
     */
    public function SupportsPrivateStripping() {
        return $this->supportsPrivateStripping;
    }

    /**
     * Method checks if the object has the minimum of required parameters
     * and fullfills semantic dependencies
     *
     * General checks:
     *     STREAMER_CHECK_REQUIRED      may have as value false (do not fix, ignore object!) or set-to-values: STREAMER_CHECK_SETZERO/ONE/TWO, STREAMER_CHECK_SETEMPTY
     *     STREAMER_CHECK_ZEROORONE     may be 0 or 1, if none of these, set-to-values: STREAMER_CHECK_SETZERO or STREAMER_CHECK_SETONE
     *     STREAMER_CHECK_NOTALLOWED    fails if is set
     *     STREAMER_CHECK_ONEVALUEOF    expects an array with accepted values, fails if value is not in array
     *
     * Comparison:
     *     STREAMER_CHECK_CMPLOWER      compares if the current parameter is lower as a literal or another parameter of the same object
     *     STREAMER_CHECK_CMPHIGHER     compares if the current parameter is higher as a literal or another parameter of the same object
     *
     * @param boolean   $logAsDebug     (opt) default is false, so messages are logged in WARN log level
     *
     * @access public
     * @return boolean
     */
    public function Check($logAsDebug = false) {
        // semantic checks general "turn off switch"
        if (defined("DO_SEMANTIC_CHECKS") && DO_SEMANTIC_CHECKS === false) {
            ZLog::Write(LOGLEVEL_DEBUG, "SyncObject->Check(): semantic checks disabled. Check your config for 'DO_SEMANTIC_CHECKS'.");
            return true;
        }

        $defaultLogLevel = LOGLEVEL_WARN;

        // in some cases non-false checks should not provoke a WARN log but only a DEBUG log
        if ($logAsDebug)
            $defaultLogLevel = LOGLEVEL_DEBUG;

        $objClass = get_class($this);
        foreach ($this->mapping as $k=>$v) {

            // check sub-objects recursively
            if (isset($v[self::STREAMER_TYPE]) && isset($this->{$v[self::STREAMER_VAR]})) {
                if ($this->{$v[self::STREAMER_VAR]} instanceof SyncObject) {
                    if (! $this->{$v[self::STREAMER_VAR]}->Check($logAsDebug))
                        return false;
                }
                else if (is_array($this->{$v[self::STREAMER_VAR]})) {
                    foreach ($this->{$v[self::STREAMER_VAR]} as $subobj)
                        if ($subobj instanceof SyncObject && !$subobj->Check($logAsDebug))
                            return false;
                }
            }

            if (isset($v[self::STREAMER_CHECKS])) {
                foreach ($v[self::STREAMER_CHECKS] as $rule => $condition) {
                    // check REQUIRED settings
                    if ($rule === self::STREAMER_CHECK_REQUIRED && (!isset($this->{$v[self::STREAMER_VAR]}) || $this->{$v[self::STREAMER_VAR]} === '' ) ) {
                        // parameter is not set but ..
                        // requested to set to 0
                        if ($condition === self::STREAMER_CHECK_SETZERO) {
                            $this->{$v[self::STREAMER_VAR]} = 0;
                            ZLog::Write($defaultLogLevel, sprintf("SyncObject->Check(): Fixed object from type %s: parameter '%s' is set to 0", $objClass, $v[self::STREAMER_VAR]));
                        }
                        // requested to be set to 1
                        else if ($condition === self::STREAMER_CHECK_SETONE) {
                            $this->{$v[self::STREAMER_VAR]} = 1;
                            ZLog::Write($defaultLogLevel, sprintf("SyncObject->Check(): Fixed object from type %s: parameter '%s' is set to 1", $objClass, $v[self::STREAMER_VAR]));
                        }
                        // requested to be set to 2
                        else if ($condition === self::STREAMER_CHECK_SETTWO) {
                            $this->{$v[self::STREAMER_VAR]} = 2;
                            ZLog::Write($defaultLogLevel, sprintf("SyncObject->Check(): Fixed object from type %s: parameter '%s' is set to 2", $objClass, $v[self::STREAMER_VAR]));
                        }
                        // requested to be set to ''
                        else if ($condition === self::STREAMER_CHECK_SETEMPTY) {
                            if (!isset($this->{$v[self::STREAMER_VAR]})) {
                                $this->{$v[self::STREAMER_VAR]} = '';
                                ZLog::Write($defaultLogLevel, sprintf("SyncObject->Check(): Fixed object from type %s: parameter '%s' is set to ''", $objClass, $v[self::STREAMER_VAR]));
                            }
                        }
                        // there is another value !== false
                        else if ($condition !== false) {
                            $this->{$v[self::STREAMER_VAR]} = $condition;
                            ZLog::Write($defaultLogLevel, sprintf("SyncObject->Check(): Fixed object from type %s: parameter '%s' is set to '%s'", $objClass, $v[self::STREAMER_VAR], $condition));

                        }
                        // no fix available!
                        else {
                            ZLog::Write($defaultLogLevel, sprintf("SyncObject->Check(): Unmet condition in object from type %s: parameter '%s' is required but not set. Check failed!", $objClass, $v[self::STREAMER_VAR]));
                            return false;
                        }
                    } // end STREAMER_CHECK_REQUIRED


                    // check STREAMER_CHECK_ZEROORONE
                    if ($rule === self::STREAMER_CHECK_ZEROORONE && isset($this->{$v[self::STREAMER_VAR]})) {
                        if ($this->{$v[self::STREAMER_VAR]} != 0 && $this->{$v[self::STREAMER_VAR]} != 1) {
                            $newval = $condition === self::STREAMER_CHECK_SETZERO ? 0:1;
                            $this->{$v[self::STREAMER_VAR]} = $newval;
                            ZLog::Write($defaultLogLevel, sprintf("SyncObject->Check(): Fixed object from type %s: parameter '%s' is set to '%s' as it was not 0 or 1", $objClass, $v[self::STREAMER_VAR], $newval));
                        }
                    }// end STREAMER_CHECK_ZEROORONE


                    // check STREAMER_CHECK_ONEVALUEOF
                    if ($rule === self::STREAMER_CHECK_ONEVALUEOF && isset($this->{$v[self::STREAMER_VAR]})) {
                        if (!in_array($this->{$v[self::STREAMER_VAR]}, $condition)) {
                            ZLog::Write($defaultLogLevel, sprintf("SyncObject->Check(): object from type %s: parameter '%s'->'%s' is not in the range of allowed values.", $objClass, $v[self::STREAMER_VAR], $this->{$v[self::STREAMER_VAR]}));
                            return false;
                        }
                    }// end STREAMER_CHECK_ONEVALUEOF


                    // Check value compared to other value or literal
                    if ($rule === self::STREAMER_CHECK_CMPHIGHER || $rule === self::STREAMER_CHECK_CMPLOWER) {
                        if (isset($this->{$v[self::STREAMER_VAR]})) {
                            $cmp = false;
                            // directly compare against literals
                            if (is_int($condition)) {
                                $cmp = $condition;
                            }
                            // check for invalid compare-to
                            else if (!isset($this->mapping[$condition])) {
                                ZLog::Write(LOGLEVEL_ERROR, sprintf("SyncObject->Check(): Can not compare parameter '%s' against the other value '%s' as it is not defined object from type %s. Please report this! Check skipped!", $objClass, $v[self::STREAMER_VAR], $condition));
                                continue;
                            }
                            else {
                                $cmpPar = $this->mapping[$condition][self::STREAMER_VAR];
                                if (isset($this->$cmpPar))
                                    $cmp = $this->$cmpPar;
                            }

                            if ($cmp === false) {
                                ZLog::Write(LOGLEVEL_WARN, sprintf("SyncObject->Check(): Unmet condition in object from type %s: parameter '%s' can not be compared, as the comparable is not set. Check failed!", $objClass, $v[self::STREAMER_VAR]));
                                return false;
                            }
                            if ( ($rule == self::STREAMER_CHECK_CMPHIGHER && $this->{$v[self::STREAMER_VAR]} < $cmp) ||
                                 ($rule == self::STREAMER_CHECK_CMPLOWER  && $this->{$v[self::STREAMER_VAR]} > $cmp)
                                ) {

                                ZLog::Write(LOGLEVEL_WARN, sprintf("SyncObject->Check(): Unmet condition in object from type %s: parameter '%s' is %s than '%s'. Check failed!",
                                                                    $objClass,
                                                                    $v[self::STREAMER_VAR],
                                                                    (($rule === self::STREAMER_CHECK_CMPHIGHER)?'LOWER':'HIGHER'),
                                                                    ((isset($cmpPar)?$cmpPar:$condition))  ));
                                return false;
                            }
                        }
                    } // STREAMER_CHECK_CMP*


                    // check STREAMER_CHECK_LENGTHMAX
                    if ($rule === self::STREAMER_CHECK_LENGTHMAX && isset($this->{$v[self::STREAMER_VAR]})) {

                        if (is_array($this->{$v[self::STREAMER_VAR]})) {
                            // implosion takes 2bytes, so we just assume ", " here
                            $chkstr = implode(", ", $this->{$v[self::STREAMER_VAR]});
                        }
                        else
                            $chkstr = $this->{$v[self::STREAMER_VAR]};

                        if (strlen($chkstr) > $condition) {
                            ZLog::Write(LOGLEVEL_WARN, sprintf("SyncObject->Check(): object from type %s: parameter '%s' is longer than %d. Check failed", $objClass, $v[self::STREAMER_VAR], $condition));
                            return false;
                        }
                    }// end STREAMER_CHECK_LENGTHMAX


                    // check STREAMER_CHECK_EMAIL
                    // if $condition is false then the check really fails. Otherwise invalid emails are removed.
                    // if nothing is left (all emails were false), the parameter is set to condition
                    if ($rule === self::STREAMER_CHECK_EMAIL && isset($this->{$v[self::STREAMER_VAR]})) {
                        if ($condition === false && ( (is_array($this->{$v[self::STREAMER_VAR]}) && empty($this->{$v[self::STREAMER_VAR]})) || strlen($this->{$v[self::STREAMER_VAR]}) == 0) )
                            continue;

                        $as_array = false;

                        if (is_array($this->{$v[self::STREAMER_VAR]})) {
                            $mails = $this->{$v[self::STREAMER_VAR]};
                            $as_array = true;
                        }
                        else {
                            $mails = array( $this->{$v[self::STREAMER_VAR]} );
                        }

                        $output = array();
                        foreach ($mails as $mail) {
                            if (! Utils::CheckEmail($mail)) {
                                ZLog::Write(LOGLEVEL_WARN, sprintf("SyncObject->Check(): object from type %s: parameter '%s' contains an invalid email address '%s'. Address is removed.", $objClass, $v[self::STREAMER_VAR], $mail));
                            }
                            else
                                $output[] = $mail;
                        }
                        if (count($mails) != count($output)) {
                            if ($condition === false)
                                return false;

                            // nothing left, use $condition as new value
                            if (count($output) == 0)
                                $output[] = $condition;

                            // if we are allowed to rewrite the attribute, we do that
                            if ($as_array)
                                $this->{$v[self::STREAMER_VAR]} = $output;
                            else
                                $this->{$v[self::STREAMER_VAR]} = $output[0];
                        }
                    }// end STREAMER_CHECK_EMAIL


                } // foreach CHECKS
            } // isset CHECKS
        } // foreach mapping

        return true;
    }

    /**
     * Returns human friendly property name from its value if a mapping is available.
     *
     * @param array $v
     * @param mixed $val
     *
     * @access public
     * @return mixed
     */
    public function GetNameFromPropertyValue($v, $val) {
        if (isset($v[self::STREAMER_VALUEMAP][$val])) {
            return $v[self::STREAMER_VALUEMAP][$val];
        }
        return $val;
    }
}
