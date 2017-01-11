<?php
/**
 * Created by PhpStorm.
 * User: figo-007
 * Date: 2016/12/9
 * Time: 11:22
 */
namespace ApigilityVendorIntegrate\Service\EaseMob;

use Zend\ServiceManager\ServiceManager;
use ApigilityVendorIntegrate\Vendor\EaseMob\HxCall;

class EaseMobService
{
    /**
     * @var \ApigilityVendorIntegrate\Vendor\EaseMob\HxCall
     */
    protected $hxCall;

    protected $config;

    public function __construct(ServiceManager $services)
    {
        $config = $services->get('config');
        if (!isset($config['apigility-vendor-integrate']['ease-mob']['enable'])) throw new \Exception('没有配置环信', 500);
        else $config = $config['apigility-vendor-integrate']['ease-mob'];

        $this->config = $config;
        $this->hxCall = new HxCall($config);
    }

    /**
     * 创建环信帐户
     *
     * @param $username
     * @param $nickname
     * @return mixed
     */
    public function createAccount($username, $nickname)
    {
        $user = $this->hxCall->hx_user_info($username);

        if (empty($user)) return $this->hxCall->hx_register($username, $this->config['account_register_password'], $nickname);
        else return $user;
    }

    /**
     * 更新环信帐户的昵称
     *
     * @param $username
     * @param $nickname
     * @return string
     */
    public function updateNickname($username, $nickname)
    {
        $user = $this->hxCall->hx_user_info($username);
        if (!empty($user)) return $this->hxCall->hx_user_update_nickname($username, $nickname);
    }
}