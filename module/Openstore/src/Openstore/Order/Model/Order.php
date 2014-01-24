<?php
namespace Openstore\Order\Model;

use Openstore\Core\Model\AbstractModel;
use Zend\Db\Adapter\AdapterAwareInterface;

use Soluble\Normalist\SyntheticTable;
use Soluble\Normalist\Exception as NormalistException;
use Soluble\Normalist\SyntheticRecord;

use ArrayObject;

class Order extends AbstractModel  {

	/**
	 * @var \Soluble\Normalist\SyntheticTable
	 */
	protected $st;
	
	
	function __construct()
	{
		
	}
	
	
	/**
	 * 
	 * @throws Exception\InvalidCustomerException
	 * @throws Exception\InvalidPricelistException
	 * @throws \Openstore\Order\Model\ExceptionInterface
	 *
	 * @param ArrayObject $data
	 * @return \Soluble\Normalist\SyntheticRecord
	 */
	function create(ArrayObject $data)
	{
		$st = new SyntheticTable($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
		$d = $st->getRecordCleanedData('order', $data);

		if (!$d->offsetExists('status_id')) {
			$default_status = $st->findOneBy('order_status', array('flag_default' => 1));
			$d['status_id'] = $default_status['status_id'];
		} 

		$now = date('Y-m-d H:i:s');
		if (!$d->offsetExists('created_at')) $d['created_at'] = $now;
		if (!$d->offsetExists('updated_at')) $d['updated_at'] = $now;
		if (!$d->offsetExists('document_date')) $d['document_date'] = $now;

		// Validation
		if (!$st->exists('customer', $d['customer_id'])) {
			throw new Exception\InvalidCustomerException("Customer '" . $d['customer_id'] . "' does not exists.");
		} 
		
		if (!$st->exists('pricelist', $d['pricelist_id'])) {
			throw new Exception\InvalidPricelistException("Pricelist '" . $d['pricelist_id'] . "' does not exists.");
		} 
		
		// End of validation
		
		try {
			$order = $st->insert('order', $d);
		} catch(NormalistException\ExceptionInterface $e) {
			throw $e;
		} 
		
		return $order;
	}

	
	/**
	 * 
	 * @param type $order_id
	 * @param ArrayObject $data
	 * @throws Exception\InvalidOrderException
	 * @throws Exception\InvalidProductException
	 * @throws \Openstore\Order\Model\ExceptionInterface
	 * @return \Soluble\Normalist\SyntheticRecord	 
	 */
	function addOrderLine($order_id, ArrayObject $data)
	{
		
		$st = new SyntheticTable($this->getServiceLocator()->get('Zend\Db\Adapter\Adapter'));
		
		$d = $st->getRecordCleanedData('order_line', $data);		
		
		// Validation
		$order = $st->find('order', $order_id);
		if (!$order) {
			throw new Exception\InvalidOrderException("Order '$order_id' does not exists.");
		}
		
		if (!$st->find('product', $d['product_id'])) {
			throw new Exception\InvalidProductException("Product '" . $d['product_id'] . " does not exists.");
		}
		
		// Check for quantity
		
		

		// Filling missing data
		$d['order_id'] = $order_id;
		
		$now = date('Y-m-d H:i:s');
		if (!$d->offsetExists('created_at')) $d['created_at'] = $now;
		if (!$d->offsetExists('updated_at')) $d['updated_at'] = $now;
		if (!$d->offsetExists('status_id')) {
			$default_line_status = $st->findOneBy('order_line_status', array('flag_default' => 1));
			$d['status_id'] = $default_line_status['status_id'];
		}
		
		// Precalculate price and conditions
		if (!$d->offsetExists('price')) {
			// retrieve price
			// Use the priceManager
			$service	  = $this->serviceLocator->get('Openstore\Service');
			$productModel = $service->getModel('Model\Product');			
			
			$pricelist_reference = $order->getParent('pricelist')->reference;
			$product = $productModel->getBrowser()->setSearchParams(
								[
									'id'		 => $d['product_id'],
									'language'	 => '',
									'pricelist'  => $pricelist_reference,
								])
								->getStore()->getData()->current();
			
			if (!$product) {
				throw new Exception\InvalidProductException("Product '" . $d['product_id'] . "' is not active in pricelist '$pricelist_reference'");
			}
			
			$d['price'] = $product['price'] * $d['quantity'];
			$d['discount_1'] = $product['discount_1'];
			$d['discount_2'] = $product['discount_2'];
			$d['discount_3'] = $product['discount_3'];
			$d['discount_4'] = $product['discount_4'];
			
		}
		
		
		try {
			$line = $st->insert('order_line', $d);
		} catch(NormalistException\ExceptionInterface $e) {
			throw $e;
		 
		}
		return $line;
		
		
	}
	
	
}
