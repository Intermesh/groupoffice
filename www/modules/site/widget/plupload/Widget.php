<?php

namespace GO\Site\Widget\Plupload;

/**
 * @deprecated Uploading from the site was removed because the upload endpoint was open to anyone. This widget
 * renders nothing and is only kept so that sites that still use it don't break.
 */
class Widget extends \GO\Site\Components\Widget {

	public $max_file_size; //The maximun filesize of a single file [defaults to \GO::config()->max_file_size]
	public $chunk_size = '2mb'; //Large files will be chunked to this size
	public $runtimes = 'html5,gears,flash,browserplus,html4'; //Runtimes to be used on order of try and fail
	public $uploadTarget; //Upload target to post $_FILES to defaults to sites/site/plupload

	public $resizeImages = false; //resize images on upload
	public $resizeWidth = 320; //if resizeImages is true se this width
	public $resizeHeight = 240; //and this height
	public $resizequality = 90; //and this jpeg quality

	/**
	 * Renders nothing
	 *
	 * @return string
	 */
	public function render() {
		return '';
	}
}
