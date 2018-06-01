<?php
namespace GO\Site\Tag;

interface  TagInterface{	
	/**
	 * 
	 * Renders a tag for the content.
	 * 
	 * Example tag
	 * 
	 * {site:thumb path="Ticket types.png" lw="300" ph="300" zoom="true" lightbox="docs" caption="Screenshot of the tickets module"}{/site:thumb}
	 * 
	 * 
	 * Example of $tag array:
	 * 
	 * array (size=4)
   * 'outerText' => string '{site:thumb path="Tickets.png" lw="300" ph="300" zoom="true" lightbox="docs" caption="Screenshot of the tickets module"}{/site:thumb}' (length=133)
   * 'tagName' => string 'thumb' (length=5)
   * 'params' => 
   *   array (size=6)
   *     'path' => string 'Tickets.png' (length=11)
   *     'lw' => string '300' (length=3)
   *     'ph' => string '300' (length=3)
   *     'zoom' => string 'true' (length=4)
   *     'lightbox' => string 'docs' (length=4)
   *     'caption' => string 'Screenshot of the tickets module' (length=32)
   * 'innerText' => string '' (length=0)
	 * 
	 * @param array $params Key value array of all attributes of the tag
	 * @param array $tag Tag parameters 
	 */
	public static function render($params, $tag, \GO\Site\Model\Content $content);	
}
