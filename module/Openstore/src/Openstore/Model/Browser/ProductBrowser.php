<?php
namespace Openstore\Model\Browser;

use Openstore\Core\Model\Browser\AbstractBrowser;
//use Openstore\Catalog\Browser\SearchParams\SearchParamsAbstract as SearchParams; 
//use Openstore\Catalog\Browser\ProductFilter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;


class ProductBrowser extends AbstractBrowser {
	
	
	/**
	 * @return array
	 */
	function getSearchableParams() {
		return array(
			'language'		=> array('required' => true),
			'pricelist'		=> array('required' => true),
			'query'			=> array('required' => false),
			'brands'		=> array('required' => false),
			'categories'	=> array('required' => false),
			'id'			=> array('required' => false)
		);
	}
	
	/**
	 * 
	 * @return \Zend\Db\Sql\Select
	 */
	function getSelect()
	{
		$params = $this->getSearchParams();
	
		$lang		= $params->get('language');
		
		$pricelist	= $params->get('pricelist');
		
		$select = new Select();
		$select->from(array('p' => 'product'), array('product_id', 'category_id'))
				->join(array('p18' => 'product_translation'),
						new Expression("p18.product_id = p.product_id and p18.lang = '$lang'"), 
						array(), $select::JOIN_LEFT)
				->join(array('psi' => 'product_search'),
						new Expression("psi.product_id = p.product_id and psi.lang = '$lang'"), 
						array(), $select::JOIN_LEFT)
                        
				->join(array('ppl' => 'product_pricelist'),
						new Expression('ppl.product_id = p.product_id'), array())
				->join(array('pl' => 'pricelist'),
						new Expression('pl.pricelist_id = ppl.pricelist_id'), array())
				->join(array('pt' => 'product_type'),
						new Expression('p.type_id = pt.type_id'), 
						array(), $select::JOIN_LEFT)
				->join(array('c' => 'currency'),
						new Expression('c.currency_id = pl.currency_id'), 
						array(), $select::JOIN_LEFT)
				->join(array('pu' => 'product_unit'),
						new Expression('pu.unit_id = p.unit_id'), 
						array(), $select::JOIN_LEFT)
				->join(array('ps' => 'product_stock'),
						new Expression('ps.stock_id = pl.stock_id and ps.product_id = p.product_id'), array())
				->join(array('pb' => 'product_brand'),
						new Expression('pb.brand_id = p.brand_id'), array())
				->join(array('pc' => 'product_category'),
						new Expression('pc.category_id = p.category_id'), array())
				->join(array('pc18' => 'product_category_translation'),
						new Expression("pc.category_id = pc18.category_id and pc18.lang = '$lang'"), 
						array(), $select::JOIN_LEFT)
				->join(array('pm' => 'product_media'),
						new Expression("pm.product_id = p.product_id and pm.flag_primary=1"), 
						array(), $select::JOIN_LEFT)
				->join(array('pmt' => 'product_media_type'),
						new Expression("pmt.type_id = p.type_id and pmt.reference = 'PICTURE'"), 
						array(), $select::JOIN_LEFT)
				
				
				->where('p.flag_active = 1')
				->where('ppl.flag_active = 1')
				
				
				->where("pl.reference = '$pricelist'");

		$this->assignFilters($select);
		
		 
		//$flag_new_min_date = ProductFilter::getParam('flag_new_minimum_date');
				
		if ($this->columns !== null && is_array($this->columns)) {
			$select->columns($this->columns);
		} else {

			$select->columns(array(
				'product_id'		=> new Expression('p.product_id'),
				'reference'			=> new Expression('p.reference'),
				'display_reference'	=> new Expression('COALESCE(p.display_reference, p.reference)'),
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
				'list_price'		=> new Expression('ppl.list_price'),
				'flag_new'			=> new Expression("(COALESCE(pl.new_product_min_date, '$flag_new_min_date') <= COALESCE(ppl.activated_at, p.activated_at))"),
				'discount_1'		=> new Expression('ppl.discount_1'),
				'discount_2'		=> new Expression('ppl.discount_2'),
				'discount_3'		=> new Expression('ppl.discount_3'),
				'discount_4'		=> new Expression('ppl.discount_4'),
				'is_promotional'	=> new Expression('ppl.is_promotional'),
				'is_bestseller'		=> new Expression('ppl.is_bestseller'),
				'is_bestvalue'		=> new Expression('ppl.is_bestvalue'),
				'is_hot'			=> new Expression('ppl.is_hot'),
				'available_stock'	=> new Expression('ps.available_stock'),
				'theoretical_stock'	=> new Expression('ps.theoretical_stock'),
				'currency_reference'=> new Expression('c.reference'),
				'unit_reference'	=> new Expression('pu.reference'),
				'type_reference'	=> new Expression('pt.reference'),
				'picture_media_id'	=> new Expression('pm.media_id'),
				
			), true);
		}

		$product_id = $params->get('id');
		if ($product_id != '') {
			$select->where("p.product_id = $product_id");
		}
		
		$brands = $params->get('brands');
		if (count($brands) > 0) {
			$brand_clauses = array();
			foreach($brands as $brand_reference) {
				$brand_clauses[] = "pb.reference = '$brand_reference'";	
			}
			$select->where('(' . join(' OR ', $brand_clauses) . ')');
		}
		
		$categories = $params->get('categories');
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
		
		if (($query = trim($params->get('query'))) != "") {
			$platform = $this->adapter->getPlatform();
			$query = str_replace(' ', '%', trim($query));				
			$qRef = $platform->quoteValue($query . '%');
			$qTitle = $platform->quoteValue('%' . $query . '%');
			
                        $ftKeywords = $platform->quoteValue('+' . str_replace(' ', ' +', $query));
                        
			$qclauses = array(
				"p.reference like $qRef",
				
				"p18.title like $qTitle",
				"p.display_reference like $qRef",
                                // FULLTEXT
                                "match(psi.keywords) against ($ftKeywords in boolean mode)"
			);
			$select->where("(" . join(' or ', $qclauses) .")");
                        $select->order(array(
                                new Expression(
                                      "if (p.reference like $qRef, 1000,
                                           if (p18.title like $qTitle, 900,
                                               if (p.display_reference like $qRef, 800,
                                                        match(psi.keywords) against ($ftKeywords in boolean mode) 
                                               )
                                            )
                                      ) desc"
                                )    
                            )
                        );
		}
                
                
                
  		
		return $select;
	}
	
}