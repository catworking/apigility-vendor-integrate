<?php
return [
    'service_manager' => [
        'factories' => [
            \ApigilityVendorIntegrate\V1\Rest\SelfHealthTestCard\SelfHealthTestCardResource::class => \ApigilityVendorIntegrate\V1\Rest\SelfHealthTestCard\SelfHealthTestCardResourceFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'apigility-vendor-integrate.rest.self-health-test-card' => [
                'type' => 'Segment',
                'options' => [
                    'route' => '/vendor-integrate/self-health/test-card[/:test_card_id]',
                    'defaults' => [
                        'controller' => 'ApigilityVendorIntegrate\\V1\\Rest\\SelfHealthTestCard\\Controller',
                    ],
                ],
            ],
        ],
    ],
    'zf-versioning' => [
        'uri' => [
            0 => 'apigility-vendor-integrate.rest.self-health-test-card',
        ],
    ],
    'zf-rest' => [
        'ApigilityVendorIntegrate\\V1\\Rest\\SelfHealthTestCard\\Controller' => [
            'listener' => \ApigilityVendorIntegrate\V1\Rest\SelfHealthTestCard\SelfHealthTestCardResource::class,
            'route_name' => 'apigility-vendor-integrate.rest.self-health-test-card',
            'route_identifier_name' => 'test_card_id',
            'collection_name' => 'self_health_test_card',
            'entity_http_methods' => [],
            'collection_http_methods' => [
                0 => 'GET',
            ],
            'collection_query_whitelist' => [
                0 => 'user_id',
            ],
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => \ApigilityVendorIntegrate\V1\Rest\SelfHealthTestCard\SelfHealthTestCardEntity::class,
            'collection_class' => \ApigilityVendorIntegrate\V1\Rest\SelfHealthTestCard\SelfHealthTestCardCollection::class,
            'service_name' => 'SelfHealthTestCard',
        ],
    ],
    'zf-content-negotiation' => [
        'controllers' => [
            'ApigilityVendorIntegrate\\V1\\Rest\\SelfHealthTestCard\\Controller' => 'HalJson',
        ],
        'accept_whitelist' => [
            'ApigilityVendorIntegrate\\V1\\Rest\\SelfHealthTestCard\\Controller' => [
                0 => 'application/vnd.apigility-vendor-integrate.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ],
        ],
        'content_type_whitelist' => [
            'ApigilityVendorIntegrate\\V1\\Rest\\SelfHealthTestCard\\Controller' => [
                0 => 'application/vnd.apigility-vendor-integrate.v1+json',
                1 => 'application/json',
            ],
        ],
    ],
    'zf-hal' => [
        'metadata_map' => [
            \ApigilityVendorIntegrate\V1\Rest\SelfHealthTestCard\SelfHealthTestCardEntity::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'apigility-vendor-integrate.rest.self-health-test-card',
                'route_identifier_name' => 'test_card_id',
                'hydrator' => \Zend\Hydrator\ClassMethods::class,
            ],
            \ApigilityVendorIntegrate\V1\Rest\SelfHealthTestCard\SelfHealthTestCardCollection::class => [
                'entity_identifier_name' => 'id',
                'route_name' => 'apigility-vendor-integrate.rest.self-health-test-card',
                'route_identifier_name' => 'test_card_id',
                'is_collection' => true,
            ],
        ],
    ],
];
