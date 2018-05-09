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
        /** @var Requests_Cookie_Jar $cookies */
        $cookies = new \Requests_Cookie_Jar([
            // 失效后记得去主页cookie里取
            'UM_distinctid' => $this->_config['umDistinctid']
        ]);

        $searchUrl = sprintf($this->_config['url'] . '/search?key=%s', $companyName);
        $response = \Requests::get(
            $searchUrl,
            [],
            [
                'cookies' => $cookies
            ]
        );
        $html = $response->body;
/**

        $rules = [
            'table' => [
                '#searchlist' , 'html'
            ]
        ];
        $items = QueryList::html($html)->rules($rules)->query()->getData()->all();

        $result = [];
        foreach ($items as $item) {
            $html = $item['table'];

            // 获取邮件
            $rules = [
                'text' => [
                    'p.m-t-xs:eq(1)', 'text', '-span'
                ]
            ];
            $res = QueryList::html($html)->rules($rules)->query()->getData()->first();
            $email = explode('：', $res['text']);
            $email = explode(' ', trim($email[1]));
            $email =  trim($email[0]);

            // 电话
            $rules = [
                'text' => [
                    'p.m-t-xs:eq(1) span.m-l', 'text'
                ]
            ];
            $res = QueryList::html($html)->rules($rules)->query()->getData()->first();
            $phone = explode('：', $res['text']);
            $phone = trim($phone[1]);

            // 获取公司营业范围
            $baseUrl = $this->_config['url'];
            $rules = [
                'url' => [
                    'table tbody td:eq(1) a', 'href'
                ]
            ];
            $url = QueryList::html($html)->rules($rules)->query()->getData(function($item) use ($baseUrl) {
                return $baseUrl . $item['url'];
            })->first();
*/
            /**
             * 详情页面获取经营范围和网站
             * 
             */
/**
            $response = \Requests::get(
                $url,
                [],
                [
                    'cookies' => $cookies
                ]
            );
            $detailHtml = $response->body;
            $rules = [
                'info' => [
                    '#Cominfo table:eq(1) tr:eq(10) td:eq(1)', 'html'
                ]
            ];
            $info = QueryList::html($detailHtml)->rules($rules)->query()->getData(function($item) {
                return $item['info'];
            })->first();

            $rules = [
                'web' => [
                    '#company-top .content .row:eq(2) span:eq(3) a', 'href'
                ]
            ];
            $web = QueryList::html($detailHtml)->rules($rules)->query()->getData(function($item) {
                return $item['web'];
            })->first();

            // 1. 获取公司名字
            $result[] = [
                'company' => QueryList::html($html)->find('a.ma_h1 em')->html(),
                'name' => QueryList::html($html)->find('p.m-t-xs a.text-primary')->html(),
                'registery_price' => str_replace('注册资本：', '', QueryList::html($html)->find('p.m-t-xs')->eq(0)->find('span')->eq(0)->html()),
                'company_email' => $email,
                'company_phone' => $phone,
                'info' => $info,
                'web' => $web,
                'status' => ''
            ];
        }
*/
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
                        $this->format($item, 'company_phone', '邮箱：');
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
            $response = \Requests::get(
                $url,
                [],
                [
                    'cookies' => $cookies
                ]
            );
            $detailHtml = $response->body;

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