<?php


namespace GO\Files\Controller;


class JuploadController extends \GO\Base\Controller\AbstractController {

//	protected function allowGuests(){ // TODO: REMOVE THIS AND FIX THE ACCESS TO THE ACTIONS
//		return array('*');
//	}
//	
	protected function actionRenderJupload($params) {

		//$cookieParams = session_get_cookie_params();

		$sessionCookie = 'Cookie: ' . session_name() . '=' . session_id();

		//TODO: Check if code below is necessary
		
//		if(!empty($cookieParams['domain']))
//			$sessionCookie .= '; Domain='.$cookieParams['domain'];
//		
//		$sessionCookie .= '; Path='.$cookieParams['path'];
//		
//		if(!empty($cookieParams['lifetime']))
//			$sessionCookie .= '; Expires='.gmdate('D, d M Y H:i:s \G\M\T', time()+$cookieParams['lifetime']);
//		
//		if($cookieParams['secure'])
//			$sessionCookie .= '; Secure';
//		
//		if($cookieParams['httponly'])
//			$sessionCookie .= '; HttpOnly';

		
//		$afterUploadScript = '
//			<script type="text/javascript">
//				function afterUpload(success){
////					opener.GO.files.juploadFileBrowser.sendOverwrite({upload:true});	
//					
//					if(success){
//						window.uploadSuccess=true;
//						setTimeout("self.close();", 1000);
//					}
//				}
//			</script>			
//		';
//
//		$appletCode = '
//			<applet
//				code="wjhk.jupload2.JUploadApplet"
//				name="JUpload"
//				archive="' . \GO::config()->host . 'go/vendor/jupload/wjhk.jupload.jar' . '"
//				width="640"
//				height="480"
//				mayscript="true"
//				alt="The java pugin must be installed.">
//				<param name="lang" value="' . \GO::user()->language . '" />
//				<param name="readCookieFromNavigator" value="false" />
//				<!--<param name="lookAndFeel" value="system" />-->
//				<param name="postURL" value="' . \GO::url('files/jupload/handleUploads') . '" />
//				<param name="afterUploadURL" value="javascript:afterUpload(%success%);" />
//				<param name="showLogWindow" value="false" />
//				<param name="maxChunkSize" value="1048576" />    
//				<param name="specificHeaders" value="' . $sessionCookie . '" />
//				<param name="maxFileSize" value="' . intval(\GO::config()->max_file_size) . '" />
//				<param name="nbFilesPerRequest" value="5" />
//				<!--<param name="debugLevel" value="99" />-->
//				Java 1.5 or higher plugin required. 
//			</applet>';
//		$this->render('jupload', array('applet' => $appletCode, 'afterUploadScript' => $afterUploadScript));
		
		$jnlp = '<?xml version="1.0" encoding="utf-8"?> 
<jnlp spec="1.0+" codebase="' . \GO::config()->full_url . 'go/vendor/jupload">
        <information>
                <title>JUpload</title>
                <vendor>JUpload</vendor>
                <homepage href="http://jupload.sourceforge.net/" />
                <description>
                        JUpload takes care of the limitation posed by traditional HTML upload forms by allowing you to upload a 
                        whole directory and the files within it with a single click. All information is available on the JUpload site.
                </description>
        </information>
        <security>
                <all-permissions/>
        </security>
        <resources>
                <j2se version="1.7+" />
                <jar href="wjhk.jupload.jar" />
        </resources>
         <applet-desc 
         name="JUpload Demo"
         main-class="wjhk.jupload2.JUploadApplet"
         width="640"
         height="400">
        <param name="postURL" value="' . \GO::url('files/jupload/handleUploads',array(), false) . '" />
				
				<param name="showLogWindow" value="false" />
				<param name="maxChunkSize" value="1048576" />    
				<param name="specificHeaders" value="' . $sessionCookie . '" />
				<param name="maxFileSize" value="' . intval(\GO::config()->max_file_size) . '" />
				<param name="nbFilesPerRequest" value="5" />        <!-- Add all your JUpload applet params here -->
     </applet-desc>
</jnlp>';
		
		$file = new \GO\Base\Fs\MemoryFile('jupload.jnlp', $jnlp);
		\GO\Base\Util\Http::downloadFile($file);

		
	}

	protected function actionHandleUploads($params) {

		if (!isset(\GO::session()->values['files']['uploadqueue']))
			\GO::session()->values['files']['uploadqueue'] = array();

		try {
			$chunkTmpFolder = new \GO\Base\Fs\Folder(\GO::config()->tmpdir . 'juploadqueue/chunks');
			$tmpFolder = new \GO\Base\Fs\Folder(\GO::config()->tmpdir . 'juploadqueue');

			$tmpFolder->create();
			$chunkTmpFolder->create();

			$count = 0;
			while ($uploadedFile = array_shift($_FILES)) {
				
				if (isset($params['jupart'])) {
					$originalFileName = $uploadedFile['name'];
					$uploadedFile['name'] = $uploadedFile['name'] . '.part' . $params['jupart'];
					$chunkTmpFolder->create();
					\GO\Base\Fs\File::moveUploadedFiles($uploadedFile, $chunkTmpFolder);
					if (!empty($params['jufinal'])) {
						$file = new \GO\Base\Fs\File($tmpFolder . '/' . $originalFileName);

						$fp = fopen($file->path(), 'w+');
						for ($i = 1; $i <= $params['jupart']; $i++) {
							$part = new \GO\Base\Fs\File($chunkTmpFolder . '/' . $originalFileName . '.part' . $i);
							fwrite($fp, $part->contents());
							$part->delete();
						}
						fclose($fp);

						$chunkTmpFolder->delete();
					} else {
						echo "SUCCESS\n";
						return;
					}
				} else {
					$files = \GO\Base\Fs\File::moveUploadedFiles($uploadedFile, $tmpFolder);
					if(!$files)
						throw new \Exception("No file received");
					
					$file = $files[0];
				}
				$subdir = false;
				if ((!empty($params['relpathinfo' . $count]) && !isset($params['jupart'])) ||
								(!empty($params['relpathinfo' . $count]) && isset($params['jupart']) && !empty($params['jufinal']))) {
					$fullpath = \GO::config()->tmpdir . 'juploadqueue' . '/' . str_replace('\\','/',$params['relpathinfo'.$count]);

					$dir = new \GO\Base\Fs\Folder($fullpath);
					$dir->create();
					$subdir = true;
					$file->move($dir);
				}
				$count++;
		
				if ($subdir) {
						
					$parent = $this->_findHighestParent($dir);
					
					\GO::debug($parent);
					if (!in_array($parent->path(), \GO::session()->values['files']['uploadqueue']))
						\GO::session()->values['files']['uploadqueue'][] = $parent->path();
				} else {
					\GO::session()->values['files']['uploadqueue'][] = $file->path();
				}
			}
		} catch (\Exception $e) {
			echo 'WARNING: ' . $e->getMessage() . "\n";
		}
		echo "SUCCESS\n";
	}
	
	private function _findHighestParent(\GO\Base\Fs\Folder $dir){
		$parent = $dir;

		while($parent->parent()->name()!="juploadqueue"){
			$parent=$parent->parent();
		}

		return $parent;
		
	}

}
