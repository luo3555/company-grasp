<?php
require 'vendor/autoload.php';
use QL\QueryList;
use Lib\Select\Base;
define('ROOT_DIR', dirname(__FILE__));
$proxy = new \Lib\Proxy();
$proxy->cleanIp();
$proxy->getIpList();