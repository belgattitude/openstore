<?php
namespace Openstore;

use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Session\Container;

use Openstore\Permission\UserCapabilities;

class UserContext implements ServiceLocatorAwareInterface
{
	/**
	 * @var ServiceLocatorInterface
	 */
    protected $serviceLocator;
	
	/**
	 *
	 * @var \Zend\Session\Container
	 */
	protected $container;
	
	
	
	
	function __construct(\Zend\Session\Container $container)
	{
		$this->container = $container;
	}
	
	public function initialize()
	{
		if (!$this->container['is_initialized']) {
			$user_id = $this->container['user_id'];
			if ($user_id !== null) {
				$userCap = new UserCapabilities($user_id);
				$userCap->setServiceLocator($this->getServiceLocator());
						
				$this->container['roles']	   = $userCap->getRoles();
				$this->container['pricelists'] = $userCap->getPricelists();
				//$this->container['customers']  =	
				
			} else {
				// PUBLIC capabilitities
				// TODO get pricelist from table
				$this->container['roles'] = array('guest');
				$this->container['pricelists'] = array('FR');
				$this->container['customers'] = array();
			}
			$this->container['is_initalized'] = true;
		}
	}
	
	
	function getAllowedPricelists() {
		return $this->container['pricelists'];
	}
	
	
	
	/**
	 * 
	 * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
	 * @return \Openstore\Service
	 */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
        return $this;
    }

	/**
	 * 
	 * @return \Zend\ServiceManager\ServiceLocatorInterface
	 */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }
	
}