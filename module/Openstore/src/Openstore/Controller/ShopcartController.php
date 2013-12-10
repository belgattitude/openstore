<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
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
	
	
	public function addProductAction() 
	{
		
		$product_id		= $this->params()->fromPost('product_id');
		$quantity		= $this->params()->fromPost('quantity');
		$pricelist_id	= $this->params()->fromPost('pricelist_id');
		$discount_1		= $this->params()->fromPost('discount_1');
		$order_id		= $this->params()->fromPost('order_id');
		$customer_id	= $this->params()->fromPost('customer_id');
		$user_id		= $this->params()->fromPost('user_id');
		
		$table = new SyntheticTable($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
		
		$order = false;
		if ($order_id != '') {
			$order = $table->find('order', $order_id);
		}
		
		if (!$order) {
			try {
				$order = $table->insert('order', array(
					'customer_id' => $customer_id,
					'pricelist_id' => $pricelist_id,
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s'),
					'type_id'	 => null
				));
			} catch(NormalistException\ExceptionInterface $e) {
				die('cool');
			} catch (\Exception $e) {
				die('rrr');
			}

			die('test');
		}
	
		
	}
}
