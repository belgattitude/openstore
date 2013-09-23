<?php
namespace Openstore\Model\Browser;

use Openstore\Core\Model\Browser\AbstractBrowser;
//use Openstore\Catalog\Browser\SearchParams\SearchParamsAbstract as SearchParams; 
//use Openstore\Catalog\Browser\ProductFilter;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Expression;


class UserBrowser extends AbstractBrowser {
	
	
	/**
	 * @return array
	 */
	function getSearchableParams() {
		return array(
		);
	}
	
	/**
	 * 
	 * @return \Zend\Db\Sql\Select
	 */
	function getSelect()
	{
		$select = new Select();
		$select->from(array('u' => 'user'));
		return $select;
	}
	
}