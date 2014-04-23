<?php
/**
 */

namespace Openstore\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;



use Openstore\Order\Model\Exception as OrderException;


class ShopcartController extends AbstractActionController
{
	/**
	 *
	 * @var Openstore\Order\Model\Order
	 */
	protected $shopcart;
	
	

	public function onDispatch(\Zend\Mvc\MvcEvent $e) 
	{
		$this->shopcart = $this->getServiceLocator()->get('Model\Order');
		parent::onDispatch($e);
	}
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
		$product_id	= $this->params()->fromPost('product_id');
		
		$view = new ViewModel();
		$view->test = 'cool';
        return $view;
		
		
	}
	
	public function newAction()
	{
		
	}
	
	/**
	 * 
	 * @return \Doctrine\ORM\EntityManager
	 */
	protected function getEntityManager()
	{
		return $this->getServiceLocator()->get('Doctrine\ORM\EntityManager');
	}
	
	public function addProductAction() {
		
		// Get shopcart type
		//$st = new SyntheticTable($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
		$em = $this->getEntityManager();
		$orderType = $em->getRepository('Openstore\Entity\OrderType');
		$shopcartType = $orderType->findOneBy(array('reference' => 'SHOPCART'));
		
		//$shopcart_order_type = $st->findOneBy('order_type', array('reference' => 'SHOPCART'));
		
		$product_id = $this->params()->fromPost('product_id');
		$order_id   = $this->params()->fromPost('order_id');
		if ($order_id === null) {
			$data = new \ArrayObject(array(
				'pricelist_id' => 1,
				'customer_id' => 3521,
				'type_id' => $shopcartType->type_id
			));
			$order = $this->shopcart->create($data);
			$order_id = $order['order_id'];		
		}
		
		$line_data = new \ArrayObject(array(
			'product_id' => $this->params()->fromPost('product_id'),
			'quantity' => $this->params()->fromPost('quantity'),
			'discount_1' => $this->params()->fromPost('discount_1'),
		));
		
		
		$request = $this->getRequest();

		if ($request->isXmlHttpRequest()) { // If it's ajax call
			
			try {
				$line = $this->shopcart->addOrderLine($order_id, $line_data);
				$response = array(
					'success' => true,
					'message' => 'Success',
					'data' => $line->toArray()
				);
				return new JsonModel($response);
			} catch(OrderException\ExceptionInterface $e) {
				$response = array(
					'success' => false,
					'message' => 'Error',
					'errors' => array(
						'exception' => get_class($e),
						'message' => $e->getMessage(),
						'fields' => array(
							'fieldname' => 'message'
						)
					)
				);
				return new JsonModel($response);

			}
			
		}

		die('Only by XMLHttpRequest');
		
	}
	
}
