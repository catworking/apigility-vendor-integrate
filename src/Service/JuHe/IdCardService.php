<?php
/**
 * Created by PhpStorm.
 * User: figo-007
 * Date: 2017/1/11
 * Time: 19:34:19
 */
namespace ApigilityVendorIntegrate\Service\JuHe;

use Zend\ServiceManager\ServiceManager;
use Requests;

class IdCardService
{

    protected $config;

    public function __construct(ServiceManager $services)
    {
        $config = $services->get('config');
        if (!$config['apigility-vendor-integrate']['ju-he']['enable']) throw new \Exception('没有配置juhe.cn服务', 500);
        else $config = $config['apigility-vendor-integrate']['ju-he'];

        $this->config = $config;
    }

    /**
     * 查询身份证信息
     *
     * @param $id_card_number
     * @return mixed
     * @throws \Exception
     */
    public function getIdCardInfo($id_card_number)
    {
        $url = 'http://apis.juhe.cn/idcard/index';
        $response = Requests::get($url.'?dtype=json&key='.$this->config['app_key'].'&cardno='.$id_card_number);

        if ($response->status_code === 200) {
            return $response->body;
        } else throw new \Exception('服务没有正常响应', 500);
    }
}