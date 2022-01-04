<?php

/*************************************************************************
 *                                                                       *
 * class.html2text.inc                                                   *
 *                                                                       *
 *************************************************************************
 *                                                                       *
 * Converts HTML to formatted plain text                                 *
 *                                                                       *
 * Copyright (c) 2005-2007 Jon Abernathy <jon@chuggnutt.com>             *
 * All rights reserved.                                                  *
 *                                                                       *
 * This script is free software; you can redistribute it and/or modify   *
 * it under the terms of the GNU General Public License as published by  *
 * the Free Software Foundation; either version 2 of the License, or     *
 * (at your option) any later version.                                   *
 *                                                                       *
 * The GNU General Public License can be found at                        *
 * http://www.gnu.org/copyleft/gpl.html.                                 *
 *                                                                       *
 * This script is distributed in the hope that it will be useful,        *
 * but WITHOUT ANY WARRANTY; without even the implied warranty of        *
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the          *
 * GNU General Public License for more details.                          *
 *                                                                       *
 * Author(s): Jon Abernathy <jon@chuggnutt.com>                          *
 *                                                                       *
 * Last modified: 08/08/07                                               *
 *                                                                       *
 *************************************************************************/


/**
 *  Takes HTML and converts it to formatted, plain text.
 *
 *  Thanks to Alexander Krug (http://www.krugar.de/) to pointing out and
 *  correcting an error in the regexp search array. Fixed 7/30/03.
 *
 *  Updated set_html() function's file reading mechanism, 9/25/03.
 *
 *  Thanks to Joss Sanglier (http://www.dancingbear.co.uk/) for adding
 *  several more HTML entity codes to the $search and $replace arrays.
 *  Updated 11/7/03.
 *
 *  Thanks to Darius Kasperavicius (http://www.dar.dar.lt/) for
 *  suggesting the addition of $allowed_tags and its supporting function
 *  (which I slightly modified). Updated 3/12/04.
 *
 *  Thanks to Justin Dearing for pointing out that a replacement for the
 *  <TH> tag was missing, and suggesting an appropriate fix.
 *  Updated 8/25/04.
 *
 *  Thanks to Mathieu Collas (http://www.myefarm.com/) for finding a
 *  display/formatting bug in the _build_link_list() function: email
 *  readers would show the left bracket and number ("[1") as part of the
 *  rendered email address.
 *  Updated 12/16/04.
 *
 *  Thanks to Wojciech Bajon (http://histeria.pl/) for submitting code
 *  to handle relative links, which I hadn't considered. I modified his
 *  code a bit to handle normal HTTP links and MAILTO links. Also for
 *  suggesting three additional HTML entity codes to search for.
 *  Updated 03/02/05.
 *
 *  Thanks to Jacob Chandler for pointing out another link condition
 *  for the _build_link_list() function: "https".
 *  Updated 04/06/05.
 *
 *  Thanks to Marc Bertrand (http://www.dresdensky.com/) for
 *  suggesting a revision to the word wrapping functionality; if you
 *  specify a $width of 0 or less, word wrapping will be ignored.
 *  Updated 11/02/06.
 *
 *  *** Big housecleaning updates below:
 *
 *  Thanks to Colin Brown (http://www.sparkdriver.co.uk/) for
 *  suggesting the fix to handle </li> and blank lines (whitespace).
 *  Christian Basedau (http://www.movetheweb.de/) also suggested the
 *  blank lines fix.
 *
 *  Special thanks to Marcus Bointon (http://www.synchromedia.co.uk/),
 *  Christian Basedau, Norbert Laposa (http://ln5.co.uk/),
 *  Bas van de Weijer, and Marijn van Butselaar
 *  for pointing out my glaring error in the <th> handling. Marcus also
 *  supplied a host of fixes.
 *
 *  Thanks to Jeffrey Silverman (http://www.newtnotes.com/) for pointing
 *  out that extra spaces should be compressed--a problem addressed with
 *  Marcus Bointon's fixes but that I had not yet incorporated.
 *
 *    Thanks to Daniel Schledermann (http://www.typoconsult.dk/) for
 *  suggesting a valuable fix with <a> tag handling.
 *
 *  Thanks to Wojciech Bajon (again!) for suggesting fixes and additions,
 *  including the <a> tag handling that Daniel Schledermann pointed
 *  out but that I had not yet incorporated. I haven't (yet)
 *  incorporated all of Wojciech's changes, though I may at some
 *  future time.
 *
 *  *** End of the housecleaning updates. Updated 08/08/07.
 *
 *  @author Jon Abernathy <jon@chuggnutt.com>
 *  @version 1.0.0
 *  @since PHP 4.0.2
 *  @package GO.base.util
 */

namespace GO\Base\Util;


class Html2Text
{

	/**
	 *  Contains the HTML content to convert.
	 *
	 *  @var StringHelper $html
	 *  @access public
	 */
	private $html;

	/**
	 *  Contains the converted, formatted text.
	 *
	 *  @var StringHelper $text
	 *  @access public
	 */
	private $text;

	/**
	 *  Maximum width of the formatted text, in columns.
	 *
	 *  Set this value to 0 (or less) to ignore word wrapping
	 *  and not constrain text to a fixed-width column.
	 *
	 *  @var integer $width
	 *  @access public
	 */
	public $width = 70;

	/**
	 *  List of preg* regular expression patterns to search for,
	 *  used in conjunction with $replace.
	 *
	 *  @var array $search
	 *  @access public
	 *  @see $replace
	 */
	private $search = array(
        "/\r/",                                  // Non-legal carriage return
        "/[\n\t]+/",                             // Newlines and tabs
        '/\s\s+/',                             // Runs of spaces, pre-handling
        '/<script[^>]*>.*?<\/script>/i',         // <script>s -- which strip_tags supposedly has problems with
        '/<style[^>]*>.*?<\/style>/i',           // <style>s -- which strip_tags supposedly has problems with
	//'/<!-- .* -->/',                         // Comments -- which strip_tags might have problem a with
//        '/<h[123][^>]*>(.*?)<\/h[123]>[ \t]*/ie',      // H1 - H3
//        '/<h[456][^>]*>(.*?)<\/h[456]>[ \t]*/ie',      // H4 - H6
        '/<p[^>]*>(.*?)<\/p>[ \t]*/i',                           // <P>
        '/<div[^>]*>[ \t]*/i',                           // <div>
        '/<br[^>]*>[ \t]*/i',                          // <br>
//        '/<b[^>]*>(.*?)<\/b>/ie',                // <b>
//        '/<strong[^>]*>(.*?)<\/strong>/ie',      // <strong>
        '/<i[^>]*>(.*?)<\/i>/i',                 // <i>
        '/<em[^>]*>(.*?)<\/em>/i',               // <em>
        '/(<ul[^>]*>|<\/ul>)[\s]*/i',                 // <ul> and </ul>
        '/(<ol[^>]*>|<\/ol>)[\s]*/i',                 // <ol> and </ol>
        '/<li[^>]*>(.*?)<\/li>[\s]*/i',               // <li> and </li>
        '/<li[^>]*>/i',                          // <li>        
        '/<hr[^>]*>/i',                          // <hr>
        '/(<table[^>]*>|<\/table>)/i',           // <table> and </table>
        '/(<tr[^>]*>|<\/tr>)/i',                 // <tr> and </tr>
        '/<td[^>]*>(.*?)<\/td>/i',               // <td> and </td>
//        '/<th[^>]*>(.*?)<\/th>/ie',              // <th> and </th>
				'/<img [^>]*alt="([^"]+)"[^>]*>/i', //img with alt text		
				'/<blockquote[^>]*>/i',                          // <blockquote>
				'/<\/blockquote[^>]*>/i',                          // <blockquote>
	/*'/&(nbsp|#160);/i',                      // Non-breaking space
	'/&(quot|rdquo|ldquo|#8220|#8221|#147|#148);/i',
	// Double quotes
	'/&(apos|rsquo|lsquo|#8216|#8217);/i',   // Single quotes
	'/&gt;/i',                               // Greater-than
	'/&lt;/i',                               // Less-than
	'/&(amp|#38);/i',                        // Ampersand
	'/&(copy|#169);/i',                      // Copyright
	'/&(trade|#8482|#153);/i',               // Trademark
	'/&(reg|#174);/i',                       // Registered
	'/&(mdash|#151|#8212);/i',               // mdash
	'/&(ndash|minus|#8211|#8722);/i',        // ndash
	'/&(bull|#149|#8226);/i',                // Bullet
	'/&(pound|#163);/i',                     // Pound sign
	'/&(euro|#8364);/i',                     // Euro sign
	'/&[^&;]+;/i',                           // Unknown/unhandled entities*/
	// '/[ ]{2,}/'                              // Runs of spaces, post-handling
	);

	/**
	 *  List of pattern replacements corresponding to patterns searched.
	 *
	 *  @var array $replace
	 *  @access public
	 *  @see $search
	 */
	private $replace = array(
        '',                                     // Non-legal carriage return
        '',                                    // Newlines and tabs
        ' ',                                    // Runs of spaces, pre-handling
        '',                                     // <script>s -- which strip_tags supposedly has problems with
        '',                                     // <style>s -- which strip_tags supposedly has problems with
	//'',                                     // Comments -- which strip_tags might have problem a with
//        "strtoupper(\"\n\n\\1\n\")",          // H1 - H3
//        "ucwords(\"\n\n\\1\n\")",             // H4 - H6
        "\n\n\\1\n\n",                               // <P>
        "\n",                               // </div>
        "\n",                                   // <br>
//        'strtoupper("\\1")',                    // <b>
//        'strtoupper("\\1")',                    // <strong>
        '_\\1_',                                // <i>
        '_\\1_',                                // <em>
        "\n\n",                                 // <ul> and </ul>
        "\n\n",                                 // <ol> and </ol>
        "\t* \\1\n",                            // <li> and </li>
        "\n\t* ",                               // <li>        
        "\n-------------------------\n",        // <hr>
        "\n\n",                                 // <table> and </table>
        "\n",                                   // <tr> and </tr>
        "\t\t\\1\n",                            // <td> and </td>
//        "strtoupper(\"\t\t\\1\n\")",            // <th> and </th>
				"\\1", //img with alt text
				"\n\n",																	// blockquote start
				"\n\n"																	// blockquote closing
				
	/*' ',                                    // Non-breaking space
	'"',                                    // Double quotes
	"'",                                    // Single quotes
	'>',
	'<',
	'&',
	'(c)',
	'(tm)',
	'(R)',
	'--',
	'-',
	'*',
	'£',
	'€',                                  // Euro sign. € ?
	'',                                     // Unknown/unhandled entities*/
	//' '                                     // Runs of spaces, post-handling
	);

	/**
	 *  Contains a list of HTML tags to allow in the resulting text.
	 *
	 *  @var StringHelper $allowed_tags
	 *  @access public
	 *  @see set_allowed_tags()
	 */
	public $allowed_tags = '';

	/**
	 *  Contains the base URL that relative links should resolve to.
	 *
	 *  @var StringHelper $url
	 *  @access public
	 */
	public $url;

	/**
	 *  Indicates whether content in the $html variable has been converted yet.
	 *
	 *  @var boolean $_converted
	 *  @access private
	 *  @see $html, $text
	 */
	private $_converted = false;

	/**
	 *  Contains URL addresses from links to be rendered in plain text.
	 *
	 *  @var StringHelper $_link_list
	 *  @access private
	 *  @see _build_link_list()
	 */
	private $_link_list = '';

	/**
	 *  Number of valid links detected in the text, used for plain text
	 *  display (rendered similar to footnotes).
	 *
	 *  @var integer $_link_count
	 *  @access private
	 *  @see _build_link_list()
	 */
	private $_link_count = 0;
	
	/**
	 * Contains a map of links to a number. It is used to use a link reference multiple times
	 * 
	 * @var Array
	 */
	
	private $refmap = array();

	/**
	 *  Constructor.
	 *
	 *  If the HTML source string (or file) is supplied, the class
	 *  will instantiate with that source propagated, all that has
	 *  to be done it to call get_text().
	 *
	 *  @param StringHelper $source HTML content
	 *  @param boolean $from_file Indicates $source is a file to pull content from
	 *  @access public
	 *  @return void
	 */
	public function __construct( $source = '', $from_file = false )
	{
		if ( !empty($source) ) {
			$this->set_html($source, $from_file);
		}
		$this->set_base_url();
	}

	/**
	 *  Loads source HTML into memory, either from $source string or a file.
	 *
	 *  @param StringHelper $source HTML content
	 *  @param boolean $from_file Indicates $source is a file to pull content from
	 *  @access public
	 *  @return void
	 */
	public function set_html( $source, $from_file = false )
	{
		$this->html = $source;

		if ( $from_file && file_exists($source) ) {
			$fp = fopen($source, 'r');
			$this->html = fread($fp, filesize($source));
			fclose($fp);
		}

		$this->_converted = false;
	}

	/**
	 *  Returns the text, converted from HTML.
	 *
	 *  @access public
	 *  @return StringHelper
	 */
	public function get_text($link_list=true)
	{
		if ( !$this->_converted ) {
			$this->_convert($link_list);
		}

		return $this->text;
	}

	/**
	 *  Prints the text, converted from HTML.
	 *
	 *  @access public
	 *  @return void
	 */
	public function print_text($link_list=true)
	{
		print $this->get_text($link_list);
	}

	/**
	 *  Sets the allowed HTML tags to pass through to the resulting text.
	 *
	 *  Tags should be in the form "<p>", with no corresponding closing tag.
	 *
	 *  @access public
	 *  @return void
	 */
	public function set_allowed_tags( $allowed_tags = '' )
	{
		if ( !empty($allowed_tags) ) {
			$this->allowed_tags = $allowed_tags;
		}
	}

	/**
	 *  Sets a base URL to handle relative links.
	 *
	 *  @access public
	 *  @return void
	 */
	public function set_base_url( $url = '' )
	{
		if ( empty($url) ) {
			if ( !empty($_SERVER['HTTP_HOST']) ) {
				$this->url = 'http://' . $_SERVER['HTTP_HOST'];
			} else {
				$this->url = '';
			}
		} else {
			// Strip any trailing slashes for consistency (relative
			// URLs may already start with a slash like "/file.html")
			if ( substr($url, -1) == '/' ) {
				$url = substr($url, 0, -1);
			}
			$this->url = $url;
		}
	}

	/**
	 *  Workhorse function that does actual conversion.
	 *
	 *  First performs custom tag replacement specified by $search and
	 *  $replace arrays. Then strips any remaining HTML tags, reduces whitespace
	 *  and newlines to a readable format, and word wraps the text to
	 *  $width characters.
	 *
	 *  @access private
	 *  @return void
	 */
	private function _convert($link_list=true)
	{
		// Variables used for building the link list
		$this->_link_count = 1;
		$this->_link_list = '';
		$this->refmap=array();

		$text = html_entity_decode(trim($this->html), ENT_QUOTES, 'UTF-8');
		$text = str_replace("\r", '', $text);
		$text = str_replace("\n", ' ', $text);
		
		if($link_list)
		{
//			$this->search[]='/<a [^>]*href="([^"]+)"[^>]*>(.*?)<\/a>/ie';
//			$this->replace[]='$this->_build_link_list("\\1", "\\2")';
			
			$text = preg_replace_callback('/<a [^>]*href="([^"]+)"[^>]*>(.*?)<\/a>/i',array($this, '_build_link_list'), $text);
		}

		// Run our defined search-and-replace
		$text = preg_replace($this->search, $this->replace, $text);

		$text = preg_replace_callback('/<h[123][^>]*>(.*?)<\/h[123]>[ \t]*/i', function($matches){
			return strtoupper("\n\n".$matches[1]."\n\n");
		}, $text);
		
		$text = preg_replace_callback('/<h[456][^>]*>(.*?)<\/h[456]>[ \t]*/i', function($matches){
			return ucwords("\n\n".$matches[1]."\n\n");
		}, $text);
		
		$text = preg_replace_callback('/<th[^>]*>(.*?)<\/th>/i', function($matches){			
			return strtoupper("\t\t".$matches[1]."\n");
		}, $text);
		
		// Strip any other HTML tags
		$text = strip_tags($text, $this->allowed_tags);

		// Bring down number of empty lines to 2 max
//		$text = preg_replace("/\n\s+\n/", "\n\n", $text);
//		$text = preg_replace("/[\n]{3,}/", "\n\n", $text);

		// Add link list
		if (!empty($this->_link_list) ) {
			$text .= "\n\nLinks:\n------\n" . $this->_link_list;
		}

		// Wrap the text to a readable format
		// for PHP versions >= 4.0.2. Default width is 75
		// If width is 0 or less, don't wrap the text.
		if ( $this->width > 0 ) {
			$text = wordwrap($text, $this->width);
		}

		$this->text = trim($text);

		$this->_converted = true;
	}

	/**
	 *  Helper function called by preg_replace() on link replacement.
	 *
	 *  Maintains an internal list of links to be displayed at the end of the
	 *  text, with numeric indices to the original point in the text they
	 *  appeared. Also makes an effort at identifying and handling absolute
	 *  and relative links.
	 *
	 *  @param StringHelper $link URL of the link
	 *  @param StringHelper $display Part of the text to associate number with
	 *  @access private
	 *  @return StringHelper
	 */
	private function _build_link_list( $matches )
	{
		$link = trim($matches[1]);
		$display=trim(strip_tags($matches[2]));
		
		if($link==$display || $link=='mailto:'.$display)
		{
			//ignore it
			$additional = '';
		}elseif ( substr($link, 0, 11) == 'javascript:' ) {
			// Don't count the link; ignore it
			$additional = '';
			// what about href="#anchor" ?
		}else
		{
			if(!isset($this->refmap[$link]))
			{
				$this->refmap[$link]=$this->_link_count;
				$ref = $this->_link_count;				
				$this->_link_count++;
				$this->_link_list .= "[" . $ref . "] $link\n";
			}else
			{
				$ref = $this->refmap[$link];
			}			
			$additional = ' [' . $ref . ']';
		}
		return $display . $additional;
	}

}
