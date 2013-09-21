<?php
namespace Openstore\Model\Filter\Product;
use Openstore\Core\Model\Browser\Filter\AbstractFilter;
use Zend\Db\Sql\Select;

class PromoProducts extends AbstractFilter
{
	public function getName() {
		return 'promos';
	}
	
	
	/**
	 * 
	 * @param \Zend\Db\Sql\Select $select
	 * @return \Zend\Db\Sql\Select
	 */
	function filter(Select $select) {
		$select->where("(ppl.promo_discount > 0)");
		return $select;
	}
	

	function addDefaultSortClause(Select $select)
	{
		$select->order(array(
			'ppl.promo_discount'=> $select::ORDER_DESCENDING, 
			'p.reference'		=> $select::ORDER_ASCENDING)
		);
		return $this;
	}
	
}
	
