<?php
namespace go\core\http;

use go\core\exception\NotFound;

/**
 * Simple RESTful router
 * 
 * @example
 * ```
 * $router = (new Router())
 *   ->addRoute('/edit\/([0-9]+)\/([0-9]+)/', 'GET', Wopi::class, 'edit')
 *   ->addRoute('/files\/([0-9]+)/', "LOCK", Wopi::class, 'lock')
 *   ->addRoute('/files\/([0-9]+)/', "GET_LOCK", Wopi::class, 'getLock')
 *   ->addRoute('/files\/([0-9]+)/', "REFRESH_LOCK", Wopi::class, 'refreshLock')
 *   ->addRoute('/files\/([0-9]+)/', "UNLOCK", Wopi::class, 'unlock')
 *   ->addRoute('/files\/([0-9]+)/', "PUT_RELATIVE", Wopi::class, 'putRelative')
 *   ->addRoute('/files\/([0-9]+)/', "RENAME_FILE", Wopi::class, 'renameFile')
 *   ->addRoute('/files\/([0-9]+)/', "DELETE", Wopi::class, 'delete')
 *   ->addRoute('/files\/([0-9]+)/', "PUT_USER_INFO", Wopi::class, 'putUserInfo')
 * 
 *   ->addRoute('/files\/([0-9]+)\/contents/', "GET", Wopi::class, 'GetFile')
 *   ->addRoute('/files\/([0-9]+)\/contents/', "POST", Wopi::class, 'PutFile')
 *   ->addRoute('/files\/([0-9]+)\/contents/', "PUT", Wopi::class, 'PutFile')
 * 
 *   ->run();
 *   ```
 */
class Router {

  private $routes = [];

  /**
   * Add a route
   * 
   * @param string $regex The regular expression to match with the router path. The path is relative to the script the router is created in.
   * @param string $httpMethod eg. GET, POST, PUT etc.
   * @param string $controller Controller class name
   * @param string $methodController method
   * 
   * @return $this 
   */
  public function addRoute($regex, $httpMethod, $controller, $method) {
    if(!isset($this->routes[$regex])) {
      $this->routes[$regex] = [];
    }
    $this->routes[$regex][$httpMethod] = ['controller' => $controller, 'method' => $method];

    return $this;
  }

  /**
   * Run the controller method that matches with the route.
   * 
   * @return mixed the controller method return value
   */
  public function run() {
    
    $route = $this->findRoute();
    if(!$route) {
      throw new NotFound();
    }

    $c = new $route['controller'];

    return call_user_func_array([$c, $route['method']], $route['params']);		
  }

  private function findRoute() {
    $path = isset($_SERVER['PATH_INFO']) ? ltrim($_SERVER['PATH_INFO'], '/') : null;
    
    $method = Request::get()->getHeader('X-WOPI-Override');
    if(!$method) {
      $method = Request::get()->getMethod();
    }

    foreach ($this->routes as $regex => $methods) {
			if(isset($methods[$method]) && preg_match($regex, $path, $params)) {
        array_shift($params); //shift full match
        return array_merge($methods[$method], ['params' => $params]);
      }
    }

    return false;
  }

}