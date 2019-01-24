<?php

namespace Ss\MVC;

use Ss\Common\Func;
use Ss\Pool\Control;
use Ss\Pool\Instance\Mysql;
use Ss\Pool\Instance\Mssql;
use Ss\Core\Config;

class Model
{
    public static $mysql_map;
    public static $mysql_obj;
    public static $mssql_map;
    public static $mssql_obj;

    public static function useMysql(string $key = 'mysql')
    {
        $hash = Func::genKey($key);
        if (!isset(self::$mysql_map[$hash])) {
            self::$mysql_map[$hash] = Control::getPool(Mysql::class, $key);
        }
        $config = Config::get($key);
        if (!isset(self::$mysql_obj[$hash])) {
            if (is_null($config) || !is_array($config)) {
                throw new InvalidArgumentException('can\'t read mssql config');
            }
            self::$mysql_obj[$hash] = self::$mysql_map[$hash]->getObj($config['connect_time_out'] ?? 0.5);
        }

        return self::$mysql_obj[$hash];
    }

    public static function useMssql(string $key = 'mssql')
    {
        $hash = Func::genKey($key);
        if (!isset(self::$mssql_map[$hash])) {
            self::$mssql_map[$hash] = Control::getPool(Mssql::class, $key);
        }
        $config = Config::get($key);
        if (!isset(self::$mssql_obj[$hash])) {
            if (is_null($config) || !is_array($config)) {
                throw new InvalidArgumentException('can\'t read mssql config');
            }
            self::$mssql_obj[$hash] = Control::getPool(Mssql::class, $key);
        }

        return self::$mssql_obj[$hash];
    }

    public static function recyceDbResource()
    {
        if (!empty(self::$mysql_obj)) {
            foreach (self::$mysql_obj as $mysql_key => $mysql_obj) {
                self::$mysql_map[$mysql_key]->recycleObj($mysql_obj);
                unset(self::$mysql_obj[$mysql_key]);
            }
        }
        if (!empty(self::$mssql_obj)) {
            foreach (self::$mssql_obj as $mssql_key => $mssql_obj) {
                self::$mssql_map[$mssql_key]->recycleObj($mssql_obj);
                unset(self::$mssql_obj[$mssql_key]);
            }
        }
    }
}
