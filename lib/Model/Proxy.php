<?php
namespace Lib\Model;

class Proxy extends Modelbase
{
    public static function firstRecord()
    {
        $sql = "select id, ip, port from proxy_list where fail_number<=:fail_number order by date desc, time asc limit 1";
        $sth = self::sqLite()->prepare($sql);
        $sth->execute([':fail_number' => self::_failMaxNum()]);
        return $sth->fetchObject();
    }

    public static function mulitRecord()
    {
        $sql = "select id, ip, port from proxy_list where fail_number<=:fail_number order by date desc, time asc limit :limit";
        $sth = self::sqLite()->prepare($sql);
        $sth->execute([':fail_number' => self::_failMaxNum(), ':limit' => 10]);
        return $sth->fetchAll(\PDO::FETCH_CLASS);
    }

    protected static function _failMaxNum()
    {
        return self::getConfig('proxy/fail/max_num');
    }

    public static function addFailNum($id)
    {
        $sth = self::sqLite()->prepare('update proxy_list set fail_number=fail_number+1 where id=:id');
        $sth->execute([':id' => $id]);
        return $sth->rowCount();
    }

    public static function clean()
    {
        // 删除错误次数大于3和一定时间内没用使用的
        $timeLine = date('Y-m-d H:i:s', strtotime(sprintf('-%d minutes', self::getConfig('proxy/live/minutes'))));
        $sth = self::sqLite()->prepare('delete from proxy_list where fail_number > :fail_number or date < :date');
        $sth->execute([':fail_number' => self::getConfig('proxy/fail/max_num'), ':date' => $timeLine]);
        return $sth->rowCount();
    }

    public static function save($data)
    {
        $table = 'proxy_list';
        $data['date'] = date('Y-m-d H:i:s', time());
        return self::insert($table, $data);
    }
}