<?php
/**
 * Created by PhpStorm.
 * User: figo-007
 * Date: 2016/12/9
 * Time: 11:23
 */
namespace ApigilityVendorIntegrate\Service\EaseMob;

use Zend\ServiceManager\ServiceManager;

class EaseMobServiceFactory
{
    public function __invoke(ServiceManager $services)
    {
        return new EaseMobService($services);
    }
}