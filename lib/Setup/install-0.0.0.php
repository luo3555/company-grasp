<?php
$init = [
    // 信息
    'customs/directory/page' => 1,
    'customs/directory/page_size' => 20,
    'customs/directory/url' => 'http://credit.customs.gov.cn/ccppAjax/queryDirectoryList.action',
    'customs/lost/page' => 1,
    'customs/lost/page_size' => 20,
    'customs/lost/url' => 'http://credit.customs.gov.cn/ccppAjax/queryLostcreditList.action',
    // 代理配置信息
    'proxy/fail/max_num' => 3,
    'proxy/live/minutes' => 5,
    'proxy/multi/record/number' => 5,
    // 
    'company/flag/live/minutes' => 20,
    'company/flag/number' => 50,
    //
    'grasp/resource/debug' => 0,
    'grasp/resource/qichacha/enable' => 1,
    'grasp/resource/tianyancha/enable' => 1,
];
foreach ($init as $key => $value) {
    \Lib\Tenf::getModel('config')::addConfig($key, $value);
}
