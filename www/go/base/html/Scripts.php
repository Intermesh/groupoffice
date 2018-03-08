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
 * Component used for rendering client script like css and javascript into the views
 *
 * @package GO.modules.sites.components
 * @copyright Copyright Intermesh
 * @version $Id ClientScript.php 2012-06-06 16:41:34 mdhart $ 
 * @author Michael de Hart <mdehart@intermesh.nl> 
 */

namespace GO\Base\Html;


class Scripts
{

	const POS_HEAD = 1; //Render script in head section before title tag
	const POS_BEGIN = 2; //Render script at beginning of body section
	const POS_END = 3; //Render script at the end of body section
	const POS_READY = 4; //Render script inside windows jquery ready function at end of body

	/**
	 * array the mapping between script files name and script URLs
	 * has javascript files and css files
	 * @var type 
	 */

	public $scriptMap = array();
	protected $cssFiles = array();
	protected $css = array();
	protected $scriptFiles = array();
	protected $gapiScripts = array();
	protected $scripts = array();
	protected $metaTags = array();
	protected $hasScripts = false;
	private $_baseUrl;

	/**
	 * Cleans all registered scripts.
	 */
	public function reset()
	{
		$this->hasScripts = false;
		$this->cssFiles = array();
		$this->css = array();
		$this->scriptFiles = array();
		$this->scripts = array();
		$this->metaTags = array();

		$this->recordCachingAction('clientScript', 'reset', array());
	}

	public function render(&$output)
	{
		if (!$this->hasScripts)
			return;

		$this->renderHead($output);
		$this->renderBodyBegin($output);
		$this->renderBodyEnd($output);
	}

	/**
	 * Inserts the scripts in the head section.
	 * @param StringHelper $output the output to be inserted with scripts.
	 */
	public function renderHead(&$output)
	{
		$html = '';
		foreach ($this->metaTags as $meta)
			$html.=self::metaTag($meta['content'], null, null, $meta) . "\n";
		foreach ($this->cssFiles as $url => $media)
			$html.=self::cssFile($url, $media) . "\n";

		foreach($this->css as $css)
			$html.=self::css($css[0],$css[1])."\n";
				
		if(isset($this->gapiScripts[self::POS_HEAD]))
		{
			foreach ($this->gapiScripts[self::POS_HEAD] as $gapiScript)
				$html.=self::scriptFile($gapiScript) . "\n";
		}
		if (isset($this->scriptFiles[self::POS_HEAD]))
		{
			foreach ($this->scriptFiles[self::POS_HEAD] as $scriptFile)
				$html.=self::scriptFile($scriptFile) . "\n";
		}

		if (isset($this->scripts[self::POS_HEAD]))
			$html.=self::script(implode("\n", $this->scripts[self::POS_HEAD])) . "\n";


		if ($html !== '')
		{
			$count = 0;
			$output = preg_replace('/(<title\b[^>]*>|<\\/head\s*>)/is', '<###head###>$1', $output, 1, $count);
			if ($count)
				$output = str_replace('<###head###>', $html, $output);
			else
				$output = $html . $output;
		}
	}

	/**
	 * Inserts the scripts at the beginning of the body section.
	 * @param StringHelper $output the output to be inserted with scripts.
	 */
	public function renderBodyBegin(&$output)
	{
		$html = '';
		if (isset($this->scriptFiles[self::POS_BEGIN]))
		{
			foreach ($this->scriptFiles[self::POS_BEGIN] as $scriptFile)
				$html.=self::scriptFile($scriptFile) . "\n";
		}
		if (isset($this->scripts[self::POS_BEGIN]))
			$html.=self::script(implode("\n", $this->scripts[self::POS_BEGIN])) . "\n";

		if ($html !== '')
		{
			$count = 0;
			$output = preg_replace('/(<body\b[^>]*>)/is', '$1<###begin###>', $output, 1, $count);
			if ($count)
				$output = str_replace('<###begin###>', $html, $output);
			else
				$output = $html . $output;
		}
	}

	/**
	 * Inserts the scripts at the end of the body section.
	 * @param StringHelper $output the output to be inserted with scripts.
	 */
	public function renderBodyEnd(&$output)
	{
		if (!isset($this->scriptFiles[self::POS_END]) && !isset($this->scripts[self::POS_END])
						&& !isset($this->scripts[self::POS_READY]) )
			return;

		$fullPage = 0;
		$output = preg_replace('/(<\\/body\s*>)/is', '<###end###>$1', $output, 1, $fullPage);
		$html = '';
		if (isset($this->scriptFiles[self::POS_END]))
		{
			foreach ($this->scriptFiles[self::POS_END] as $scriptFile)
				$html.=self::scriptFile($scriptFile) . "\n";
		}
		$scripts = isset($this->scripts[self::POS_END]) ? $this->scripts[self::POS_END] : array();
		if (isset($this->scripts[self::POS_READY]))
		{
			if ($fullPage)
				$scripts[] = "jQuery(document).ready(function($) {\n" . implode("\n", $this->scripts[self::POS_READY]) . "\n});";
			else
				$scripts[] = implode("\n", $this->scripts[self::POS_READY]);
		}
		if (!empty($scripts))
			$html.=self::script(implode("\n", $scripts)) . "\n";

		if ($fullPage)
			$output = str_replace('<###end###>', $html, $output);
		else
			$output = $output . $html;
	}

	/**
	 * Registers a CSS file
	 * @param StringHelper $url Url to the CSS file
	 * @param StringHelper $media media that the CSS file should be applied to. If empty, it means all media types.
	 * @return Scripts myself for chaining.
	 */
	public function registerCssFile($url, $media = '')
	{
//		$url = Site::model()->templateUrl.$url;
		
		$this->hasScripts = true;
		$this->cssFiles[$url] = $media;
		return $this;
	}

	/**
	 * Register a javascript file
	 * @param StringHelper $url url to the javascript file
	 * @param integer $position (HEAD, BEGIN, END)
	 * @return Scripts myself for chaining
	 */
	public function registerScriptFile($url, $position = self::POS_HEAD)
	{
//		$url = Site::model()->templateUrl.$url;
		
		$this->hasScripts = true;
		$this->scriptFiles[$position][$url] = $url;
		return $this;
	}
	
	/**
	 * Register a google api script
	 * @param StringHelper $package can be jquery or jquery-ui
	 * @param integer $position where to add the scriptfile
	 * @return Scripts myself for chaining
	 */
	public function registerGapiScript($package, $position = self::POS_HEAD)
	{
		switch($package)
		{
			case 'jquery':
				$this->gapiScripts[$position][$package] = 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js';
				break;
			case 'jquery-ui':
				$this->gapiScripts[$position][$package] = 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js';
				break;
			default:
				throw new \Exception('unknown GapiScript');
				break;
		}
		$this->hasScripts = true;
		return $this;
	}

	/**
	 * Reguster a ouece if havascript
	 * @param StringHelper $id unique identifier for the piece of code
	 * @param StringHelper $script the javascript code
	 * @param int $position position code shoudl be inserted (HEAD, BEGIN, END, READY)
	 * @return Scripts myself for chaining
	 */
	public function registerScript($id, $script, $position = self::POS_READY)
	{
		$this->hasScripts = true;
		$this->scripts[$position][$id] = $script;
		//TODO: check if jquery is loaded when adding script to POS_READY
		//if ($position === self::POS_READY)
		//	$this->registerCoreScript('jquery');
		return $this;
	}
/**
	 * Registers a piece of CSS code.
	 * @param StringHelper $id ID that uniquely identifies this piece of CSS code
	 * @param StringHelper $css the CSS code
	 * @param StringHelper $media media that the CSS code should be applied to. If empty, it means all media types.
	 * @return CClientScript the CClientScript object itself (to support method chaining, available since version 1.1.5).
	 */
	public function registerCss($id,$css,$media='')
	{
		$this->hasScripts=true;
		$this->css[$id]=array($css,$media);
		return $this;
	}
	/**
	 * Registers a meta tag that will be inserted in the head section before title element
	 * $this->registerMetaTag('example', 'description', null, array('lang' => 'en'))
	 * 
	 * @param StringHelper $content content attribute of metatag
	 * @param StringHelper $name name attribute of metatag
	 * @param StringHelper $httpEquiv httpequiv attribute of metatage
	 * @param array $options other option in name-value pair for metatag
	 * @return Scripts mysql for chaining
	 */
	public function registerMetaTag($content, $name = null, $httpEquiv = null, $options = array())
	{
		$this->hasScripts = true;
		if ($name !== null)
			$options['name'] = $name;
		if ($httpEquiv !== null)
			$options['http-equiv'] = $httpEquiv;
		$options['content'] = $content;
		$this->metaTags[serialize($options)] = $options;
		return $this;
	}
	
	//helper for generating script tags
	
	protected static function cssFile($url,$media='')
	{
		if($media!=='')
			$media=' media="'.$media.'"';
		return '<link rel="stylesheet" type="text/css" href="'.htmlspecialchars($url,ENT_QUOTES,'UTF-8').'"'.$media.' />';
	}
	
	protected static function script($text)
	{
		return "<script type=\"text/javascript\">\n/*<![CDATA[*/\n{$text}\n/*]]>*/\n</script>";
	}
	
	
	/**
	 * Encloses the given CSS content with a CSS tag.
	 * @param StringHelper $text the CSS content
	 * @param StringHelper $media the media that this CSS should apply to.
	 * @return StringHelper the CSS properly enclosed
	 */
	public static function css($text,$media='')
	{
		if($media!=='')
			$media=' media="'.$media.'"';
		return "<style type=\"text/css\"{$media}>\n/*<![CDATA[*/\n{$text}\n/*]]>*/\n</style>";
	}

	protected static function scriptFile($url)
	{
		return '<script type="text/javascript" src="'.htmlspecialchars($url,ENT_QUOTES,'UTF-8').'"></script>';
	}
	
	protected static function metaTag($content,$name=null,$httpEquiv=null,$options=array())
	{
		if($name!==null)
			$options['name']=$name;
		if($httpEquiv!==null)
			$options['http-equiv']=$httpEquiv;
		$options['content']=$content;
		
		$html = '<meta';
		foreach($options as $name=>$value)
			$html .= ' ' . $name . '="' . $value . '"';
		return $html.' />';
	}
}

?>
