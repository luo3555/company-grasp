<?php
namespace Lib\Resource\Grasp;

class Qichacha extends \Lib\Resource\Graspbase
{
    protected function initConfig()
    {
        return [
            'url' => 'http://www.qichacha.com',
            'umDistinctid' => $this->getConfig('qichacha/um_distinctid'),
            'limit' => $this->getConfig('qichacha/select/limit')
        ];
    }

    protected function graspDataByKeyword($companyName)
    {
        // get valicate cookie
        $response = \Requests::get('http://www.qichacha.com/index_verify?type=companysearch');
        $acwTc = $response->cookies->offsetGet('acw_tc');
        $phpSessid = $response->cookies->offsetGet('PHPSESSID');

        /** @var Requests_Cookie_Jar $cookies */
        $cookies = $this->getCookies([
            // 失效后记得去主页cookie里取
            'UM_distinctid' => $this->_config['umDistinctid'],
            'PHPSESSID' => $phpSessid,
            'acw_tc' => $acwTc
        ]);

        $searchUrl = sprintf($this->_config['url'] . '/search?key=%s', $companyName);
        $html = $this->request($searchUrl,[], [
                    'cookies' => $cookies
                ]);
        $this->debug($html);

        $result = [];
        
        $rules = [
            'item' => [
                '#searchlist table tbody tr' , 'html'
            ]
        ];
        $items = $this->setContent($html)->rules($rules)->query()->getData(function($item){
            foreach ($item as $key => $value) {
                    $rules = [
                        'company' => ['td:eq(1) a', 'html'],
                        'name' => ['td:eq(1) a:eq(1)', 'text'],
                        'url'  => ['td:eq(1) a', 'href'],
                        'registery_price' => ['td:eq(1) span', 'html'],
                        'company_email' => ['td:eq(1) p:eq(1)', 'text', '-span -a'],
                        'company_phone' => ['td:eq(1) p:eq(1) span', 'text'],
                        'company_address' => ['td:eq(1) p:eq(2)', 'html'],
                        'company_status' => ['td:eq(2) span', 'text']
                    ];
                    $item = $this->setContent($value)->rules($rules)->query()->getData(function($item){
                        $this->format($item, 'company');
                        $this->format($item, 'registery_price', '注册资本：');
                        $this->format($item, 'company_email', ['邮箱：', '-']);
                        $this->format($item, 'company_phone', ['电话：', '-']);
                        $this->format($item, 'company_address', '地址：');
                        return $item;
                    })->first();
            }
            return $item;
        })->all();

        foreach ($items as $idx => $item) {
            $url = $this->_config['url'] . $item['url'];
            /**
             * 详情页面获取经营范围和网站
             * 
             */
            $detailHtml = $this->request($url, [], [
                                'cookies' => $cookies
                            ]);

            $rules = [
                'info' => [
                    '#Cominfo table:eq(1) tr:eq(10) td:eq(1)', 'html'
                ],
                'web' => [
                    '#company-top .content .row:eq(2) span:eq(3) a', 'href'
                ],

            ];
            $_cItem = $this->setContent($detailHtml)->rules($rules)->query()->getData(function($item) {
                return $item;
            })->first();
            if (is_array($_cItem)) {
                $result[] = array_merge($item, $_cItem);
            } else {
                $resule[] = $item;
            }
        }
        return $result;
    }

    protected function format(&$array, $field, $search=null, $replace='')
    {
        if (isset($array[$field])) {
            $array[$field] = strip_tags($array[$field]);
            if (!is_null($search)) {
                if (!is_array($search)) {
                    $search = (array)$search;
                }
                foreach ($search as $need) {
                    $array[$field] = str_replace($need, $replace, $array[$field]);
                }
            }
        }
    }
}