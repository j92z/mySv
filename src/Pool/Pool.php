<?php

namespace Ss\Pool;

use Swoole\Coroutine\Channel;

abstract class Pool
{
    private $chan;
    private $pool_create_num = 0;
    private $pool_max_create_num = 20;
    private $pool_check_time = 60000;
    private $pool_expire_time = 200;
    private $default_time_out = 0.5;
    private $hash_map;
    protected $db_config;

    public function __construct($config)
    {
        $this->db_config = $config;
        if (intval($config['pool_expire_time'] ?? 0) > 0) {
            $this->pool_expire_time = intval($config['pool_expire_time']);
        }
        if (intval($config['pool_check_time'] ?? 0) > 0) {
            $this->pool_check_time = intval($config['pool_check_time']);
        }
        if (intval($config['pool_max_num'] ?? 0) > 0) {
            $this->pool_max_create_num = intval($config['pool_max_num']);
        }
        $this->chan = new Channel($this->pool_max_create_num + 1);
        swoole_timer_tick($this->pool_check_time, [$this, 'checkObjExpire']);
    }

    abstract public function create();

    public function getObj(float $time_out, int $try_times = 3)
    {
        if (!$time_out) {
            $time_out = $this->default_time_out;
        }
        if ($try_times <= 0) {
            return null;
        }
        $obj = null;
        if ($this->chan->isEmpty()) {
            if ($this->pool_create_num < $this->pool_max_create_num) {
                ++$this->pool_create_num;
                $obj = $this->create();
                if (!is_object($obj)) {
                    --$this->pool_create_num;
                    $obj = $this->chan->pop($time_out);
                }
            } else {
                $obj = $this->chan->pop($time_out);
            }
        } else {
            $obj = $this->chan->pop($time_out);
        }
        if (is_object($obj)) {
            $obj->use_time = time();
            $hash = spl_object_hash($obj);
            $this->hash_map[$hash] = 1;

            return $obj;
        }

        return null;
    }

    public function unsetObj($obj)
    {
        if (is_object($obj)) {
            $hash = spl_object_hash($obj);
            if (isset($this->hash_map[$hash])) {
                unset($this->hash_map[$hash]);
            }
            unset($obj);
            --$this->pool_create_num;

            return !isset($this->hash_map[$hash]);
        }

        return false;
    }

    public function recycleObj($obj)
    {
        if (is_object($obj)) {
            $hash = spl_object_hash($obj);
            if (isset($this->hash_map[$hash])) {
                unset($this->hash_map[$hash]);
                $obj->use_time = time();
                $this->chan->push($obj);
            }
        }
    }

    public function gcObj(int $expire_time, float $time_out = 0.001)
    {
        $list = [];
        $time = time();
        while (true) {
            if ($this->chan->isEmpty()) {
                break;
            }
            $obj = $this->chan->pop($time_out);
            if (is_object($obj)) {
                if ($time - $obj->use_time <= $expire_time) {
                    $this->unsetObj($obj);
                } else {
                    array_push($list, $obj);
                }
            }
        }
        foreach ($list as $val) {
            $this->chan->push($val);
        }
    }

    private function checkObjExpire()
    {
        $this->gcObj($this->pool_expire_time);
    }

    public function setMaxCapacity(int $num)
    {
        $this->pool_max_create_num = $num;
    }
}
