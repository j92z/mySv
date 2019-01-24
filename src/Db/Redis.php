<?php

namespace Ss\Db;

class Redis
{
    private $db;

    public function __construct($config = null)
    {
        $host = $config['host'] ?: '127.0.0.1';
        $port = $config['port'] ?: 6379;
        $this->db = new \redis();
        $this->db->connect($host, $port);
        if ($config['auth']) {
            $this->db->auth($config['auth']);
        }
        $this->db->select(0);
    }

    public function __call($name, $arguments)
    {
        return \call_user_func_array([$this->db, $name], $arguments);
    }
}
