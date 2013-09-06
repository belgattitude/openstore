<?php
/**
 * 
 */
namespace Openstore\Catalog\Browser;

use Openstore\Catalog\Browser\SearchParams\SearchParamsAbstract as SearchParams; 
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;


class Category extends BrowserAbstract
{
	
	/**
	 * 
	 * @return \Openstore\Catalog\Browser\SearchParams\Category
	 */
	function getDefaultParams()
	{
		$params = new \Openstore\Catalog\Browser\SearchParams\Category();		
		$params->setIncludeEmptyNodes($include_empty_nodes=false);
		return $params;
	}
	
	/**
	 * 
	 * @param \Openstore\Catalog\Browser\SearchParams\Category
	 * @return \Zend\Db\Sql\Select
	 */
	function getSelect(SearchParams $params=null)
	{
		if ($params === null) $params = $this->getDefaultParams();
		
		$lang		= $this->filter->getLanguage();
		
		$pricelist	= $this->filter->getPricelist();
		
		
		$subselect = new Select();
		$subselect->from(array('p' => 'product'), array())
				->join(array('ppl' => 'product_pricelist'),
						new Expression('ppl.product_id = p.product_id'), array())
				->join(array('pl' => 'pricelist'),
						new Expression('pl.pricelist_id = ppl.pricelist_id'), array())
				->join(array('pb' => 'product_brand'),
						new Expression('pb.brand_id = p.brand_id'), array())
				->where('p.flag_active = 1')
				->where('ppl.flag_active = 1')
				->where("pl.reference = '$pricelist'")
				
				->columns(array(
					'product_id' => new Expression('p.product_id'), 
					'category_id' => new Expression('p.category_id'), 
				))
				->group(array('p.product_id', 'p.category_id'));

		$flag_new_min_date = date('2012-06-30');

		switch($params->getFilter()) {
			case 'new' :
				$subselect->where("(COALESCE(pl.new_product_min_date, '$flag_new_min_date') <= COALESCE(ppl.activated_at, p.activated_at))");
				break;
			case 'promos' :
				$subselect->where("(ppl.promo_discount > 0)");
				break;
			case 'onstock' :
				$subselect->where("(ppl.stock > 0)");
				break;
		}
		
		
		$brands = $params->getBrands();
		if ($brands != '' && count($brands) > 0) {
			$brand_clauses = array();
			foreach ($brands as $brand_reference) {
				$brand_clauses[] = "pb.reference = '$brand_reference'";
			}
			
			$subselect->where('(' . join(' OR ', $brand_clauses) . ')');
		}
		
		/*
		echo $subselect->getSqlString();
		die();
		*/
		$select = new Select();
		
		if (($expanded_category = $params->getExpandedCategory()) !== null) {
			
			$open_categories = array();
			$ancestors = $this->getAncestors($expanded_category);
			foreach($ancestors as $ancestor) {
				$open_categories[$ancestor['category_id']] = $ancestor['reference'];
			}
			$open_categories = "(" . join(',', array_keys($open_categories)) . ")";
		} else {
			
			$open_categories = '(null)';
		}
		
		
		$select->from(array('parent' => 'product_category'),  array())
				->join(array('node' => 'product_category'), 
							new Expression("node.lft BETWEEN parent.lft AND parent.rgt"), 
							array(), $select::JOIN_LEFT)
				->join(array('pc18' => 'product_category_translation'), 
							new Expression("pc18.category_id = parent.category_id and pc18.lang = '$lang'"), 
							array(), $select::JOIN_LEFT)
				->join(array('p' => $subselect),
							new Expression("node.category_id = p.category_id"), 
							array(), $select::JOIN_LEFT);
		

		$columns = array(
			'id' => new Expression('parent.category_id'), 
			'reference' => new Expression('parent.reference'), 
			'title' => new Expression('COALESCE(pc18.title, parent.title)'), 
			//'title' => new Expression('parent.title'), 
			//'is_leaf' => new Expression('if(parent.rgt = (parent.lft+1), 1, 0)'), 
			'is_leaf' => new Expression('CASE WHEN parent.rgt = (parent.lft+1) THEN 1 ELSE 0 END'), 
			'parent_id' => new Expression('parent.parent_id'),	
			'lvl' => new Expression('parent.lvl'),
			'lft' => new Expression('parent.lft'),
			'rgt' => new Expression('parent.rgt'),
			'is_expanded' => new Expression("parent.category_id in $open_categories")
		);
		
		$select->columns(
				array_merge($columns, array(
					'count_product' => new Expression('COUNT(p.product_id)'),
					'count_subcategs' => new Expression('GROUP_CONCAT(distinct if(node.lvl = parent.lvl+1, node.reference, null))')
				)), true);
		
		$select->group($columns);
		
		if (($depth = $params->getDepth()) != 0) {
			if ($expanded_category != '') {
				
//echo "(parent.lvl <= $depth or parent.id in $open_categories or (parent.lft between $parent_left and $parent_right)"; die();
				//$select->where("(parent.lvl <= $depth or parent.id in $open_categories");
				
				$ancestors = $this->getAncestors($expanded_category)->toArray();				

				// close all levels
				
				
				$clauses = array('parent.lvl = 1');
				foreach($ancestors as $idx => $ancestor) {
					//if ($idx < 4) {
						$clauses[$ancestor['reference']] = "(parent.lft between " . $ancestor['lft'] . " and " . $ancestor['rgt'] . ' and parent.lvl = ' . ($ancestor['lvl'] +1) . ')';
					//}
					
				}
				/*
				echo '<pre>';
				var_dump($clauses);
				echo '</pre>';
				die();
				 * 
				 */
				$select->where('(' . join(' or ', $clauses) . ')');
				
				//$select->where('parent.lvl <=1')
				
			} else {
				
				$select->where("(parent.lvl <= $depth)");
			}
		}
		
		
		
		if (!$params->getIncludeEmptyNodes()) {
			$select->having('count_product > 0');
		}
		
		
		
		
		
		//$select->order(array('pc.root' => $select::ORDER_ASCENDING, 'pc.lft' => $select::ORDER_ASCENDING));
		$select->order(array('parent.lft' => $select::ORDER_ASCENDING));
		
		$adapter = $this->adapter;
		$sql = new Sql($adapter);
		$sql_string = $sql->getSqlStringForSqlObject($select);
		
		
		//$results = $adapter->query($sql_string, Adapter::QUERY_MODE_EXECUTE);			
		//echo '<pre>';
		//var_dump($results->toArray());
		//die();
		
		return $select;
	}
	
	
	function getAncestors($category)
	{
		$adapter = $this->adapter;
		
		$lang		= $this->filter->getLanguage();
		
		$sql = new Sql($adapter);
		
		$select = $sql->select();
		
		$select->from(array('pc1' => 'product_category'),  array())
				->join(array('pc2' => 'product_category'), 
							new Expression("pc1.lft BETWEEN pc2.lft AND pc2.rgt"), 
							array(), $select::JOIN_LEFT)
				->join(array('pc18' => 'product_category_translation'), 
							new Expression("pc18.category_id = pc2.category_id and pc18.lang = '$lang'"), 
							array(), $select::JOIN_LEFT);
		
		
		$select->columns(array(
			'category_id'	=> new Expression('pc2.category_id'), 
			'parent_id'		=> new Expression('pc2.parent_id'), 
			'reference'		=> new Expression('pc2.reference'), 
			'title'			=> new Expression('if (pc18.title is null, pc2.title, pc18.title)'), 
			'is_leaf'		=> new Expression('if(pc2.rgt = (pc2.lft+1), 1, 0)'), 
			'lft'			=> new Expression('pc2.lft'),
			'rgt'			=> new Expression('pc2.rgt'),
			'lvl'			=> new Expression('pc2.lvl'),
		));
				
		$select->where(array('pc1.reference' => $category));
		$select->where(array('pc2.lvl > 0'));
		$select->order(array('pc2.lvl' => $select::ORDER_ASCENDING));
		
		$sql_string = $sql->getSqlStringForSqlObject($select);
		
		//echo '<pre>';
		//var_dump($sql_string);die();
		
		$results = $adapter->query($sql_string, $adapter::QUERY_MODE_EXECUTE);			
		//var_dump($results->toArray());
		//die();
		return $results;
		
	}
	
	function getParent($category)
	{
		$adapter = $this->adapter;
		$lang		= $this->filter->getLanguage();
		
		$sql = new Sql($adapter);
		
		$select = $sql->select();
		
		$select->from(array('pc' => 'product_category'),  array())
				->join(array('pc18' => 'product_category_translation'), 
							new Expression("pc18.category_id = pc.id and pc18.lang = '$lang'"), 
							array(), $select::JOIN_LEFT);
		
		
		$select->columns(array(
			'category_id'	=> new Expression('pc.category_id'), 
			'parent_id'		=> new Expression('pc.parent_id'), 
			'reference'		=> new Expression('pc.reference'), 
			'title'			=> new Expression('if (pc18.title is null, pc.title, pc18.title)'), 
			'is_leaf'		=> new Expression('if(pc.rgt = (pc.lft+1), 1, 0)'), 
			'lvl'			=> new Expression('pc.lvl'),
			'lft'			=> new Expression('pc.lft'),
			'rgt'			=> new Expression('pc.rgt'), 
			 
		));
				
		$select->where(array('pc.reference' => $category));
		
		
		
		$sql_string = $sql->getSqlStringForSqlObject($select);
		
		//echo '<pre>';
		//var_dump($sql_string);die();
		//die();
		$results = $adapter->query($sql_string, $adapter::QUERY_MODE_EXECUTE)->toArray();			
		$parent = $results[0];
		//die();
		return $parent;
		
	}
	
	
	
}