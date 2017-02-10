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
		$pattern = '';

		if(is_array($parsed)) {
			$parts = $parsed[0];

			for($i = 0; $i < count($parts); $i++) {
				if(is_array($parts[$i])) {
					$pattern .= $parts[$i][1];
				} else {
					$pattern .= $parts[$i];
				}
			}

			$pattern = '~^(' . $pattern . ')$~';
		}

		return $pattern;
	}

	public function size() {
		return count($this->routes);
	}
}