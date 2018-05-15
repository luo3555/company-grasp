<?php
namespace Lib\Resource\Proxy;

class Ip89 extends \Lib\Resource\Proxybase
{
    protected function getUrl()
    {
        return 'http://www.89ip.cn/apijk/?&tqsl=50&sxa=&sxb=&tta=&ports=&ktip=&cf=1';
    }

    protected function getProxy()
    {
        $content = $this->request($this->getUrl());
        $content = explode('高', $content);
        $content = explode('<br>', $content[0]);
        array_shift($content);
        array_pop($content);
        $ips = explode(PHP_EOL, $content);
        $ips = array_map(function($item) {
            return trim($item);
        }, $ips);
        return $ips;
    }
}