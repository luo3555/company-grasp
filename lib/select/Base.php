<?php
namespace Lib\Select;

use Lib\Filterlist;

abstract class Base
{
    protected $_config;

    protected $_headers = [];

    protected $_options = [];

    public function __construct()
    {
        $this->_config = $this->initConfig();
    }

    abstract public function graspData($company);

    /**
     * [
     *   'limit' => 25, // 这个字段是必须的，每天只抓这么多次
     *   'url'   => 'http://www.qichacha.com', // required
     * ]
     */
    abstract protected function initConfig();

    public function run()
    {
        // 根据 limit 从 list 表获取限定数量的需要抓取的数据
        $list = \Lib\Sqlite::getListRecored($this->_config['limit']);

        foreach ($list as $item) {
            //echo $item['nameSaic'] . PHP_EOL;
            $relateInfo = $this->graspData($item['nameSaic']);
            $hasResult = false;
            if (is_array($relateInfo)) {
                foreach ($relateInfo as $company) {
                    $company['saicSysNo'] = $item['saicSysNo'];
                    if (!empty($company['web'])) {
                        $webDomain = substr($company['web'], (stripos($company['web'], '.') + 1));
                        $domain = new \Lib\Domain();
                        $domainInfo = $domain->getInfo($webDomain);
                        $company = array_merge($company, $domainInfo);
                    }
                    $company = $this->getQq($company);
                    $id = (int)\Lib\Sqlite::addDetailRecord($company);
                    $hasResult = $id > 0 ? true : false ;
                }
            }
            if (empty($relateInfo)) {
                $configPrefix = strtolower(substr(get_class($this), (strripos(get_class($this), '\\')+1)));
                //\Lib\Sqlite::updateConfig($configPrefix . '/run/allow', 0);
                break;
            }
            if ($hasResult) {
                \Lib\Sqlite::updateListRecordStatus($item['id'], 'c');
            }
        }
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

    public function getConfig($path)
    {
        return \Lib\Sqlite::getConfig($path);
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
        return \Requests::get($url,$headers, $options)->body;
    }
}