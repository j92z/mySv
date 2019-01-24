<?php

namespace Ss\Core;

class Config
{
    public static $config_map;

    public static function load($path)
    {
        self::$config_map = require $path;
    }

    public static function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $data = self::$config_map;
        while ($k = array_shift($keys)) {
            if (isset($data[$k])) {
                $data = $data[$k];
            } else {
                return $default;
            }
        }

        return $data;
    }

    public static function set(string $key, $value)
    {
        $keys = explode('.', $key);
        $data = self::$config_map;
        while ($k = array_shift($keys)) {
            $data = &$data[$k];
        }
        $data = $value;
    }
}
