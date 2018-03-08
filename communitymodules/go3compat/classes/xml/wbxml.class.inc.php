<?php
/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @copyright Copyright Intermesh
 * @version $Id: wbxml.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @author Merijn Schering <mschering@intermesh.nl>
 * @package go.xml
 */

/**
 * Converts binary XML (wbxml) to XML and vice versa using libwbxml2 with shell commands
 *
 * @author Merijn Schering <mschering@intermesh.nl>
 * @version $Id: wbxml.class.inc.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @copyright Copyright Intermesh
 * @license AGPL Affero General Public License
 * @package go.xml
 * @uses db
 * @since Group-Office 3.0
 */
class wbxml
{
	/**
	* Temporary file for the WBXML data
	*
	* @var     String
	* @access  private
	*/
	var $wbxmlfile = '/tmp/tmp.wbxml';

	/**
	* Temporary file for the XML data
	*
	* @var     String
	* @access  private
	*/
	var $xmlfile = '/tmp/tmp.xml';

	/**
	* Constructor. Set's temporary file names
	*
	* @access public
	* @return void
	*/
	function wbxml()
	{
		global $GO_CONFIG;

		//$this->wbxmlfile = $GLOBALS['GO_CONFIG']->tmpdir.md5(uniqid(time())).'.wbxml';
		//$this->xmlfile = $GLOBALS['GO_CONFIG']->tmpdir.md5(uniqid(time())).'.xml';
	}

	/**
	* Converts a WBXML string to XML
	*
	* @param	string	wbxml	The WBXML data
	* @access public
	* @return StringHelper XML
	*/
	function to_xml($wbxml)
	{
		global $GO_CONFIG;

		$this->wbxmlfile = $GLOBALS['GO_CONFIG']->tmpdir.'wbxml2xml_'.md5(uniqid(time())).'.wbxml';
		$this->xmlfile = $GLOBALS['GO_CONFIG']->tmpdir.'wbxml2xml_'.md5(uniqid(time())).'.xml';

		//create temp file

		if(!is_dir($GLOBALS['GO_CONFIG']->tmpdir))
			mkdir($GLOBALS['GO_CONFIG']->tmpdir, 0755, true);

		//file_put_contents did not work with nokia phones because the
		//line ends got mixed up somehow.
		$fp = fopen($this->wbxmlfile, 'w+');
		fwrite($fp, $wbxml);
		fclose($fp);
		//convert temp file

		if(is_windows())
		{
			$cmd = $GLOBALS['GO_CONFIG']->cmd_wbxml2xml.' -o '.$this->xmlfile.' '.$this->wbxmlfile;
		}else
		{
			$cmd = $GLOBALS['GO_CONFIG']->cmd_wbxml2xml.' -o '.$this->xmlfile.' '.$this->wbxmlfile.' 2>/dev/null';
		}
		exec($cmd);

		if(!file_exists($this->xmlfile))
		{
			go_log(LOG_DEBUG, 'Fatal error: wbxml2xml conversion failed');
			return false;
		}

		//read xml
		$xml = trim(file_get_contents($this->xmlfile));

		//remove temp files
		unlink($this->xmlfile);
		unlink($this->wbxmlfile);
		return $xml;
	}

	/**
	* Converts a XML string to WBXML
	*
	* @param	string	wbxml	The WBXML data
	* @access public
	* @return StringHelper WBXML
	*/
	function to_wbxml($xml)
	{
		global $GO_CONFIG;

		$this->wbxmlfile = $GLOBALS['GO_CONFIG']->tmpdir.'xml2wbxml_'.md5(uniqid(time())).'.wbxml';
		$this->xmlfile = $GLOBALS['GO_CONFIG']->tmpdir.'xml2wbxml_'.md5(uniqid(time())).'.xml';

		//create temp file
		$fp = fopen($this->xmlfile, 'w+');
		fwrite($fp, $xml);
		fclose($fp);


		if(is_windows())
		{
			$cmd = $GLOBALS['GO_CONFIG']->cmd_xml2wbxml.' -v 1.2 -o '.$this->wbxmlfile.' '.$this->xmlfile;
		}else
		{
			$cmd = $GLOBALS['GO_CONFIG']->cmd_xml2wbxml.' -v 1.2 -o '.$this->wbxmlfile.' '.$this->xmlfile.' 2>/dev/null';
		}

		//convert temp file
		exec($cmd);
		if(!file_exists($this->wbxmlfile))
		{
			go_log(LOG_DEBUG, 'Fatal error: xml2wbxml conversion failed');
			return false;
		}

		//read xml
		$wbxml = trim(file_get_contents($this->wbxmlfile));

		//remove temp files
		unlink($this->xmlfile);
		unlink($this->wbxmlfile);
		return $wbxml;
	}
}
