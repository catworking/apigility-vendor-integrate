<?php
/**
 * Created by PhpStorm.
 * User: figo-007
 * Date: 2016/11/16
 * Time: 14:52
 */
return [
    'service_manager' => array(
        'factories' => array(
            'ApigilityVendorIntegrate\Service\SelfHealth\SelfHealthService' => 'ApigilityVendorIntegrate\Service\SelfHealth\SelfHealthServiceFactory',
            'ApigilityVendorIntegrate\Service\EaseMob\EaseMobService' => 'ApigilityVendorIntegrate\Service\EaseMob\EaseMobServiceFactory',
        ),
    )
];