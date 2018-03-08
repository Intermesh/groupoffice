<?php

/**
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 * @version $Id: thumb.php 17809 2014-07-22 11:23:28Z mschering $
 * @copyright Copyright Intermesh
 * @author Merijn Schering <mschering@intermesh.nl>
 */
/**
 * Generates thumbnail.
 *
 * 3 parameters can be passed:
 *
 * w = width
 * h = height
 * zc = 0 or 1. When set to 1 thumbnail will zoom in to the center and keep
 * aspect ratio.
 *
 * You should pass the filemtime of a file too so the browser will refresh the
 * thumbnail when this changes because this script will instruct the browser
 * to cache the thumbnail for one year.
 * 
 */
require('../Group-Office.php');

session_write_close();

$path = $_REQUEST['src'];

if (File::path_leads_to_parent($path))
	die('Invalid request');


$w = isset($_REQUEST['w']) ? intval($_REQUEST['w']) : 0;
$h = isset($_REQUEST['h']) ? intval($_REQUEST['h']) : 0;

$zc = !empty($_REQUEST['zc']) && !empty($w) && !empty($h);
$fb = !empty($_REQUEST['fb']) && !empty($w) && !empty($h);

$lw = isset($_REQUEST['lw']) ? intval($_REQUEST['lw']) : 0;
$lh = isset($_REQUEST['lh']) ? intval($_REQUEST['lh']) : 0;

$pw = isset($_REQUEST['pw']) ? intval($_REQUEST['pw']) : 0;
$ph = isset($_REQUEST['ph']) ? intval($_REQUEST['ph']) : 0;


if (File::get_extension($path) == 'xmind') {

	$filename = File::strip_extension(basename($path)) . '.jpeg';

	if (!file_exists($GLOBALS['GO_CONFIG']->file_storage_path . 'thumbcache/' . $filename) || filectime($GLOBALS['GO_CONFIG']->file_storage_path . 'thumbcache/' . $filename) < filectime($GLOBALS['GO_CONFIG']->file_storage_path . $path)) {
		$zipfile = zip_open($GLOBALS['GO_CONFIG']->file_storage_path . $path);

		while ($entry = zip_read($zipfile)) {
			if (zip_entry_name($entry) == 'Thumbnails/thumbnail.jpg') {
				require_once($GLOBALS['GO_CONFIG']->class_path . 'filesystem.class.inc');
				zip_entry_open($zipfile, $entry, 'r');
				file_put_contents($GLOBALS['GO_CONFIG']->file_storage_path . 'thumbcache/' . $filename, zip_entry_read($entry, zip_entry_filesize($entry)));
				zip_entry_close($entry);
				break;
			}
		}
		zip_close($zipfile);
	}
	$path = 'thumbcache/' . $filename;
}


$full_path = $GLOBALS['GO_CONFIG']->file_storage_path . $path;

$cache_dir = $GLOBALS['GO_CONFIG']->file_storage_path . 'thumbcache';
if (!is_dir($cache_dir)) {
	mkdir($cache_dir, 0755, true);
}
$filename = basename($path);
$file_mtime = filemtime($full_path);


$cache_filename = str_replace(array('/', '\\'), '_', dirname($path)) . '_' . $w . '_' . $h . '_' . $lw . '_' . $lh . '_' . $pw . '_' . $lw;
if ($zc) {
	$cache_filename .= '_zc';
}
//$cache_filename .= '_'.filesize($full_path);
$cache_filename .= $filename;

$readfile = $cache_dir . '/' . $cache_filename;
$thumb_exists = file_exists($cache_dir . '/' . $cache_filename);
$thumb_mtime = $thumb_exists ? filemtime($cache_dir . '/' . $cache_filename) : 0;

if (!empty($_REQUEST['nocache']) || !$thumb_exists || $thumb_mtime < $file_mtime || $thumb_mtime < filectime($full_path)) {
	$image = new Image($full_path);
	if (!$image->load_success) {
		//failed. Stream original image
		$readfile=$full_path;
	} else {


		if ($zc) {
			$image->zoomcrop($w, $h);
		}
		else if($fb) {
			$image->fitbox($w, $h);
		} else {
			if ($lw || $lh || $pw || $lw) {
				//treat landscape and portrait differently
				$landscape = $image->landscape();
				if ($landscape) {
					$w = $lw;
					$h = $lh;
				} else {
					$w = $pw;
					$h = $ph;
				}
			}

			if ($w && $h) {
				$image->resize($w, $h);
			} elseif ($w) {
				$image->resizeToWidth($w);
			} else {
				$image->resizeToHeight($h);
			}
		}

		$image->save($cache_dir . '/' . $cache_filename);

		
	}
}


header("Expires: " . date("D, j M Y G:i:s ", time() + (86400 * 365)) . 'GMT'); //expires in 1 year
header('Cache-Control: cache');
header('Pragma: cache');
$mime = File::get_mime($full_path);
header('Content-Type: ' . $mime);
header('Content-Disposition: inline; filename="' . $cache_filename . '"');
header('Content-Transfer-Encoding: binary');

readfile($readfile);
