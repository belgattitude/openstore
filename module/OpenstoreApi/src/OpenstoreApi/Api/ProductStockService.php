<?php
namespace OpenstoreApi\Api;

use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Soluble\FlexStore\FlexStore;

class ProductStockService extends AbstractService {

	/**
	 * 
	 * @param array $params
	 * @throws \Exception
	 */
	protected function checkListParams(array $params) {
		$required_params = array(
			'pricelist', 
			'language');
		foreach ($required_params as $param) {
			if (!array_key_exists($param, $params)) {
				throw new \Exception("Missing required '$param' parameter");
			}
			if (trim($params[$param]) == '') {
				throw new \Exception("Parameter '$param' is empty");
			}
		}
	}
	
	/**
	 * @param array $params [brands,pricelists] 
	 * @return \Soluble\FlexStore\FlexStore
	 */
	function getList(array $params=array()) {
		$this->checkListParams($params);
		$select = new Select();
		$lang = $params['language'];
		$pricelist_reference = $params['pricelist'];
		
		$select->from(array('p' => 'product'), array())
				->join(array('p18' => 'product_translation'),
						new Expression("p18.product_id = p.product_id and p18.lang='$lang'"), array(), $select::JOIN_LEFT)
				
				->join(array('pb' => 'product_brand'),
						new Expression('pb.brand_id = p.brand_id'), array())
				->join(array('p2' => 'product'),
						new Expression('p2.product_id = p.parent_id'), array(), $select::JOIN_LEFT)
				->join(array('pu' => 'product_unit'),
						new Expression('p.unit_id = pu.unit_id'), array(), $select::JOIN_LEFT)
				
				->join(array('pm' => 'product_model'),
						new Expression('pm.model_id = p.model_id'), array(), $select::JOIN_LEFT)
				->join(array('pc' => 'product_category'),
						new Expression('p.category_id = pc.category_id'), array(), $select::JOIN_LEFT)
				
				->join(array('pg' => 'product_group'),
						new Expression('pg.group_id = p.group_id'), array(), $select::JOIN_LEFT)
				->join(array('pg18' => 'product_group_translation'),
						new Expression("pg18.group_id = pg.group_id and pg18.lang='$lang'"), array(), $select::JOIN_LEFT)
				
				->join(array('ppl' => 'product_pricelist'),
						new Expression("ppl.product_id = p.product_id"), array(), $select::JOIN_LEFT)
				->join(array('pl' => 'pricelist'),
						new Expression("ppl.pricelist_id = pl.pricelist_id and pl.reference = '$pricelist_reference'"), 
							array(), $select::JOIN_LEFT)
				->join(array('pt' => 'product_type'),
						new Expression('p.type_id = pt.type_id'), 
						array(), $select::JOIN_LEFT)
				->join(array('c' => 'currency'),
						new Expression('c.currency_id = pl.currency_id'), 
						array(), $select::JOIN_LEFT)
				->join(array('ps' => 'product_stock'),
						new Expression('ps.stock_id = pl.stock_id and ps.product_id = p.product_id'), 
						array(), $select::JOIN_LEFT)
				->join(array('pmed' => 'product_media'),
						new Expression("pmed.product_id = p.product_id and pmed.flag_primary=1"), 
						array(), $select::JOIN_LEFT)
				->join(array('pmt' => 'product_media_type'),
						new Expression("pmt.type_id = p.type_id and pmt.reference = 'PICTURE'"), 
						array(), $select::JOIN_LEFT);
				
		$max_stock = 30;		
				
		/*
		    Liquidation
			DateCreation		
		*/
		$columns = array(
			'product_id'			=> new Expression('p.product_id'),
			'product_reference'		=> new Expression('p.reference'),
			
			'on_stock'				=> new Expression('if (ps.available_stock > 0, 1, 0)'),
			'available_stock'		=> new Expression("LEAST(GREATEST(ps.available_stock, 0), $max_stock)"),
			'next_available_stock_at' => new Expression('ps.next_available_stock_at'),
			'next_available_stock'  => new Expression("LEAST(GREATEST(ps.next_available_stock, 0), $max_stock)"),
			'stock_updated_at'		=> new Expression('ps.updated_at'),
			
			'product_barcode_ean13'	=> new Expression('p.barcode_ean13'),
			'product_barcode_upca'	=> new Expression('p.barcode_upca'),

			'pricelist_id'			=> new Expression('pl.pricelist_id'),
			'pricelist_reference'	=> new Expression('pl.reference'),
			
		);
				
		$select->columns($columns, true);
		/*
		$select->columns(array_merge($columns, array(
			'count_picture' => new Expression('GROUP_CONCAT(distinct pl.reference)'),
		)), true);*/
				
		$select->group($columns);
		
		$select->where('p.flag_active = 1');
		$select->where('ppl.flag_active = 1');

		

		if (array_key_exists('brands', $params)) {
			$select->where->in('pb.reference', explode(',', $params['brands']));
		}		
		
		if (array_key_exists('groups', $params)) {
			$select->where->in('pg.reference', explode(',', $params['groups']));
		}				
		//$select->limit(1000);
		/*
		$select->where("pl.reference = 'BE'");
		 */
		
		$select->order(array('p.product_id' => $select::ORDER_ASCENDING));		
		
		/**
		 * 
		 */
		
		$parameters = array(
			'adapter' => $this->adapter,
			'select' => $select
		);
		$store = new FlexStore('zend\select', $parameters);
		$store->getSource()->getData();

		//var_dump($store->getSource()->getData()->toArray());
		//die();
		return $store;
		
	}
}
