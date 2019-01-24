<?php

namespace Ss\Means;

class FileStream extends Stream
{
    public function __construct($file, $mode = 'c+')
    {
        $fp = fopen($file, $mode);
        parent::__construct($fp);
    }

    public function lock($mode = LOCK_EX)
    {
        return flock($this->getStream(), $mode);
    }

    public function unlock($mode = LOCK_UN)
    {
        return flock($this->getStream(), $mode);
    }
}
