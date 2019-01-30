<?php

namespace Ss\Core;

use Ss\Http\Request;
use Ss\Http\Response;

class Route
{
    private $namespace;
    private $controller;

    public function __construct($namespace, $controller)
    {
        $this->namespace = $namespace ?: 'App';
        $this->controller = $controller ?: 'Controller';
    }

    public function dispatch(Request $request, Response $response)
    {
        if ($request->request()->server['path_info'] == '/favicon.ico') {
            return;
        }
        $path_map = explode('/', trim($request->request()->server['path_info'], '/'));
        $c = isset($path_map[0]) && !empty($path_map[0]) && $path_map[0] != '/' ? $path_map[0] : 'index';
        $a = isset($path_map[1]) && !empty($path_map[1]) ? $path_map[1] : 'index';
        $class = $this->namespace.'\\'.$this->controller.'\\'.ucfirst($c);

        try {
            $controller = new $class($request, $response);

            $controller->$a();
        } catch (\Exception $e) {
            throw new \Exception('route fectch error');
        }
    }
}
