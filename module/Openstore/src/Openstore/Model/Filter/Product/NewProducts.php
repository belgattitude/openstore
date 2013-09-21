<?php
namespace Openstore\Model\Filter\Product;
use Openstore\Core\Model\Browser\Filter\AbstractFilter;
use Zend\Db\Sql\Select;

class NewProducts extends AbstractFilter
{
	
	public function getName() {
		return 'new';
	}
	
	/**
	 * 
	 * @param \Zend\Db\Sql\Select $select
	 * @return \Zend\Db\Sql\Select
	 */
	function filter(Select $select) {
		//$this->getServiceLocator()->get('Openstore\Config');
		//$config['product']['filter']['minimum_date'];
		$minimum_date = '2012-06-01';
		$select->where("(COALESCE(pl.new_product_min_date, '$minimum_date') <= COALESCE(ppl.activated_at, p.activated_at))");
		return $select;
	}
	
	
	/**  
	 * @param \Zend\Db\Sql\Select $select
	 * @return \Openstore\Model\Filter\Product\NewProducts
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
	
	
