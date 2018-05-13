<?php
/**
 * 升级脚本
 */
require 'vendor/autoload.php';
use Lib\Tenf;

define('ROOT_DIR', dirname(__FILE__));

Tenf::Upgrade();

echo PHP_EOL . 'Upgrade Finish' . PHP_EOL;