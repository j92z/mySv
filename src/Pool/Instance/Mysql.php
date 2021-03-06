<?php

namespace Ss\Pool\Instance;

use Ss\Pool\Pool;
use Ss\Db\Mysql as DB;

class Mysql extends Pool
{
    public function create()
    {
        return new DB($this->db_config);
    }
}
