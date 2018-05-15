<?php
$init = [
    // 信息
    'mailchimp/api/key' => '860b4f5a4a18dae3b454420c9f0883b4-us2',
];
foreach ($init as $key => $value) {
    \Lib\Tenf::getModel('config')::addConfig($key, $value);
}