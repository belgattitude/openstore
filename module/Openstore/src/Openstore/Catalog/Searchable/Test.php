<?php
/**
 * 
 */
namespace Openstore\Catalog\Element;

use Openstore\Catalog\Browser\SearchParams\SearchParamsAbstract as SearchParams; 
use Openstore\Catalog\Browser\ProductFilter;
use Openstore\Element\Searchable\Params;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;



class Test extends AbstractElement
{

	
	/**
	 * 
	 * @return \Openstore\Element\Searchable\Params
	 */
	function getSearchableParams()
	{
		return new Params(array(
				'brands'	 => array('required' => false, 'default' => null),
				'query'		 => array('required' => false, 'default' => null),
				'language'	 => array('required' => true),
				'pricelist'	 => array('required' => true),
				'categories' => array('required' => false, 'default' => null),
			)
		);
	}
	
		
}