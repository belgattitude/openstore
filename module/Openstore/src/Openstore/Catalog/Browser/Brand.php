<?php
/**
 * 
 */
namespace Openstore\Catalog\Browser;

use Openstore\Catalog\Browser\SearchParams\SearchParamsAbstract as SearchParams; 
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Sql;

class Brand extends BrowserAbstract
{
	
	/**
	 * 
	 * @return \Openstore\Catalog\Browser\SearchParams\Brand
	 */
	function getDefaultParams()
	{
		$options = new \Openstore\Catalog\Browser\SearchParams\Brand();		
		return $options;
	}	
	
	/**
	 * 
	 * @param \Openstore\Catalog\Browser\SearchParams\Brand $brand
	 * @return \Zend\Db\Sql\Select
	 */
	function getSelect(SearchParams $params=null)
	{
		if ($params === null) $params = $this->getDefaultParams();		
		
		$lang = $this->filter->getLanguage();
		$pricelist = $this->filter->getPricelist();
		
		$select = new Select();
		$select->from(array('pb' => 'product_brand'), array())
				->join(array('pb18' => 'product_brand_translation'),
						new Expression("pb.brand_id = pb18.brand_id and pb18.lang = '$lang'"), 
						array(), $select::JOIN_LEFT)
				->join(array('p' => 'product'), 
						new Expression("p.brand_id = pb.brand_id"), array())
				->join(array('ppl' => 'product_pricelist'),
						new Expression('ppl.product_id = p.product_id'), array())
				->join(array('pl' => 'pricelist'),
						new Expression('pl.pricelist_id = ppl.pricelist_id'), array())
				->join(array('pc' => 'product_category'),
						new Expression('pc.category_id = p.category_id'), array())
				
				->where('p.flag_active = 1')
				->where('ppl.flag_active = 1')
				->where("pl.reference = '$pricelist'");

		$productFilter = ProductFilter::getFilter($params->getFilter());
		if ($productFilter !== null) {
			$productFilter->setConstraints($select);
		}
		
		
		$columns = array(
			'brand_id'		=> new Expression('pb.brand_id'),
			'reference'		=> new Expression('pb.reference'),
			'title'			=> new Expression('pb.title'),
			'description'	=> new Expression('COALESCE(pb18.description, pb.description)'),
		);
		
		$select->columns(array_merge($columns, array(
			'count_product' => new Expression('count(p.product_id)')
		)), true);
		
		$select->group($columns);
		
		$select->order(array('pb.title' => $select::ORDER_ASCENDING));
		
		if (($categories = $params->getCategories()) !== null) {
			
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
			$select->where('(' . join(' or ', $category_clauses) . ')');
		}
		
		return $select;
	}
}