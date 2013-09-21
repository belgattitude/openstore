<?php
namespace Openstore\Model;
use Openstore\Core\Model\AbstractModel;
use Openstore\Core\Model\BrowsableInterface;
use Openstore\Model\Browser\ProductBrowser;

class Product extends AbstractModel implements BrowsableInterface {
	
	/**
	 * @return \Openstore\Model\Browser\ProductBrowser
	 */
	function getBrowser()
	{
		return new ProductBrowser($this);
	}
	
}
