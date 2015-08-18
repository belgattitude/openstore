<?php

namespace OpenstoreApi\Authorize;

use Zend\Db\Adapter\Adapter;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Expression;
use Soluble\Normalist\Synthetic\TableManager;
use Soluble\Normalist\Synthetic\Record;
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
     * @var TableManager
     */
    protected $tm;

    /**
     * @param string $api_key
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator

     */
    public function __construct($api_key, ServiceLocatorInterface $serviceLocator)
    {
        $this->api_key = trim($api_key);
        $this->setServiceLocator($serviceLocator);
        $this->setDbAdapter($serviceLocator->get("Zend\Db\Adapter\Adapter"));



        if ($this->api_key == '') {
            throw new Exception\AuthorizationException("Missing required api_key");
        }

        $tm = $this->getTableManager();
        $apiTable = $tm->table('api_key');
        $record = $apiTable->findOneBy(array('api_key' => $this->api_key));
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
     * @return Record
     * @throws Exception\ServiceUnavailableException
     */
    public function addLog($service_reference)
    {
        $tm = $this->getTableManager();

        $service = $tm->table('api_service')->findOneBy(array('reference' => $service_reference));
        if (!$service) {
            throw new Exception\ServiceUnavailableException("Service '$service_reference' unavailable or not exists");
        }

        $data = array(
            'api_id' => $this->api_id,
            'service_id' => $service->service_id,
            'remote_ip' => $_SERVER['REMOTE_ADDR'],
            'message' => $_SERVER['REQUEST_URI'],
            'created_at' => date('Y-m-d H:i:s'),
        );

        $api_key_log = $tm->table('api_key_log')->insert($data);


        return $api_key_log;
    }

    /**
     *
     * @param string $service_reference
     * @return \OpenstoreApi\Authorize\ApiKeyAccess
     * @throws Exception\ServiceUnavailableException
     * @throws Exception\ForbiddenServiceException
     */
    public function checkServiceAccess($service_reference)
    {
        $api_id = $this->api_id;
        $api_key = $this->api_key;
        $tm = $this->getTableManager();
        $serviceTable = $tm->table('api_service');
        $service = $serviceTable->findOneBy(array('reference' => $service_reference));
        if (!$service) {
            throw new Exception\ServiceUnavailableException("Service '$service_reference' unavailable or not exists");
        }

        $service_id = $service->service_id;


        $apiServiceTable = $tm->table('api_key_service');
        $access = $apiServiceTable->findOneBy(array('service_id' => $service_id, 'api_id' => $api_id));
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
        $tm = $this->getTableManager();
        $acTable = $tm->table('api_key_customer');
        $customers = $acTable->select('api_key_customer')->where(array('api_id' => $api_id))->execute();
        $customers = array_column($customers->toArray(), 'customer_id');
        return $customers;
    }

    /**
     *
     * @param string $pricelist
     * @return \OpenstoreApi\Authorize\ApiKeyAccess
     * @throws Exception\ForbiddenServiceException
     */
    public function checkPricelistAccess($pricelist)
    {
        $pricelists = $this->getPricelists();
        if (!in_array($pricelist, $pricelists)) {
            $api_key = $this->api_key;
            throw new Exception\ForbiddenServiceException("Not allowed, api key '$api_key' does not provide access to pricelist '$pricelist'.");
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getPricelists()
    {
        $customers = $this->getCustomers();
        $pricelists = array();
        foreach ($customers as $customer_id) {
            $sql = new Sql($this->adapter);
            $select = $sql->select();
            $select->from(array('cp' => 'customer_pricelist'), array())
                    ->join(array('pl' => 'pricelist'), new Expression('cp.pricelist_id = pl.pricelist_id'), array())
                    ->where(array('customer_id' => $customer_id))
                    ->quantifier('DISTINCT')
                    ->columns(array(
                        'pricelist' => new Expression('pl.reference')
                            ), false);
            $sql_string = $sql->getSqlStringForSqlObject($select);
            $result = array_column($this->adapter->query($sql_string, Adapter::QUERY_MODE_EXECUTE)->toArray(), 'pricelist');
            $pricelists = array_merge($pricelists, $result);
        }
        return array_unique($pricelists);
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
    public function setDbAdapter(Adapter $adapter)
    {
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
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * @param TableManager $tm
     * @return ApiKeyAccess
     */
    public function setTableManager(TableManager $tm)
    {
        $this->tm = $tm;
        return $this;
    }

    /**
     * @return TableManager
     */
    public function getTableManager()
    {
        if ($this->tm === null) {
            $this->tm = $this->getServiceLocator()->get('SolubleNormalist\TableManager');
        }
        return $this->tm;
    }
}
