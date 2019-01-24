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
}
