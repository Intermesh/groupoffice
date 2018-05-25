<?php


namespace GO\files\Controller;


class NotificationController extends \GO\Base\Controller\AbstractModelController {
    
    protected $model = 'GO\Files\Model\FolderNotification';
    
    protected function actionUnsent($params){
        \GO\Files\Model\FolderNotification::model()->notifyUser();

        $response = array(
            'success' => true
        );

        return $response;
    }
}
