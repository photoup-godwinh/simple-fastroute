<?php
namespace SimplePHP\SimpleFastRoute\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\Request;

interface MiddlewareInterface {
    
    public function handle(Request $request, Closure $next);
    
}