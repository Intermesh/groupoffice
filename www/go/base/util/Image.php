<?php
/*
 * Copyright Intermesh
 *
 * This file is part of Group-Office. You should have received a copy of the
 * Group-Office license along with Group-Office. See the file /LICENSE.TXT
 *
 * If you have questions write an e-mail to info@intermesh.nl
 *
 */

/**
 * This class has functions that handle dates and takes the user's date
 * preferences into account.
 *
 * @copyright Copyright Intermesh BV.
 * @version $Id: Image.php 19784 2016-01-26 13:56:16Z michaelhart86 $
 * @package GO.base.util
 */


namespace GO\Base\Util;


class Image {

	private $original_image;
	private $resized_image;
	private $image_type;
	public $load_success;
	private $_original_filename;

	public function __construct($filename=false) {
		if ($filename)
			$this->load_success=$this->load($filename);
	}
	
	/**
	 * See IMAGETYPE GD PHP contants
	 * 
	 * @return int
	 */
	public function getImageType(){
		return $this->image_type;
	}
	
	/**
	 * Load an image file
	 * 
	 * @param StringHelper $filename
	 * @return boolean
	 */
	public function load($filename) {
		
		if(!function_exists("imagecreatefromjpeg")){
			trigger_error("Can't resize image because the PHP GD extension is not installed", E_USER_WARNING);
			return false;
		}
		
		$image_info = getimagesize($filename);
		$this->image_type = $image_info[2];
		
		$this->_original_filename=$filename;

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

	/**
	 * Set a transparent background for GIF or PNG images
	 */
	private function transperancy() {
		if ($this->image_type == IMAGETYPE_GIF || $this->image_type == IMAGETYPE_PNG) {
			$trnprt_indx = imagecolortransparent($this->original_image);
			$palletsize = imagecolorstotal($this->original_image);
			// If we have a specific transparent color
			if ($trnprt_indx >= 0 && $trnprt_indx < $palletsize) {

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

	/**
	 * Output image to browser
	 * 
	 * @param int $image_type
	 */
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
	
	private function _getImage(){
		return isset($this->resized_image) ? $this->resized_image :$this->original_image;
	}

	/**
	 * Save the imaage to a file
	 * 
	 * @param StringHelper $filename
	 * @param int $image_type
	 * @param int $compression
	 * @param oct $permissions file permissions
	 * @return boolean
	 */
	public function save($filename, $image_type=false, $compression=85, $permissions=null) {
		
		if(isset($this->resized_image) || $image_type!=$this->image_type){
		
			if (!$image_type)
				$image_type = $this->image_type;

			$ret = false;
			if ($image_type == IMAGETYPE_JPEG) {
				$ret = imagejpeg($this->_getImage(), $filename, $compression);
			} elseif ($image_type == IMAGETYPE_GIF) {
				$ret = imagegif($this->_getImage(), $filename);
			} elseif ($image_type == IMAGETYPE_PNG) {
				$ret = imagepng($this->_getImage(), $filename);
			}

			if(!$ret)
				return false;
		}else
		{
			//image type and dimension unchanged. Simply copy original.
			if(!copy($this->_original_filename, $filename))
				return false;
		}
		
		if ($permissions != null)
			chmod($filename, $permissions);
		
		return true;
		
	}

	/**
	 * Get the width in pixels
	 * 
	 * @return int
	 */
	public function getWidth() {
		return imagesx($this->original_image);
	}

	/**
	 * Get the height in pixels
	 * @return int
	 */
	public function getHeight() {
		return imagesy($this->original_image);
	}

	/**
	 * Returns true if this is a landscape image
	 * 
	 * @return boolean
	 */
	public function landscape() {
		return $this->getWidth() > $this->getHeight();
	}

	public function resizeToHeight($height) {
		$ratio = $height / $this->getHeight();
		$width = $this->getWidth() * $ratio;
		$this->resize($width, $height);
	}

	/**
	 * Resize to given width keeping aspect ration
	 * 
	 * @param int $width
	 */
	public function resizeToWidth($width) {
		$ratio = $width / $this->getWidth();
		$height = $this->getheight() * $ratio;
		$this->resize($width, $height);
	}

	/**
	 * Scale image to given scale factor
	 * 
	 * @param int $scale eg. 0.5
	 */
	public function scale($scale) {
		$width = $this->getWidth() * $scale / 100;
		$height = $this->getheight() * $scale / 100;
		$this->resize($width, $height);
	}

	/**
	 * Resize image
	 * 
	 * @param int $width
	 * @param int $height
	 */
	public function resize($width, $height) {
		$current_width = $this->getWidth();
		$current_height = $this->getHeight();

		$this->resized_image = imagecreatetruecolor($width, $height);

		$this->transperancy();

		imagecopyresampled($this->resized_image, $this->original_image, 0, 0, 0, 0, $width, $height, $current_width, $current_height);
	}
	
	/**
	 * Resize the image to fit a box while keeping aspect ratio.
	 * 
	 * @param int $width
	 * @param int $height
	 * @param boolean $enlarge Enlarge image if it's smaller then the box
	 */
	public function fitBox($width, $height, $enlarge=false){
		if($this->landscape()){
			
			if($width<$this->getWidth() || $enlarge)			
				$this->resizeToWidth($width);
		}else
		{
			if($height<$this->getHeight() || $enlarge)			
				$this->resizeToHeight($height);
		}
	}

	/**
	 * Zoom th image to fir the given height and width. Image aspect ratio is used 
	 * but if the image goes out of bounds then crop these parts.
	 * 
	 * @param int $thumbnail_width
	 * @param int $thumbnail_height
	 */
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
