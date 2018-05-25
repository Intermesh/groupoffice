<?php


namespace GO\Phpbb3\Controller;


class BridgeController extends \GO\Base\Controller\AbstractController {

	protected function actionRedirect() {
		$tmpFile = \GO\Base\Fs\File::tempFile();
		$tmpFile->putContents(\GO::user()->id);
		
		if (empty(\GO::config()->phpbb3_url)) {
			throw new \Exception('You must configure phpbb3_url in your config.php file');
		}

		$url = \GO::config()->phpbb3_url. '?goauth=' . base64_encode($tmpFile->path()) . '&sid=' . md5(uniqid(time()));
		header('Location: ' . $url);
		exit();
	}

}
