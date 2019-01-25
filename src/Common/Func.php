<?php

namespace Ss\Common;

class Func
{
    public static function genKey(string $str): string
    {
        return substr(md5($str), 8, 16);
    }

    public static function genKeyByArr(array $arr): string
    {
        return substr(md5(implode('', $arr)), 8, 16);
    }

    public static function getInfoByKey($object, string $key, string $delimeter = '.')
    {
        $key_arr = explode($delimeter, $key);
        foreach ($key_arr as $item) {
            if (is_object($object)) {
                if (isset($object->$item)) {
                    $object = $object->$item;
                } else {
                    return null;
                }
            } elseif (is_array($object)) {
                if (isset($object[$item])) {
                    $object = $object[$item];
                } else {
                    return null;
                }
            } else {
                return null;
            }
        }

        return $object;
    }

    public static function isJosn(string $string)
    {
        json_decode($string);

        return json_last_error() == JSON_ERROR_NONE;
    }

    public static function json(string $string, bool $bool = false)
    {
        $object = json_decode($string, $bool);
        if (json_last_error() == JSON_ERROR_NONE) {
            return $object;
        } else {
            return false;
        }
    }
}
