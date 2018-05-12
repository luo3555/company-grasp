<?php
namespace Lib\Resource;

abstract class Base
{
    protected $_config;

    protected $_headers = [];

    protected $_options = [];

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

    abstract protected function graspDateByKeyword($graspObject);

    public function execute()
    {
        $result = [];
        $relateInfo = $this->graspDateByKeyword($this->getGraspObject()->nameSaic);
        if (is_array($relateInfo)) {
            foreach ($relateInfo as $company) {
                // @TODO 如果，有点电话，网站都没有，就跳过这条记录
                $company['saicSysNo'] = $graspObject->saicSysNo;
                if (!empty($company['web'])) {
                    $webDomain = substr($company['web'], (stripos($company['web'], '.') + 1));
                    $domain = new \Lib\Domain();
                    $domainInfo = $domain->getInfo($webDomain);
                    $company = array_merge($company, $domainInfo);
                }
                $company = $this->getQq($company);
                $this->executeProcessDo($company);
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

    public function request($url, $headers=[], $options=[])
    {
        $headers = array_merge($headers, $this->_headers);
        $options = array_merge($options, $this->_options);
        $response = \Requests::get($url,$headers, $options);
        return $response->body;
    }

    public function setOption($key, $value)
    {
        $this->_options[$key] = $value;
    }

    public function setHeader($key, $value)
    {
        $this->_headers[$key] = $value;
    }

    public function getConfig($path)
    {
        return \Lib\Sqlite::getConfig($path);
    }
}