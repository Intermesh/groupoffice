<?php
namespace go\core\util;

use Exception;
use IFW;

/**
 * Image resizer
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Image {

	private $_originalImage;
	private $_resizedImage;
	private $_imageType;
	private $_originalFilename;

	private $jpegCompression = 85;

	public $loadSuccess = false;
	
	
	public function __construct($filename) {
		$this->loadSuccess = $this->load($filename);
	}

	/**
	 * See IMAGETYPE GD PHP contants
	 *
	 * @return int
	 */
	public function getImageType() {
		return $this->_imageType;
	}

	/**
	 * Load an image file
	 *
	 * @param string $filename
	 * @return boolean
	 */
	public function load($filename) {

		if (!function_exists("imagecreatefromjpeg")) {
			trigger_error("Can't resize image because the PHP GD extension is not installed", E_USER_WARNING);
			return false;
		}

		$image_info = getimagesize($filename);
		$this->_imageType = $image_info[2];

		$this->_originalFilename = $filename;

		if ($this->_imageType == IMAGETYPE_JPEG) {
			$this->_originalImage = imagecreatefromjpeg($filename);
		} elseif ($this->_imageType == IMAGETYPE_GIF) {
			$this->_originalImage = imagecreatefromgif($filename);
		} elseif ($this->_imageType == IMAGETYPE_PNG) {
			$this->_originalImage = imagecreatefrompng($filename);
		} else {
			return false;
		}
		return true;
	}

	/**
	 * Set a transparent background for GIF or PNG images
	 */
	private function _transperancy() {
		if ($this->_imageType == IMAGETYPE_GIF || $this->_imageType == IMAGETYPE_PNG) {
			$trnprt_indx = imagecolortransparent($this->_originalImage);

			// If we have a specific transparent color
			if ($trnprt_indx >= 0) {

				// Get the original image's transparent color's RGB values
				$trnprt_color = imagecolorsforindex($this->_originalImage, $trnprt_indx);

				// Allocate the same color in the new image resource
				$trnprt_indx = imagecolorallocate($this->_resizedImage, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);

				// Completely fill the background of the new image with allocated color.
				imagefill($this->_resizedImage, 0, 0, $trnprt_indx);

				// Set the background color for new image to transparent
				imagecolortransparent($this->_resizedImage, $trnprt_indx);
			}
			// Always make a transparent background color for PNGs that don't have one allocated already
			elseif ($this->_imageType == IMAGETYPE_PNG) {

				// Turn off transparency blending (temporarily)
				imagealphablending($this->_resizedImage, false);

				// Create a new transparent color for image
				$color = imagecolorallocatealpha($this->_resizedImage, 0, 0, 0, 127);

				// Completely fill the background of the new image with allocated color.
				imagefill($this->_resizedImage, 0, 0, $color);

				// Restore transparency blending
				imagesavealpha($this->_resizedImage, true);
			}
		}
	}

	/**
	 * Output image to browser
	 *
	 * @param int $image_type
	 */
	public function output($image_type = false) {
		
		if(ob_get_contents() != '') {			
			throw new \Exception("Could not output file because output has already been sent. Turn off output buffering to find out where output has been started.");
		}
		
		//to send headers
		\go\core\App::get()->getResponse()->send();
		
		if (!$image_type)
			$image_type = $this->_imageType;

		if ($image_type == IMAGETYPE_JPEG) {
			imagejpeg($this->_getImage(), null, $this->jpegCompression);
		} elseif ($image_type == IMAGETYPE_GIF) {
			imagegif($this->_getImage());
		} elseif ($image_type == IMAGETYPE_PNG) {
			imagepng($this->_getImage());
		}
	}

	private function _getImage() {
		return isset($this->_resizedImage) ? $this->_resizedImage : $this->_originalImage;
	}

	public function contents() {
		ob_start();
		switch($this->_imageType) {
			case IMAGETYPE_JPEG: 
				imagejpeg($this->_getImage(), null, $this->jpegCompression); break;
			case IMAGETYPE_GIF: 
				imagegif($this->_getImage(), null); break;
			case IMAGETYPE_PNG: 
				imagepng($this->_getImage(), null); break;
		}
		$data = ob_get_contents();
		ob_end_clean();
		return $data;
	}

	/**
	 * Save the imaage to a file
	 *
	 * @param string $filename
	 * @param int $imageType
	 * @param int $compression
	 * @param oct $permissions file permissions
	 * @return boolean
	 */
	public function save($filename, $imageType = false, $compression = 85) {

		if (isset($this->_resizedImage) || $imageType != $this->_imageType) {

			if (!$imageType){
				$imageType = $this->_imageType;
			}

			$ret = false;
			if ($imageType == IMAGETYPE_JPEG) {
				$ret = imagejpeg($this->_getImage(), $filename, $compression);
			} elseif ($imageType == IMAGETYPE_GIF) {
				$ret = imagegif($this->_getImage(), $filename);
			} elseif ($imageType == IMAGETYPE_PNG) {
				$ret = imagepng($this->_getImage(), $filename);
			}

			if (!$ret)
				return false;
		}else {
			//image type and dimension unchanged. Simply copy original.
			if (!copy($this->_originalFilename, $filename))
				return false;
		}

		return true;
	}

	/**
	 * Get the width in pixels
	 *
	 * @return int
	 */
	public function getWidth() {
		return imagesx($this->_originalImage);
	}

	/**
	 * Get the height in pixels
	 * @return int
	 */
	public function getHeight() {
		return imagesy($this->_originalImage);
	}

	/**
	 * Returns true if this is a landscape image
	 *
	 * @return bool
	 */
	public function landscape() {
		return $this->getWidth() > $this->getHeight();
	}

	/**
	 * Resize this image to this height and keep aspect ratio
	 *
	 * @param int $height
	 * @return bool
	 */
	public function resizeToHeight($height) {
		$ratio = $height / $this->getHeight();
		$width = $this->getWidth() * $ratio;

		return $this->resize($width, $height);
	}

	/**
	 * Resize to given width and keep aspect ratio
	 *
	 * @param int $width
	 * @return bool
	 */
	public function resizeToWidth($width) {
		$ratio = $width / $this->getWidth();
		$height = $this->getheight() * $ratio;
		return $this->resize($width, $height);
	}

	/**
	 * Scale image to given scale factor
	 *
	 * @param int $scale eg. 0.5
	 * @return bool
	 */
	public function scale($scale) {
		$width = $this->getWidth() * $scale / 100;
		$height = $this->getheight() * $scale / 100;
		return $this->resize($width, $height);
	}

	/**
	 * Resize image
	 *
	 * @param int $width
	 * @param int $height
	 *
	 * @return bool
	 */
	public function resize($width, $height) {

		if(empty($width) || empty($height))
		{
			throw new Exception("invalid dimensions ".$width."x".$height);
		}

		$currentWidth = $this->getWidth();
		$currentHeight = $this->getHeight();

		if(!$this->_resizedImage = imagecreatetruecolor($width, $height)){
			throw new Exception("Could not create image");
		}

		$this->_transperancy();

		return imagecopyresampled($this->_resizedImage, $this->_originalImage, 0, 0, 0, 0, $width, $height, $currentWidth, $currentHeight);
	}

	/**
	 * Resize the image to fit a box while keeping aspect ratio.
	 *
	 * @param int $width
	 * @param int $height
	 */
	public function fitBox($width, $height) {
		if ($this->landscape()) {
			return $this->resizeToWidth($width);
		}else {
			return $this->resizeToHeight($height);
		}
	}

	/**
	 * Zoom th image to fir the given height and width. Image aspect ratio is used
	 * but if the image goes out of bounds then crop these parts.
	 *
	 * @param int $thumbnailWidth
	 * @param int $thumbnailHeight
	 *
	 * @return bool
	 */
	public function zoomcrop($thumbnailWidth, $thumbnailHeight) { //$imgSrc is a FILE - Returns an image resource.
		$widthOrig = $this->getWidth();
		$heightOrig = $this->getHeight();

		//getting the image dimensions
		$ratioOrig = $widthOrig / $heightOrig;

		if ($thumbnailWidth / $thumbnailHeight > $ratioOrig) {
			$newHeight = $thumbnailWidth / $ratioOrig;
			$newWidth = $thumbnailWidth;
		} else {
			$newWidth = $thumbnailHeight * $ratioOrig;
			$newHeight = $thumbnailHeight;
		}

		$this->_resizedImage = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);

		$x = ($newWidth - $thumbnailWidth) / -2;
		$y = ($newHeight - $thumbnailHeight) / -2;

		$this->_transperancy();
		return imagecopyresampled($this->_resizedImage, $this->_originalImage, $x, $y, 0, 0, $newWidth, $newHeight, $widthOrig, $heightOrig);
	}
}
