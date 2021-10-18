<?php
/**
 * Example usage:
 * 
 * $pluploader = new \GO\Site\Widgets\Plupload\Widget();
 * echo $pluploader->render();
 */

namespace GO\Site\Widget\Plupload;


class Widget extends \GO\Site\Components\Widget {
	
	public $max_file_size; //The maximun filesize of a single file [defaults to \GO::config()->max_file_size]
	public $chunk_size = '2mb'; //Large files will be chunked to this size
	public $runtimes = 'html5,gears,flash,browserplus,html4'; //Runtimes to be used on order of try and fail
	public $uploadTarget; //Upload target to post $_FILES to defaults to sites/site/plupload
	
	public $resizeImages = false; //resize images on upload
	public $resizeWidth = 320; //if resizeImages is true se this width
	public $resizeHeight = 240; //and this height
	public $resizequality = 90; //and this jpeg quality
	
	private $_swfUrl = '/plupload/js/plupload.flash.swf';
		
	public function init() {
		try{
			$this->uploadTarget = \Site::urlManager()->createUrl('site/front/ajaxWidget', array('widget_method'=>'upload', 'widget_class'=>$this->className()));

			if(empty($this->max_file_size))
				$this->max_file_size = \GO::config()->max_file_size;

//			\Site::scripts()->registerGapiScript('jquery');
//			\Site::scripts()->registerGapiScript('jquery-ui');

			$assetUrl = \Site::assetManager()->publish(\GO::config()->root_path.'modules/site/widget/plupload/assets');

			$this->_swfUrl = $assetUrl.'/assets/js/plupload.flash.swf';

			\Site::scripts()->registerCssFile($assetUrl.'/assets/style.css');
			\Site::scripts()->registerScriptFile($assetUrl.'/assets/js/plupload.full.js');
			\Site::scripts()->registerScriptFile($assetUrl.'/assets/js/jquery.plupload.queue/jquery.plupload.queue.js'); 

			$langFile = '/assets/js/i18n/'.\GO::language()->getLanguage().'.js';
			if(file_exists(\Site::assetManager()->getBasePath().$langFile)){
				\Site::scripts()->registerScriptFile($assetUrl.$langFile); 
			}
		}
		catch(\Exception $e){
			echo '<h2 style="color:red;">AN ERROR HAS OCCURED</h2>';
			//echo '<p style="color:red;">'.$e->getMessage().'</p>';
			echo '<p style="color:red;">Please check if the folder( <b>'.\GO::config()->assets_path.'</b> ) is writable for the webserver.<br />This path is also configurable in the Group-Office <b>config.php</b> file.<br />Please check the options: <b>assets_path</b> and <b>assets_url</b></p>';
		}
	}
	
	/**
	 * Render an empty div where the uploader will eventualy be rendered after the javascript loads
	 * @return type
	 */
	public function render() {

		\Site::scripts()->registerScript('plupload#'.$this->id, $this->createjs(), \GO\Site\Components\Scripts::POS_END);

		return '<div id="'.$this->id.'">Loading upload widget...</div>';
	}
	
	private function createjs(){	
		$script = <<<EOD

			$("#$this->id").pluploadQueue({
				runtimes : '$this->runtimes',
				url : '$this->uploadTarget',
				max_file_size : '$this->max_file_size',
				chunk_size : '$this->chunk_size',
				multiple_queues : true,
				dragdrop : false,
				flash_swf_url : '$this->_swfUrl'
			});
			
			var uploader = $("#$this->id").pluploadQueue();

			uploader.bind('FilesAdded', function(up, files) {
					uploader.start();
			});
EOD;
		return $script;
	} 	
	
	public static function upload($params){
		\GO\Base\Component\Plupload::handleUpload();
	}
}
