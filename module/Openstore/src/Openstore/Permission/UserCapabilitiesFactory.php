<?php
namespace Openstore\Permission;

use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class UserCapabilitiesFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $locator
     * @return \Openstore\Service
     */
    public function createService(ServiceLocatorInterface $sl)
    {
        $userCapabilities = new UserCapabilities();
        $userCapabilities->setServiceLocator($sl);
        $userCapabilities->setServiceLocator($sl);

        $auth = $sl->get('zfcuser_auth_service');
        if ($auth->hasIdentity()) {
            $userCapabilities->setUserId($auth->getIdentity()->getUserId());
        }
        return $userCapabilities;
    }
}
