<?php
namespace Lib\Resource\Proxy;

class Kuaidaili extends \Lib\Resource\Proxybase
{
    protected function getUrl()
    {
        return sprintf('https://www.kuaidaili.com/free/inha/1/');
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
            $poxy = $this->setContent($tr['tr'])->rules($rules)->query()->getData(function($td){
                return $td['ip'] . ':' . $td['port'];
            })->first();
            return $poxy;
        })->all();
        return $ips;
    }
}