<?php
require 'vendor/autoload.php';

define('ROOT_DIR', dirname(__FILE__));

$start = time();
$today = date('Y-m-d', $start);
echo 'Start Time:' . date('Y-m-d H:i:s', $start) . PHP_EOL;

$dir = opendir('lib/select');
while($file = readdir($dir)) {
    if ($file!='.' && $file!='..' && $file!='Base.php') {
        // if model disable continue
        $className = substr($file, 0, stripos($file, '.'));
        // get last config date
        $lastRunDate = Lib\Sqlite::getConfig(strtolower($className) . '/run/date');
        $allow = Lib\Sqlite::getConfig(strtolower($className) . '/run/allow');

        if ($lastRunDate < $today) {
            $allow = 1;
            \Lib\Sqlite::updateConfig(strtolower($className) . '/run/allow', $allow);
            \Lib\Sqlite::updateConfig(strtolower($className) . '/run/date', $today);
        }

        if (Lib\Sqlite::getConfig(sprintf('%s/enable', strtolower($className)))==1) {
            // if today not allow, continue
            if ($allow==0) {
                continue;
            }
            $className = 'Lib\Select\\' . $className;
            $obj = new $className();
            echo $className . ' loading.....' . PHP_EOL;
            $obj->run();
        }
    }
}
closedir($dir);

$end = time();
echo 'End Time:' . date('Y-m-d H:i:s', $end) . PHP_EOL;
echo 'exec:' . ($end-$start)/60 . PHP_EOL;