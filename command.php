<?php
/***
 * 逻辑
 * 1. 获取最新的代理IP
 * 2. 通过代理获取数据，如果数据为空就将IP失败次数标记加1，失败三次则删除该IP
 * 3. 每次取一个要抓取的公司名字，标记为 r 表示 run, 如果抓取为空或者失败重新改状态为 p 表示 pending
 * 4. 添加 r_start_date, 如果超过 5 分钟则重新将这条数据改为 p 表示 pending
 * 5. 一次只用搜索一个公司名字
 * 6. 每 20min 统一检查一次所有 ip 是否可用，删除不可用的 ip
 *
 */
require 'vendor/autoload.php';
use Lib\Tenf;

define('ROOT_DIR', dirname(__FILE__));

// step 1
// 获取最新的代理IP
/** @var \Lib\Model\Proxy $proxyMod **/
$proxyMod = Tenf::getModel('proxy');
$proxy = $proxyMod::firstRecord();

// step 2
// 标记要抓取的公司
/** @var \Lib\Model\Company $companyMod **/
$companyMod = Tenf::getModel('company');
$company = $companyMod::getFlagComapny();
print_r($company);
// step 3
// 开始抓取数据
$brow = [
    'Mozilla/5.0 (X11; Ubuntu; Linu…) Gecko/20100101 Firefox/59.0',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:59.0) Gecko/20100101 Firefox/59.0'
];
$agent = $agent = rand(0, (count($brow) -1));

/** @var \Lib\Resource\Base $resourceMod **/
$resourceMod = Tenf::getResource('qichacha');
$resourceMod->setHeader('User-Agent', $brow[$agent]);
$resourceMod->setOption('timeout', 30000);
$resourceMod->setOption('proxy', sprintf('%s:%d', $proxy->ip, $proxy->port));
$resourceMod->setGraspObject($company);

echo get_class($resourceMod) . ' loading.....' . PHP_EOL;

try {
    $resourceMod->execute();
    if ($resourceMod->getCount()) {
        // 保存公司信息
        foreach ($resourceMod->getResponse() as $item) {
            $companyMod::addDetailRecord($item);
        }
        $companyMod::updateStatusById($company->id, 'c');
    } else {
        $companyMod::updateStatusById($company->id, 'p');
        echo 'Empay' . PHP_EOL;
    }
} catch (\Exception $e) {
    $companyMod::updateStatusById($company->id, 'p');
    $proxyMod::addFailNum($proxy->id);
    echo $e->getMessage() . PHP_EOL;
}





echo get_class($resourceMod) . ' execute finish' . PHP_EOL;







