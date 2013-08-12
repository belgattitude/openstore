<?php

/**
 * @author Vanvelthem Sébastien
 */

namespace Smart\Data\Store\Helper;

class Paginator
{

	protected $limit;
	protected $offset;
	protected $behaviour;
	protected $maximumPages;

	function __construct() {
		
	}

	/**
	 * 
	 * @param type $behaviour
	 * @return \Smart\Data\Store\Helper\Paginator
	 */
	function setBehaviour($behaviour) {
		$this->behaviour = $behaviour;
		return $this;
	}

	/**
	 * 
	 * @param type $maximumPages
	 * @return \Smart\Data\Store\Helper\Paginator
	 */
	function setMaximumPages($maximumPages) {
		$this->maximumPages = $maximumPages;
		return $this;
	}

	/**
	 * 
	 * @param type $totalRows
	 * @return \Smart\Data\Store\Helper\Paginator
	 */
	function setTotalRows($totalRows) {
		$this->totalRows = $totalRows;
		return $this;
	}

	/**
	 * 
	 * @param type $offset
	 * @return \Smart\Data\Store\Helper\Paginator
	 */
	function setOffset($offset) {
		$this->offset = $offset;
		return $this;
	}

	/**
	 * 
	 * @param type $limit
	 * @return \Smart\Data\Store\Helper\Paginator
	 */
	function setLimit($limit) {
		$this->limit = $limit;
		return $this;
	}

	/**
	 * 
	 * @return int
	 */
	function getTotalPages() {
		return ceil($this->totalRows / $this->limit);
	}

	/**
	 * 
	 * @return int
	 */
	function getCurrentPage() {
		return ceil(($this->offset + 1) / $this->limit);
	}

	function getPages() {
		$pages = new \ArrayObject();
		for ($i = 0; $i < $this->getTotalPages(); $i++) {
			$pages[$i] = $i + 1;
		}
		return $pages;
	}

}
