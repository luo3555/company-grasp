<?php
namespace Lib;

class Sqlite extends Objectdata
{
    protected static $_sqlite = null;

    protected static $_cache = [];

    public static function sqLite()
    {
        if (is_null(self::$_sqlite)) {
            self::$_sqlite = new \PDO(sprintf('sqlite:%s', ROOT_DIR . '/database.sqlite'));
            self::$_sqlite->exec('set names utf8'); 
        }
        return self::$_sqlite;
    }

    public static function getConfig($path)
    {
        $sth = self::sqLite()->prepare('SELECT `value` FROM `config_data` WHERE `path`=:path');
        $sth->execute([':path'=>$path]);
        return $sth->fetchColumn();
    }

    public static function updateConfig($path, $value)
    {
        $sth = self::sqLite()->prepare("UPDATE `config_data` SET `value`=:value WHERE `path`=:path");
        return $sth->execute([':value' => $value, ':path' => $path]);
    }

    public static function addListRecord($data)
    {
        $table = 'company_grasp_list';
        return self::insert($table, $data);
    }

    public static function updateListRecordStatus($id, $status)
    {
        $sql = "update company_grasp_list set status=:status where id=:id";
        $sth = self::sqLite()->prepare($sql);
        $sth->execute([':id' => $id, ':status' => $status]);
        return $sth->rowCount();
    }

    public static function getListRecored($number)
    {
        $sql = "select * from company_grasp_list where status=:status limit :num";
        $sth = self::sqLite()->prepare($sql);
        $sth->execute([':status' => 'p', ':num' => $number]);
        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    public static function addDetailRecord($data)
    {
        $table = 'company_detail_info';
        return self::insert($table, $data);
    }

    public static function addProxyRecord($data)
    {
        $table = 'proxy_list';
        $data['date'] = date('Y-m-d H:i:s', time());
        return self::insert($table, $data);
    }

    public static function deleteProxyRecord($ip, $port)
    {
        self::sqLite()->query(sprintf("delete from proxy_list where ip='%s' AND port=%d", $ip, $port));
    }

    public static function getProxyList()
    {
        $sql = "select ip, port from proxy_list where enable=1 order by date desc limit 50";
        $sth = self::sqLite()->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(\PDO::FETCH_ASSOC);
    }

    protected static function insert($table, $data)
    {
        $fields = self::getCache('fields', $table);
        if (is_null($fields)) {
            $fields = self::getTableFields($table);
            self::setCache('fields', $table, $fields);
        }
        $insertField = array_intersect_key($data, $fields);
        $values = [];
        $_fields = [];
        foreach ($insertField as $field => $value)
        {
            $_fields[$field] = ':' . $field;
            $values[] = $value;
        }
        $sql = "INSERT INTO %s(%s) VALUES(%s)";
        $sql = sprintf($sql, $table, implode(',', array_keys($_fields)), implode(',', $_fields));
        $sth = self::sqLite()->prepare($sql);
        $sth->execute($values);
        return self::sqLite()->lastInsertId();
    }

    protected static function getTableFields($table)
    {
        $sth = self::sqLite()->prepare(sprintf("PRAGMA TABLE_INFO('%s')", $table));
        $sth->execute();
        $struct = $sth->fetchAll(\PDO::FETCH_ASSOC);

        $fields = [];
        foreach ($struct as $field) {
            if ($field=='id') continue;
            $fields[$field['name']] = $field['name'];
        }
        return $fields;
    }

    protected static function setCache($nameSpace, $key, $data)
    {
        if (!isset(self::$_cache[$nameSpace])) {
            self::$_cache[$nameSpace] = [];
        }
        self::$_cache[$nameSpace][$key] = $data;
    }

    protected static function getCache($nameSpace, $key)
    {
        if (isset(self::$_cache[$nameSpace])) {
            if (isset(self::$_cache[$nameSpace][$key])) {
                return self::$_cache[$nameSpace][$key];
            }
        }
        return null;
    }
}