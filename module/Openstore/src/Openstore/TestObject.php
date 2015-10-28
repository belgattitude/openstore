<?php
namespace Openstore;

class TestObject
{
    protected $data = array(
        'cool' => 'pasbon'
    );

    public function __construct()
    {
        $this->data['cool'] = 'hello';
    }


    public function getRoles()
    {
    }

    public function hasRole()
    {
    }

    public function getPricelists()
    {
    }

    public function hasAccessToPricelist()
    {
    }

    public function getCustomers()
    {
        return $this->data['cool'];
    }


    public function hasAccessToCustomer($customer_id)
    {
    }

    public function getUserId()
    {
    }

    public function getCoolData()
    {
        return $this->data['cool'];
    }
}
