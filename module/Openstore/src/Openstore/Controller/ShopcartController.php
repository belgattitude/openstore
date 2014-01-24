<?php
/**
 */

namespace Openstore\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Soluble\Normalist\SyntheticTable;
use Soluble\Normalist\Exception as NormalistException;

class ShopcartController extends AbstractActionController
{
    public function indexAction()
    {
		
		$view = new ViewModel();
        return $view;
    }
	
	
	/**
	 * Create a shopcart
	 * 
	 */
	public function createAction()
	{
		$product_id		= $this->params()->fromPost('product_id');
		
		
		
	}
	
}
