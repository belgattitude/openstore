<?php

namespace Openstore\View\Helper\Service;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Openstore\View\Helper\RouteParams;

class RouteParamsFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return \Openstore\View\Helper\RouteParams
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $services = $serviceLocator->getServiceLocator();
        $application = $services->get('Application');
        return new RouteParams($application->getMvcEvent());
    }
}
