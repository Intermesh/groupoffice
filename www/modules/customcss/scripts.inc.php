<?php
if(\GO::modules()->files){
	
	$folder = \GO\Files\Model\Folder::model()->findByPath ('public/customcss', true);

	$GO_SCRIPTS_JS .= 'GO.customcss.filesFolderId='.$folder->id.';';
}
