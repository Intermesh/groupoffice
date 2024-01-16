<?php
require 'SegmentIterator.php';
class SegmentException extends \Exception
{}
/**
 * Class for handling templating segments with odt files
 * You need PHP 5.2 at least
 * You need Zip Extension or PclZip library
 * Encoding : ISO-8859-1
 * Last commit by $Author: neveldo $
 * Date - $Date: 2009-06-17 12:12:59 +0200 (mer., 17 juin 2009) $
 * SVN Revision - $Rev: 44 $
 * Id : $Id: Segment.php 44 2009-06-17 10:12:59Z neveldo $
 *
 * @copyright  GPL License 2008 - Julien Pauli - Cyril PIERRE de GEYER - Anaska (http://www.anaska.com)
 * @license    http://www.gnu.org/copyleft/gpl.html  GPL License
 * @version 1.3
 */
class Segment implements IteratorAggregate, Countable
{
    protected $xml;
    protected $xmlParsed = '';
    protected $name;
    protected $children = array();
    protected $vars = array();
	protected $images = array();
	protected $odf;
	protected $file;
    /**
     * Constructor
     *
     * @param StringHelper $name name of the segment to construct
     * @param StringHelper $xml XML tree of the segment
     */
    public function __construct($name, $xml, $odf)
    {
        $this->name = (string) $name;
        $this->xml = (string) $xml;
		$this->odf = $odf;
        $zipHandler = $this->odf->getConfig('ZIP_PROXY');
        $this->file = new $zipHandler();	
        $this->_analyseChildren($this->xml);
    }
    /**
     * Returns the name of the segment
     *
     * @return StringHelper
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * Does the segment have children ?
     *
     * @return bool
     */
    public function hasChildren()
    {
        return $this->getIterator()->hasChildren();
    }
    /**
     * Countable interface
     *
     * @return int
     */

	#[\ReturnTypeWillChange]
    public function count()
    {
        return count($this->children);
    }
    /**
     * IteratorAggregate interface
     *
     * @return Iterator
     */

	#[\ReturnTypeWillChange]
    public function getIterator()
    {
        return new RecursiveIteratorIterator(new SegmentIterator($this->children), 1);
    }
    /**
     * Replace variables of the template in the XML code
     * All the children are also called
     *
     * @return StringHelper
     */
    public function merge()
    {
			if(!count($this->vars)){
				return '';
			}
        //$this->xmlParsed .= str_replace(array_keys($this->vars), array_values($this->vars), $this->xml);
		$this->xmlParsed.=preg_replace_callback('/{([^}]*)}/U', array($this, "replacetag"), $this->xml);
		
        if ($this->hasChildren()) {
            foreach ($this->children as $child) {
                $this->xmlParsed = str_replace($child->xml, ($child->xmlParsed=="")?$child->merge():$child->xmlParsed, $this->xmlParsed);
                $child->xmlParsed = '';
            }
        }
        $reg = "/\[!--\sBEGIN\s$this->name\s--\](.*)\[!--\sEND\s$this->name\s--\]/sm";
        $this->xmlParsed = preg_replace($reg, '$1', $this->xmlParsed);
        $this->file->open($this->odf->getTmpfile());
        foreach ($this->images as $imageKey => $imageValue) {
			if ($this->file->getFromName('Pictures/' . $imageValue) === false) {
				$this->file->addFile($imageKey, 'Pictures/' . $imageValue);
			}
        }
        $this->file->close();
			//	go_debug($this->xmlParsed);


				$this->vars=array();
		    return $this->xmlParsed;
    }
	
	public function replacetag($tag) {
		
		$tag = stripslashes($tag[1]);
		$orig_tag = $tag;

		//Sometimes people change styles within a {autodata} tag.
		//Then there are XML tags inside the GO template tag.
		//We place them outside the tag.
		//go_debug($tag);
		preg_match_all('/<[^>]*>/', $tag, $matches);
		$garbage_tags = implode('', $matches[0]);

		$tag = strip_tags($tag);
		$arr = explode('|', $tag);

		$math = false;
		$ops = array('/', '*', '+', '-');
		foreach ($ops as $op) {
			if (strpos($arr[0], $op)) {
				$math = true;
				break;
			}
		}

		if (!$math) {
			if (!isset($this->vars[$arr[0]])) {
				return '{' . $orig_tag . '}';
			} else {
				$v = $this->vars[$arr[0]];
			}
		} else {
			$v = $arr[0];
			foreach ($this->vars as $key => $value) {
				$v = str_replace($key, $value, $v);
			}

			\GO::config()->debug_display_errors = false;
			@eval("\$result_string=" . $v . ";");
			\GO::config()->debug_display_errors = true;

			$v = isset($result_string) ? $result_string : 'invalid math expression!';
		}

//		if (isset($arr[1])) {
//			$args = explode(':', $arr[1]);
//
//			//first value = function name
//			$func = array_shift($args);
//
//			//add value as first argument
//			array_unshift($args, $v);
//
//			$v = call_user_func_array(array('odf_renderers', $func), $args);
//		}
		return $garbage_tags . $v;
	}
    /**
     * Analyse the XML code in order to find children
     *
     * @param StringHelper $xml
     * @return Segment
     */
    protected function _analyseChildren($xml)
    {
        // $reg2 = "#\[!--\sBEGIN\s([\S]*)\s--\](?:<\/text:p>)?(.*)(?:<text:p\s.*>)?\[!--\sEND\s(\\1)\s--\]#sm";
        $reg2 = "#\[!--\sBEGIN\s([\S]*)\s--\](.*)\[!--\sEND\s(\\1)\s--\]#sm";
        preg_match_all($reg2, $xml, $matches);
        for ($i = 0, $size = count($matches[0]); $i < $size; $i++) {
            if ($matches[1][$i] != $this->name) {
                $this->children[$matches[1][$i]] = new self($matches[1][$i], $matches[0][$i], $this->odf);
            } else {
                $this->_analyseChildren($matches[2][$i]);
            }
        }
        return $this;
    }
    /**
     * Assign a template variable to replace
     *
     * @param StringHelper $key
     * @param StringHelper $value
     * @throws SegmentException
     * @return Segment
     */
    public function setVars($key, $value, $encode = true, $charset = 'UTF-8')
    {		
        if (strpos($this->xml, $this->odf->getConfig('DELIMITER_LEFT') . $key . $this->odf->getConfig('DELIMITER_RIGHT')) === false) {
            //throw new SegmentException("var $key not found in {$this->getName()}");
        }
		$value = $encode ? htmlspecialchars($value) : $value;
		$value = ($charset == 'ISO-8859') ? utf8_encode($value) : $value;
        //$this->vars[$this->odf->getConfig('DELIMITER_LEFT') . $key . $this->odf->getConfig('DELIMITER_RIGHT')] = str_replace("\n", "<text:line-break/>", $value);
		$this->vars[ $key ] = str_replace("\n", "<text:line-break/>", $value);
        return $this;
    }
    /**
     * Assign a template variable as a picture
     *
     * @param StringHelper $key name of the variable within the template
     * @param StringHelper $value path to the picture
     * @throws OdfException
     * @return Segment
     */
    public function setImage($key, $value)
    {
        $filename = strtok(strrchr($value, '/'), '/.');
        $file = substr(strrchr($value, '/'), 1);
        $size = @getimagesize($value);
        if ($size === false) {
            throw new OdfException("Invalid image");
        }
        list ($width, $height) = $size;
        $width *= Odf::PIXEL_TO_CM;
        $height *= Odf::PIXEL_TO_CM;
        $xml = <<<IMG
<draw:frame draw:style-name="fr1" draw:name="$filename" text:anchor-type="char" svg:width="{$width}cm" svg:height="{$height}cm" draw:z-index="3"><draw:image xlink:href="Pictures/$file" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/></draw:frame>
IMG;
        $this->images[$value] = $file;
        $this->setVars($key, $xml, false);
        return $this;
    }	
    /**
     * Shortcut to retrieve a child
     *
     * @param StringHelper $prop
     * @return Segment
     * @throws SegmentException
     */
    public function __get($prop)
    {
        if (array_key_exists($prop, $this->children)) {
            return $this->children[$prop];
        } else {
            //throw new SegmentException('child ' . $prop . ' does not exist');
        }
    }
    /**
     * Proxy for setVars
     *
     * @param StringHelper $meth
     * @param array $args
     * @return Segment
     */
    public function __call($meth, $args)
    {
        try {
            return $this->setVars($meth, $args[0]);
        } catch (SegmentException $e) {
            //throw new SegmentException("method $meth nor var $meth exist");
        }
    }
    /**
     * Returns the parsed XML
     *
     * @return StringHelper
     */
    public function getXmlParsed()
    {
        return $this->xmlParsed;
    }
}

?>
