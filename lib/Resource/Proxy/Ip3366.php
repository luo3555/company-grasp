<?php
namespace Lib\Resource\Proxy;

class Ip3366 extends \Lib\Resource\Proxybase
{
    protected function getUrl()
    {
        return  'http://www.ip3366.net/free/';
    }

    protected function getProxy()
    {
        $html = $this->request($this->getUrl());
        $rules = [
            'tr' => [
                '#list tbody tr', 'html'
            ]
        ];
        $ips = $this->setContent($html)->rules($rules)->query()->getData(function($tr) {
            $rules = [
                    'ip' => ['td:eq(0)', 'text'],
                    'port' => ['td:eq(1)', 'text']
                ];
            $poxy = $this->setContent($tr['tr'])->rules($rules)->query()->getData(function($item) {
                return $item['ip'] . ':' . $item['port'];
            })->first();
            return $poxy;
        })->all();
        return $ips;
    }
}