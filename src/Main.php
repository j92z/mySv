<?php

namespace Ss;

use Ss\Core\Config;
use Ss\Core\Route;
use Ss\Core\Log;
use Ss\Http\Request;
use Ss\Http\Response;

class Main
{
    final public static function run()
    {
        define('DS', DIRECTORY_SEPARATOR);

        Config::load(ROOT_PATH.DS.'config.php');
        Log::init(ROOT_PATH.DS.'log');

        \date_default_timezone_set(Config::get('time_zone', 'Asia/Shanghai'));

        $http = new \Swoole\Http\Server(Config::get('server.host', '0.0.0.0'), Config::get('server.port', 9501));
        $http->set(Config::get('server.setting'));
        $http->on('request', function (\swoole_http_request $request, \swoole_http_response $response) {
            try {
                $resquest_obj = new Request($request);
                $response_obj = new Response($response);
                $route = new Route(Config::get('namespace', 'App'), Config::get('controller', 'Controller'));
                $route->dispatch($resquest_obj, $response_obj);
                $response_obj->__res();
            } catch (\Exception $error) {
                Log::alert($error->getMessage(), $error->getTrace());
                $response->end($error->getMessage());
            } catch (\Error $error) {
                Log::emergency($error->getMessage(), $error->getTrace());
                $response->status(500);
            } catch (\Throwable $error) {
                Log::emergency($error->getMessage(), $error->getTrace());
                $response->status(500);
            } finally {
            }
        });
        $http->start();
    }
}
