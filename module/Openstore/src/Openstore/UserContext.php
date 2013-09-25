<?php
namespace Openstore;

use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;



class UserContext implements ServiceLocatorAwareInterface
{
	/**
	 * @var ServiceLocatorInterface
	 */
    protected $serviceLocator;
	
	/**
	 *
	 * @var ArrayObject
	 */
	protected $context;
	
	function __construct()
	{
		$this->context = new \ArrayObject();
		
	}

	/**
	 * @return \Openstore\Model\User
	 */
	function getUserModel()
	{
		$userModel = $this->getServiceLocator()->get('Openstore\Service')->getModel('Model\User');
		return $userModel;
	}
	
	function initialize()
	{
		$auth = $this->serviceLocator->get('zfcuser_auth_service');
		if (!$auth->hasIdentity()) {
			throw new \Exception('Not logged in user');
		}
		$user_id = $auth->getIdentity()->getUserId();
		
		$userModel = $this->getUserModel();
		$user_pricelists = $userModel->getUserPricelists($user_id);
		
		$this->pricelists = array_column($user_pricelists, 'reference', 'pricelist_id');		
		
	}
	
	/**
	 * 
	 * @param int $customer_id
	 * @return \Openstore\UserContext
	 */
	function setCustomerId($customer_id) 
	{
		$this->context['customer_id'] = $customer_id;
		return $this;
	}
	
	/**
	 * 
	 * @return int
	 */
	function getCustomerId()
	{
		return $this->context['customer_id'];
	}
	
	/**
	 * 
	 * @param string $pricelist
	 * @return \Openstore\UserContext
	 */
	function setPricelist($pricelist) 
	{
		$this->context['pricelist'] = $pricelist;
		return $this;
	}
	
	/**
	 * 
	 * @return int
	 */
	function getPricelist()
	{
		return $this->context['pricelist'];
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