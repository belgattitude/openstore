<?php
/**
 * 
 */
namespace Openstore\Catalog\Browser;

use Openstore\Catalog\Browser\SearchParams\SearchParamsAbstract as SearchParams; 
use Openstore\Catalog\Browser\ProductFilter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;


class Product extends BrowserAbstract
{
	
	/**
	 * 
	 * @return \Openstore\Catalog\Browser\SearchParams\Product
	 */
	function getDefaultParams()
	{
		$params = new \Openstore\Catalog\Browser\SearchParams\Product();		
		
		return $params;
	}	
	
	
	
	/**
	 * 
	 * @param \Openstore\Catalog\Browser\SearchParams\Product $params
	 * @return \Zend\Db\Sql\Select
	 */
	function getSelect(SearchParams $params=null)
	{
		
		if ($params === null) $params = $this->getDefaultParams();
	
		$lang = $this->filter->getLanguage();
		$pricelist = $this->filter->getPricelist();
		
		$select = new Select();
		$select->from(array('p' => 'product'), array('product_id', 'category_id'))
				->join(array('p18' => 'product_translation'),
						new Expression("p18.product_id = p.product_id and p18.lang = '$lang'"), 
						array(), $select::JOIN_LEFT)
				->join(array('ppl' => 'product_pricelist'),
						new Expression('ppl.product_id = p.product_id'), array())
				->join(array('pl' => 'pricelist'),
						new Expression('pl.pricelist_id = ppl.pricelist_id'), array())
				->join(array('ps' => 'product_stock'),
						new Expression('ps.stock_id = pl.stock_id and ps.product_id = p.product_id'), array())
				->join(array('pb' => 'product_brand'),
						new Expression('pb.brand_id = p.brand_id'), array())
				->join(array('pc' => 'product_category'),
						new Expression('pc.category_id = p.category_id'), array())
				->join(array('pc18' => 'product_category_translation'),
						new Expression("pc.category_id = pc18.category_id and pc18.lang = '$lang'"), 
						array(), $select::JOIN_LEFT)
				
				->where('p.flag_active = 1')
				->where('ppl.flag_active = 1')
				->where("pl.reference = '$pricelist'");

		$productFilter = ProductFilter::getFilter($params->getFilter());
		if ($productFilter !== null) {
			$productFilter->setConstraints($select);
			$productFilter->addDefaultSortClause($select);
		}
		$flag_new_min_date = ProductFilter::getParam('flag_new_minimum_date');
		
		$select->columns(array(
			'product_id'		=> new Expression('p.product_id'),
			'reference'			=> new Expression('p.reference'),
			'brand_id'			=> new Expression('p.brand_id'),
			'brand_reference'	=> new Expression('pb.reference'),
			'brand_title'		=> new Expression('pb.title'),
			'category_reference'=> new Expression('pc.reference'),
			'category_title'	=> new Expression('COALESCE(pc18.title, pc.title)'),
			'title'				=> new Expression('COALESCE(p18.title, p.title)'),
			'invoice_title'		=> new Expression('COALESCE(p18.invoice_title, p.invoice_title)'),
			'description'		=> new Expression('COALESCE(p18.description, p.description)'),
			'characteristic'	=> new Expression('COALESCE(p18.characteristic, p.characteristic)'),
			'price'				=> new Expression('ppl.price'),
			'list_price'		=> new Expression('ppl.price'),
			'flag_new'			=> new Expression("(COALESCE(pl.new_product_min_date, '$flag_new_min_date') <= COALESCE(ppl.activated_at, p.activated_at))"),
			'promo_discount'	=> new Expression('ppl.promo_discount'),
			'available_stock'	=> new Expression('ps.available_stock'),
			'theoretical_stock'	=> new Expression('ps.theoretical_stock'),
		), true);
		
		
		$select->limit(50);

		$product_id = $params->getId();
		if ($product_id != '') {
			$select->where("p.product_id = $product_id");
		}
		
		$brands = $params->getBrands();
		if (count($brands) > 0) {
			$brand_clauses = array();
			foreach($brands as $brand_reference) {
				$brand_clauses[] = "pb.reference = '$brand_reference'";	
			}
			$select->where('(' . join(' OR ', $brand_clauses) . ')');
		}
		
		$categories = $params->getCategories();
		if ($categories !== null && count($categories) > 0) {
			
			$sql = new Sql($this->adapter);
			$category_clauses = array();
			
			foreach($categories as $category_reference) {
				$spb = new Select();
				$spb->from('product_category')
						->columns(array('category_id', 'lft', 'rgt'))
						->where(array('reference' => $category_reference))
						->limit(1);
				
				$sql_string = $sql->getSqlStringForSqlObject($spb);
				$results = $this->adapter->query($sql_string, Adapter::QUERY_MODE_EXECUTE)->toArray();
				if (count($results) > 0) {
					$category_clauses[] = 'pc.lft between ' . $results[0]['lft'] . ' and ' . $results[0]['rgt'];
				}
			}
			if (count($category_clauses) > 0) {
				$select->where('(' . join(' or ', $category_clauses) . ')');
			}
		}
		
		if (($query = $params->getQuery()) != null) {
			$query = str_replace(' ', '%', trim($query));				
			$q = $this->adapter->getPlatform()->quoteValue($query . '%');
			$select->where("p.reference like $q");
		}
		
		return $select;
	}
	
		
}