<?php
namespace Lib\Resource;

use QL\QueryList;

abstract class Graspbase extends Base
{
    protected $_config;

    protected $_graspObject;

    protected $_response = [];

    public function __construct()
    {
        $this->_config = $this->initConfig();
    }

    /**
     * [
     *   'limit' => 25, // 这个字段是必须的，每天只抓这么多次
     *   'url'   => 'http://www.qichacha.com', // required
     * ]
     */
    abstract protected function initConfig();

    public function setGraspObject($graspObject)
    {
        $this->_graspObject = $graspObject;
        return $this;
    }

    public function getGraspObject()
    {
        return $this->_graspObject;
    }

    abstract protected function graspDataByKeyword($graspObject);

    public function execute()
    {
        $result = [];
        $name = explode('公司', $this->getGraspObject()->nameSaic);
        $relateInfo = $this->graspDataByKeyword($name[0]);
        if (is_array($relateInfo)) {
            foreach ($relateInfo as $company) {
                // @TODO 如果，有点电话，网站都没有，就跳过这条记录
                $company['saicSysNo'] = $this->getGraspObject()->saicSysNo;
                if (!empty($company['web'])) {
                    $webDomain = substr($company['web'], (stripos($company['web'], '.') + 1));
                    $domain = new \Lib\Domain();
                    $domainInfo = $domain->getInfo($webDomain);
                    $company = array_merge($company, $domainInfo);
                }
                $company = $this->getQq($company);
                $this->executeProcessDo($company);
                if (empty($company['company_email']) && empty($company['company_phone']) && empty($company['web'])) {
                    continue;
                }
                $result[] = $company;
            }
        }
        $this->_response = $result;
        return $result;
    }

    protected function executeProcessDo($company)
    {
        // 做你想做的
    }

    public function getResponse()
    {
        return $this->_response;
    }

    public function getCount()
    {
        return count($this->_response);
    }

    protected function getQq($data)
    {
        foreach ($data as $field => $value) {
            if (preg_match('/_email/', $field)) {
                $value = strtolower($value);
                if (preg_match('/\qq\.com/', $value)) {
                    $data['qq'] = substr($value, 0, stripos($value, '@'));
                }
            }
        }
        return $data;
    }

    protected function _filter(&$array, $field, $defaultValue=null, $callback=null)
    {
        if (isset($array[$field])) {
            if (in_array($array[$field], \Lib\Filterlist::$limit)) {
                $array[$field] = $defaultValue;
            }
            if (!is_null($callback)) {
                $array = $this->$callback($field, $array, $defaultValue);
            }
        }
        return $array;
    }

    public function debug($data)
    {
        $enable = (int)self::getConfig('grasp/resource/debug');
        if ($enable) {
            print_r($data);
        }
    }
}