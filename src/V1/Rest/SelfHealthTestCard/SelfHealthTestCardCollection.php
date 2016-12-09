<?php
namespace ApigilityVendorIntegrate\V1\Rest\SelfHealthTestCard;

use ApigilityCatworkFoundation\Base\ApigilityObjectStorageAwareCollection;

class SelfHealthTestCardCollection extends ApigilityObjectStorageAwareCollection
{
    protected $itemType = SelfHealthTestCardEntity::class;
}
