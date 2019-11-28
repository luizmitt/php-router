<?php

namespace Lz\PHP;

use \Lz\PHP\Request;
use \Lz\PHP\Response;

class Router
{
    protected $routes = [];

    public function __construct()
    {

    }

    public function __call($method, $arguments)
    {
        if (in_array(strtoupper($method), ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'])) {
            $method   = strtoupper($method);
            $route    = !empty($arguments[0]) ? $arguments[0] : false;
            $callback = !empty($arguments[1]) ? $arguments[1] : false;

            return $this->map($method, $route, $callback);
        }
    }

    public function map($method, $route, $callback)
    {
        $index = $route . '@' . $method;

        if (isset($this->routes[$index])) {
            throw new Exception("Conflito de rota: $route em $method já existe!");
        }

        $this->routes[$index] = $callback;
    }

    public function dispatch()
    {
        if (preg_match('/(.+)\.php(.+)?/', $_SERVER['PHP_SELF'], $matched)) {
            $root       = $matched[1];

            if (empty($matched[2])) {
                $requestUri = '/';
            } else {
                $requestUri = $matched[2];
            }

            foreach ($this->routes as $request => $callback) {
                $parts  = explode('@', $request);
                $route  = $parts[0];
                $method = $parts[1];

                if ($_SERVER['REQUEST_METHOD'] === $method) {
                    if ($requestUri === $route) {
                        return call_user_func_array($callback, [new Request(), new Response()]);
                    }
                }


            }
        }
    }
}