<?php
/**
 * Created by PhpStorm.
 * User: figo-007
 * Date: 2016/12/9
 * Time: 16:29
 */
namespace ApigilityVendorIntegrate\Service\SelfHealth;

use ApigilityUser\DoctrineEntity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Zend\ServiceManager\ServiceManager;
use Requests;
use Zend\Cache\Storage\Adapter\Filesystem as FilesystemCache;
use ApigilityVendorIntegrate\DoctrineEntity\SelfHealth\TestCard;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrineToolPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrinePaginatorAdapter;

class SelfHealthService
{
    const CACHE_KEY = 'self-health-token';

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ServiceManager
     */
    protected $serviceManager;

    /**
     * @var \ApigilityUser\Service\IdentityService
     */
    protected $identityService;

    /**
     * @var \ApigilityUser\Service\UserService
     */
    protected $userService;

    protected $config;

    protected $tokenCache;



    public function __construct(ServiceManager $services)
    {
        $this->serviceManager = $services;
        $this->em = $services->get('Doctrine\ORM\EntityManager');

        $config = $services->get('config');
        if (!$config['apigility-vendor-integrate']['self-health']['enable']) throw new \Exception('没有配置 北京燕鑫康达科技有限公司 测评卡服务', 500);
        else $config = $config['apigility-vendor-integrate']['self-health'];

        $this->config = $config;

        if (!file_exists($this->config['token_cache_path'])) {
            $old_mask = umask(0);
            mkdir($this->config['token_cache_path'], 0777, true);
            umask($old_mask);
        }
        $this->tokenCache = new FilesystemCache([
            'cache_dir'=>$this->config['token_cache_path'],
            'dir_permission'=>0777,
            'file_permission'=>0666,
        ]);
        $this->tokenCache->getOptions()->setTtl(36000);
    }

    /**
     * 获取一个有效的token
     */
    public function getAccessToken()
    {
        if ($this->tokenCache->hasItem(self::CACHE_KEY)) {
            return $this->tokenCache->getItem(self::CACHE_KEY);
        } else {
            $response = Requests::post($this->config['server_url'].'/get_token?format=json',array(),[
                'apikey' => $this->config['api_key'],
                'secret' => $this->config['secret']
            ]);

            if ($response->success) {
                $token_object = json_decode($response->body);

                if (empty($token_object)) throw new \Exception('第三方服务器响应出错', 500);
                else if ($token_object->res != 'SUCCESS') throw new \Exception('第三方服务器认证失败'.$token_object->error->message, 500);
                else {
                    // 保存到文件缓存
                    $this->tokenCache->addItem(self::CACHE_KEY, $token_object->data->token);

                    return $token_object->data->token;
                }
            } else throw new \Exception('第三方服务器响应出错', 500);
        }
    }

    /**
     * 从服务器生成一个测试卡
     *
     * @param $user_id
     * @param $phone
     * @return mixed
     * @throws \Exception
     */
    public function generateTestCardId($user_id, $phone)
    {
        // 先查测评卡是否已存在服务器
        $card_id = $this->checkTestCard($user_id);
        if (!empty($card_id)) return $card_id;

        // 不存在，尝试新建一个
        $response = Requests::post($this->config['server_url'].'/card_v2?format=json',array(),[
            'uniqueId' => $user_id,
            'mobile' => $phone,
            'token' => $this->getAccessToken()
        ]);

        if ($response->success) {
            $token_object = json_decode($response->body);

            if (empty($token_object)) throw new \Exception('第三方服务器响应出错', 500);
            else if ($token_object->res != 'SUCCESS') throw new \Exception('第三方服务器认证失败:['.$token_object->error->code.']'.$token_object->error->message, 500);
            else {
                return $token_object->data;
            }
        } else throw new \Exception('第三方服务器响应出错', 500);
    }

    /**
     * 创建一个测评卡
     *
     * @param User $user
     * @return TestCard
     */
    public function createTestCard(User $user)
    {
        $this->identityService = $this->serviceManager->get('ApigilityUser\Service\IdentityService');
        $identity = $this->identityService->getIdentity($user->getId());

        $test_card = new TestCard();
        $test_card->setUser($user);
        $test_card->setSelfHealthCardId($this->generateTestCardId($user->getId(), $identity->getPhone()));

        $this->em->persist($test_card);
        $this->em->flush();

        return $test_card;
    }

    public function getTestCards($params)
    {
        $qb = new QueryBuilder($this->em);
        $qb->select('tc')->from('ApigilityVendorIntegrate\DoctrineEntity\SelfHealth\TestCard', 'tc');

        $where = '';
        if (isset($params->user_id)) {
            $qb->innerJoin('tc.user', 'u');
            if (!empty($where)) $where .= ' AND ';
            $where .= 'u.id = :user_id';
        }

        if (!empty($where)) {
            $qb->where($where);
            if (isset($params->user_id)) $qb->setParameter('user_id', $params->user_id);
        }

        $doctrine_paginator = new DoctrineToolPaginator($qb->getQuery());

        // 如果找不到测评卡,尝试从服务器生成
        if ($doctrine_paginator->count() == 0) {
            $this->userService = $this->serviceManager->get('ApigilityUser\Service\UserService');
            $this->createTestCard($this->userService->getUser($params->user_id));
        }

        return new DoctrinePaginatorAdapter($doctrine_paginator);
    }

    public function makeLoginUrl($card_id)
    {
        return $this->config['login_url'].'?token='.$this->getAccessToken().'&cardId='.$card_id;
    }

    private function checkTestCard($user_id)
    {
        $response = Requests::request($this->config['server_url'].'/check_card', array(), [
            'uniqueId' => $user_id,
            'format' => 'json',
            'token' => $this->getAccessToken()
        ], Requests::GET, [
            'data_format'=> 'query'
        ]);

        if ($response->success) {
            $token_object = json_decode($response->body);

            if (empty($token_object)) throw new \Exception('第三方服务器响应出错', 500);
            else if ($token_object->res != 'SUCCESS') {
                if ((int)$token_object->error->code = 1) return null;
                else throw new \Exception('第三方服务器返回错误:['.$token_object->error->code.']'.$token_object->error->message, 500);
            } else {
                return $token_object->data->cardId;
            }
        } else throw new \Exception('第三方服务器响应出错', 500);
    }
}