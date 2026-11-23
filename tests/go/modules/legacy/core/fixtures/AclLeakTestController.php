<?php

namespace GO\Files\Controller;

/**
 * Controller for MultiRequestAclTest. One action leaves permissions ignored, the other records if they are ignored.
 */
class AclLeakTestController extends \GO\Base\Controller\AbstractController
{
	public static ?bool $ignoredInNextRequest = null;

	protected function actionLeak($params)
	{
		\GO::$ignoreAclPermissions = true;
		return ['success' => true];
	}

	protected function actionRecord($params)
	{
		self::$ignoredInNextRequest = \GO::$ignoreAclPermissions;
		return ['success' => true];
	}
}
