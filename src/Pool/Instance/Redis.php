<?php

namespace Ss\Pool\Instance;

use Ss\Pool\Pool;
use Ss\Db\Redis as DB;

class Redis extends Pool
{
    public function create()
    {
        return new DB($this->db_config);
    }
}
