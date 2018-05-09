<?php 
namespace Lib;

class Objectdata
{
    protected $_data;

    public function setData($data)
    {
        if (!is_array($data)) {
            $data = (array)$data;
        }
        foreach ($data as $key => $value)
        {
            $this->_data[$key] = $value;
        }
        return $this;
    }

    public function addData($key, $data)
    {
        $this->_data[$key] = $data;
        return $this;
    }

    public function getData($key=null)
    {
        if (is_null($key)) {
            return $this->_data;
        }
        if (isset($this->_data[$key])) {
            return $this->_data[$key];
        }
        return null;
    }

    public function getDataSetDefaultValue($key, $defaltValue)
    {
        $value = $this->getData($key);
        return $value ? $value : $defaltValue;
    }

    public function reset()
    {
        $this->_data = [];
        return $this;
    }
}