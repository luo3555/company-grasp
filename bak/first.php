<?php
/**
 * http://requests.ryanmccue.info/
 * https://querylist.cc/
 */
require 'vendor/autoload.php';
use QL\QueryList;

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

/***********************************************************************************************************************************
海关信息 ***************************************************************************************************************************
************************************************************************************************************************************
************************************************************************************************************************************
***********************************************************************************************************************************/

/**
 * 抓取公司名字和机构代码
 */
class CompanyBaseInfo
{
    const URL = 'http://credit.customs.gov.cn/ccppAjax/queryLostcreditList.action';

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
        $cacheKey = $currentPage .'-' . $pageSize . '.txt';

        $file = new File();
        $info = $file->getData($cacheKey);
        if (is_null($info)) {
            $response = Requests::post(self::URL, $this->getHeaders(),[
                'ccppListQueryRequest.casePage.curPage' => $currentPage,
                'ccppListQueryRequest.casePage.pageSize' => $pageSize,
                'ccppListQueryRequest.manaType' => 'C'
            ]);

            $data = json_decode($response->body, true);

            $info = [];
            if ($data['responseResult']['responseCode']===0) {
                $info = $data['responseResult']['responseData']['copInfoResultList'];
                $file->add($cacheKey, $info);
            }
        }
        return $info;
    }
}

/***********************************************************************************************************************************
QiChaCha ***************************************************************************************************************************
************************************************************************************************************************************
************************************************************************************************************************************
***********************************************************************************************************************************/

/**
 * 获取公司法人信息
 */
class QiChaCha
{
    const URL = 'http://www.qichacha.com';

    /**
     * 失效后记得去主页cookie里取
     */
    const UM_distinctid = '16325fe2b315c8-04ddf93633b617-33657f07-13c680-16325fe2b329ec';

    public function graspData($companyName)
    {
        /** @var Requests_Cookie_Jar $cookies */
        $cookies = new Requests_Cookie_Jar([
            'UM_distinctid' => self::UM_distinctid
        ]);

        $searchUrl = sprintf(self::URL . '/search?key=%s', $companyName);
        $response = Requests::get(
            $searchUrl,
            [],
            [
                'cookies' => $cookies
            ]
        );
        $html = $response->body;


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
            $email = trim($email[1]);

            // 电话
            $rules = [
                'text' => [
                    'p.m-t-xs:eq(1) span', 'text'
                ]
            ];
            $res = QueryList::html($html)->rules($rules)->query()->getData()->first();
            $phone = explode('：', $res['text']);
            $phone = trim($phone[1]);

            // 获取公司营业范围
            $baseUrl = self::URL;
            $rules = [
                'url' => [
                    'table tbody td:eq(1) a', 'href'
                ]
            ];
            $url = QueryList::html($html)->rules($rules)->query()->getData(function($item) use ($baseUrl) {
                return $baseUrl . $item['url'];
            })->first();


            /**
             * 详情页面获取经营范围和网站
             * 
             */
            $response = Requests::get(
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
                'registery_price' => QueryList::html($html)->find('p.m-t-xs')->eq(0)->find('span')->eq(0)->html(),
                'email' => $email,
                'phone' => $phone,
                'info' => $info,
                'web' => $web,
                'status' => ''
            ];
        }
        return $result;
    }
}

/***********************************************************************************************************************************
域名信息 ***************************************************************************************************************************
************************************************************************************************************************************
************************************************************************************************************************************
***********************************************************************************************************************************/
class Domain
{
    const URL = 'http://whois.xinnet.com/php/domain_seo.php?domain=%s';

    protected $_data = [];

    public function getInfo($domain)
    {
        $response = Requests::get(sprintf(self::URL, $domain));
        $json = json_decode($response->body, true);
        $whoisInfo = $json['whoisInfoStr'];
        $info = explode('</br>', $whoisInfo);
        
        foreach ($this->_filterList() as $field => $item) {
            if (isset($info[$item['idx']])) {
                $this->_data[$field] = $this->_filter($info[$item['idx']], $item);
            }
        }
        print_r($this->_data);
        return $this->_data;
    }

    protected function _filter($string, $rules)
    {
        $string = trim(str_replace($rules['string'], '', $string));
        if (isset($rules['rules'])) {
            foreach ($rules['rules'] as $limit) {
                if (preg_match('/.*(' . $limit . '){1}.*?/', $string)) {
                    $string = '';
                    break;
                }
            }
        }
        return $string;
    }

    protected function _filterList()
    {
        return [
            'domain' => ['idx' => 0, 'string' => 'Domain Name: '],
            'reg_url' => ['idx' => 3, 'string' => 'Registrar URL: '],
            'reg_name' => ['idx' => 13, 'string' => 'Registrant Name: '],
            'reg_organ' => ['idx' => 14, 'string' => 'Registrant Organization: '],
            'reg_street' => ['idx' => 15, 'string' => 'Registrant Street: '],
            'reg_city' => ['idx' => 16, 'string' => 'Registrant City: '],
            'reg_state' => ['idx' => 17, 'string' => 'Registrant State/Province: '],
            'reg_post' => ['idx' => 18, 'string' => 'Registrant Postal Code: '],
            'reg_country' => ['idx' => 19, 'string' => 'Registrant Country: '],
            'reg_phone' => ['idx' => 20, 'string' => 'Registrant Phone: ', 'rules' => ['86.57185022088']],
            'reg_email' => ['idx' => 24, 'string' => 'Registrant Email: ', 'rules' => ['YinSiBaoHu.AliYun.com']],
            'admin_phone' => ['idx' => 33, 'string' => 'Admin Phone: ', 'rules' => ['86.57185022088']],
            'admin_email' => ['idx' => 37, 'string' => 'Admin Email: ', 'rules' => ['YinSiBaoHu.AliYun.com']],
            'tech_phone' => ['idx' => 46, 'string' => 'Tech Phone: ', 'rules' => ['86.57185022088']],
            'tech_email' => ['idx' => 50, 'string' => 'Tech Email: ', 'rules' => ['YinSiBaoHu.AliYun.com']]
        ];
    }
}

//$domain = new Domain();
//$domain->getInfo('luoluo3.top');


$qiChaCha = new QiChaCha();
$result = $qiChaCha->graspData('甘肃华昌达贸易有限公司');
print_r($result);

/**



$start = time();
echo 'Start Time:' . date('Y-m-d H:i:s', $start) . PHP_EOL;
$cache = new File();
$obj = new CompanyBaseInfo();
$list = $obj->getBaseInfo();
$limit = 5;

$offset = $cache->getOffset();
$graspData = $cache->getComanyData([]);
foreach ($list as $idx => $item) {
    if ($offset>$idx) {
        continue;
    }

    $companyName = $item['nameSaic'];
    if ($idx>$limit) {
        break;
    }

    $qiChaCha = new QiChaCha();
    $result = $qiChaCha->graspData($companyName);
    $graspData = array_merge($graspData, $result);

    // 如果抓取结果为空，自动切换搜索网站
    if (empty($result)) {

    }

    

    // 如果所有抓取结果都是空的
    if ($result) {
        break;
    }
    $offset = $idx;
}
$cache->setComanyData($graspData);
$cache->setOffset($offset);

$end = time();
echo 'End Time:' . date('Y-m-d H:i:s', $end) . PHP_EOL;
echo 'exec:' . ($end-$start)/60 . PHP_EOL;







