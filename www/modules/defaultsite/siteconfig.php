<?php

$siteconfig['templates']=array(
		'/site/content'=>'Default',

);

$siteconfig['urls']=array(
//		'newticket' => 'tickets/externalpage/newticket',
//		'/' => 'tickets/externalpage/ticketlist',
//		'<action:(login|logout|register|profile|resetpassword|recoverpassword)>' => 'site/account/<action>',//TODO: login, logout, profile resetpassword, register, recover/lostpassword
////		'site/<controller:\w+>/<action:\w+>'=>'site/<controller>/<action>',
////		'<slug:[\w\/.-]+>.html'=>'site/front/content', //TODO: requirements, contact	
//		'<action:(photo|home)>' => 'giralis/site/<action>',
//		'<action:(ajaxwidget|search|thumb)>' => 'site/front/<action>',
//		'<slug:[\w\/.-]+>'=>'site/front/content', //TODO: requirements, contact	
		'<module:\w+>/<controller:\w+>/<action:\w+>'=>'<module>/<controller>/<action>'
);