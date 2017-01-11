<?php
namespace ApigilityVendorIntegrate;

use Zend\Config\Config;
use Zend\Mvc\MvcEvent;
use ZF\Apigility\Provider\ApigilityProviderInterface;

class Module implements ApigilityProviderInterface
{
    public function getConfig()
    {
        $doctrine_config = new Config(include __DIR__ . '/config/doctrine.config.php');
        $service_config = new Config(include __DIR__ . '/config/service.config.php');
        $manual_config = new Config(include __DIR__ . '/config/manual.config.php');

        $module_config = new Config(include __DIR__ . '/config/module.config.php');
        $module_config->merge($doctrine_config);
        $module_config->merge($service_config);
        $module_config->merge($manual_config);

        return $module_config;
    }

    public function getAutoloaderConfig()
    {
        return [
            'ZF\Apigility\Autoloader' => [
                'namespaces' => [
                    __NAMESPACE__ => __DIR__ . '/src',
                ],
            ],
        ];
    }

    public function onBootstrap(MvcEvent $e)
    {
        // This method is called once the MVC bootstrapping is complete
        $application = $e->getApplication();
        $services    = $application->getServiceManager();

        $identityServiceEvents = $services->get('ApigilityUser\Service\IdentityService')->getEventManager();
        $userServiceEvents = $services->get('ApigilityUser\Service\UserService')->getEventManager();

        // 创建健康测评卡
        $config = $services->get('config');
        if ($config['apigility-vendor-integrate']['self-health']['enable']) {
            $selfHealth_listener = new SelfHealthTestCardListener($services);
            $selfHealth_listener->attach($userServiceEvents);
        }

        // 创建环信帐号
        $config = $services->get('config');
        if ($config['apigility-vendor-integrate']['ease-mob']['enable']) {
            $easeMob_listener = new EaseMobListener($services);
            $easeMob_listener->attachToIdentityService($identityServiceEvents);
            $easeMob_listener->attachToIdentityServiceEventGettingList($identityServiceEvents);
            $easeMob_listener->attachToUserService($userServiceEvents);
        }
    }
}
