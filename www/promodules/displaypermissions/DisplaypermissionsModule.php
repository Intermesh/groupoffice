<?php
namespace GO\Displaypermissions;

use GO\Professional\Module;

class DisplaypermissionsModule extends Module {

	public static function initListeners() {
//		$commentController = new CommentController();
//		$commentController->addListener('submit', "GO\Commentsreport\CommentsreportModule", "onCommentSubmit");
	}

	public function package() {
		return self::PACKAGE_CUSTOM;
	}
	
}
