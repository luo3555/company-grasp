<?php
require 'vendor/autoload.php';

define('ROOT_DIR', dirname(__FILE__));

$start = time();
$today = date('Y-m-d', $start);

$brow = [
    'Mozilla/5.0 (X11; Ubuntu; Linu…) Gecko/20100101 Firefox/59.0',
    'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:59.0) Gecko/20100101 Firefox/59.0'
];

$processing = Lib\Sqlite::getConfig('processing');
//echo $processing . PHP_EOL;
if ($processing<1) {
    echo 'Start Time:' . date('Y-m-d H:i:s', $start) . PHP_EOL;
    Lib\Sqlite::updateConfig('processing', 1);
    $proxyList = \Lib\Sqlite::getProxyList();
    echo 'Proxy Number:[' . count($proxyList) . ']' . PHP_EOL;
    foreach ($proxyList as $proxy) {
        $dir = opendir(ROOT_DIR . '/lib/select');
        while($file = readdir($dir)) {
            if ($file!='.' && $file!='..' && $file!='Base.php') {
                // if model disable continue
                $className = substr($file, 0, stripos($file, '.'));
                if (Lib\Sqlite::getConfig(sprintf('%s/enable', strtolower($className)))==1) {
                    try {
                        $className = 'Lib\Select\\' . $className;
                        $obj = new $className();
                        echo $className . ' loading.....' . PHP_EOL;

                        $agent = $agent = rand(0, (count($brow) -1));
                        $obj->setHeader('User-Agent', $brow[$agent]);
                        $obj->setOption('timeout', 30000);
                        $obj->setOption('proxy', sprintf('%s:%d', $proxy['ip'], $proxy['port']));
                        //$obj->setOption('proxy', '60.177.231.101:18118');
                        //$obj->setOption('proxy', '111.183.229.175:61234');
                        $obj->run();
                        echo $className . ' execute finish' . PHP_EOL;
                    } catch (\Exception $e) {
                        echo 'Error: ' . $e->getMessage() . PHP_EOL;
                    }
                }
            }
        }
        closedir($dir);
    }
    Lib\Sqlite::updateConfig('processing', 0);
    $end = time();
    echo 'End Time:' . date('Y-m-d H:i:s', $end) . PHP_EOL;
    echo 'exec:' . ($end-$start)/60 . PHP_EOL;
}