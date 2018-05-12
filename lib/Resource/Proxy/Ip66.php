<?php
namespace Lib\Resource\Proxy;

class Ip66 extends \Lib\Resource\Proxybase
{
    protected function getUrl()
    {
        return 'http://www.66ip.cn/mo.php?sxb=&tqsl=50&port=&export=80&ktip=&sxa=&submit=%CC%E1++%C8%A1&textarea=';
    }

    protected function getProxy()
    {
        $content = $this->request($this->getUrl());
        $content = strip_tags($content);
        $content = substr($content, stripos($content, ':'));
        $content = substr($content, 0, strripos($content, ':'));
        $ips = explode(PHP_EOL, $content);
        array_shift($ips);
        array_pop($ips);
        $ips = array_map(function($item) {
            return trim($item);
        }, $ips);
        return $ips;
    }
}