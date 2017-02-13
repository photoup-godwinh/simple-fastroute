<?php
namespace SimplePHP\SimpleFastRoute\Route;

use Symfony\Component\HttpFoundation\Request;

class Collection {
	
	private $routes; 

	public function __construct() {
		$this->routes = [];
	}

	public function add(Route $route) {
		$this->routes[] = $route;
	}

	public function get($index) {
		return $this->routes[$index];
	}

	/* 
	 * Matches a Route by its method and path 
	 */
	public function match($method, $path) {
		$std = new \FastRoute\RouteParser\Std;

		foreach($this->routes as $route) {
			$pattern = $this->buildMatchablePattern($std->parse($route->getPattern()));

			if(preg_match($pattern, $path) && 
				strcasecmp($route->getMethod(), $method) === 0) {
				return $route;
			}
		}

		return false;
	}

	private function buildMatchablePattern($parsed) {
		$patterns = [];

		if(is_array($parsed)) {
			foreach($parsed as $p) {
				$pattern = '';

				foreach($p as $r) {
					if(is_array($r)) {
						$pattern .= $r[1];
					} else {
						$pattern .= $r;
					}
				}

				$patterns[] = '(' . $pattern . ')';
			}
		}

		if($patterns) {
			$new_pattern = '~^';

			if(count($patterns) > 1) {
				$new_pattern .= '(' . implode('|', $patterns) . ')';
			} else {
				$new_pattern .= implode('|', $patterns);
			}

			$new_pattern .= '$~';
			// $patterns = '~^' . implode('|', $patterns) . '$~';
		} 

		return $patterns;
	}

	public function size() {
		return count($this->routes);
	}
}