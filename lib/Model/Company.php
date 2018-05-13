<?php
namespace Lib\Model;

class Company extends Modelbase
{
    const PENDING_SEARCH = 'p';

    const RUN_SEARCH = 'r';

    const COMPLETE_SEARCH = 'c';

    const EMPTY_SEARCH = 'e';

    public static function getFlagComapny()
    {
        $sth = self::sqLite()->query("select id, nameSaic, saicSysNo from company_grasp_list where status='p' limit 1");
        $sth->execute();
        $row = $sth->fetchObject();
        self::updateStatusById($row->id, self::RUN_SEARCH);
        return $row;
    }

    public static function getMultiFlagCompany()
    {
        $sth = self::sqLite()->prepare("select id, nameSaic, saicSysNo from company_grasp_list where status='p' limit :limit");
        $sth->execute([':limit' => self::getConfig('company/flag/number')]);
        $rows = $sth->fetchAll(\PDO::FETCH_CLASS);
        foreach ($rows as $row) {
            self::updateStatusById($row->id, self::RUN_SEARCH);
        }
        return $rows;
    }

    public static function restExpiredFlag()
    {
        $timeLine = date('Y-m-d H:i:s', strtotime(sprintf('-%d minutes', self::getConfig('company/flag/live/minutes'))));
        $sth = self::sqLite()->prepare("update company_grasp_list set status='p' where status='r' and updated < :updated");
        $sth->execute([':updated' => $timeLine]);
        return $sth->rowCount();
    }

    public static function updateStatusById($id, $status)
    {
        $sth = self::sqLite()->prepare('update company_grasp_list set status=:status, updated=:updated where id=:id');
        $sth->execute([':status' => $status, ':updated' => date('Y-m-d H:i:s', time()), ':id' => $id]);
        return $sth->rowCount();
    }
}