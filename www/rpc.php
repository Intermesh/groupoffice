<?php
// Was used for testing only
// 
//require('GO.php');
//
//\GO::session()->login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PASS']);
//
//function rpcRouter($method, $param_arr, $data=null){
//	\GO::debug($method.'('.var_export($param_arr, true).')');
//	
//	$parts = explode('_',$method);
//	
//	$class = "GO_".ucfirst(array_shift($parts))."_Rpc_".ucfirst( array_shift($parts));
//	$method = implode('_',$parts);
//	
////	if(!class_exists($class) || !method_exists($class, $method)){
////		return 'notfound';
////	}
//	
//	return call_user_func_array(array($class, $method), $param_arr);
//}
//
//$xmlrpcServer = xmlrpc_server_create();
//
//$classes = \GO::modules()->findClasses('rpc');
//
//foreach($classes as $class){
//	
//	$methods = $class->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_STATIC);
//	
//	foreach($methods as $method){
//		
//		$parts = explode('_',$class->getName());
//		$methodName = strtolower($parts[1]).'_'. strtolower($parts[3]).'_'.$method->getName();
//		xmlrpc_server_register_method($xmlrpcServer, $methodName, "rpcRouter");
//	}
//	
//}
//
//$response = xmlrpc_server_call_method($xmlrpcServer, file_get_contents('php://input'), null); 
//
//header('Content-Type: text/xml;charset=utf-8');
//
//print $response;
//
//xmlrpc_server_destroy($xmlrpcServer);