<?php

namespace Ss\MVC\ModelCase;

use Ss\MVC\Model;

class AbstractModel
{
    protected $db_config_name;
    protected $table_name;
    const DB_MYSQL = 'mysql';
    const DB_MSSQL = 'mssql';
    protected $db_type = self::DB_MYSQL;
    protected $model;
    private $allow_auto_table = ['select', 'insert', 'update', 'delete', 'replace', 'get', 'has', 'rand', 'aggregate', 'count', 'avg', 'max', 'min', 'sum'];

    public function __construct()
    {
        if (!$this->table_name) {
            $this->table_name = $this->getTableName();
        }
        if ($this->db_type != self::DB_MYSQL) {
            $this->db_type = self::DB_MSSQL;
        }
    }

    public function model($return = false)
    {
        if ($this->db_type == self::DB_MYSQL) {
            $this->model = Model::useMysql($this->db_config_name);
        } else {
            $this->model = Model::useMssql($this->db_config_name);
        }
        if ($return) {
            return $this->model;
        }
    }

    public function __call($name, $arguments)
    {
        if (empty($this->model)) {
            $this->model();
        }
        if (in_array($name, $this->allow_auto_table)) {
            array_unshift($arguments, $this->table_name);
        }

        return \call_user_func_array([$this->model, $name], $arguments);
    }

    private function getTableName()
    {
        $table_name = '';
        $class_arr = explode('\\', get_class($this));
        $str_arr = str_split(lcfirst(array_pop($class_arr)));
        foreach ($str_arr as $str) {
            if (ord($str) >= 65 && ord($str) <= 90) {
                $table_name .= '_'.strtolower($str);
            } else {
                $table_name .= $str;
            }
        }

        return $table_name;
    }
}
