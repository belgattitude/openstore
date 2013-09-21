<?php

namespace Openstore\Core\Model\Browser;
use Openstore\Core\Model\Browser\Filter\FilterInterface;

interface FilterableInterface {
	
	/**
	 * @return array
	 */
	function getFilters();
	
	/**
	 * 
	 * @param \Openstore\Core\Model\Browser\Filter\FilterInterface $filter
	 * @return \Openstore\Core\Model\Browser\FilterableInterface
	 */
	function addFilter(FilterInterface $filter);
	
	
}