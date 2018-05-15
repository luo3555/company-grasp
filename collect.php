<?php
/**
 * 逻辑
 * 根据传递的参数执行不同的脚本抓取数据
 *
 */
require 'vendor/autoload.php';
use Lib\Tenf;

define('ROOT_DIR', dirname(__FILE__));
Tenf::getResource('collect_mohusou')->execute(false);