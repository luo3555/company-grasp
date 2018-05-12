<?php
namespace Lib\Resource\Proxy;

class Xicidaili extends \Lib\Resource\Proxybase
{
    protected function getUrl()
    {
        return  'http://www.xicidaili.com/wt/';
    }

    protected function getProxy()
    {
        $rules = [
            'tr' => [
                '#ip_list tr:gt(0)', 'html'
            ]
        ];
        // 代理 ip 有效时间短，只获取第一页的就行了
        $list = $this->setContent($this->getUrl() . '1', 'url')->rules($rules)->query()->getData(function($tr) {
            return $this->setContent($tr['tr'])->rules([
                    'ip' => [
                        'td:eq(1)', 'text'
                    ],
                    'port' => [
                        'td:eq(2)', 'text'
                    ]
                ])->query()->getData(function($td){
                    return $td['ip'] . ':' . $td['port'];
                })->first();
        })->all();
        // validate is enable
        $ips = [
            array_slice($list, 0, 49),
            array_slice($list, 50)
        ];
        return $ips;
    }
}