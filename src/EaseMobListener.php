<?php
/**
 * Created by PhpStorm.
 * User: figo-007
 * Date: 2016/12/9
 * Time: 11:02
 */
namespace ApigilityVendorIntegrate;

use ApigilityUser\Service\IdentityService;
use ApigilityUser\Service\UserService;
use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\EventManager\ListenerAggregateTrait;
use Zend\EventManager\EventInterface;
use Zend\ServiceManager\ServiceManager;
use ApigilityUser\DoctrineEntity\User;

class EaseMobListener
{
    protected $listeners = [];

    private $services;

    /**
     * @var \ApigilityVendorIntegrate\Service\EaseMob\EaseMobService
     */
    private $easeMobService;

    public function __construct(ServiceManager $services)
    {
        $this->services = $services;
    }

    public function attachToIdentityService(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(IdentityService::EVENT_IDENTITY_CREATED, [$this, 'createAccount'], $priority);
    }

    public function attachToIdentityServiceEventGettingList(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(IdentityService::EVENT_GETTING_IDENTITIES, [$this, 'createAccountWhenLogin'], $priority);
    }

    public function attachToUserService(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(UserService::EVENT_USER_NICKNAME_UPDATE, [$this, 'updateAccountNickname'], $priority);

    }

    public function createAccount(EventInterface $e)
    {
        $params = $e->getParams();

        try {
            return $this->getEaseMobService()->createAccount($params['user_id'], '用户'.$params['user_id']);
        } catch (\Exception $exception) {
            return false;
        }
    }

    public function createAccountWhenLogin(EventInterface $e)
    {
        $params = $e->getParams();
        $doctrine_paginator_adapter = $params['doctrine_paginator_adapter'];

        // 每次登录时者尝试创建环信帐号，防止注册时注册失败，后面将无法再创建
        try {

            if ($doctrine_paginator_adapter->count() == 1) {
                $identity = $doctrine_paginator_adapter->getItems(0,1)[0];
                $this->getEaseMobService()->createAccount($identity->getId(), '用户'.$identity->getId());
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    public function updateAccountNickname(EventInterface $e)
    {
        $params = $e->getParams();

        try {
            return $this->getEaseMobService()->updateNickname($params['user']->getId(), $params['user']->getNickname());
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @return \ApigilityVendorIntegrate\Service\EaseMob\EaseMobService
     */
    private function getEaseMobService()
    {
        if (empty($this->easeMobService)) $this->easeMobService = $this->services->get('ApigilityVendorIntegrate\Service\EaseMob\EaseMobService');

        return $this->easeMobService;
    }
}