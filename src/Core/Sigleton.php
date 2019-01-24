<?php

namespace Ss\Core;

trait Sigleton
{
    public static $instance;

    public static function getInstance(...$args)
    {
        if (!isset(self::$instance)) {
            self::$instance = new static(...$args);
        }

        return self::$instance;
    }
}
