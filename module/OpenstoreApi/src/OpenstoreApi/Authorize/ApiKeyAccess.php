<?php
namespace OpenstoreApi\Authorize;

use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;

use Soluble\Normalist\SyntheticTable;
use Soluble\Normalist\Exception as NormalistException;




class ApiKeyAccess implements ServiceLocatorAwareInterface
{
	
	/**
	 *
	 * @var \Zend\ServiceManager\ServiceLocatorInterface
	 */
	protected $serviceLocator;
	
	/**
	 *
	 * @var \Zend\Db\Adapter\Adapter
	 */
	protected $adapter;
	
	
	/**
	 *
	 * @var string
	 */
	protected $api_key;
	
	/**
	 *
	 * @var id
	 */
	protected $api_id;
	
	
	/**
	 *
	 * @var SyntheticTable
	 */
	protected $table;
	
	/**
	 * @param string $pai_key 
	 * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
	 
	 */
	function __construct($api_key, ServiceLocatorInterface $serviceLocator=null) 
	{
		$this->api_key = trim($api_key);
		$this->setServiceLocator($serviceLocator);
		$this->setDbAdapter($serviceLocator->get("Zend\Db\Adapter\Adapter"));
		
		$this->table = new SyntheticTable($this->adapter);
		
		if ($this->api_key == '') {
			throw new Exception\AuthorizationException("Missing required api_key");
		}
		
		$record = $this->table->findOneBy('api_key', array('api_key' => $this->api_key));
		if ($record === false) {
			throw new Exception\AuthorizationException("Api key '{$this->api_key}' does not exists.");
		}
		
		if ($record->flag_active != 1) {
			throw new Exception\DisabledApiKeyException("Api key '{$this->api_key}' is not enabled.");
		}
		
		$this->api_id = $record->api_id;
	}
	
	
	
	
	
	/**
	 * 
	 * @param string $service_reference
	 */
	public function checkServiceAccess($service_reference) 
	{
		$api_id = $this->api_id;
		$api_key = $this->api_key;
		$service = $this->table->findOneBy('api_service', array('reference' => $service_reference));
		if (!$service) {
			throw new Exception\ServiceUnavailableException("Service '$service_reference' unavailable or not exists");
		}
		
		$service_id = $service->service_id;
		
		
		$access = $this->table->findOneBy('api_key_service', array('service_id' => $service_id, 'api_id' => $api_id));
		if (!$access) {
			throw new Exception\ForbiddenServiceException("Not allowed, api key '$api_key' does not provide access to service '$service_reference'.");
		}
		
		if ($access->flag_active != 1) {
			throw new Exception\ForbiddenServiceException("Not allowed, service '$service_reference' for api key '$api_key' is disabled.");
		}
		$this->getCustomers();
		return $this;
	}
	
	
	/**
	 * @return array
	 */
	public function getCustomers() 
	{
		$api_id = $this->api_id;
		$api_key = $this->api_key;
		$customers = $this->table->select('api_key_customer')->where(array('api_id' => $api_id))->execute();
		
		$customers = array_column($customers->toArray(), 'customer_id');
		return $cutomers;
	}
	
	/**
	 * @return array
	 */
	public function getPricelists() 
	{
		$customers = $this->getCustomers();
		$sql = new Sql($this->adapter);
		//$select = $sql->
		//return $pricelists;
	}
	
	
	
	/**
	 * Check whether the api key provided is valid
	 * @param string $api_key
	 * @return boolean
	 */
	public function isValidApi($api_key) 
	{
		
		$table = new SyntheticTable($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
		$record = $table->findOneBy('api_key', array('api_key' => $api_key));
		if ($record === false) {
			return false;
		}
		
		if ($record['flag_active'] != 1) {
			var_dump($record->toArray());
		} else {
			echo 'cool';
			$api_id = $record->api_id;
			var_dump($api_id);
		}
		
		
		
		
		die();
		
	}
	
	
    /**
     * Set db adapter
     *
     * @param Adapter $adapter
     * @return \OpenstoreApi\Api\AbstractService
     */
    public function setDbAdapter(Adapter $adapter) {
		
		$this->adapter = $adapter;
		return $this;
	}	
	
	
	/**
	 * 
	 * @return \Zend\Db\Adapter\Adapter
	 */
	public function getDbAdapter() 
	{
		return $this->adapter;
	}
	
    /**
     * Set service locator
     *
     * @param ServiceLocatorInterface $serviceLocator
	 * @return \OpenstoreApi\Api\AbstractService
     */
    public function setServiceLocator(ServiceLocatorInterface $serviceLocator)
	{
		
		$this->serviceLocator = $serviceLocator;
		return $this;
	}

    /**
     * Get service locator
     *
     * @return ServiceLocatorInterface
     */
    public function getServiceLocator() {
		return $this->serviceLocator;
	}	
	
}