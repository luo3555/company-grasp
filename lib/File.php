<?php
namespace Lib;

class File
{
    public function add($fileName, $data)
    {
        if (file_exists($fileName) && !is_dir($fileName)) {
            $fileData = file_get_contents($fileName);
            $fileData = unserialize($fileData);
            $fileData = array_merge($fileData, $data);
        }
        $fp = fopen($fileName, 'w');
        fwrite($fp, serialize($data));
        fclose($fp);
    }

    public function setOffset($offset)
    {
        $this->add('offset.txt', $offset);
    }

    public function getOffset()
    {
        return $this->getData('offset.txt', 0);
    }

    public function addCompany($data)
    {
        $this->add('company.txt', $data);
    }

    public function getComany($defaultValue=null)
    {
        return $this->getData('company.txt', $defaultValue);
    }

    public function setComanyData($data)
    {
        $this->add('companyInfo.txt', $data);
    }

    public function getComanyData($defaultValue=null)
    {
        return $this->getData('companyInfo.txt', $defaultValue);
    }

    public function getData($fileName, $defaultValue=null)
    {
        if (file_exists($fileName) && !is_dir($fileName)) {
            return unserialize(file_get_contents($fileName));
        }
        return $defaultValue;
    }
}