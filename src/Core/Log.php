<?php

namespace Ss\Core;

class Log
{

    public static function init($path)
    {
        \SeasLog::setBasePath($path);
    }

    public static function __callStatic($name, $arguments)
    {
        forward_static_call_array(['SeasLog', $name], $arguments);
    }
}
