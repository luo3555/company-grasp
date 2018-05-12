<?php
namespace Lib\Resource\Proxy;

class Data5ufree extends \Lib\Resource\Proxybase
{
    protected function getUrl()
    {
        return 'http://www.data5u.com/free/gnpt/index.shtml';
    }

    protected function getProxy()
    {
        $html = $this->request($this->getUrl());

        $rules = [
            'tr' => [
                '.wlist ul:eq(0) li:eq(1) ul:gt(0)', 'html'
            ]
        ];
        $list = $this->setContent($html)->rules($rules)->query()->getData(function($tr) {
            $rules = [
                    'ip' => ['li:eq(0)', 'text'],
                    'port' => ['li:eq(1)', 'text']
                ];
            $poxy = $this->setContent($tr['tr'])->rules($rules)->query()->getData(function($item) {
                return $item['ip'] . ':' . $item['port'];
            })->first();
            return $poxy;
        })->all();
        return $list;
    }
}