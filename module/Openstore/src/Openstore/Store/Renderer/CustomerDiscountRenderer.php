<?php
namespace Openstore\Store\Renderer;

use Soluble\FlexStore\Renderer\RowRendererInterface;
use ArrayObject;

class CustomDiscountRenderer implements RowRendererInterface
{
    
    /**
     *
     * @var string
     */
    protected $customer_id;
    
    
    /**
     *
     * @var string
     */
    protected $pricelist_reference;
    
    
    /**
     * 
     * @param int $customer_id customer_id
     * @param string $pricelist_reference pricelist reference
     */
    function __construct($customer_id, $pricelist_reference)
    {
        $this->customer_id = $customer_id;
        $this->pricelist_reference = $pricelist_reference;
        
        $this->loadCustomerDiscounts();
        
    }
    
    protected function loadCustomerDiscounts()
    {
        
    }
    
    /**
     * 
     * @param ArrayObject $row
     */
    function apply(ArrayObject $row) {
        
        if (!$row->offsetExists($this->source_column)) {
            throw new \Exception(__METHOD__ . " Source column '{$this->source_column} does not exists in row.");
        }
        /*
        if (!$row->offsetExists($this->target_column)) {
            throw new \Exception(__METHOD__ . " Target column '{$this->target_column} does not exists in row.");
        } */       

        $media_id = $row[$this->source_column];
        if ($media_id != '') {
            $prefix = str_pad(substr($media_id, -2), 2, "0", STR_PAD_LEFT);
            $row[$this->target_column] = $this->url .  $prefix . '/' . $media_id . ".jpg";
        } 
    }
    
}
