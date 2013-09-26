<?php
namespace Openstore\Permission;

use Openstore\Service;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;



class UserCapabilities implements ServiceLocatorAwareInterface
{
	
	/**
	 *
	 * @var \Zend\ServiceManager\ServiceLocatorInterface
	 */
	protected $serviceLocator;
	

	/**
	 *
	 * @var integer 
	 */
	protected $user_id;
	
	
	/**
	 *
	 * @var \Openstore\Service
	 */
	protected $service;
	/**
	 * 
	 * @param integer $user_id
	 */
	function __construct($user_id) {
		
		$this->setUserId($user_id);
		
	}
	
	
	/**
	 * 
	 * @param integer $user_id
	 * @return \Openstore\Permission\Capabilities
	 */
	protected function setUserId($user_id) {
		$this->user_id = $user_id;
		return $this;
	}
	
	/**
	 * @return integer
	 */
	function getUserId() {
		return $this->user_id;
	}
	
	/**
	 * @return array
	 */
	function getRoles() {
		$userModel = $this->getService()->getModel('Model\User');
		$roles = array_column($userModel->getUserRoles($this->getUserId()), 'reference', 'role_id');	
		return $roles;
	}
	
	
	/**
	 * @param string $role role reference
	 * @return boolean
	 */
	function hasRole($role) {
		return in_array($role, $this->getRoles());
	}
	
	/**
	 * 
	 * @return boolean
	 */
	function isAdmin() {
		return $this->getRole() == 'admin';
	}
	
	/**
	 * Return pricelists the user can have
	 */
	function getPricelists() {
		
		$pricelists = array();
		
		$user_id = $this->getUserId();
		
		if ($this->hasRole('admin')) {
			$plModel = $this->getService()->getModel('Model\Pricelist');
			$pricelists = array_column($plModel->getPricelists(), 'reference', 'pricelist_id');	
		} elseif ($this->hasRole('customer')) {
			$customerModel = $this->getService()->getModel('Model\Customer');
			$userModel = $this->getService()->getModel('Model\User');
			$customer_id = $userModel->getCustomerId();
			$pricelists = array_column($customerModel->getCustomerPricelists($customer_id), 'reference', 'pricelist_id');	
		} else {
			// ($this->hasRole('user')) Default behaviour
			$userModel = $this->getService()->getModel('Model\User');
			$pricelists = array_column($userModel->getUserPricelists($user_id), 'reference', 'pricelist_id');	
		} 
	}
	
	/**
	 * 
	 * @param string $pricelist
	 * @return boolean
	 */
	function hasPricelist($pricelist) {
		return in_array($pricelist, $this->getPricelists());
	}
	
	/**
	 * @return customers the user can choose
	 */
	function getCustomers() {

		$customers = array();
		if ($this->hasRole('admin')) {
			
		} elseif ($this->hasRole('customer')) {
			
		} elseif ($this->hasRole('user')) {
			
		} elseif ($this->hasRole('guest')) {
			
		}
		return $customers;
		
	}
	
	
	
	
	
	/**
	 *
	 * @param int $customer_id
	 * @param int $user_id
	 * @return boolean 
	 */
	static function hasAccessToCustomer($customer_id, $user_id=null)
	{
		return self::getUserPermission($user_id)->getUserScope()->hasAccessToCustomer($customer_id);
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
	 * 
	 * @return \Openstore\Service
	 */
	function getService() {
		if ($this->service === null) {
			$this->service = $this->getServiceLocator()->get('Openstore\Service');
		}
		return $this->service;
		
	}
	
	
}