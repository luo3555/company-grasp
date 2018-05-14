<?php
namespace Lib\Resource\Grasp;

class tianyancha extends \Lib\Resource\Graspbase
{
    protected function initConfig()
    {
        return [
            'url' => 'https://www.tianyancha.com',
            'limit' => $this->getConfig('tianyancha/select/limit'),
        ];
    }

    protected function graspDataByKeyword($companyName)
    {
        $html = $this->request(sprintf('%s/search?key=%s', $this->_config['url'], $companyName));
        $this->debug($html);
        $rules = [
            'href' => [
                '.search_result_container .search_result_single .search_right_item>div>a.sv-search-company', 'href'
            ]
        ];
        $list = $this->setContent($html)->rules($rules)->query()->getData()->all();
        $result = [];
        foreach ($list as $href) {
            $html = $this->request($href['href']);
            $rules = [
                'company' => [
                    '#company_web_top h1', 'html'
                ],
                'name' => [
                    '#_container_baseInfo tbody td .human-top .in-block a' , 'title'
                ],
                'web' => [
                    '#company_web_top .company_header_interior .sec-c2:eq(1) .in-block:eq(0) span:eq(1)', 'text'
                ],
                'company_address' => [
                    '#company_web_top .company_header_interior .sec-c2:eq(1) .in-block:eq(1) span:eq(1)', 'text'
                ],
                'company_phone' => [
                    '#company_web_top .company_header_interior .sec-c2:eq(0) .vertical-top:eq(0) span:eq(1)' , 'html'
                ],
                'company_phones' => [
                    '#company_web_top .company_header_interior .sec-c2:eq(0) .vertical-top:eq(0) span:eq(2) script' , 'html'
                ],
                'company_email' => [
                    '#company_web_top .company_header_interior .sec-c2:eq(0) .vertical-top:eq(1) span:eq(1)' , 'title'
                ],
                'registery_price' => [
                    '#_container_baseInfo tbody tr:eq(0) td:eq(1) text:eq(0)', 'text'
                ],
                'info' => [
                    '#_container_baseInfo .base0910 tbody tr:eq(6) td:eq(1) text:eq(0)', 'text'
                ]
            ];
            $data = $this->setContent($html)->rules($rules)->query()->getData(function($item) {
                $this->_filter($item, 'company_phones', null, '_phones');

                $this->_filter($item, 'web');
                $this->_filter($item, 'company_phone');
                return $item;
            })->all();
            if (is_array($data)) {
                $result = array_merge($result, $data);
            }
            if (empty($data)) {
                $this->debug($html);
            }
        }
        return $result;
    }

    protected function _phones($field, $defaultValue, $item)
    {
        $phones = json_decode($item[$field], true);
        if (is_array($phones)) {
            $phones[] = $item['company_phone'];
            $phones = array_unique($phones);
            $item['company_phone'] = implode(';', $phones);
        }
        return $item;
    }
}