<?php
$GO_SCRIPTS_JS .='GO.comments.enableReadMore="'.\GO\Comments\CommentsModule::loadReadMore().'";';

$GO_SCRIPTS_JS .='GO.comments.categoryRequired="'.\GO\Comments\CommentsModule::commentsRequired().'";';

$GO_SCRIPTS_JS .='GO.comments.disableOriginalCommentsCompany="'.\GO\Comments\CommentsModule::disableOriginalCommentsCompany().'";';
$GO_SCRIPTS_JS .='GO.comments.disableOriginalCommentsContact="'.\GO\Comments\CommentsModule::disableOriginalCommentsContact().'";';

$commentsDisableOriginalContactInConfig = empty(\GO::config()->comments_disable_original_contact)?0:1;
$commentsDisableOriginalCompanyInConfig = empty(\GO::config()->comments_disable_original_company)?0:1;

$GO_SCRIPTS_JS .='GO.comments.disabledOriginalCommentsContactInConfig='.$commentsDisableOriginalContactInConfig.';';
$GO_SCRIPTS_JS .='GO.comments.disabledOriginalCommentsCompanyInConfig='.$commentsDisableOriginalCompanyInConfig.';';
