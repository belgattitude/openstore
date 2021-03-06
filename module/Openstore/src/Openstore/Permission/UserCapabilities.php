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
    public function __construct($user_id)
    {
        $this->setUserId($user_id);
    }


    /**
     *
     * @param integer $user_id
     * @return \Openstore\Permission\Capabilities
     */
    protected function setUserId($user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        $userModel = $this->getService()->getModel('Model\User');
        $roles = array_column($userModel->getUserRoles($this->getUserId()), 'reference', 'role_id');
        return $roles;
    }


    /**
     * @param string $role role reference
     * @return boolean
     */
    public function hasRole($role)
    {
        return in_array($role, $this->getRoles());
    }

    /**
     *
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->getRole() == 'admin';
    }

    /**
     * Return pricelists the user can have
     * @return array associative array with pricelist_id as key and pricelist reference as value
     */
    public function getPricelists()
    {
        $pricelists = [];

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

        return $pricelists;
    }

    /**
     *
     * @param string $pricelist Pricelist reference
     * @return boolean
     */
    public function hasAccessToPricelist($pricelist)
    {
        return in_array($pricelist, $this->getPricelists());
    }

    /**
     * @return customers the user can choose
     */
    public function getCustomers()
    {
        $customers = [];
        if ($this->hasRole('admin')) {
            $customers = [];
        } elseif ($this->hasRole('customer')) {
            $customerModel = $this->getService()->getModel('Model\Customer');
            $userModel = $this->getService()->getModel('Model\User');
            return [$userModel->getCustomerId()];
        } elseif ($this->hasRole('user')) {
            $customers = []; // TODO FIX IT
        } elseif ($this->hasRole('guest')) {
            $customers = [];
        }
        return $customers;
    }





    /**
     *
     * @param int $customer_id
     * @param int $user_id
     * @return boolean
     */
    public function hasAccessToCustomer($customer_id)
    {
        if ($this->hasRole('admin')) {
            return true;
        }

        return in_array($customer_id, $this->getCustomers());
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
    public function getService()
    {
        if ($this->service === null) {
            $this->service = $this->getServiceLocator()->get('Openstore\Service');
        }
        return $this->service;
    }
}
