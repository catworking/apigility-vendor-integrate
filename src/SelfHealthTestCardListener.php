<?php
/**
 * Created by PhpStorm.
 * User: figo-007
 * Date: 2016/11/21
 * Time: 15:58
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

class SelfHealthTestCardListener implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    private $services;

    /**
     * @var \ApigilityVendorIntegrate\Service\SelfHealth\SelfHealthService
     */
    protected $selfHealthService;

    public function __construct(ServiceManager $services)
    {
        $this->services = $services;
    }

    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(UserService::EVENT_USER_CREATED, [$this, 'createTestCard'], $priority);
    }

    public function createTestCard(EventInterface $e)
    {
        $params = $e->getParams();

        // 创建测评卡
        $this->selfHealthService = $this->services->get('ApigilityVendorIntegrate\Service\SelfHealth\SelfHealthService');

        $this->selfHealthService->createTestCard($params['user']);
    }
}