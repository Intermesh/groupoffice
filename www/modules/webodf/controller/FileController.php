<?php


namespace GO\Webodf\Controller;

use Exception;
use GO;
use GO\Base\Controller\AbstractController;


class FileController extends AbstractController {

	protected function actionEdit($id) {
		
		echo '<!DOCTYPE HTML>
<html style="width:100%; height:100%; margin:0px; padding:0px" xml:lang="en" lang="en">
  <head>
    <!--
    Example page for how to use the Wodo.TextEditor
    This page is not usable directly from the WebODF sources, only from the build or from the released Wodo.TextEditor package.
    -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

    <title>Wodo ODF editor</title>

    <script src="modules/webodf/wodotexteditor/wodotexteditor.js" type="text/javascript" charset="utf-8"></script>
    <script type="text/javascript">

		var editor;

        function createEditor() {
            // begin: check for being served by a webserver
            // just done to catch a mistake sometimes done by people testing Wodo.TextEditor
            // who might have missed this requirement before
            var href = window.location.href;
            if (! /^http(s)?:/.test(href)) {
                alert("texteditor.html must be served by a webserver.");
                return;
            }
            // end: check for being served by a webserver

            var editorOptions = {
                userData: {
                    fullName: "Tim Lee",
                    color:    "blue"
                },
                //annotationsEnabled: true,
				allFeaturesEnabled: true,
				saveCallback: save
            };
            function onEditorCreated(err, e) {
				
				editor = e;

                if (err) {
                    // something failed unexpectedly, deal with it (here just a simple alert)
                    alert(err);
                    return;
                }
                editor.openDocumentFromUrl("'.GO::url('files/file/download', array('id'=>$id)).'", function(err) {
                    if (err) {
                        // something failed unexpectedly, deal with it (here just a simple alert)
                        alert("There was an error on opening the document: " + err);
                    }
                });
            }
			
			function save() {
				function saveByteArrayLocally(err, data) {
					if (err) {
						alert(err);
						return;
					}
					
					var mimetype = "application/vnd.oasis.opendocument.text",
						blob = new Blob([data.buffer], {type: mimetype});
					
					var xhr = new XMLHttpRequest();
					
					xhr.open("POST", "'.GO::url('webodf/file/save', array('id'=>$id)).'");
					  var formData = new FormData();
					  formData.append("data", blob);
					  xhr.send(formData);
				}

				editor.getDocumentAsByteArray(saveByteArrayLocally);
			}
            Wodo.createTextEditor("editorContainer", editorOptions, onEditorCreated);
        }
    </script>
  </head>

  <body style="width:100%; height:100%; margin:0px; padding:0px" onload="createEditor();">
    <div id="editorContainer" style="width:100%; height:100%; margin:0px; padding:0px">
    </div>
  </body>
</html>';
		
		
	}
	
	protected function actionSave($id){
		
//		throw new \GO\Base\Exception\AccessDenied();
		
		$file = \GO\Files\Model\File::model()->findByPk($id);
		
		if(!$file){
			throw new \GO\Base\Exception\NotFound();
		}
		
		if(empty($_FILES)){
			throw new Exception("Server did not recieve a file. Perhaps the file was too large?");
		}
		
		$upfile = array_shift($_FILES);
		
		if(empty($upfile['tmp_name'])){			
			throw new Exception("Server did not recieve a file. Perhaps the file was too large?");
		}

		
		$file->replace(new \GO\Base\Fs\File($upfile['tmp_name']), false);

		//GOTA java program will check for the following string. Anything else will
		//be treated as an error.
//		echo 'SUCCESS';
		
		$response['success']=true;
		
		return $response;
	}
}