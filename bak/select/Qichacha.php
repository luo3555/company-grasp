<?php
namespace Lib\Select;

use QL\QueryList;
use Lib\Select\Base;

/**
 * 获取公司法人信息
 */
class Qichacha extends Base
{
    protected function initConfig()
    {
        return [
            'url' => 'http://www.qichacha.com',
            'umDistinctid' => $this->getConfig('qichacha/um_distinctid'),
            'limit' => $this->getConfig('qichacha/select/limit')
        ];
    }


    public function graspData($companyName)
    {
        // get valicate cookie
        $response = \Requests::get('http://www.qichacha.com/index_verify?type=companysearch');
        $acwTc = $response->cookies->offsetGet('acw_tc');
        $phpSessid = $response->cookies->offsetGet('PHPSESSID');

        /** @var Requests_Cookie_Jar $cookies */
        $cookies = new \Requests_Cookie_Jar([
            // 失效后记得去主页cookie里取
            'UM_distinctid' => $this->_config['umDistinctid'],
            'PHPSESSID' => $phpSessid,
            'acw_tc' => $acwTc
        ]);

        $searchUrl = sprintf($this->_config['url'] . '/search?key=%s', $companyName);
        $html = $this->request($searchUrl,[], [
                    'cookies' => $cookies
                ]);
        //print_r($html);

        $result = [];
        
        $rules = [
            'item' => [
                '#searchlist table tbody tr' , 'html'
            ]
        ];
        $items = QueryList::html($html)->rules($rules)->query()->getData(function($item){
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
                    $item = QueryList::html($value)->rules($rules)->query()->getData(function($item){
                        $this->format($item, 'company');
                        $this->format($item, 'registery_price', '注册资本：');
                        $this->format($item, 'company_email', '邮箱：');
                        $this->format($item, 'company_phone', '电话：');
                        $this->format($item, 'company_address', '地址：');
                        
                        // $item['company'] = strip_tags($item['company']);
                        // $item['registery_price'] = str_replace('注册资本：', '', $item['registery_price']);
                        // $item['company_email'] = str_replace('邮箱：', '', $item['company_email']);
                        // $item['company_phone'] = str_replace('电话：', '', $item['company_phone']);
                        // $item['company_address'] = str_replace('地址：', '', strip_tags($item['company_address']));
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
            // $response = \Requests::get(
            //     $url,
            //     [],
            //     [
            //         'cookies' => $cookies
            //     ]
            // );
            // $detailHtml = $response->body;
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
            $_cItem = QueryList::html($detailHtml)->rules($rules)->query()->getData(function($item) {
                return $item;
            })->first();
            if (is_array($_cItem)) {
                $result[] = array_merge($item, $_cItem);
            } else {
                $resule[] = $item;
            }
        }
        echo empty($result) ? 'empty' . PHP_EOL : 'hasData' . PHP_EOL ;
        return $result;
    }

    protected function format(&$array, $field, $search=null, $replace='')
    {
        if (isset($array[$field])) {
            $array[$field] = strip_tags($array[$field]);
            if (!is_null($search)) {
                $array[$field] = str_replace($search, $replace, $array[$field]);
            }
        }
    }
}