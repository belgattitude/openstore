<?php
namespace Openstore\Model;
use Openstore\Core\Model\AbstractModel;
use Openstore\Core\Model\BrowsableInterface;
use Openstore\Model\Browser\CategoryBrowser;

class Product extends AbstractModel implements BrowsableInterface {
	
	/**
	 * @return \Openstore\Model\Browser\CategoryBrowser
	 */
	function getBrowser()
	{
		return new CategoryBrowser($this);
	}
	
}
