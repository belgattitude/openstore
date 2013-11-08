<?php
namespace OpenstoreApi\Api;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Soluble\FlexStore\FlexStore;

class ProductCatalogService extends AbstractService {
	
	
	
	/**
	 * @param array $params [brands,pricelists] 
	 * @return \Soluble\FlexStore\FlexStore
	 */
	function getList(array $params=array()) {
		
		$select = new Select();
		$lang = 'fr';
		$select->from(array('p' => 'product'), array())
				->join(array('p18' => 'product_translation'),
						new Expression("p18.product_id = p.product_id and p18.lang='$lang'"), array(), $select::JOIN_LEFT)
				
				->join(array('pb' => 'product_brand'),
						new Expression('pb.brand_id = p.brand_id'), array())
				->join(array('pg' => 'product_group'),
						new Expression('pg.group_id = p.group_id'), array(), $select::JOIN_LEFT)
				->join(array('ppl' => 'product_pricelist'),
						new Expression('ppl.product_id = p.product_id'), array(), $select::JOIN_LEFT)
				->join(array('pl' => 'pricelist'),
						new Expression('ppl.pricelist_id = pl.pricelist_id'), array(), $select::JOIN_LEFT);
	
		$columns = array(
			'product_id'			=> new Expression('p.product_id'),
			'product_reference'		=> new Expression('p.reference'),
			'product_barcode_ean13'	=> new Expression('p.barcode_ean13'),
			'brand_id'				=> new Expression('pb.brand_id'),
			'brand_reference'		=> new Expression('pb.reference'),
			'group_id'				=> new Expression('pg.group_id'),
			'group_reference'		=> new Expression('pg.reference'),
			'product_title'			=> new Expression('COALESCE(p18.title, p18.invoice_title, p.title, p.invoice_title)'),
			'product_description'	=> new Expression('COALESCE(p18.description, p.description)')
			
		);
				
		$select->columns(array_merge($columns, array(
			'active_pricelists' => new Expression('GROUP_CONCAT(distinct pl.reference)'),
		)), true);
				
		$select->group($columns);
		
		$select->where('p.flag_active = 1');
		$select->where('ppl.flag_active = 1');
		
		if (array_key_exists('type', $params)) {
			$select->where(array('pmt.reference' => $params['type']));
		}


		if (array_key_exists('types', $params)) {
			$select->where->in('pmt.reference', explode(',', $params['types']));
		}
		
		if (array_key_exists('pricelists', $params)) {
			$select->where->in('pl.reference', explode(',', $params['pricelists']));
		}

		if (array_key_exists('brands', $params)) {
			$select->where->in('pb.reference', explode(',', $params['brands']));
		}		
		
		/*
		$select->where("pl.reference = 'BE'");
		 */
		
		$select->having('active_pricelists is not null');
		$select->order(array('p.product_id' => $select::ORDER_ASCENDING));		
		
		$parameters = array(
			'adapter' => $this->adapter,
			'select' => $select
		);
		$store = new FlexStore('zend\select', $parameters);

		return $store;
		
	}
}
