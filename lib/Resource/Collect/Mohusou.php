<?php
namespace Lib\Resource\Collect;

class Mohusou extends \Lib\Resource\Graspbase
{
    const PATE_TPL = 'collect/resource/mohusou/%s/page';

    const PROVINCE_TPL = 'collect/resource/mohusou/%s/id';

    const BASE_URL = 'http://www.mohuso.com';

    //const ENABLE_TPL = 'grasp/resource/mohusou/%s/enable';

    protected function initConfig()
    {
        return [
            'url' => 'http://www.mohuso.com/company/search?&province_id=%s&p=%d',
        ];
    }

    protected function graspDataByKeyword($province)
    {
        $provinceId = $this->getConfig(sprintf(self::PROVINCE_TPL, $province));
        $page = $this->getConfig(sprintf(self::PATE_TPL, $province));
        $url = sprintf($this->_config['url'], $provinceId, $page);
        $html = $this->request($url);
        $this->debug($html);

        $result = [];
        $rules = [
            'href' => [
                '.container .firm_l .home_table tr td .detail a.btn-token-click', 'href'
            ]
        ];
        $href = $this->setContent($html)->rules($rules)->query()->getData(function($item) {
            return self::BASE_URL . $item['href'];
        })->all();

        $result = [];
        foreach ($href as $url) {
            $url = 'http://local.grasp.com/text-g.html';
            $html = $this->request($url);
            $rules = [
                'company' => ['.bd-e5 .mid .name span', 'html' , '-a'],
                'company_phone' => ['.bd-e5 .mid div:eq(0) tr:eq(0) td:eq(0) span', 'html'],
                'company_email' => ['.bd-e5 .mid div:eq(0) tr:eq(0) td:eq(1) span', 'html'],
                'web' => ['.bd-e5 .mid div:eq(0) tr:eq(1) td:eq(0) a', 'href'],
                'company_address' => ['.bd-e5 .mid div:eq(0) tr:eq(1) td:eq(1) li span', 'html'],
                'name' => ['.firm_l .lan3_table tr:eq(0) td:eq(0)', 'html'],
                'registery_price' => ['.firm_l .lan3_table tr:eq(0) td:eq(1)', 'html'],
                'company_status' => ['.firm_l .lan3_table tr:eq(0) td:eq(3)', 'html'],
                'socialCreditCode' => ['.firm_info .info_list li:eq(0) span', 'html'],
                'info' => ['.firm_info .info_list li:eq(5) span', 'html'],
                'state' => $province
            ];
            $result[] = $this->setContent($html)->rules($rules)->query()->getData(function($item) {
                 $this->format($item, 'web', 'http://');
                 return $item;
            })->all();
        }
        $this->updateConfig(sprintf(self::PROVINCE_TPL, $province), $page +1);
        return $result;
    }
}