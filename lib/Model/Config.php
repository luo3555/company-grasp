<?php
namespace Lib\Model;

class Config extends Modelbase
{
    CONST TABLE = 'config_data';

    public static function addConfig($key, $value)
    {
        return self::insert(self::TABLE, ['path' => $key, 'value' => $value]);
    }
}