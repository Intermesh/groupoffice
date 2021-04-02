<?php

/**
* A Class for representing properties within an iCalendar
*
* @package awl
* @subpackage iCalProp
* @author Andrew McMillan <andrew@mcmillan.net.nz>
* @copyright Catalyst IT Ltd, Morphoss Ltd <http://www.morphoss.com/>
* @license   http://gnu.org/copyleft/gpl.html GNU GPL v2 or later
*/

class iCalProp {
  /**#@+
   * @access private
   */

  /**
   * The name of this property
   *
   * @var string
   */
  var $name;

  /**
   * An array of parameters to this property, represented as key/value pairs.
   *
   * @var array
   */
  var $parameters;

  /**
   * The value of this property.
   *
   * @var string
   */
  var $content;

  /**
   * The original value that this was parsed from, if that's the way it happened.
   *
   * @var string
   */
  var $rendered;

  /**#@-*/

  /**
   * The constructor parses the incoming string, which is formatted as per RFC2445 as a
   *   propname[;param1=pval1[; ... ]]:propvalue
   * however we allow ourselves to assume that the RFC2445 content unescaping has already
   * happened when iCalComponent::ParseFrom() called iCalComponent::UnwrapComponent().
   *
   * @param string $propstring The string from the iCalendar which contains this property.
   */
  function __construct( $propstring = null ) {
    $this->name = "";
    $this->content = "";
    $this->parameters = array();
    unset($this->rendered);
    if ( $propstring != null && gettype($propstring) == 'string' ) {
      $this->ParseFrom($propstring);
    }
  }


  /**
   * The constructor parses the incoming string, which is formatted as per RFC2445 as a
   *   propname[;param1=pval1[; ... ]]:propvalue
   * however we allow ourselves to assume that the RFC2445 content unescaping has already
   * happened when iCalComponent::ParseFrom() called iCalComponent::UnwrapComponent().
   *
   * @param string $propstring The string from the iCalendar which contains this property.
   */
  function ParseFrom( $propstring ) {
//     $this->rendered = (strlen($propstring) < 72 ? $propstring : null);  // Only pre-rendered if we didn't unescape it
    // FMBIETE - unset rendered content; if we alter some properties inside an object (VEVENT/ATTENDEE for example) we won't see the changes calling Render
    // FIXME: if you find the bug, let me know
        //$ical = new iCalComponent();
        //$ical->ParseFrom(VCALENDAR DATA);
            // Doing this will refresh the rendered data, but if this line is not executed, you won't see PARTSTAT changed
        //$ical->SetPValue("METHOD", "REPLY");
        //$ical->SetCPParameterValue("VEVENT", "ATTENDEE", "PARTSTAT", "ACCEPTED");
        //printf("%s\n", $ical->Render());
    unset($this->rendered);

    // Unescape newlines
    $unescaped = preg_replace('{\\\\[nN]}', "\n", $propstring);

    /*
     * Split propname with params from propvalue. Searches for the first unquoted COLON.
     *
     * RFC5545 3.2
     *
     * Property parameter values that contain the COLON, SEMICOLON, or COMMA
     * character separators MUST be specified as quoted-string text values.
     * Property parameter values MUST NOT contain the DQUOTE character.
     */
    $split = $this->SplitQuoted($unescaped, ':', 2);
    if (count($split) != 2) {
      // Bad things happended...
      ZLog::Write(LOGLEVEL_DEBUG, sprintf("iCalendar->ParseFrom(): Couldn't parse property from string: '%s', skipping", $unescaped));
      return;
    }
    list($prop, $value) = $split;

    // Unescape ESCAPED-CHAR
    $this->content = preg_replace( "/\\\\([,;:\"\\\\])/", '$1', $value);

    // Split property name and parameters
    $parameters = $this->SplitQuoted($prop, ';');
    $this->name = array_shift($parameters);
    $this->parameters = array();
    foreach ($parameters AS $k => $v) {
      $pos = strpos($v, '=');
      $name = substr($v, 0, $pos);
      $value = substr($v, $pos + 1);
      $this->parameters[$name] = preg_replace('/^"(.+)"$/', '$1', $value); // Removes DQUOTE on demand
    }
    ZLog::Write(LOGLEVEL_DEBUG, sprintf("iCalendar->ParseFrom(): found '%s' = '%s' with %d parameters", $this->name, substr($this->content,0,200), count($this->parameters)));
  }

  /**
   * Splits quoted strings
   *
   * @param string $str The string
   * @param string $sep The delimeter character
   * @param integer $limit Limit number of results, rest of string in last element
   * @return array
   */
  function SplitQuoted($str, $sep = ',', $limit = 0) {
    $result = array();
    $cursor = 0;
    $inquote = false;
    $num = 0;
    for($i = 0, $len = strlen($str); $i < $len; ++$i) {
      $ch = $str[$i];
      if ($ch == '"') {
        $inquote = !$inquote;
      }
      if (!$inquote && $ch == $sep) {
        //var_dump("Found sep `$sep` - Splitting from $cursor to $i from $len.");
        // If we reached the maximal number of splits, we cut till the end and stop here.
        ++$num;
        if ($limit > 0 && $num == $limit) {
          $result[] = substr($str, $cursor);
          break;
        }
        $result[] = substr($str, $cursor, $i - $cursor);
        $cursor = $i + 1;
      }
      // Add rest of string on end reached
      if ($i + 1 == $len) {
        //var_dump("Reached end - Splitting from $cursor to $len.");
        $result[] = substr($str, $cursor);
      }
    }

    return $result;
  }

  /**
   * Get/Set name property
   *
   * @param string $newname [optional] A new name for the property
   *
   * @return string The name for the property.
   */
  function Name( $newname = null ) {
    if ( $newname != null ) {
      $this->name = $newname;
      if ( isset($this->rendered) ) unset($this->rendered);
      ZLog::Write(LOGLEVEL_DEBUG, sprintf("iCalProp->Name(%s)", $this->name));
    }
    return $this->name;
  }


  /**
   * Get/Set the content of the property
   *
   * @param string $newvalue [optional] A new value for the property
   *
   * @return string The value of the property.
   */
  function Value( $newvalue = null ) {
    if ( $newvalue != null ) {
      $this->content = $newvalue;
      if ( isset($this->rendered) ) unset($this->rendered);
    }
    return $this->content;
  }


  /**
   * Get/Set parameters in their entirety
   *
   * @param array $newparams An array of new parameter key/value pairs
   *
   * @return array The current array of parameters for the property.
   */
  function Parameters( $newparams = null ) {
    if ( $newparams != null ) {
      $this->parameters = $newparams;
      if ( isset($this->rendered) ) unset($this->rendered);
    }
    return $this->parameters;
  }


  /**
   * Test if our value contains a string
   *
   * @param string $search The needle which we shall search the haystack for.
   *
   * @return string The name for the property.
   */
  function TextMatch( $search ) {
    if ( isset($this->content) ) {
      return (stristr( $this->content, $search ) !== false);
    }
    return false;
  }


  /**
   * Get the value of a parameter
   *
   * @param string $name The name of the parameter to retrieve the value for
   *
   * @return string The value of the parameter
   */
  function GetParameterValue( $name ) {
    if ( isset($this->parameters[$name]) ) return $this->parameters[$name];
  }

  /**
   * Set the value of a parameter
   *
   * @param string $name The name of the parameter to set the value for
   *
   * @param string $value The value of the parameter
   */
  function SetParameterValue( $name, $value ) {
    if ( isset($this->rendered) ) unset($this->rendered);
    // Unset parameter
    if ($value === null) {
        unset($this->parameters[$name]);
    }
    else {
        $this->parameters[$name] = $value;
    }
  }

  /**
  * Render the set of parameters as key1=value1[;key2=value2[; ...]] with
  * any colons or semicolons escaped.
  */
  function RenderParameters() {
    $rendered = "";
    foreach( $this->parameters AS $k => $v ) {
      $escaped = preg_replace( "/([;:])/", '\\\\$1', $v);
      $rendered .= sprintf( ";%s=%s", $k, $escaped );
    }
    return $rendered;
  }


  /**
  * Render a suitably escaped RFC2445 content string.
  */
  function Render() {
    // If we still have the string it was parsed in from, it hasn't been screwed with
    // and we can just return that without modification.
    if ( isset($this->rendered) ) return $this->rendered;

    $property = preg_replace( '/[;].*$/', '', $this->name );
    $escaped = $this->content;
    switch( $property ) {
        /** Content escaping does not apply to these properties culled from RFC2445 */
      case 'ATTACH':                case 'GEO':                       case 'PERCENT-COMPLETE':      case 'PRIORITY':
      case 'DURATION':              case 'FREEBUSY':                  case 'TZOFFSETFROM':          case 'TZOFFSETTO':
      case 'TZURL':                 case 'ATTENDEE':                  case 'ORGANIZER':             case 'RECURRENCE-ID':
      case 'URL':                   case 'EXRULE':                    case 'SEQUENCE':              case 'CREATED':
      case 'RRULE':                 case 'REPEAT':                    case 'TRIGGER':
        break;

      case 'COMPLETED':             case 'DTEND':
      case 'DUE':                   case 'DTSTART':
      case 'DTSTAMP':               case 'LAST-MODIFIED':
      case 'CREATED':               case 'EXDATE':
      case 'RDATE':
        if ( isset($this->parameters['VALUE']) && $this->parameters['VALUE'] == 'DATE' ) {
          $escaped = substr( $escaped, 0, 8);
        }
        break;

        /** Content escaping applies by default to other properties */
      default:
        $escaped = str_replace( '\\', '\\\\', $escaped);
        $escaped = preg_replace( '/\r?\n/', '\\n', $escaped);
        $escaped = preg_replace( "/([,;:])/", '\\\\$1', $escaped);
    }
    $property = sprintf( "%s%s:", $this->name, $this->RenderParameters() );
    if ( (strlen($property) + strlen($escaped)) <= 72 ) {
      $this->rendered = $property . $escaped;
    }
    else if ( (strlen($property) + strlen($escaped)) > 72 && (strlen($property) < 72) && (strlen($escaped) < 72) ) {
      $this->rendered = $property . "\r\n " . $escaped;
    }
    else {
      $this->rendered = preg_replace( '/(.{72})/u', '$1'."\r\n ", $property . $escaped );
    }
    return $this->rendered;
  }

}


/**
* A Class for representing components within an iCalendar
*
* @package awl
* @subpackage iCalComponent
* @author Andrew McMillan <andrew@mcmillan.net.nz>
* @copyright Catalyst IT Ltd, Morphoss Ltd <http://www.morphoss.com/>
* @license   http://gnu.org/copyleft/gpl.html GNU GPL v2 or later
*/

class iCalComponent {
  /**#@+
   * @access private
   */

  /**
   * The type of this component, such as 'VEVENT', 'VTODO', 'VTIMEZONE', etc.
   *
   * @var string
   */
  var $type;

  /**
   * An array of properties, which are iCalProp objects
   *
   * @var array
   */
  var $properties;

  /**
   * An array of (sub-)components, which are iCalComponent objects
   *
   * @var array
   */
  var $components;

  /**
   * The rendered result (or what was originally parsed, if there have been no changes)
   *
   * @var array
   */
  var $rendered;

  /**#@-*/

  /**
  * A basic constructor
  */
  function __construct( $content = null ) {
    $this->type = "";
    $this->properties = array();
    $this->components = array();
    $this->rendered = "";
    if ( $content != null && (gettype($content) == 'string' || gettype($content) == 'array') ) {
      $this->ParseFrom($content);
    }
  }


  /**
  * Apply standard properties for a VCalendar
  * @param array $extra_properties Key/value pairs of additional properties
  */
  function VCalendar( $extra_properties = null ) {
    $this->SetType('VCALENDAR');
    $this->AddProperty('PRODID', '-//davical.org//NONSGML AWL Calendar//EN');
    $this->AddProperty('VERSION', '2.0');
    $this->AddProperty('CALSCALE', 'GREGORIAN');
    if ( is_array($extra_properties) ) {
      foreach( $extra_properties AS $k => $v ) {
        $this->AddProperty($k,$v);
      }
    }
  }

  /**
  * Collect an array of all parameters of our properties which are the specified type
  * Mainly used for collecting the full variety of references TZIDs
  */
  function CollectParameterValues( $parameter_name ) {
    $values = array();
    foreach( $this->components AS $k => $v ) {
      $also = $v->CollectParameterValues($parameter_name);
      $values = array_merge( $values, $also );
    }
    foreach( $this->properties AS $k => $v ) {
      $also = $v->GetParameterValue($parameter_name);
      if ( isset($also) && $also != "" ) {
        ZLog::Write(LOGLEVEL_DEBUG, sprintf("iCalendar->CollectParameterValues(%s): Found '%s'", $parameter_name, $also));
        $values[$also] = 1;
      }
    }
    return $values;
  }


  /**
  * Parse the text $content into sets of iCalProp & iCalComponent within this iCalComponent
  * @param string $content The raw RFC2445-compliant iCalendar component, including BEGIN:TYPE & END:TYPE
  */
  function ParseFrom( $content ) {
    $this->rendered = $content;
    $content = $this->UnwrapComponent($content);

    $type = false;
    $subtype = false;
    $finish = null;
    $subfinish = null;

    $length = strlen($content);
    $linefrom = 0;
    while( $linefrom < $length ) {
      $lineto = strpos( $content, "\n", $linefrom );
      if ( $lineto === false ) {
        $lineto = strpos( $content, "\r", $linefrom );
      }
      if ( $lineto > 0 ) {
        $line = substr( $content, $linefrom, $lineto - $linefrom);
        $linefrom = $lineto + 1;
      }
      else {
        $line = substr( $content, $linefrom );
        $linefrom = $length;
      }
      if ( preg_match('/^\s*$/', $line ) ) continue;
      $line = rtrim( $line, "\r\n" );
      ZLog::Write(LOGLEVEL_DEBUG, sprintf("iCalendar->ParseFrom(): Parsing line: '%s'", $line));

      if ( $type === false ) {
        if ( preg_match( '/^BEGIN:(.+)$/', $line, $matches ) ) {
          // We have found the start of the main component
          $type = $matches[1];
          $finish = "END:$type";
          $this->type = $type;
          ZLog::Write(LOGLEVEL_DEBUG, sprintf("iCalendar->ParseFrom(): Start component of type '%s'", $type));
        }
        else {
          ZLog::Write(LOGLEVEL_DEBUG, sprintf("iCalendar->ParseFrom(): Ignoring crap before start of component: '%s'", $line));
          // unset($lines[$k]);  // The content has crap before the start
          if ( $line != "" ) $this->rendered = null;
        }
      }
      else if ( $type == null ) {
        ZLog::Write(LOGLEVEL_DEBUG, "iCalendar->ParseFrom(): Ignoring crap after end of component.");
        if ( $line != "" ) $this->rendered = null;
      }
      else if ( $line == $finish ) {
        ZLog::Write(LOGLEVEL_DEBUG, "iCalendar->ParseFrom(): End of component.");
        $type = null;  // We have reached the end of our component
      }
      else {
        if ( $subtype === false && preg_match( '/^BEGIN:(.+)$/', $line, $matches ) ) {
          // We have found the start of a sub-component
          $subtype = $matches[1];
          $subfinish = "END:$subtype";
          $subcomponent = $line . "\r\n";
          ZLog::Write(LOGLEVEL_DEBUG, sprintf("iCalendar->ParseFrom(): Found a subcomponent '%s'", $subtype));
        }
        else if ( $subtype ) {
          // We are inside a sub-component
          $subcomponent .= $this->WrapComponent($line);
          if ( $line == $subfinish ) {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("iCalendar->ParseFrom(): End of subcomponent '%s'", $subtype));
            // We have found the end of a sub-component
            $this->components[] = new iCalComponent($subcomponent);
            $subtype = false;
          }
          else {
            ZLog::Write(LOGLEVEL_DEBUG, sprintf("iCalendar->ParseFrom(): Inside a subcomponent '%s'", $subtype));
          }
        }
        else {
          ZLog::Write(LOGLEVEL_DEBUG, "iCalendar->ParseFrom(): Parse property of component.");
          // It must be a normal property line within a component.
          $this->properties[] = new iCalProp($line);
        }
      }
    }
  }


  /**
    * This unescapes the (CRLF + linear space) wrapping specified in RFC2445. According
    * to RFC2445 we should always end with CRLF but the CalDAV spec says that normalising
    * XML parsers often muck with it and may remove the CR.  We accept either case.
    */
  function UnwrapComponent( $content ) {
    return preg_replace('/\r?\n[ \t]/', '', $content );
  }

  /**
    * This imposes the (CRLF + linear space) wrapping specified in RFC2445. According
    * to RFC2445 we should always end with CRLF but the CalDAV spec says that normalising
    * XML parsers often muck with it and may remove the CR.  We output RFC2445 compliance.
    *
    * In order to preserve pre-existing wrapping in the component, we split the incoming
    * string on line breaks before running wordwrap over each component of that.
    */
  function WrapComponent( $content ) {
    $strs = preg_split( "/\r?\n/", $content );
    $wrapped = "";
    foreach ($strs as $str) {
      $wrapped .= preg_replace( '/(.{72})/u', '$1'."\r\n ", $str ) ."\r\n";
    }
    return $wrapped;
  }

  /**
  * Return the type of component which this is
  */
  function GetType() {
    return $this->type;
  }


  /**
  * Set the type of component which this is
  */
  function SetType( $type ) {
    if ( isset($this->rendered) ) unset($this->rendered);
    $this->type = $type;
    return $this->type;
  }


  /**
  * Get all properties, or the properties matching a particular type
  */
  function GetProperties( $type = null ) {
    $properties = array();
    foreach( $this->properties AS $k => $v ) {
      if ( $type == null || $v->Name() == $type ) {
        $properties[$k] = $v;
      }
    }
    return $properties;
  }


  /**
  * Get the value of the first property matching the name. Obviously this isn't
  * so useful for properties which may occur multiply, but most don't.
  *
  * @param string $type The type of property we are after.
  * @return string The value of the property, or null if there was no such property.
  */
  function GetPValue( $type ) {
    foreach( $this->properties AS $k => $v ) {
      if ( $v->Name() == $type ) return $v->Value();
    }
    return null;
  }


  /**
  * Set the value of all properties matching the name.
  *
  * @param string $type The type/name of property we are after
  * @param string $value The value of the property
  */
  function SetPValue( $type, $value )  {
    for ( $i = 0; $i < count($this->properties); $i++ ) {
      if ( $this->properties[$i]->Name() == $type ) {
        if ( isset($this->rendered) ) unset($this->rendered);
        // FMBIETE - unset property
        if ($value == null) {
          unset($this->properties[$i]);
        }
        else {
          $this->properties[$i]->Value($value);
        }
      }
    }
  }


  /**
  * Set the value of all the parameters matching the name. Component -> Property -> Parameter
  *
  * @param string $component_type Type of the component
  * @param string $property_name Type/Name of the property
  * @param string $parameter_name Type/Name of the parameter
  * @param string $value New value of the parameter
  * @param string $condition_value Change the parameter_value only if the property_value is equals to condition_value
  */
  function SetCPParameterValue( $component_type, $property_name, $parameter_name, $value, $condition_value = null ) {
    for ( $j = 0; $j < count($this->components); $j++ ) {
      if ( $this->components[$j]->GetType() == $component_type ) {
        for ( $i = 0; $i < count($this->components[$j]->properties); $i++ ) {
          if ( $this->components[$j]->properties[$i]->Name() == $property_name ) {
            if ( isset($this->components[$j]->rendered) ) unset($this->components[$j]->rendered);
            if ($condition_value === null) {
              $this->components[$j]->properties[$i]->SetParameterValue($parameter_name, $value);
            }
            else {
              if (strcasecmp($this->components[$j]->properties[$i]->Value(), $condition_value) == 0) {
                $this->components[$j]->properties[$i]->SetParameterValue($parameter_name, $value);
              }
            }
          }
        }
      }
    }
  }


  /**
  * Get the value of the specified parameter for the first property matching the
  * name. Obviously this isn't so useful for properties which may occur multiply, but most don't.
  *
  * @param string $type The type of property we are after.
  * @param string $type The name of the parameter we are after.
  * @return string The value of the parameter for the property, or null in the case that there was no such property, or no such parameter.
  */
  function GetPParamValue( $type, $parameter_name ) {
    foreach( $this->properties AS $k => $v ) {
      if ( $v->Name() == $type ) return $v->GetParameterValue($parameter_name);
    }
    return null;
  }


  /**
  * Clear all properties, or the properties matching a particular type
  * @param string $type The type of property - omit for all properties
  */
  function ClearProperties( $type = null ) {
    if ( $type != null ) {
      // First remove all the existing ones of that type
      foreach( $this->properties AS $k => $v ) {
        if ( $v->Name() == $type ) {
          unset($this->properties[$k]);
          if ( isset($this->rendered) ) unset($this->rendered);
        }
      }
      $this->properties = array_values($this->properties);
    }
    else {
      if ( isset($this->rendered) ) unset($this->rendered);
      $this->properties = array();
    }
  }


  /**
  * Set all properties, or the ones matching a particular type
  */
  function SetProperties( $new_properties, $type = null ) {
    if ( isset($this->rendered) && count($new_properties) > 0 ) unset($this->rendered);
    $this->ClearProperties($type);
    foreach( $new_properties AS $k => $v ) {
      $this->AddProperty($v);
    }
  }


  /**
  * Adds a new property
  *
  * @param iCalProp $new_property The new property to append to the set, or a string with the name
  * @param string $value The value of the new property (default: param 1 is an iCalProp with everything
  * @param array $parameters The key/value parameter pairs (default: none, or param 1 is an iCalProp with everything)
  */
  function AddProperty( $new_property, $value = null, $parameters = null ) {
    if ( isset($this->rendered) ) unset($this->rendered);
    if ( isset($value) && gettype($new_property) == 'string' ) {
      $new_prop = new iCalProp();
      $new_prop->Name($new_property);
      $new_prop->Value($value);
      if ( $parameters != null ) $new_prop->Parameters($parameters);
      ZLog::Write(LOGLEVEL_DEBUG, sprintf("iCalendar->AddProperty(): Adding new property '%s'", $new_prop->Render()));
      $this->properties[] = $new_prop;
    }
    else if ( gettype($new_property) ) {
      $this->properties[] = $new_property;
    }
  }


  /**
  * Get all sub-components, or at least get those matching a type
  * @return array an array of the sub-components
  */
  function &FirstNonTimezone( $type = null ) {
    foreach( $this->components AS $k => $v ) {
      if ( $v->GetType() != 'VTIMEZONE' ) return $this->components[$k];
    }
    $result = false;
    return $result;
  }


  /**
  * Return true if the person identified by the email address is down as an
  * organizer for this meeting.
  * @param string $email The e-mail address of the person we're seeking.
  * @return boolean true if we found 'em, false if we didn't.
  */
  function IsOrganizer( $email ) {
    if ( !preg_match( '#^mailto:#', $email ) ) $email = 'mailto:'.$email;
    $props = $this->GetPropertiesByPath('!VTIMEZONE/ORGANIZER');
    foreach( $props AS $k => $prop ) {
      if ( $prop->Value() == $email ) return true;
    }
    return false;
  }


  /**
  * Return true if the person identified by the email address is down as an
  * attendee or organizer for this meeting.
  * @param string $email The e-mail address of the person we're seeking.
  * @return boolean true if we found 'em, false if we didn't.
  */
  function IsAttendee( $email ) {
    if ( !preg_match( '#^mailto:#', $email ) ) $email = 'mailto:'.$email;
    if ( $this->IsOrganizer($email) ) return true; /** an organizer is an attendee, as far as we're concerned */
    $props = $this->GetPropertiesByPath('!VTIMEZONE/ATTENDEE');
    foreach( $props AS $k => $prop ) {
      if ( $prop->Value() == $email ) return true;
    }
    return false;
  }


  /**
  * Get all sub-components, or at least get those matching a type, or failling to match,
  * should the second parameter be set to false.
  *
  * @param string $type The type to match (default: All)
  * @param boolean $normal_match Set to false to invert the match (default: true)
  * @return array an array of the sub-components
  */
  function GetComponents( $type = null, $normal_match = true ) {
    $components = $this->components;
    if ( $type != null ) {
      foreach( $components AS $k => $v ) {
        if ( ($v->GetType() != $type) === $normal_match ) {
          unset($components[$k]);
        }
      }
      $components = array_values($components);
    }
    return $components;
  }


  /**
  * Clear all components, or the components matching a particular type
  * @param string $type The type of component - omit for all components
  */
  function ClearComponents( $type = null ) {
    if ( $type != null ) {
      // First remove all the existing ones of that type
      foreach( $this->components AS $k => $v ) {
        if ( $v->GetType() == $type ) {
          unset($this->components[$k]);
          if ( isset($this->rendered) ) unset($this->rendered);
        }
        else {
          if ( ! $this->components[$k]->ClearComponents($type) ) {
            if ( isset($this->rendered) ) unset($this->rendered);
          }
        }
      }
      return isset($this->rendered);
    }
    else {
      if ( isset($this->rendered) ) unset($this->rendered);
      $this->components = array();
    }
  }


  /**
  * Sets some or all sub-components of the component to the supplied new components
  *
  * @param array of iCalComponent $new_components The new components to replace the existing ones
  * @param string $type The type of components to be replaced.  Defaults to null, which means all components will be replaced.
  */
  function SetComponents( $new_component, $type = null ) {
    if ( isset($this->rendered) ) unset($this->rendered);
    if ( count($new_component) > 0 ) $this->ClearComponents($type);
    foreach( $new_component AS $k => $v ) {
      $this->components[] = $v;
    }
  }


  /**
  * Adds a new subcomponent
  *
  * @param iCalComponent $new_component The new component to append to the set
  */
  function AddComponent( $new_component ) {
    if ( is_array($new_component) && count($new_component) == 0 ) return;
    if ( isset($this->rendered) ) unset($this->rendered);
    if ( is_array($new_component) ) {
      foreach( $new_component AS $k => $v ) {
        $this->components[] = $v;
      }
    }
    else {
      $this->components[] = $new_component;
    }
  }


  /**
  * Mask components, removing any that are not of the types in the list
  * @param array $keep An array of component types to be kept
  */
  function MaskComponents( $keep ) {
    foreach( $this->components AS $k => $v ) {
      if ( ! in_array( $v->GetType(), $keep ) ) {
        unset($this->components[$k]);
        if ( isset($this->rendered) ) unset($this->rendered);
      }
      else {
        $v->MaskComponents($keep);
      }
    }
  }


  /**
  * Mask properties, removing any that are not in the list
  * @param array $keep An array of property names to be kept
  * @param array $component_list An array of component types to check within
  */
  function MaskProperties( $keep, $component_list=null ) {
    foreach( $this->components AS $k => $v ) {
      $v->MaskProperties($keep, $component_list);
    }

    if ( !isset($component_list) || in_array($this->GetType(), $component_list) ) {
      foreach( $this->properties AS $k => $v ) {
        if ( ! in_array( $v->name, $keep ) ) {
          unset($this->properties[$k]);
          if ( isset($this->rendered) ) unset($this->rendered);
        }
      }
    }
  }


  /**
  * Clone this component (and subcomponents) into a confidential version of it.  A confidential
  * event will be scrubbed of any identifying characteristics other than time/date, repeat, uid
  * and a summary which is just a translated 'Busy'.
  */
  function CloneConfidential() {
    $confidential = clone($this);
    $keep_properties = array( 'DTSTAMP', 'DTSTART', 'RRULE', 'DURATION', 'DTEND', 'DUE', 'UID', 'CLASS', 'TRANSP', 'CREATED', 'LAST-MODIFIED' );
    $resource_components = array( 'VEVENT', 'VTODO', 'VJOURNAL' );
    $confidential->MaskComponents(array( 'VTIMEZONE', 'STANDARD', 'DAYLIGHT', 'VEVENT', 'VTODO', 'VJOURNAL' ));
    $confidential->MaskProperties($keep_properties, $resource_components );

    if ( isset($confidential->rendered) )
      unset($confidential->rendered); // we need to re-render the whole object

    if ( in_array( $confidential->GetType(), $resource_components ) ) {
      $confidential->AddProperty( 'SUMMARY', translate('Busy') );
    }
    foreach( $confidential->components AS $k => $v ) {
      if ( in_array( $v->GetType(), $resource_components ) ) {
        $v->AddProperty( 'SUMMARY', translate('Busy') );
      }
    }

    return $confidential;
  }


  /**
  *  Renders the component, possibly restricted to only the listed properties
  */
  function Render( $restricted_properties = null) {

    $unrestricted = (!isset($restricted_properties) || count($restricted_properties) == 0);

    if ( isset($this->rendered) && $unrestricted )
      return $this->rendered;

    $rendered = "BEGIN:$this->type\r\n";
    foreach( $this->properties AS $k => $v ) {
      if ( method_exists($v, 'Render') ) {
        if ( $unrestricted || isset($restricted_properties[$v]) ) $rendered .= $v->Render() . "\r\n";
      }
    }
    foreach( $this->components AS $v ) {   $rendered .= $v->Render();  }
    $rendered .= "END:$this->type\r\n";

    $rendered = preg_replace('{(?<!\r)\n}', "\r\n", $rendered);
    if ( $unrestricted ) $this->rendered = $rendered;

    return $rendered;
  }


  /**
  * Return an array of properties matching the specified path
  *
  * @return array An array of iCalProp within the tree which match the path given, in the form
  *  [/]COMPONENT[/...]/PROPERTY in a syntax kind of similar to our poor man's XML queries. We
  *  also allow COMPONENT and PROPERTY to be !COMPONENT and !PROPERTY for ++fun.
  *
  * @note At some point post PHP4 this could be re-done with an iterator, which should be more efficient for common use cases.
  */
  function GetPropertiesByPath( $path ) {
    $properties = array();
    ZLog::Write(LOGLEVEL_DEBUG, sprintf("iCalendar->GetPropertiesByPath(): Querying within '%s' for path '%s'", $this->type, $path));
    if ( !preg_match( '#(/?)(!?)([^/]+)(/?.*)$#', $path, $matches ) ) return $properties;

    $adrift = ($matches[1] == '');
    $normal = ($matches[2] == '');
    $ourtest = $matches[3];
    $therest = $matches[4];
    ZLog::Write(LOGLEVEL_DEBUG, sprintf("iCalendar->GetPropertiesByPath(): Matches: %s -- %s -- %s -- %s", $matches[1], $matches[2], $matches[3], $matches[4]));
    if ( $ourtest == '*' || (($ourtest == $this->type) === $normal) && $therest != '' ) {
      if ( preg_match( '#^/(!?)([^/]+)$#', $therest, $matches ) ) {
        $normmatch = ($matches[1] =='');
        $proptest  = $matches[2];
        foreach( $this->properties AS $k => $v ) {
          if ( $proptest == '*' || (($v->Name() == $proptest) === $normmatch ) ) {
            $properties[] = $v;
          }
        }
      }
      else {
        /**
        * There is more to the path, so we recurse into that sub-part
        */
        foreach( $this->components AS $k => $v ) {
          $properties = array_merge( $properties, $v->GetPropertiesByPath($therest) );
        }
      }
    }

    if ( $adrift ) {
      /**
      * Our input $path was not rooted, so we recurse further
      */
      foreach( $this->components AS $k => $v ) {
        $properties = array_merge( $properties, $v->GetPropertiesByPath($path) );
      }
    }
    ZLog::Write(LOGLEVEL_DEBUG, sprintf("iCalendar->GetPropertiesByPath(): Found %d within '%s' for path '%s'", count($properties), $this->type, $path));
    return $properties;
  }

}
