<?php
namespace Lib\Resource;

use QL\QueryList;

class Base
{
    protected $_headers = [];

    protected $_options = [];

    public function request($url, $headers=[], $options=[])
    {
        $headers = array_merge($headers, $this->_headers);
        $options = array_merge($options, $this->_options);
        $response = \Requests::get($url,$headers, $options);
        return $response->body;
    }

    public function getCookies($options)
    {
        /** @var Requests_Cookie_Jar $cookies */
        return new \Requests_Cookie_Jar($options);
    }

    public function setContent($input, $mod='html')
    {
        switch ($mod) {
            case 'url':
                return QueryList::get($input);
                break;
            
            default:
                return QueryList::html($input);
                break;
        }
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