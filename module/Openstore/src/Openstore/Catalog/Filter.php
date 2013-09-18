<?php

namespace Openstore\Catalog;

class Filter
{
	protected $customer_id;
	
	function __construct() {
	}
	
	function setCustomer($customer_id)
	{
		$this->customer_id = $customer_id;
	}
	
	function getCustomer()
	{
		return $this->customer_id;
	}
	
	
}