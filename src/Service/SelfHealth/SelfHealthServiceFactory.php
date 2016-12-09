<?php
/**
 * Created by PhpStorm.
 * User: figo-007
 * Date: 2016/12/9
 * Time: 16:29
 */
namespace ApigilityVendorIntegrate\Service\SelfHealth;

use Zend\ServiceManager\ServiceManager;

class SelfHealthServiceFactory
{
    public function __invoke(ServiceManager $services)
    {
        return new SelfHealthService($services);
    }
}