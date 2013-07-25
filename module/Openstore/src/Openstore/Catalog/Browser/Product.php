<?php
/**
 * 
 */
namespace Openstore\Catalog\Browser;

use Openstore\Catalog\Browser\Search\Options as SearchOptions;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;

class Product extends BrowserAbstract
{
	
	/**
	 * 
	 * @return \Openstore\Catalog\Browser\Search\Options\Category
	 */
	function getDefaultOptions()
	{
		$options = new \Openstore\Catalog\Browser\Search\Options\Product();		
		
		return $options;
	}	
	
	/**
	 * 
	 * @param \Openstore\Catalog\Browser\Search\Options $options
	 * @return \Zend\Db\Sql\Select
	 */
	function getSelect(SearchOptions $options=null)
	{
		
		if ($options === null) $options = $this->getDefaultOptions();
	
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
				->join(array('pb' => 'product_brand'),
						new Expression('pb.brand_id = p.brand_id'), array())
				->join(array('pc' => 'product_category'),
						new Expression('pc.category_id = p.category_id'), array())
				->where('p.flag_active = 1')
				->where("pl.reference = '$pricelist'");
		
		$select->columns(array(
			'product_id'	=> new Expression('p.product_id'),
			'reference'		=> new Expression('p.reference'),
			'brand_id'		=> new Expression('p.brand_id'),
			'brand_reference'	=> new Expression('pb.reference'),
			'brand_title'	=> new Expression('pb.title'),
			'title'			=> new Expression('COALESCE(p18.title, p.title)'),
			'invoice_title'	=> new Expression('COALESCE(p18.invoice_title, p.invoice_title)'),
			'description'	=> new Expression('COALESCE(p18.description, p.description)'),
			'characteristic'=> new Expression('COALESCE(p18.characteristic, p.characteristic)'),
			'price'			=> new Expression('ppl.price'),
		), true);
		
		$select->order(array('p.reference' => $select::ORDER_ASCENDING));
		$select->limit(50);

		if (($brand = $options->getBrand()) != '') {
			$select->where("pb.reference = '$brand'");
		}
		
		
		if (($category = $options->getCategory()) !== null) {
			$sc = new Select();
			$sc->from('product_category')
					->columns(array('category_id', 'lft', 'rgt'))
					->where(array('reference' => $category))
					->limit(1);
			
			$sql = new Sql($this->adapter);
			$sql_string = $sql->getSqlStringForSqlObject($sc);
			$results = $this->adapter->query($sql_string, Adapter::QUERY_MODE_EXECUTE)->toArray();
			if (count($results) > 0) {
				$select->where('pc.lft between ' . $results[0]['lft'] . ' and ' . $results[0]['rgt']);
			}
		}
		
		if (($keywords = $options->getKeywords()) != null) {
			$query = str_replace(' ', '%', trim($keywords));				
			$select->where("p.reference like '$query%'");
		}
		
		return $select;
	}
	
		
}