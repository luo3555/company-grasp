<?php
namespace Lib\Model;

class Upgrade extends Modelbase
{
    CONST UPDATE_PATH = 'version';

    public static function getCurrentVersion()
    {
        $version = self::getConfig(self::UPDATE_PATH);
        if (!$version) {
            \Lib\Tenf::getModel('config')::addConfig(self::UPDATE_PATH, '');
        }
    }

    public static function update()
    {
        $dirPath = ROOT_DIR . '/lib/Setup';

        $currentVersion = self::getCurrentVersion();
        $dir = opendir($dirPath);

        $versionList = [];
        while($file = readdir($dir)) {
            if ($file!='.' && $file!='..' && substr($file, 0, stripos('-', $file)) == 'update') {
                $file = str_replace('update-', '', $file);
                $tmp = str_replace('.php', '', $file);
                $versionList[] = $tmp;
                unset($tmp);
            }
        }
        closedir($dir);
        sort($versionList);

        if ($currentVersion == '') {
            $version = '0.0.0';
            include $dirPath . '/install-0.0.0.php';
            \Lib\Tenf::getModel('config')::updateConfig(self::UPDATE_PATH, $version);
            echo PHP_EOL . 'Have install.';
        }

        foreach ($versionList as $version) {
            if (version_compare($version, $currentVersion, '>')) {
                include $dirPath . '/update-' . $version . '.php';
                \Lib\Tenf::getModel('config')::updateConfig(self::UPDATE_PATH, $version);
                echo PHP_EOL . 'Have update to ' . $version;
            }
        }
    }
}