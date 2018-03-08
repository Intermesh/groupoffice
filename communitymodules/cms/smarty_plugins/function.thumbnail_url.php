<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty substring modifier plugin
 *
 * Type:     modifier<br>
 * Name:     substring<br>
 * Purpose:  Substring a string
 *
 * @author   Merijn Schering <mschering@intermesh.nl>
 * @param StringHelper
 * @param integer
 * @param StringHelper
 * @param boolean
 * @param boolean
 * @return StringHelper
 */
function smarty_function_thumbnail_url($params)
{
	$path = $params['path'];
	$w = isset($params['w']) ? $params['w'] : 0;
	$h = isset($params['h']) ? $params['h'] : 0;
	$zc = isset($params['zc']) ? $params['zc'] : 1;

	return get_thumb_url($path, $w, $h, $zc);
}
?>