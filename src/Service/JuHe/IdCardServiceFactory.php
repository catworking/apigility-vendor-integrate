<?php
/**
 * Created by PhpStorm.
 * User: figo-007
 * Date: 2017/1/11
 * Time: 19:34:29
 */
namespace ApigilityVendorIntegrate\Service\JuHe;

use Zend\ServiceManager\ServiceManager;

class IdCardServiceFactory
{
    public function __invoke(ServiceManager $services)
    {
        return new IdCardService($services);
    }
}