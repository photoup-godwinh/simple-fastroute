<?php
namespace SimplePHP\SimpleFastRoute\Route;

class Route {

	private $method; 

	private $pattern; 

	private $handler;

	private $group;

	private $middlewares;

	private $handler_class;

	private $handler_class_method;

	public function __construct($method, $pattern, $handler) {
		$this->method = $method; 

		$this->pattern = $pattern; 

		$this->setHandler($handler);

		$this->middlewares = [];
	}	

	public function attachMiddlewares($middlewares = array()) {
		foreach($middlewares as $middleware) {
			$this->middlewares[] = $middleware;
		}
	}

	public function getMethod() {
		return $this->method;
	}

	public function getPattern() {
		return $this->pattern;
	}

	public function getHandler() {
		return $this->handler;
	}

	public function getMiddlewares() {
		return $this->middlewares;
	}

	public function getGroup() {
		return $this->group;
	}

	public function getHandlerClass() {
		return $this->handler_class;
	}

	public function getHandlerClassMethod() {
		return $this->handler_class_method;
	}

	public function hasMiddlewares() {
		return count($this->middlewares) > 0;
	}

	public function setGroup($group) {
		$this->group = $group;

		return $this;
	}

	public function setMethod($method) {
		$this->method = $method;

		return $this;
	}

	public function setPattern($pattern) {
		$this->pattern = $pattern;

		return $this;
	}

	public function setHandler($handler) {
		$this->handler = $handler;

		if($this->handler) {
			$handler_parts = explode("@", $this->handler);

			if(!isset($handler_parts[1])) {
				throw new \InvalidArgumentException('Invalid router handler method');
			}

			$this->handler_class = $handler_parts[0];
			$this->handler_class_method =  $handler_parts[1];
		}

		return $this;
	}
}