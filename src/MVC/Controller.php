<?php

namespace Ss\MVC;

use Ss\Core\Config;
use Ss\Http\Request;
use Ss\Http\Response;
use Ss\Http\Session;
use Ss\Common\Func;

// use duncan3dc\Laravel\BladeInstance;

// use Ss\Db\Mysql;
// use Ss\Db\Redis;
// use Ss\Db\Mssql;

abstract class Controller
{
    private $request;
    private $response;
    private $view;
    private $mysql;
    private $mssql;
    private $redis;
    private $model;
    private $cache;
    private $session;

    public function __construct()
    {
        $this->view = new \Smarty();
        $tempPath = ROOT_PATH.DS.'temp';    // 临时文件目录
        $this->view->setCompileDir("{$tempPath}/templates_c/");      // 模板编译目录
        $this->view->setCacheDir("{$tempPath}/cache/");              // 模板缓存目录
        $this->view->setTemplateDir(ROOT_PATH.DS.Config::get('namespace', 'App').DS.'/Views/');    // 模板文件目录
        $this->view->setCaching(false);
        // $this->view = new BladeInstance(ROOT_PATH.DS.Config::get('namespace', 'App').DS.'/Views/', "{$tempPath}/templates_c");
        $this->model = new Model();
        $this->cache = new Cache();
    }

    // public function render(string $view, array $params = [])
    // {
    //     $content = $this->view->render($view, $params);
    //     var_dump($content);
    //     $this->write($content);
    // }

    public function touch(Request $request, Response $response, $action)
    {
        $this->request = $request;
        $this->response = $response;
        $this->$action();
    }

    public function request()
    {
        return $this->request;
    }

    public function input(string $key)
    {
        if (\substr($key, 0, 4) == 'json') {
            $json_param = Func::json($this->request->raw(), true);
            $key = substr($key, 5, -1);
            if (!is_string($key) && !$key) {
                return $json_param;
            }

            return Func::getInfoByKey($json_param, $key);
        } else {
            return $this->request->getRequestInfo($key);
        }
    }

    public function response()
    {
        return $this->response;
    }

    public function fetch($template = null)
    {
        $content = $this->view->fetch($template);
        $this->response->write($content);
        $this->view->clearAllAssign();
        $this->view->clearAllCache();
    }

    public function assign($tpl_var, $value = null, $nocache = false)
    {
        $this->view->assign($tpl_var, $value, $nocache);
    }

    public function json($content = [])
    {
        $this->response->header('Content-Type', 'application/json');
        $this->response->write(json_encode($content));
    }

    public function write($content = '')
    {
        $this->response->header('Content-Type', 'text/html');
        $this->response->write($content);
    }

    public function mysql($db_config_name = '')
    {
        $db_config_name = $db_config_name ?: 'mysql';
        if (!isset($this->mysql[$db_config_name])) {
            $config = Config::get($db_config_name);
            if (is_null($config) || !is_array($config)) {
                throw new InvalidArgumentException('can\'t read mysql config');
            }
            $this->mysql[$db_config_name] = $this->model->useMysql($db_config_name)->getObj($config['connect_time_out'] ?? 0.5);
        }

        return $this->mysql[$db_config_name];
    }

    private function recycleDbSource()
    {
        if (!empty($this->mysql)) {
            foreach ($this->mysql as $mysql_key => $mysql_obj) {
                $this->model->useMysql($mysql_key)->recycleObj($mysql_obj);
                unset($this->mysql[$mysql_key]);
            }
        }
        if (!empty($this->mssql)) {
            foreach ($this->mssql as $mssql_key => $mssql_obj) {
                $this->model->useMssql($mssql_key)->recycleObj($mssql_obj);
                unset($this->mysql[$mssql_key]);
            }
        }
        if (!empty($this->redis)) {
            foreach ($this->redis as $redis_key => $redis_obj) {
                $this->cache->useMssql($redis_key)->recycleObj($redis_obj);
                unset($this->redis[$redis_key]);
            }
        }
    }

    public function __destruct()
    {
        // $this->recycleDbSource();
        Model::recyceDbResource();
        Cache::recycleCacheSource();
        if ($this->session instanceof Session) {
            $this->session->writeClose();
        }
    }

    public function mssql($db_config_name = '')
    {
        $db_config_name = $db_config_name ?: 'mssql';
        if (!isset($this->mssql[$db_config_name])) {
            $config = Config::get($db_config_name);
            if (is_null($config) || !is_array($config)) {
                throw new InvalidArgumentException('can\'t read mssql config');
            }
            $this->mssql[$db_config_name] = $this->model->useMssql($db_config_name)->getObj($config['connect_time_out'] ?? 0.5);
        }

        return $this->mssql[$db_config_name];
    }

    public function redis($db_config_name = '')
    {
        $db_config_name = $db_config_name ?: 'redis';
        if (!isset($this->redis[$db_config_name])) {
            $config = Config::get($db_config_name);
            if (is_null($config) || !is_array($config)) {
                throw new InvalidArgumentException('can\'t read redis config');
            }
            $this->redis[$db_config_name] = $this->cache->useRedis($db_config_name)->getObj($config['connect_time_out'] ?? 0.5);
        }

        return $this->redis[$db_config_name];
    }

    public function cookie($key, $value = '', int $expire = 0, string $path = '/', string $domain = '', bool $secure = false, bool $httponly = false)
    {
        if (is_null($key)) {
            $all_cookie = $this->request()->cookie();
            $time = time() - 3600;
            if (!empty($all_cookie)) {
                foreach ($all_cookie as $name => $val) {
                    $this->response->cookie($name, $val, $time);
                }
            }
            $this->cookie = null;

            return true;
        } elseif ($key == '') {
            $all_cookie = $this->request()->cookie();
            if (!empty($this->cookie)) {
                foreach ($this->cookie as $cookie) {
                    $all_cookie[$cookie->key] = $cookie->value;
                }
            }

            return $all_cookie;
        } else {
            if (is_null($value)) {
                $this->response->cookie($key, $value, time() - 3600);
                unset($this->cookie[$key]);

                return true;
            } elseif ($value == '') {
                $all_cookie = $this->request()->cookie();

                return isset($this->cookie[$key]) ? $this->cookie[$key]->value : $all_cookie[$key] ?? null;
            } else {
                $this->response->cookie($key, $value, $expire, $path, $domain, $secure, $httponly);

                return true;
            }
        }
    }

    public function session($key = '', $value = '')
    {
        if ($this->session == null) {
            $this->session = new Session($this->request, $this->response);
            $this->session->start();
        }
        if (is_null($key)) {
            return $this->session->destroy();
        }
        if ($value != '') {
            return $this->session->set($key, $value);
        } else {
            return $this->session->get($key);
        }
    }
}
