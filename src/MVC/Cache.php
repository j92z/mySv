<?php

namespace Ss\MVC;

use Ss\Pool\Instance\Redis;
use Ss\Pool\Control;
use Ss\Common\Func;
use Ss\Core\Config;

class Cache
{
    public static $redis_map;
    public static $redis_obj;

    // public static function useRedis(string $key = 'redis')
    // {
    //     $hash = Func::genKey($key);
    //     if (!isset(self::$redis_map[$hash])) {
    //         self::$redis_map[$hash] = Control::getPool(Redis::class, $key);
    //     }

    //     return self::$redis_map[$hash];
    // }

    public static function useRedis(string $key = 'redis')
    {
        $hash = Func::genKey($key);
        if (!isset(self::$redis_map[$hash])) {
            self::$redis_map[$hash] = Control::getPool(Redis::class, $key);
        }
        $config = Config::get($key);
        if (!isset(self::$redis_obj[$hash])) {
            if (is_null($config) || !is_array($config)) {
                throw new InvalidArgumentException('can\'t read redis config');
            }
            self::$redis_obj[$hash] = self::$redis_map[$hash]->getObj($config['connect_time_out'] ?? 0.5);
        }

        return self::$redis_obj[$hash];
    }

    public static function recycleCacheSource()
    {
        if (!empty(self::$redis_obj)) {
            foreach (self::$redis_obj as $redis_key => $redis_obj) {
                self::$redis_map[$redis_key]->recycleObj($redis_obj);
                unset(self::$redis_obj[$redis_key]);
            }
        }
    }
}
