<?php
namespace Openstore\Model;
use Openstore\Core\Model\AbstractModel;
use Openstore\Core\Model\BrowsableInterface;
use Openstore\Model\Browser\ProductSerieBrowser;

class ProductSerie extends AbstractModel implements BrowsableInterface {
	
	/**
	 * @return \Openstore\Model\Browser\ProductSerieBrowser
	 */
	function getBrowser()
	{
		return new ProductSerieBrowser($this);
	}
	
}
