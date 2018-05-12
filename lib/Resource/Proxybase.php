<?php
namespace Lib\Resource;

abstract class Proxybase extends Base
{
    /**
     * usually page 5 ip is new, over 5 most are disable
     */
    const MAX_PAGE = 5;

    /**
     * Unit is ms
     */
    const MAX_TIME_DELAY = 1000;

    abstract protected function getUrl();

    /**
     * Resture array
     * ['ip' => xxx.xxx.xxx.xxx, 'port' => 89, 'time' => '']
     */
    abstract protected function getProxy();

    public function execute()
    {
        $ips = $this->getProxy();
        $this->_ping($ips);
    }

    protected function getCheckUrl()
    {
        return 'http://www.xdaili.cn/ipagent//checkIp/ipList?';
    }

    protected function _ping($ips)
    {
        foreach ($ips as $item) {
            $item = is_array($item) ? $item : [$item] ;
            $checkList = implode('&', array_map(function($value){
                return 'ip_ports[]=' . $value;
            }, $item));
            $url = $this->getCheckUrl() . $checkList;
            $response = $this->request($url, [
                'User-Agent' => 'Mozilla/5.0 (X11; Ubuntu; Linu…) Gecko/20100101 Firefox/59.0'
            ]);
            
            // time
            $checkResult = json_decode($response, true);
            if ($checkResult['ERRORCODE']==0) {
                foreach ($checkResult['RESULT'] as $poxy) {
                    if (isset($poxy['time'])) {
                        $poxy['time'] = (int)str_replace('ms', '', $poxy['time']);
                        if ($poxy['time']<self::MAX_TIME_DELAY && $poxy['port'] != 80) {
                            \Lib\Tenf::getModel('proxy')::save($poxy);
                        }
                    }
                }
            }
        }
    }
}