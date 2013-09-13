<?php
/**
 * BjyAuthorize Module (https://github.com/bjyoungblood/BjyAuthorize)
 *
 * @link https://github.com/bjyoungblood/BjyAuthorize for the canonical source repository
 * @license http://framework.zend.com/license/new-bsd New BSD License
 */

//namespace BjyAuthorize\Service;
namespace Openstore\Authorize\Service;

use Openstore\Authorize\Provider\Identity\OpenstoreDb;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class OpenstoreDbIdentityProviderServiceFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     *
     * @return \BjyAuthorize\Provider\Identity\ZfcUserZendDb
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $adapter \Zend\Db\Adapter\Adapter */
        $adapter     = $serviceLocator->get('zfcuser_zend_db_adapter');
        /* @var $userService \ZfcUser\Service\User */
        $userService = $serviceLocator->get('zfcuser_user_service');
        $config      = $serviceLocator->get('BjyAuthorize\Config');

        $provider = new OpenstoreDb($adapter, $userService);

        $provider->setDefaultRole($config['default_role']);

        return $provider;
    }
}
