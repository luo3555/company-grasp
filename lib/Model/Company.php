<?php
namespace Lib\Model;

use Lib\Sqlite;

class Company extends Sqlite
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

    public static function updateStatusById($id, $status)
    {
        $sth = self::sqLite()->prepare('update company_grasp_list set status=:status, updated=:updated where id=:id');
        $sth->execute([':status' => $status, ':updated' => date('Y-m-d H:i:s', time()), ':id' => $id]);
        return $sth->rowCount();
    }
}