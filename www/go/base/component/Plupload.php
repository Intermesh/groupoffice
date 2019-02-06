<?php


namespace GO\Base\Component;


class Plupload {

	public static function handleUpload() {
		$tmpFolder = new \GO\Base\Fs\Folder(\GO::config()->tmpdir . 'uploadqueue');
		//$tmpFolder->delete();
		$tmpFolder->create();

//		$files = \GO\Base\Fs\File::moveUploadedFiles($_FILES['attachments'], $tmpFolder);
//		\GO::session()->values['files']['uploadqueue'] = array();
//		foreach ($files as $file) {
//			\GO::session()->values['files']['uploadqueue'][] = $file->path();
//		}

		if (!isset(\GO::session()->values['files']['uploadqueue']))
			\GO::session()->values['files']['uploadqueue'] = array();

		$targetDir = $tmpFolder->path();

		// Get parameters
		$chunk = isset($_POST["chunk"]) ? $_POST["chunk"] : 0;
		$chunks = isset($_POST["chunks"]) ? $_POST["chunks"] : 0;
		$fileName = isset($_POST["name"]) ? $_POST["name"] : '';

// Clean the fileName for security reasons
		$fileName = \go\core\util\StringUtil::normalize(\GO\Base\Fs\File::stripInvalidChars($fileName));

// Make sure the fileName is unique but only if chunking is disabled
//		if ($chunks < 2 && file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName)) {
//			$ext = strrpos($fileName, '.');
//			$fileName_a = substr($fileName, 0, $ext);
//			$fileName_b = substr($fileName, $ext);
//
//			$count = 1;
//			while (file_exists($targetDir . DIRECTORY_SEPARATOR . $fileName_a . '_' . $count . $fileName_b))
//				$count++;
//
//			$fileName = $fileName_a . '_' . $count . $fileName_b;
//		}
// Look for the content type header
		if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];

		if (isset($_SERVER["CONTENT_TYPE"]))
			$contentType = $_SERVER["CONTENT_TYPE"];

		if (!in_array($targetDir . DIRECTORY_SEPARATOR . $fileName, \GO::session()->values['files']['uploadqueue']))
			\GO::session()->values['files']['uploadqueue'][] = $targetDir . DIRECTORY_SEPARATOR . $fileName;

		$file = new \GO\Base\Fs\File($targetDir . DIRECTORY_SEPARATOR . $fileName);
		if ($file->exists() && $file->size() > \GO::config()->max_file_size)
			throw new \Exception("File too large");

// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos($contentType, "multipart") !== false) {

			if (isset($_FILES['file']['tmp_name']) && is_uploaded_file($_FILES['file']['tmp_name'])) {

				// Open temp file
				$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = fopen($_FILES['file']['tmp_name'], "rb");

					if ($in) {
						while ($buff = fread($in, 4096))
							fwrite($out, $buff);
					} else
						die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
					fclose($in);
					fclose($out);
					@unlink($_FILES['file']['tmp_name']);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
		} else {
			// Open temp file
			$out = fopen($targetDir . DIRECTORY_SEPARATOR . $fileName, $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen("php://input", "rb");

				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

				fclose($in);
				fclose($out);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}

// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "result": null, "success":true, "id" : "id"}');
	}

}
