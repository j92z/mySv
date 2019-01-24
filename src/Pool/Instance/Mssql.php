<?php

namespace Ss\Pool\Instance;

use Ss\Pool\Pool;
use Ss\Db\Mssql as DB;

class Mssql extends Pool
{
    public function create()
    {
        return new DB($this->db_config);
    }
}
