<?php
namespace Openstore\Model;
use Zend\Db\Adapter\AdapterAwareInterface;
use Openstore\Core\Model\AbstractModel;
use Openstore\Core\Model\BrowsableInterface;

use Soluble\Normalist\SyntheticTable;
use Soluble\Normalist\Exception as NormalistException;
use Soluble\Normalist\SyntheticRecord;

class Order extends AbstractModel  {

	
	
	function __construct()
	{
		
	}
	
	
	/**
	 * 
	 * @param array $data
	 * @return SyntheticRecord
	 */
	function create($data)
	{
		$table = new SyntheticTable($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
		
		$customer_id  = $data['customer_id'];
		$pricelist_id = $data['pricelist_id'];
		$type_id	  = $data['type_id'];

		if (!array_key_exists('status_id', $data)) {
			$default_status = $table->findOneBy('order_status', array('flag_default' => 1));
			$status_id = $default_status['status_id'];
		} else {
			$status_id = $data['status_id'];
		}
		
		
		try {
			$order = $table->insert('order', array(
				'customer_id' => $customer_id,
				'pricelist_id' => $pricelist_id,
				'created_at' => date('Y-m-d H:i:s'),
				'updated_at' => date('Y-m-d H:i:s'),
				'status_id'  => $status_id,
				'type_id'	 => $type_id
			));
		} catch(NormalistException\ExceptionInterface $e) {
			throw $e;
		} 
		
		return $order;
	}

	
	function addOrderLine($data)
	{
		
		$product_id		= $this->params()->fromPost('product_id');
		$quantity		= $this->params()->fromPost('quantity');
		$pricelist_id	= $this->params()->fromPost('pricelist_id');
		$discount_1		= $this->params()->fromPost('discount_1');
		$order_id		= $this->params()->fromPost('order_id');
		$customer_id	= $this->params()->fromPost('customer_id');
		$user_id		= $this->params()->fromPost('user_id');
		
		$table = new SyntheticTable($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
		
		// Step 1. Try to get the order.
		$order = false;
		if ($order_id != '') {
			$order = $table->find('order', $order_id);
		}
		
		$shopcart_order_type = $table->findOneBy('order_type', array('reference' => 'SHOPCART'));
		
		$type_id = $shopcart_order_type['type_id'];
		$customer_id = 3521;
		$pricelist_id = 1;
		
		
		$default_status = $table->findOneBy('order_status', array('flag_default' => 1));
		$status_id = $default_status['status_id'];
		$default_line_status = $table->findOneBy('order_line_status', array('flag_default' => 1));
		$line_status_id = $default_line_status['status_id'];
		
		if (!$order) {
			try {
				$order = $table->insert('order', array(
					'customer_id' => $customer_id,
					'pricelist_id' => $pricelist_id,
					'created_at' => date('Y-m-d H:i:s'),
					'updated_at' => date('Y-m-d H:i:s'),
					'status_id'  => $status_id,
					'type_id'	 => $type_id
				));
			} catch(NormalistException\ExceptionInterface $e) {
				throw $e;
			} 
		}
		
		// Adding product into shopcart
		
		$order_line = $table->insert('order_line', array(
				'product_id' =>  $product_id,
				'quantity'	 => $quantity,
				//'discount_1' => $discount_1,
				'order_id'	 => $order['order_id']
			
		));
		
		
	}
	
	
}
