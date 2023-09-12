<?php
namespace go\core\util;

use Exception;
use go\core\App;
use go\core\http\Response;

/**
 * Image resizer
 *
 * @copyright (c) 2014, Intermesh BV http://www.intermesh.nl
 * @author Merijn Schering <mschering@intermesh.nl>
 * @license http://www.gnu.org/licenses/agpl-3.0.html AGPLv3
 */
class Image {

	private $originalImage;
	private $resizedImage;
	private $imageType;
	private $originalFilename;

	private $jpegCompression = 85;

	public $loadSuccess = false;
	
	
	public function __construct(string $filename) {
		$this->loadSuccess = $this->load($filename);
	}

	/**
	 * See IMAGETYPE GD PHP contants
	 *
	 * @return int
	 */
	public function getImageType(): int
	{
		return $this->imageType;
	}

	/**
	 * Load an image file
	 *
	 * @param string $filename
	 * @return boolean
	 */
	public function load(string $filename): bool
	{
		if (!function_exists("imagecreatefromjpeg")) {
			trigger_error("Can't resize image because the PHP GD extension is not installed", E_USER_WARNING);
			return false;
		}

		$image_info = getimagesize($filename);
		if(!$image_info) {
			return false;
		}

		$this->imageType = $image_info[2];

		$this->originalFilename = $filename;

		if ($this->imageType == IMAGETYPE_JPEG) {
			$this->originalImage = imagecreatefromjpeg($filename);
		} elseif ($this->imageType == IMAGETYPE_GIF) {
			$this->originalImage = imagecreatefromgif($filename);
		} elseif ($this->imageType == IMAGETYPE_PNG) {
			$this->originalImage = imagecreatefrompng($filename);
		} else {
			return false;
		}
		return true;
	}

	/**
	 * Set a transparent background for GIF or PNG images
	 */
	private function transperancy() {
		if ($this->imageType == IMAGETYPE_GIF || $this->imageType == IMAGETYPE_PNG) {
			$trnprt_indx = imagecolortransparent($this->originalImage);

			// If we have a specific transparent color
			if ($trnprt_indx >= 0) {

				// Get the original image's transparent color's RGB values
				$trnprt_color = imagecolorsforindex($this->originalImage, $trnprt_indx);

				// Allocate the same color in the new image resource
				$trnprt_indx = imagecolorallocate($this->resizedImage, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);

				// Completely fill the background of the new image with allocated color.
				imagefill($this->resizedImage, 0, 0, $trnprt_indx);

				// Set the background color for new image to transparent
				imagecolortransparent($this->resizedImage, $trnprt_indx);
			}
			// Always make a transparent background color for PNGs that don't have one allocated already
			elseif ($this->imageType == IMAGETYPE_PNG) {

				// Turn off transparency blending (temporarily)
				imagealphablending($this->resizedImage, false);

				// Create a new transparent color for image
				$color = imagecolorallocatealpha($this->resizedImage, 0, 0, 0, 127);

				// Completely fill the background of the new image with allocated color.
				imagefill($this->resizedImage, 0, 0, $color);

				// Restore transparency blending
				imagesavealpha($this->resizedImage, true);
			}
		}
	}

	/**
	 * Automatically rotate image according to exif data
	 * @return void
	 */
	public function fixOrientation() {
		if(!function_exists("exif_read_data")) {
			return false;
		}
		$exif = exif_read_data($this->originalFilename);

		if (!empty($exif['Orientation'])) {
			switch ($exif['Orientation']) {
				case 3:
					$this->resizedImage = imagerotate($this->getImage(), 180, 0);
					break;

				case 6:
					$this->resizedImage = imagerotate($this->getImage(), -90, 0);
					break;

				case 8:
					$this->resizedImage = imagerotate($this->getImage(), 90, 0);
					break;
			}
		}
	}

	/**
	 * Output image to browser
	 *
	 * @param int $image_type
	 * @throws Exception
	 */
	public function output($image_type = false) {
		
		if(ob_get_contents() != '') {			
			throw new Exception("Could not output file because output has already been sent. Turn off output buffering to find out where output has been started.");
		}
		
		//to send headers
		Response::get()->sendHeaders();
		
		if (!$image_type)
			$image_type = $this->imageType;

		if ($image_type == IMAGETYPE_JPEG) {
			imagejpeg($this->getImage(), null, $this->jpegCompression);
		} elseif ($image_type == IMAGETYPE_GIF) {
			imagegif($this->getImage());
		} elseif ($image_type == IMAGETYPE_PNG) {
			imagepng($this->getImage());
		}
	}

	private function getImage() {
		return $this->resizedImage ?? $this->originalImage;
	}

	public function contents() {
		ob_start();
		switch($this->imageType) {
			case IMAGETYPE_JPEG: 
				imagejpeg($this->getImage(), null, $this->jpegCompression); break;
			case IMAGETYPE_GIF: 
				imagegif($this->getImage(), null); break;
			case IMAGETYPE_PNG: 
				imagepng($this->getImage(), null); break;
		}
		$data = ob_get_contents();
		ob_end_clean();
		return $data;
	}

	/**
	 * Save the imaage to a file
	 *
	 * @param string $filename
	 * @param int|null $imageType
	 * @param int $compression
	 * @return boolean
	 */
	public function save(?string $filename = null, ?int $imageType = null, int $compression = 85): bool
	{
		if($filename == null) {
			$filename = $this->originalFilename;
		}

		if (isset($this->resizedImage) || $imageType != $this->imageType) {

			if (!isset($imageType)) {
				$imageType = $this->imageType;
			}

			$ret = false;
			if ($imageType == IMAGETYPE_JPEG) {
				$ret = imagejpeg($this->getImage(), $filename, $compression);
			} elseif ($imageType == IMAGETYPE_GIF) {
				$ret = imagegif($this->getImage(), $filename);
			} elseif ($imageType == IMAGETYPE_PNG) {
				$ret = imagepng($this->getImage(), $filename);
			}

			if (!$ret)
				return false;
		}else {
			//image type and dimension unchanged. Simply copy original.
			if ($this->originalFilename != $filename && !copy($this->originalFilename, $filename))
				return false;
		}

		return true;
	}

	/**
	 * Get the width in pixels
	 *
	 * @return int
	 */
	public function getWidth(): int
	{
		return imagesx($this->getImage());
	}

	/**
	 * Get the height in pixels
	 * @return int
	 */
	public function getHeight(): int
	{
		return imagesy($this->getImage());
	}

	/**
	 * Returns true if this is a landscape image
	 *
	 * @return bool
	 */
	public function landscape(): bool
	{
		return $this->getWidth() > $this->getHeight();
	}

	/**
	 * Resize this image to this height and keep aspect ratio
	 *
	 * @param int $height
	 * @return bool
	 * @throws Exception
	 */
	public function resizeToHeight(int $height): bool
	{
		$ratio = $height / $this->getHeight();
		$width = floor($this->getWidth() * $ratio);

		return $this->resize($width, $height);
	}

	/**
	 * Resize to given width and keep aspect ratio
	 *
	 * @param int $width
	 * @return bool
	 * @throws Exception
	 */
	public function resizeToWidth(int $width): bool
	{
		$ratio = $width / $this->getWidth();
		$height = floor($this->getheight() * $ratio);
		return $this->resize($width, $height);
	}

	/**
	 * Scale image to given scale factor
	 *
	 * @param float $scale eg. 0.5
	 * @return bool
	 * @throws Exception
	 */
	public function scale(float $scale): bool
	{
		$width = floor($this->getWidth() * $scale / 100);
		$height = floor($this->getheight() * $scale / 100);
		return $this->resize($width, $height);
	}

	/**
	 * Resize image
	 *
	 * @param int $width
	 * @param int $height
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function resize(int $width, int $height): bool
	{
		if(empty($width) || empty($height))
		{
			throw new Exception("invalid dimensions ".$width."x".$height);
		}

		$currentWidth = $this->getWidth();
		$currentHeight = $this->getHeight();
		$original = $this->getImage();

		if(!$this->resizedImage = imagecreatetruecolor($width, $height)){
			throw new Exception("Could not create image");
		}

		$this->transperancy();

		return imagecopyresampled($this->resizedImage, $original, 0, 0, 0, 0, $width, $height, $currentWidth, $currentHeight);
	}

	/**
	 * Resize the image to fit a box while keeping aspect ratio.
	 *
	 * @param int $width
	 * @param int $height
	 * @return bool
	 * @throws Exception
	 * @throws Exception
	 * @throws Exception
	 * @throws Exception
	 */
	public function fitBox(int $width, int $height): bool
	{
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
	public function zoomcrop(int $thumbnailWidth, int $thumbnailHeight): bool
	{ //$imgSrc is a FILE - Returns an image resource.
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

		$this->resizedImage = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);

		$x = ($newWidth - $thumbnailWidth) / -2;
		$y = ($newHeight - $thumbnailHeight) / -2;

		$this->transperancy();
		return imagecopyresampled($this->resizedImage, $this->originalImage, $x, $y, 0, 0, $newWidth, $newHeight, $widthOrig, $heightOrig);
	}
}
