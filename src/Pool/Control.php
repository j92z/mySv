<?php

namespace Ss\Pool;

use Ss\Common\Func;
use Ss\Core\Config;

class Control
{
    public static $pool;

    public static function registerPool(string $class_name, string $key, array $config)
    {
        $class = new $class_name($config);
        if ($class instanceof Pool) {
            self::$pool[Func::genKey($class_name)][Func::genKey($key)] = $class;
        }
    }

    public static function getPool(string $pool_name, string $key)
    {
        $pool_hash = Func::genKey($pool_name);
        $key_hash = Func::genKey($key);
        if (!isset(self::$pool[$pool_hash][$key_hash])) {
            $config = Config::get($key);
            self::registerPool($pool_name, $key, $config);
        }

        return self::$pool[$pool_hash][$key_hash];
    }
}
