<?php
namespace Lib;

use QL\QueryList;

/**
 * 抓取公司名字和机构代码
 */
class Customs extends Objectdata
{
    protected $_url;

    public function setUrl($url=null)
    {
        if (is_null($url)) {
            $this->_url = 'http://credit.customs.gov.cn/ccppAjax/queryLostcreditList.action';
        }
        $this->_url = $url;
        return $this;
    }

    public function getUrl()
    {
        return $this->_url;
    }

    public function getHeaders()
    {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json'
        ];
    }

    public function getBaseInfo($currentPage=1, $pageSize=20)
    {
        $response = \Requests::post($this->getUrl(), $this->getHeaders(),[
            'ccppListQueryRequest.casePage.curPage' => $currentPage,
            'ccppListQueryRequest.casePage.pageSize' => $pageSize,
            'ccppListQueryRequest.manaType' => 'C'
        ]);

        $data = json_decode($response->body, true);

        $info = [];
        if ($data['responseResult']['responseCode']===0) {
            $this->_data['list'] = $data['responseResult']['responseData']['copInfoResultList'];
            $this->_data['totalPage'] = $data['responseResult']['responseData']['casePage']['totalPages'];
        }
        return $this->_data['list'];
    }

    public function getBaseInfoTotalPage()
    {
        return $this->_data['totalPage'];
    }
}