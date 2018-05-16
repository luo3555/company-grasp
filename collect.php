<?php
/**
 * 逻辑
 * 根据传递的参数执行不同的脚本抓取数据
 *
 */
require 'vendor/autoload.php';
use Lib\Tenf;

define('ROOT_DIR', dirname(__FILE__));
$object = new \stdClass();
$object->saicSysNo = 0;
$object->nameSaic = 'guangdong';

/** @var \Lib\Model\Company $companyMod **/
$companyMod = Tenf::getModel('company');

/** @var \Lib\Resource\Base $resourceMod **/
$resourceMod = Tenf::getResource('collect_mohusou');
$resourceMod->setGraspObject($object)->execute(false);

if ($resourceMod->getCount()) {
    // 保存公司信息
    foreach ($resourceMod->getResponse() as $item) {
        $item['saicSysNo'] = 'T-' . uniqid();
        $companyMod::addListRecord([
                'nameSaic' => $item['company'],
                'saicSysNo' => $item['saicSysNo'],
                'socialCreditCode' => '',
                'status' => 'c',
                'updated' => date('Y-m-d H:i:s', time())
            ]);
        $companyMod::addDetailRecord($item) . PHP_EOL;
    }
    //$companyMod::updateStatusById($company->id, 'c');
    echo 'Get Data Complete!' . PHP_EOL;
}