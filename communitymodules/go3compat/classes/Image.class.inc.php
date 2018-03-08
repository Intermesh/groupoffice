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
 * @version $Id: Image.class.inc.php 17809 2014-07-22 11:23:28Z mschering $
 * @author Merijn Schering <mschering@intermesh.nl>
 */
class Image {

	var $original_image;
	var $resized_image;
	var $image_type;
	var $load_success;

	public function __construct($filename=false) {
		if ($filename)
			$this->load_success=$this->load($filename);
	}

	public function load($filename) {
		$image_info = getimagesize($filename);
		$this->image_type = $image_info[2];
		go_debug($this->image_type);
		if ($this->image_type == IMAGETYPE_JPEG) {
			$this->original_image = imagecreatefromjpeg($filename);
		} elseif ($this->image_type == IMAGETYPE_GIF) {
			$this->original_image = imagecreatefromgif($filename);
		} elseif ($this->image_type == IMAGETYPE_PNG) {
			$this->original_image = imagecreatefrompng($filename);
		} else {
			return false;
		}
		return true;
	}

	private function transperancy() {
		if ($this->image_type == IMAGETYPE_GIF || $this->image_type == IMAGETYPE_PNG) {
			$trnprt_indx = imagecolortransparent($this->original_image);

			// If we have a specific transparent color
			if ($trnprt_indx >= 0) {

				// Get the original image's transparent color's RGB values
				$trnprt_color = imagecolorsforindex($this->original_image, $trnprt_indx);

				// Allocate the same color in the new image resource
				$trnprt_indx = imagecolorallocate($this->resized_image, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);

				// Completely fill the background of the new image with allocated color.
				imagefill($this->resized_image, 0, 0, $trnprt_indx);

				// Set the background color for new image to transparent
				imagecolortransparent($this->resized_image, $trnprt_indx);
			}
			// Always make a transparent background color for PNGs that don't have one allocated already
			elseif ($this->image_type == IMAGETYPE_PNG) {

				// Turn off transparency blending (temporarily)
				imagealphablending($this->resized_image, false);

				// Create a new transparent color for image
				$color = imagecolorallocatealpha($this->resized_image, 0, 0, 0, 127);

				// Completely fill the background of the new image with allocated color.
				imagefill($this->resized_image, 0, 0, $color);

				// Restore transparency blending
				imagesavealpha($this->resized_image, true);
			}
		}
	}

	public function output($image_type=false) {
		if (!$image_type)
			$image_type = $this->image_type;

		if ($image_type == IMAGETYPE_JPEG) {
			imagejpeg($this->resized_image);
		} elseif ($image_type == IMAGETYPE_GIF) {
			imagegif($this->resized_image);
		} elseif ($image_type == IMAGETYPE_PNG) {
			imagepng($this->resized_image);
		}
	}

	public function save($filename, $image_type=false, $compression=75, $permissions=null) {
		if (!$image_type)
			$image_type = $this->image_type;

		if ($image_type == IMAGETYPE_JPEG) {
			imagejpeg($this->resized_image, $filename, $compression);
		} elseif ($image_type == IMAGETYPE_GIF) {
			imagegif($this->resized_image, $filename);
		} elseif ($image_type == IMAGETYPE_PNG) {
			imagepng($this->resized_image, $filename);
		}
		if ($permissions != null) {
			chmod($filename, $permissions);
		}
	}

	public function getWidth() {
		return imagesx($this->original_image);
	}

	public function getHeight() {
		return imagesy($this->original_image);
	}

	public function landscape() {
		return $this->getWidth() > $this->getHeight();
	}

	public function resizeToHeight($height) {
		$ratio = $height / $this->getHeight();
		$width = $this->getWidth() * $ratio;
		$this->resize($width, $height);
	}

	public function resizeToWidth($width) {
		$ratio = $width / $this->getWidth();
		$height = $this->getheight() * $ratio;
		$this->resize($width, $height);
	}

	public function scale($scale) {
		$width = $this->getWidth() * $scale / 100;
		$height = $this->getheight() * $scale / 100;
		$this->resize($width, $height);
	}

	public function resize($width, $height) {
		$current_width = $this->getWidth();
		$current_height = $this->getHeight();

		$this->resized_image = imagecreatetruecolor($width, $height);

		$this->transperancy();

		imagecopyresampled($this->resized_image, $this->original_image, 0, 0, 0, 0, $width, $height, $current_width, $current_height);
	}

		
	public function fitbox($box_width, $box_height) {
		$width_orig = $this->getWidth();
		$height_orig = $this->getHeight();
		
		$ratio_orig = $width_orig / $height_orig;
		
		if ($box_width / $box_height < $ratio_orig) {
			$this->resizeToWidth($box_width);
		} else {
			$this->resizeToHeight($box_height);
		}
	}
	
	public function zoomcrop($thumbnail_width, $thumbnail_height) { //$imgSrc is a FILE - Returns an image resource.
		$width_orig = $this->getWidth();
		$height_orig = $this->getHeight();

		//getting the image dimensions
		$ratio_orig = $width_orig / $height_orig;

		if ($thumbnail_width / $thumbnail_height > $ratio_orig) {
			$new_height = $thumbnail_width / $ratio_orig;
			$new_width = $thumbnail_width;
		} else {
			$new_width = $thumbnail_height * $ratio_orig;
			$new_height = $thumbnail_height;
		}

		$this->resized_image = imagecreatetruecolor($thumbnail_width, $thumbnail_height);

		$x = ($new_width - $thumbnail_width) / -2;
		$y = ($new_height - $thumbnail_height) / -2;

		$this->transperancy();
		imagecopyresampled($this->resized_image, $this->original_image, $x, $y, 0, 0, $new_width, $new_height, $width_orig, $height_orig);
	}

}