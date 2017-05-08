<?php
namespace SimplePHP\SimpleFastRoute\Route;

use DI\Container;
use Symfony\Component\HttpFoundation\Request;
use SimplePHP\SimpleFastRoute\Exception\RouteNotAllowedException; 
use SimplePHP\SimpleFastRoute\Exception\RouteNotFoundException; 
use SimplePHP\SimpleFastRoute\Middleware\Layer;

class Router {

	private $route_collection; 

	private $dispatcher; 

	private $group_name; 

	private $middlewares;

	private $middleware_ns;

	private $request;

	public function __construct(Request $request) {
		$this->request = $request;

		$this->route_collection = new Collection;

		$this->middleware_ns = null;
	}		

	public function __call($name, $args) {
		$method 	= strtoupper($name); 
		$pattern 	= empty($this->group_name) ? $args[0] : ('/' . $this->group_name . $args[0]);
		$pattern 	= rtrim($pattern, '/');
		$handler 	= $args[1];
		$route 		= new Route($method, $pattern, $handler);

		if(!empty($this->group_middlewares)) {
			$route->attachMiddlewares($this->getMiddlewares($this->group_middlewares));
		}

		if(!empty($args[2]) && is_array($args[2])) {
			$route->attachMiddlewares($this->getMiddlewares($args[2]));
		}

		if(!empty($this->group_name)) {
			$route->setGroup($this->group_name);
		}

		$this->route_collection->add($route);
	}

	public function group($group_name, $closure, $middlewares = array()) {
		$this->group_name = $group_name; 
		$this->group_middlewares = $middlewares;

		$closure($this);

		$this->group_name = null; 
		$this->group_middlewares = [];
	}

	public function run(Container $container) {
		$this->dispatcher = \FastRoute\simpleDispatcher(function(\FastRoute\RouteCollector $r) {
			for($i = 0; $i < $this->route_collection->size(); $i++) {
				$route = $this->route_collection->get($i);
				$r->addRoute($route->getMethod(), $route->getPattern(), [$route->getHandlerClass(), $route->getHandlerClassMethod()]);
			}
		});

		return $this->dispatch($container);
	}

	public function setMiddlewareNs($middleware_ns) {
		$this->middleware_ns = $middleware_ns;
	}

	private function dispatch(Container $container) {
		$route_info = $this->dispatcher->dispatch($this->request->getMethod(), $this->request->getPathInfo());

		switch ($route_info[0]) {
            case \FastRoute\Dispatcher::NOT_FOUND:
            	throw new RouteNotFoundException('Route not found');
            break; 
            
            case \FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
                throw new RouteNotAllowedException('Route not allowed');
            break;
        
            case \FastRoute\Dispatcher::FOUND:
                if(!empty($route_info[2])) {
                    foreach($route_info[2] as $key => $value) {
                        $this->request->attributes->set($key, $value); 
                    }
                }
                
                $route = $this->route_collection->match($this->request->getMethod(), $this->request->getPathInfo());

                $controller = $route_info[1];
                $param = $route_info[2];

                if(!$route) {
                	throw new \Exception('No Route matches in collection');
                }

                if($route->hasMiddlewares()) {
                	$layer = new Layer($container, $route->getMiddlewares());

                	return $layer->handle($this->request, function(Request $request) use ($container, $controller, $param) {
                        return $container->call($controller, $param);
                    });
                } else {
                	# removed support for closure as of 2017-02-09
					
					return $container->call($controller, $param);
                }
            break;
        }
	}

	private function getMiddlewares($middlewares) {
		$new_middlewares = [];

		foreach($middlewares as $middleware) {
			$class = $this->middleware_ns ? $this->middleware_ns . '\\' : '';

			$class .= ucfirst($middleware);

			if(class_exists($class)) {
				// $new_middlewares[] = new $class;
				$new_middlewares[] = $class;
			}
		}

		return $new_middlewares;
	}
}
