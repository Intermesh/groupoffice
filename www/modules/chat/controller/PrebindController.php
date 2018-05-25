<?php

namespace GO\Chat\Controller;

use GO\Chat\ChatModule;

class PrebindController extends \GO\Base\Controller\AbstractController {
	public function actionGet() {
		echo json_encode(ChatModule::getPrebindInfo());
	}
}
