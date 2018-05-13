<?php
require 'vendor/autoload.php';

define('ROOT_DIR', dirname(__FILE__));

$customs = new Lib\Customs();
/***********************************************************************************************************************************
海关信息 ***************************************************************************************************************************
1. 未失信企业 ************************************************************************************************************************
************************************************************************************************************************************
***********************************************************************************************************************************/
$url = Lib\Sqlite::getConfig('customs/directory/url');
$page = Lib\Sqlite::getConfig('customs/directory/page');
$pageSize = Lib\Sqlite::getConfig('customs/directory/page_size');

$customs->setUrl($url);
// while (true)
// {
    $customs->getBaseInfo($page, $pageSize);

    // if (empty($customs->getData('list'))) {
    //     break;
    // }

    // if ($page>$customs->getData('totalPage')) {
    //     break;
    // }

    foreach ($customs->getData('list') as $item) {
        $item['lost_credit'] = 0;
        Lib\Sqlite::addListRecord($item);
    }

    $page++;
    Lib\Sqlite::updateConfig('customs/directory/page', $page);
//}



/***********************************************************************************************************************************
2. 失信企业 ************************************************************************************************************************
************************************************************************************************************************************
***********************************************************************************************************************************/
// $url = Lib\Sqlite::getConfig('customs/lost/url');
// $page = Lib\Sqlite::getConfig('customs/lost/page');
// $pageSize = Lib\Sqlite::getConfig('customs/lost/page_size');

// $customs->setUrl($url);
// while (true)
// {
//     $customs->getBaseInfo($page, $pageSize);

//     if (empty($customs->getData('list'))) {
//         break;
//     }

//     if ($page>$customs->getData('totalPage')) {
//         break;
//     }

//     foreach ($customs->getData('list') as $item) {
//         $item['lost_credit'] = 1;
//         Lib\Sqlite::addListRecord($item);
//     }

//     $page++;
//     Lib\Sqlite::updateConfig('customs/lost/page', $page);
// }



// echo ROOT_DIR;
// $pdo = Lib\Sqlite::sqLite();
// $sql = 'select * from config_data limit 0,1000';
// $sth = $pdo->prepare($sql);
// $sth->execute();
// $res = $sth->fetchAll();


//$res = Lib\Sqlite::getConfig('customs/grasp/page');
// $res = Lib\Sqlite::getTableFields('company_detail_info');
// print_r($res);


// foreach ($pdo->query('select * from config_data limit 0,1000') as $item)
// {
//     print_r($item);
// }

// $file = file_get_contents('1-20.txt');
// $list = unserialize($file);

// foreach ($list as $key => $value) {
//     Lib\sqLite::addListRecord($value);
// }

//Lib\Sqlite::updateConfig('customs/grasp/page', 2);