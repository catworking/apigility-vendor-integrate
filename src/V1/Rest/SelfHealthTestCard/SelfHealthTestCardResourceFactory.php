<?php
namespace ApigilityVendorIntegrate\V1\Rest\SelfHealthTestCard;

class SelfHealthTestCardResourceFactory
{
    public function __invoke($services)
    {
        return new SelfHealthTestCardResource($services);
    }
}
