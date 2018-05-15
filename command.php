<?php
/***
 * 逻辑
 * 1. 获取最新的代理IP
 * 2. 通过代理获取数据，如果数据为空就将IP失败次数标记加1，失败三次则删除该IP
 * 3. 每次取一个要抓取的公司名字，标记为 r 表示 run, 如果抓取为空或者失败重新改状态为 p 表示 pending
 * 4. 添加 updated, 如果超过 5 分钟且状态依旧是 r 表示 run, 则重新将这条数据改为 p 表示 pending
 * 5. 一次只用搜索一个公司名字
 * 6. 每 20min 统一检查一次所有 ip 是否可用，删除不可用的 ip
 *
 */
require 'vendor/autoload.php';
use Lib\Tenf;

define('ROOT_DIR', dirname(__FILE__));

$start = time();
$today = date('Y-m-d', $start);

// step 1
// 获取最新的代理IP
/** @var \Lib\Model\Proxy $proxyMod **/
$proxyMod = Tenf::getModel('proxy');
// 清除无效代理
$proxyMod::clean();
// 获取最新的代理
$proxies = $proxyMod::mulitRecord();

if (!$proxies) {
    echo 'No enable proxy!' . PHP_EOL;
    exit;
}

//print_r($proxies);

// step 2
// 标记要抓取的公司
/** @var \Lib\Model\Company $companyMod **/
$companyMod = Tenf::getModel('company');
$companyMod->restExpiredFlag();

foreach ($proxies as $proxy) {
    $proxyHasError = false;
    $companies = $companyMod::getMultiFlagCompany();
    if (empty($companies)) {
        echo 'No company enable!' . PHP_EOL;
        exit;
    }

    // step 3
    // 开始抓取数据
    $brow = [
        'Mozilla/5.0 (X11; Ubuntu; Linu…) Gecko/20100101 Firefox/59.0',
        'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:59.0) Gecko/20100101 Firefox/59.0'
    ];
    $agent = $agent = rand(0, (count($brow) -1));

    echo 'Start Time:' . date('Y-m-d H:i:s', $start) . PHP_EOL;
    foreach ($companies as $idx => $company) {
        $resources = Tenf::getEnableGraspResources();
        foreach ($resources as $uri) {
            try {
                /** @var \Lib\Resource\Base $resourceMod **/
                $resourceMod = Tenf::getResource($uri);
                $resourceMod->setHeader('User-Agent', $brow[$agent]);
                $resourceMod->setOption('timeout', 30);
                $resourceMod->setOption('proxy', sprintf('%s:%d', $proxy->ip, $proxy->port));
                //print_r($company);
                $resourceMod->setGraspObject($company);

                echo get_class($resourceMod) . ' loading.....' . PHP_EOL;
                $resourceMod->execute();
                if ($resourceMod->getCount()) {
                    // 保存公司信息
                    foreach ($resourceMod->getResponse() as $item) {
                        $companyMod::addDetailRecord($item);
                    }
                    $companyMod::updateStatusById($company->id, 'c');
                    echo 'Get Data Complete!' . PHP_EOL;
                    break;
                } else {
                    $companyMod::updateStatusById($company->id, 'p');
                    echo 'Empty' . PHP_EOL;
                }
            } catch (\Exception $e) {
                $companyMod::updateStatusById($company->id, 'p');
                foreach ($companies as $_idx => $_company ) {
                    if ($_idx > $idx) {
                        $companyMod::updateStatusById($_company->id, 'p');
                    }
                }
                $proxyHasError = true;
                $num = 1;
                if (preg_match('/cURL\s{1}error\s{1}(\d+)/', $e->getMessage(), $match)) {
                    $num = $match[1] == 28 ? 1 : 3;
                }
                $proxyMod::addFailNum($proxy->id, $num);
                echo $num . PHP_EOL;
                echo $e->getMessage() . PHP_EOL;
                echo $proxy->ip . PHP_EOL;
                break;
            }
        }
        if ($proxyHasError) {
            break;
        }
    }
}

echo get_class($resourceMod) . ' execute finish' . PHP_EOL;
$end = time();
echo 'End Time:' . date('Y-m-d H:i:s', $end) . PHP_EOL;
echo 'exec:' . ($end-$start)/60 . PHP_EOL;

