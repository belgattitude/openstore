<?php

namespace Openstore\Core\Model\Browser;
use Openstore\Core\Model\Browser\Search\Params;

interface SearchableInterface {
	
	/**
	 * @return array
	 */
	function getSearchableParams();
	
	/**
	 * 
	 * @param array|\Openstore\Core\Model\Browser\Search\Params $params
	 * @return \Openstore\Core\Model\Browser\SearchableInterface
	 */
	function setSearchParams($params);
	
	/**
	 * @return \Openstore\Core\Model\Browser\Search\Params
	 */
	function getSearchParams();
	
}