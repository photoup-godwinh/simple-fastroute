<?php
namespace SimplePHP\SimpleFastRoute\Middleware;

use Closure;
use DI\Container;
use Symfony\Component\HttpFoundation\Request;

class Layer {
    private $layers;

    public function __construct(Container $container, array $layers = array()) {
        $this->layers = [];

        foreach($layers as $layer) {
            $this->layers[] = $container->get($layer);
        }
    }
    
    /*
     * @deprecated 
    */
    public function layer($layers) {
        if ($layers instanceof Layer) {
            $layers = $layers->toArray();
        }
        
        if ($layers instanceof MiddlewareInterface) {
            $layers = [$layers];
        }
        
        if (!is_array($layers)) {
            throw new \InvalidArgumentException(get_class($layers) . " is not a valid middleware layer.");
        }
        
        return new static(array_merge($this->layers, $layers));
    } */

    public function handle(Request $request, Closure $core) {
        $core_func = $this->createCoreFunc($core);

        $layers = array_reverse($this->layers);

        $complete = array_reduce($layers, function($next_layer, $layer) {
            return $this->createLayer($next_layer, $layer);
        }, $core_func);

        return $complete($request);
    }
    
    public function toArray() {
        return $this->layers;
    }

    private function createCoreFunc(Closure $core) {
        return function(Request $request) use ($core) {
            return call_user_func($core, $request);
        };
    }

    private function createLayer($next_layer, $layer) {
        return function(Request $request) use ($next_layer, $layer) {
            return call_user_func_array(
                    array($layer, 'handle'),
                    array($request, $next_layer));
        };
    }
}