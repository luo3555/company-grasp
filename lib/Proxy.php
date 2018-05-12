<?php
namespace Lib;
// https://www.kuaidaili.com/free/inha/3/

use QL\QueryList;

class Proxy
{
    const URL = 'http://www.xicidaili.com/wt/';

    const CHECK_STATUS_URL = 'http://www.xdaili.cn/ipagent//checkIp/ipList?';

    /**
     * Before page 50 ip is new, over 50 most are disable
     */
    const MAX_PAGE = 5;

    public static function getIpList()
    {
        // get current page
        $page = (int)\Lib\Sqlite::getConfig('proxy/page/offset');

        // get ip
        try {
            //self::_xicidaili($page);
        } catch (\Exception $e) {
            echo $e->getMessage()  . PHP_EOL;
        }
        // 
        try {
            self::_kuaidaili($page);
        } catch (\Exception $e) {
            echo $e->getMessage()  . PHP_EOL;
        }
        //
        try {
            self::_data5uFree();
        } catch (\Exception $e) {
            echo $e->getMessage()  . PHP_EOL;
        }
        //
        try {
            self::_ip3366();
        } catch (\Exception $e) {
            echo $e->getMessage()  . PHP_EOL;
        }
        //
        try {
            self::_66ip();
        } catch (\Exception $e) {
            echo $e->getMessage()  . PHP_EOL;
        }

        $page++;
        $page = $page > self::MAX_PAGE ? 1 : $page ;
        \Lib\Sqlite::updateConfig('proxy/page/offset', $page);
    }

    public static function cleanIp()
    {
        $list = \Lib\Sqlite::getProxyList();
        $checkList = [];
        foreach ($list as $row) {
            $checkList[] = 'ip_ports[]=' . $row['ip'] . ':' . $row['port'];
        }
        $url = self::CHECK_STATUS_URL . implode('&', $checkList);
        $response = \Requests::get($url)->body;
        $checkResult = json_decode($response, true);
        $deleteList = [];
        if ($checkResult['ERRORCODE']==0) {
            foreach ($checkResult['RESULT'] as $poxy) {
                if (!isset($poxy['time'])) {
                    $deleteList[] = [
                        'ip' => $poxy['ip'],
                        'port' => $poxy['port']
                    ];
                }
            }
        }

        if (!empty($deleteList)) {
            foreach ($deleteList as $poxy) {
                \Lib\Sqlite::deleteProxyRecord($poxy['ip'], $poxy['port']);
            }
        }
    }

    protected static function _xicidaili($page)
    {
        $rules = [
            'tr' => [
                '#ip_list tr:gt(0)', 'html'
            ]
        ];
        $list = QueryList::get(self::URL. $page)->rules($rules)->query()->getData(function($tr) {
            return QueryList::html($tr['tr'])->rules([
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
        self::_ping($ips);
        return $ips;
    }

    protected static function _kuaidaili($page)
    {
        $url = sprintf('https://www.kuaidaili.com/free/inha/%d/', $page);
        $html  =\Requests::get($url)->body;

        $rules = [
            'tr' => [
                '#list tbody tr', 'html'
            ]
        ];
        $ips = QueryList::html($html)->rules($rules)->query()->getData(function($tr) {
            $rules = [
                    'ip' => ['td:eq(0)', 'text'],
                    'port' => ['td:eq(1)', 'text']
                ];
            $poxy = QueryList::html($tr['tr'])->rules($rules)->query()->getData(function($td){
                return $td['ip'] . ':' . $td['port'];
            })->first();
            return $poxy;
        })->all();
        self::_ping($ips);
        return $ips;
    }

    protected static function _data5uFree()
    {
        $url = 'http://www.data5u.com/free/gnpt/index.shtml';
        $html  =\Requests::get($url)->body;

        $rules = [
            'tr' => [
                '.wlist ul:eq(0) li:eq(1) ul:gt(0)', 'html'
            ]
        ];
        $list = QueryList::html($html)->rules($rules)->query()->getData(function($tr) {
            $rules = [
                    'ip' => ['li:eq(0)', 'text'],
                    'port' => ['li:eq(1)', 'text']
                ];
            $poxy = QueryList::html($tr['tr'])->rules($rules)->query()->getData(function($item) {
                return $item['ip'] . ':' . $item['port'];
            })->first();
            return $poxy;
        })->all();
        return $list;
    }

    protected static function _ip3366()
    {
        $url = 'http://www.ip3366.net/free/';
        $html  =\Requests::get($url)->body;

        $rules = [
            'tr' => [
                '#list tbody tr', 'html'
            ]
        ];
        $ips = QueryList::html($html)->rules($rules)->query()->getData(function($tr) {
            $rules = [
                    'ip' => ['td:eq(0)', 'text'],
                    'port' => ['td:eq(1)', 'text']
                ];
            $poxy = QueryList::html($tr['tr'])->rules($rules)->query()->getData(function($item) {
                return $item['ip'] . ':' . $item['port'];
            })->first();
            return $poxy;
        })->all();
        self::_ping($ips);
        return $ips;
    }

    protected static function _66ip()
    {
        $url = 'http://www.66ip.cn/mo.php?sxb=&tqsl=50&port=&export=80&ktip=&sxa=&submit=%CC%E1++%C8%A1&textarea=';
        $content = \Requests::get($url)->body;
        $content = strip_tags($content);
        $content = substr($content, stripos($content, ':'));
        $content = substr($content, 0, strripos($content, ':'));
        $ips = explode(PHP_EOL, $content);
        array_shift($ips);
        array_pop($ips);
        $ips = array_map(function($item) {
            return trim($item);
        }, $ips);
        self::_ping($ips);
        return $ips;
    }

    protected static function _ping($ips)
    {
        foreach ($ips as $item) {
            $item = is_array($item) ? $item : [$item] ;
            $checkList = implode('&', array_map(function($value){
                return 'ip_ports[]=' . $value;
            }, $item));
            $url = self::CHECK_STATUS_URL . $checkList;
            $response = \Requests::get($url)->body;
            
            // time
            $checkResult = json_decode($response, true);
            if ($checkResult['ERRORCODE']==0) {
                foreach ($checkResult['RESULT'] as $poxy) {
                    if (isset($poxy['time'])) {
                        \Lib\Sqlite::addProxyRecord($poxy);
                    }
                }
            }
        }
    }
}