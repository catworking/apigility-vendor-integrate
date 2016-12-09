<?php
namespace ApigilityVendorIntegrate\V1\Rest\SelfHealthTestCard;

use ApigilityCatworkFoundation\Base\ApigilityResource;
use Zend\ServiceManager\ServiceManager;
use ZF\ApiProblem\ApiProblem;

class SelfHealthTestCardResource extends ApigilityResource
{
    /**
     * @var \ApigilityVendorIntegrate\Service\SelfHealth\SelfHealthService
     */
    protected $selfHealthService;

    public function __construct(ServiceManager $services)
    {
        parent::__construct($services);
        $this->selfHealthService = $services->get('ApigilityVendorIntegrate\Service\SelfHealth\SelfHealthService');
    }

    public function fetchAll($params = [])
    {
        try {
            return new SelfHealthTestCardCollection($this->selfHealthService->getTestCards($params), $this->serviceManager);
        } catch (\Exception $exception) {
            return new ApiProblem($exception->getCode(), $exception->getMessage());
        }
    }
}
