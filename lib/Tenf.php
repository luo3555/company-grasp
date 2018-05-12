<?php
namespace Lib;

final class Tenf
{
    const MODEL_NAMESPACE = '\Lib\Model';

    const RESOURCE_NAMESPACE = '\Lib\Resource';


    public static function getModel($uri, $args=[])
    {
        return self::_getClassByUri($uri, self::MODEL_NAMESPACE, $args);
    }

    public static function getResource($uri)
    {
        return self::_getClassByUri($uri, self::RESOURCE_NAMESPACE);
    }

    protected static function _getClassByUri($uri, $namespace, $args=null)
    {
        $uri = ucfirst($uri);
        $uri = str_replace('_', '\\', $uri);
        $className = sprintf('%s\%s', $namespace, $uri);
        if (class_exists($className)) {
            return new $className($args);
        }
        throw new \Exception(sprintf("Class [%s] not exist", $className), 0);
    }
}