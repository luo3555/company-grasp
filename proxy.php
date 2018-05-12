<?php
/**
 * 逻辑
 * 根据传递的参数执行不同的脚本获取代理信息
 *
 */
require 'vendor/autoload.php';
use Lib\Tenf;

define('ROOT_DIR', dirname(__FILE__));

$proxyName = strtolower($argv[1]);
if (!empty($proxyName)) {
    $proxyResource = Tenf::getResource(sprintf('proxy_%s', $proxyName));
    $proxyResource->execute();
}
