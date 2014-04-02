<?php
namespace Openstore;

use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

use Zend\Session\Container;

use Openstore\Permission\UserCapabilities;

use Soluble\Normalist\Synthetic\TableManager;

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
				
				$this->container['caps'] = array();
				$this->container['user_id'] = $user_id;
				$this->container['caps']['roles']	   = $userCap->getRoles();
				$this->container['caps']['pricelists'] = $userCap->getPricelists();
				$this->container['caps']['customers']  = $userCap->getCustomers();	
				$this->container['testobject'] = new \Openstore\TestObject();
				
				//var_dump($this->container['testobject']->getCustomers());
				//die();
				
			} else {

				$tm = $this->getTableManager();
				
		
				$all_pricelists = $tm->table('pricelist')->search()->toArrayColumn('reference', 'pricelist_id');
				
				// PUBLIC capabilitities
				// TODO get pricelist from table
				$this->container['caps'] = array();
				$this->container['caps']['roles']	   = array('guest');
				$this->container['caps']['pricelists'] = $all_pricelists;
				$this->container['caps']['customers']  = array();	
				
			}
			$this->container['is_initialized'] = true;
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
	
	/**
	 * @return TableManager
	 */
	protected function getTableManager()
    {
		return $this->getServiceLocator()->get('SolubleNormalist\TableManager');
	}
	
	
}