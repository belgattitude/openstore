<?php
namespace Openstore;


class TestObject 
{
	protected $data = array(
		'cool' => 'pasbon' 
	);

	function __construct()
	{
		$this->data['cool'] = 'hello';
	}
	
	
	function getRoles()
	{
		
	}
	
	function hasRole()
	{
		
	}
	
	function getPricelists()
	{
		
	}
	
	function hasAccessToPricelist()
	{
		
	}
	
	function getCustomers()
	{
		return $this->data['cool'];
	}
	
	
	function hasAccessToCustomer($customer_id)
	{
		
	}
	
	function getUserId()
	{
		
	}
	
	function getCoolData() 
	{
		return $this->data['cool'];
	}
	
	

	
}