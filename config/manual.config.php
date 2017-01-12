<?php
/**
 * Created by PhpStorm.
 * User: figo-007
 * Date: 2016/11/30
 * Time: 16:32
 */
return [
    'apigility-vendor-integrate' => [
        'ease-mob' => [
            'enable'=>false,
            'app_key'=>'',
            'client_id'=>'',
            'client_secret'=>'',
            'server_url'=>'',
            'account_register_password'=>'',
            'cache_path'=>dirname(__FILE__).'/../../../data/ApigilityVendorIntegrate/EaseMob/Cache'
        ],
        'self-health' => [
            'enable'=>false,
            'api_key'=>'',
            'secret'=>'',
            'server_url'=>'http://new.selfhealth.cn/api/AssessApi',
            'login_url' => 'http://new.selfhealth.cn/user/#/login',
            'token_cache_path' => dirname(__FILE__).'/../../../data/ApigilityVendorIntegrate/SelfHealth'
        ],
        'ju-he' => [
            'enable'=>false,
            'app_key'=>'',
        ],
    ],
];