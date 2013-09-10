<?php

namespace Openstore\Catalog\Browser\ProductFilter;

use Openstore\Catalog\Browser\ProductFilter;
use Openstore\Catalog\Browser\ProductFilter\FilterAbstract;
use Zend\Db\Sql\Select;

class NewProducts extends FilterAbstract
{
	
	function __construct(array $params=array()) {
		parent::__construct($params);
		if (!array_key_exists('minimum_date', $this->params)) {
			$this->params['minimum_date'] = ProductFilter::getParam('flag_new_minimum_date');
		}
		
	}
	
	
	
	/**
	 * @param \Zend\Db\Sql\Select $select
	 * @return \Openstore\Catalog\Browser\ProductFilter\FilterAbstract
	 */
	function setConstraints(Select $select) {
		
		$minimum_date = $this->params['minimum_date'];
		$select->where("(COALESCE(pl.new_product_min_date, '$minimum_date') <= COALESCE(ppl.activated_at, p.activated_at))");
		return $this;
	}
	
	
	/**  
	 * @param \Zend\Db\Sql\Select $select
	 * @return \Openstore\Catalog\Browser\ProductFilter\AllProducts
	 */
	function addDefaultSortClause(Select $select)
	{
		$select->order(array(
			'ppl.activated_at'	=> $select::ORDER_DESCENDING, 
			'p.activated_at'	=> $select::ORDER_DESCENDING, 
			'p.reference'		=> $select::ORDER_ASCENDING)
		);
		return $this;
	}
	
}