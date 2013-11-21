<?php
namespace OpenstoreApi\Api;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Soluble\FlexStore\FlexStore;

class ProductBrandService extends AbstractService {
	
	
	
	/**
	 * @param array $params [brands,pricelists] 
	 * @return \Soluble\FlexStore\FlexStore
	 */
	function getList(array $params=array()) {
		
		$select = new Select();
		
		$select->from(array('pb' => 'product_brand'), array())
				->join(array('p' => 'product'),
						new Expression('pb.brand_id = p.brand_id'), array())
				->join(array('ppl' => 'product_pricelist'),
						new Expression('ppl.product_id = p.product_id'), array(), $select::JOIN_LEFT)
				->join(array('pl' => 'pricelist'),
						new Expression('ppl.pricelist_id = pl.pricelist_id'), array(), $select::JOIN_LEFT);
	
		$columns = array(
			'brand_id'			=> new Expression('pb.brand_id'),
			'brand_reference'		=> new Expression('pb.reference'),
			'title'	=> new Expression('pb.title'),
			'url'	=> new Expression('pb.url')
			
		);
				
		$select->columns(array_merge($columns, array(
			'active_pricelists' => new Expression('GROUP_CONCAT(distinct pl.reference)'),
		)), true);
				
		$select->group($columns);
		
		$select->where('p.flag_active = 1');
		$select->where('ppl.flag_active = 1');
		
		if (array_key_exists('pricelists', $params)) {
			$select->where->in('pl.reference', explode(',', $params['pricelists']));
		}

		if (array_key_exists('brands', $params)) {
			$select->where->in('pb.reference', explode(',', $params['brands']));
		}		
		
		$select->having('active_pricelists is not null');
		//$select->order(array('p.product_id' => $select::ORDER_ASCENDING));		
		
		$parameters = array(
			'adapter' => $this->adapter,
			'select' => $select
		);
		$store = new FlexStore('zend\select', $parameters);
		return $store;
		
	}
}
