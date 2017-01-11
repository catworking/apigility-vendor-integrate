<?php
namespace ApigilityVendorIntegrate\Vendor\EaseMob;

use Requests;
use Zend\Cache\Storage\Adapter\Filesystem as FilesystemCache;

class HxCall
{
    private $app_key;

    private $client_id;

    private $client_secret;

    private $url;

    protected $tokenCache;
    const TOKEN_CACHE_KEY = 'token';

    protected $userCache;
    const USER_CACHE_KEY_PREFIX = 'user_';

    /*
     * 获取APP管理员Token
     */
    function __construct($config)
    {
        $this->app_key = isset($config['app_key']) ? $config['app_key'] : '';
        $this->client_id = isset($config['client_id']) ? $config['client_id'] : '';
        $this->client_secret = isset($config['client_secret']) ? $config['client_secret'] : '';
        $this->url = isset($config['server_url']) ? $config['server_url'] : '';

        if (!file_exists($config['cache_path'])) {
            $old_mask = umask(0);
            mkdir($config['cache_path'], 0777, true);
            umask($old_mask);
        }

        $this->tokenCache = new FilesystemCache([
            'cache_dir'=>$config['cache_path'],
            'dir_permission'=>0777,
            'file_permission'=>0666,
            'namespace'=>'token',
            'ttl'=>3600
        ]);

        $this->userCache = new FilesystemCache([
            'cache_dir'=>$config['cache_path'],
            'dir_permission'=>0777,
            'file_permission'=>0666,
            'namespace'=>'user',
            'ttl'=>36000
        ]);

        if ($this->tokenCache->hasItem(self::TOKEN_CACHE_KEY)) {
            $this->token = $this->tokenCache->getItem(self::TOKEN_CACHE_KEY);
        } else {
            $url = $this->url . "/token";
            $data = array(
                'grant_type' => 'client_credentials',
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret
            );
            $rs = json_decode($this->curl($url, $data)->body, true);
            $this->token = $rs['access_token'];

            $this->tokenCache->addItem(self::TOKEN_CACHE_KEY, $this->token);
        }
    }
    /*
     * 注册IM用户(授权注册)
     */
    public function hx_register($username, $password, $nickname)
    {
        $url = $this->url . "/users";
        $data = array(
            'username' => $username,
            'password' => $password,
            'nickname' => $nickname
        );
        $header = array(
            'Content-Type'=>'application/json',
            'Authorization'=>'Bearer ' . $this->token
        );

        $cache_key = self::USER_CACHE_KEY_PREFIX.$username;

        $response = $this->curl($url, $data, $header, "POST");

        if ($response->status_code == 200 && $this->userCache->hasItem($cache_key)) {
            $this->userCache->setItem($cache_key, $response->body);
        }

        return $response->body;
    }
    /*
     * 给IM用户的添加好友
     */
    public function hx_contacts($owner_username, $friend_username)
    {
        $url = $this->url . "/users/${owner_username}/contacts/users/${friend_username}";
        $header = array(
            'Authorization'=>'Bearer ' . $this->token
        );
        return $this->curl($url, "", $header, "POST")->body;
    }
    /*
     * 解除IM用户的好友关系
     */
    public function hx_contacts_delete($owner_username, $friend_username)
    {
        $url = $this->url . "/users/${owner_username}/contacts/users/${friend_username}";
        $header = array(
            'Authorization'=>'Bearer ' . $this->token
        );
        return $this->curl($url, "", $header, "DELETE")->body;
    }
    /*
     * 查看好友
     */
    public function hx_contacts_user($owner_username)
    {
        $url = $this->url . "/users/${owner_username}/contacts/users";
        $header = array(
            'Authorization'=>'Bearer ' . $this->token
        );
        return $this->curl($url, "", $header, "GET")->body;
    }

    /* 发送文本消息 */
    public function hx_send($sender, $receiver, $msg)
    {
        $url = $this->url . "/messages";
        $header = array(
            'Authorization'=>'Bearer ' . $this->token
        );
        $data = array(
            'target_type' => 'users',
            'target' => array(
                '0' => $receiver
            ),
            'msg' => array(
                'type' => "txt",
                'msg' => $msg
            ),
            'from' => $sender,
            'ext' => array(
                'attr1' => 'v1',
                'attr2' => "v2"
            )
        );
        return $this->curl($url, $data, $header, "POST")->body;
    }
    /* 查询离线消息数 获取一个IM用户的离线消息数 */
    public function hx_msg_count($owner_username)
    {
        $url = $this->url . "/users/${owner_username}/offline_msg_count";
        $header = array(
            'Authorization'=>'Bearer ' . $this->token
        );
        return $this->curl($url, "", $header, "GET")->body;
    }

    /*
     * 获取IM用户[单个]
     */
    public function hx_user_info($username)
    {
        $cache_key = self::USER_CACHE_KEY_PREFIX.$username;

        if ($this->userCache->hasItem($cache_key)) {
            return $this->userCache->getItem($cache_key);
        } else {
            $url = $this->url . "/users/${username}";
            $header = array(
                'Authorization'=>'Bearer ' . $this->token
            );
            $response = $this->curl($url, "", $header, "GET");
            if ($response->status_code == 404) {
                $this->userCache->addItem($cache_key, '');
                return null;
            }
            else {
                $this->userCache->addItem($cache_key, $response->body);
                return $response->body;
            }
        }


    }
    /*
     * 获取IM用户[批量]
     */
    public function hx_user_infos($limit)
    {
        $url = $this->url . "/users?${limit}";
        $header = array(
            'Authorization'=>'Bearer ' . $this->token
        );
        return $this->curl($url, "", $header, "GET")->body;
    }
    /*
     * 重置IM用户密码
     */
    public function hx_user_update_password($username, $newpassword)
    {
        $url = $this->url . "/users/${username}/password";
        $header = array(
            'Authorization'=>'Bearer ' . $this->token
        );
        $data['newpassword'] = $newpassword;
        return $this->curl($url, $data, $header, "PUT")->body;
    }

    /*
     * 删除IM用户[单个]
     */
    public function hx_user_delete($username)
    {
        $url = $this->url . "/users/${username}";
        $header = array(
            'Authorization'=>'Bearer ' . $this->token
        );
        return $this->curl($url, "", $header, "DELETE")->body;
    }
    /*
     * 修改用户昵称
     */
    public function hx_user_update_nickname($username, $nickname)
    {
        $url = $this->url . "/users/${username}";
        $header = array(
            'Authorization'=>'Bearer ' . $this->token
        );
        $data['nickname'] = $nickname;
        return $this->curl($url, $data, $header, "PUT")->body;
    }
    /*
     *
     * curl
     */
    private function curl($url, $data, $header = [], $method = "POST")
    {
        $headers = array_merge($header,array('Content-Type' => 'application/json'));

        $response = null;
        switch ($method) {
            case 'GET':
                $response = Requests::post($url,$headers,json_encode($data));
                break;

            case 'POST':
                $response = Requests::post($url,$headers,json_encode($data));
                break;

            case 'PUT':
                $response = Requests::put($url,$headers,json_encode($data));
                break;

            default:
                throw  new \Exception('未处理的HTTP METHOD', 500);
        }

        return $response;
    }
}

