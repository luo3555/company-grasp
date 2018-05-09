<?php
namespace Lib;

class Domain
{
    const URL = 'http://whois.xinnet.com/php/domain_seo.php?domain=%s';

    protected $_data = [];

    public function getInfo($domain)
    {
        $response = \Requests::get(sprintf(self::URL, $domain));
        $json = json_decode($response->body, true);

        if (isset($json['whoisInfoStr'])) {
            $whoisInfoStr = $json['whoisInfoStr'];
            $pattern = '/((?<=\<\/br\>)\s?(Registrant{1}\s{1}|Admin{1}\s{1}|Tech{1}\s{1})\S?.*?(?=\<\/br\>))/';
            preg_match_all($pattern, $whoisInfoStr, $whoisInfo);
            if (isset($whoisInfo[0])) {
                foreach ($whoisInfo[0] as $item) {
                    foreach ($this->_mappingRules() as $string => $rule) {
                        $access = true;
                        if (preg_match('/' . str_replace('/', '\/', $string) . '/', $item)) {
                            if (isset($rule['exclude'])) {
                                foreach ($rule['exclude'] as $limit) {
                                    if (preg_match('/' . $limit . '/', $item)) {
                                        $value = '';
                                        $access = false;
                                        break;
                                    }
                                }
                            }
                            if ($access) {
                                $value = trim(str_replace($string, '', $item));
                            }
                            $this->_data[$rule['field']] = $value; 
                            break;
                        }
                    }
                }
            }
        }
        return $this->_data;
    }

    protected function _mappingRules()
    {
        return [
            'Domain Name: ' => [
                'field' => 'domain'
            ],
            'Registrar URL: ' => [
                'field' => 'reg_url'
            ],
            'Registrant Name: ' => [
                'field' => 'reg_name'
            ],
            'Registrant Organization: ' => [
                'field' => 'reg_organ'
            ],
            'Registrant Street: ' => [
                'field' => 'reg_street'
            ],
            'Registrant City: ' => [
                'field' => 'reg_city'
            ],
            'Registrant State/Province: ' => [
                'field' => 'reg_state'
            ],
            'Registrant Postal Code: ' => [
                'field' => 'reg_post'
            ],
            'Registrant Country: ' => [
                'field' => 'reg_country'
            ],
            'Registrant Phone: ' => [
                'field' => 'reg_phone',
                'exclude' => ['86.57185022088']
            ],
            'Registrant Email: ' => [
                'field' => 'reg_email',
                'exclude' => ['YinSiBaoHu.AliYun.com']
            ],
            'Admin Phone: ' => [
                'field' => 'admin_phone',
                'exclude' => ['86.57185022088']
            ],
            'Admin Email: ' => [
                'field' => 'admin_email',
                'exclude' => ['YinSiBaoHu.AliYun.com']
            ],
            'Tech Phone: ' => [
                'field' => 'tech_phone',
                'exclude' => ['86.57185022088']
            ],
            'Tech Email: ' => [
                'field' => 'tech_email',
                'exclude' => ['YinSiBaoHu.AliYun.com']
            ]
        ];
    }
}









