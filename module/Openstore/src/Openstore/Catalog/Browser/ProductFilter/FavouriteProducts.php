<?php

namespace Openstore\Catalog\Browser\ProductFilter;

use Openstore\Catalog\Browser\ProductFilter;
use Openstore\Catalog\Browser\ProductFilter\AllProducts;
use Zend\Db\Sql\Select;

class FavouriteProducts extends AllProducts
{
	
	
	
	/**
	 * @param \Zend\Db\Sql\Select $select
	 * @return \Openstore\Catalog\Browser\ProductFilter\FilterAbstract
	 */
	function setConstraints(Select $select) {

		// TODO
		return $this;
	}
	
	
	
}