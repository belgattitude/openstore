<?php
namespace Openstore\Model;
use Zend\Db\Adapter\AdapterAwareInterface;
use Openstore\Core\Model\AbstractModel;
use Openstore\Core\Model\BrowsableInterface;

use Soluble\Normalist\SyntheticTable;
use Soluble\Normalist\Exception as NormalistException;
use Soluble\Normalist\SyntheticRecord;

use ArrayObject;

class Order extends AbstractModel  {

	
	
	function __construct()
	{
		
	}
	
	
	/**
	 * 
	 * @param ArrayObject $data
	 * @return SyntheticRecord
	 */
	function create(ArrayObject $data)
	{
		$table = new SyntheticTable($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
		$d = $table->getRecordCleanedData('order', $data);

		if (!$d->offsetExists('status_id')) {
			$default_status = $table->findOneBy('order_status', array('flag_default' => 1));
			$d['status_id'] = $default_status['status_id'];
		} 
		
		$now = date('Y-m-d H:i:s');
		if (!$d->offsetExists('created_at')) $d['created_at'] = $now;
		if (!$d->offsetExists('updated_at')) $d['updated_at'] = $now;
		if (!$d->offsetExists('document_date')) $d['document_date'] = $now;
		
		try {
			$order = $table->insert('order', $d);
		} catch(NormalistException\ExceptionInterface $e) {
			throw $e;
		} 
		
		return $order;
	}

	
	function addOrderLine($order_id, ArrayObject $data)
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
