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
        //配置跨域选项
        if ($request->request()->server['request_method'] == 'OPTIONS') {
            $area = $request->getRequestInfo('header.origin') ?: '*';
            $response->header('Access-Control-Allow-Origin', $area);
            $response->header('Access-Control-Allow-Credentials', 'true');
            $response->header('Access-Control-Allow-Headers', 'X-Requested-With');

            return;
        }
        $path_map = explode('/', trim($request->request()->server['path_info'], '/'));
        $path_length = count($path_map);
        if ($path_length > 2) {
            $this->namespace = '';
            for ($i = 0; $i < $path_length - 2; ++$i) {
                if (empty($this->namespace)) {
                    $this->namespace .= ucfirst($path_map[$i]);
                } else {
                    $this->namespace .= '\\'.$path_map[$i];
                }
            }
        }
        $c = isset($path_map[$path_length - 2]) && !empty($path_map[$path_length - 2]) && $path_map[$path_length - 2] != '/' ? $path_map[$path_length - 2] : 'index';
        $a = isset($path_map[$path_length - 1]) && !empty($path_map[$path_length - 1]) ? $path_map[$path_length - 1] : 'index';
        $class = $this->namespace.'\\'.$this->controller.'\\'.ucfirst($c);

        $controller = new $class($request, $response);
        if ($controller instanceof Controller) {
            if (method_exists($controller, $a)) {
                $controller->$a();
            } else {
                throw new \Exception('Method Not Found!');
            }
        } else {
            throw new \Exception('Controller Not Found!');
        }
    }
}
