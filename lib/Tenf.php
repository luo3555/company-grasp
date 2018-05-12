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
        $space = ' ';
        $uri = str_replace('_', $space, $uri);
        $uri = ucwords($uri);
        $uri = str_replace($space, '\\', $uri);
        $className = sprintf('%s\%s', $namespace, $uri);
        if (class_exists($className)) {
            return new $className($args);
        }
        throw new \Exception(sprintf("Class [%s] not exist", $className), 0);
    }
}