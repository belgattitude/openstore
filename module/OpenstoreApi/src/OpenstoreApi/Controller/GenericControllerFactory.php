<?php

namespace OpenstoreApi\Controller;

use OpenstoreApi\Api\NammProductCatalogService;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Db\Adapter\Adapter;

class GenericControllerFactory
{
    public function __invoke(ServiceLocatorInterface $container)
    {
        $sl = $container->getServiceLocator();

        $adapter = $sl->get(Adapter::class);

        return new GenericController(new NammProductCatalogService($sl, $adapter));
    }
}
