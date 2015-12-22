<?php

namespace OpenstoreSchema\Core\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="sale_order_line",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_legacy_mapping_idx",columns={"legacy_mapping"}),
 *   },
 *   indexes={
 *     @ORM\Index(name="delivered_at_idx", columns={"delivered_at"}),
 *     @ORM\Index(name="invoiced_at_idx", columns={"invoiced_at"}),
 *     @ORM\Index(name="line_number_idx", columns={"line_number"})
 *   },
 *   options={"comment" = "Order line table"}
 * )
 */
class SaleOrderLine
{
    /**
     * @ORM\Id
     * @ORM\Column(name="line_id", type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $line_id;


    /**
     * @ORM\Column(name="line_number", type="smallint", nullable=true, options={"unsigned"=true, "comment" = "Order line number for display, sort...."})
     */
    private $line_number;

    /**
     * @ORM\Column(type="string", length=60, nullable=true, options={"comment" = "Reference"})
     */
    private $reference;

    /**
     * @ORM\ManyToOne(targetEntity="SaleOrder", inversedBy="lines")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="order_id", onDelete="CASCADE")
     */
    private $order_id;

    /**
     * @ORM\ManyToOne(targetEntity="SaleOrderLineStatus", inversedBy="lines")
     * @ORM\JoinColumn(name="status_id", nullable=false, referencedColumnName="status_id", onDelete="CASCADE")
     */
    private $status_id;

    /**
     * @ORM\ManyToOne(targetEntity="SaleDelivery", inversedBy="lines")
     * @ORM\JoinColumn(name="delivery_id", nullable=true, referencedColumnName="delivery_id", onDelete="CASCADE")
     */
    private $delivery_id;

    /**
     * @ORM\ManyToOne(targetEntity="SaleInvoice", inversedBy="lines")
     * @ORM\JoinColumn(name="invoice_id", nullable=true, referencedColumnName="invoice_id", onDelete="CASCADE")
     */
    private $invoice_id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="orders", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id", nullable=false)
     */
    private $product_id;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=6, nullable=false, options={"comment"="Ordered quantity"})
     */
    private $quantity;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=6, nullable=false, options={"comment"="Total price of line"})
     */
    private $price;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=6, nullable=false, options={"default"=0, "comment"="Regular discount 1"})
     */
    private $discount_1;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=6, nullable=false, options={"default"=0, "comment"="Regular discount 2"})
     */
    private $discount_2;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=6, nullable=false, options={"default"=0, "comment"="Regular discount 3"})
     */
    private $discount_3;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=6, nullable=false, options={"default"=0, "comment"="Regular discount 4"})
     */
    private $discount_4;

    /**
     * @ORM\Column(type="string", length=512, nullable=true, options={"comment" = "Internal comment"})
     */
    private $comment;

    /**
     * @ORM\Column(type="string", length=60, nullable=true, options={"comment" = "Customer reference"})
     */
    private $customer_reference;

    /**
     * @ORM\Column(type="string", length=255, nullable=true, options={"comment" = "Customer comment"})
     */
    private $customer_comment;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"comment" = "Delivery date"})
     */
    private $delivered_at;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"comment" = "Invoice date"})
     */
    private $invoiced_at;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true, options={"comment" = "Record creation timestamp"})
     */
    private $created_at;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true, options={"comment" = "Record last update timestamp"})
     */
    private $updated_at;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"comment" = "Record deletion date"})
     */
    private $deleted_at;

    /**
     * @Gedmo\Blameable(on="create")
     * @ORM\Column(type="string", length=40, nullable=true, options={"comment" = "Creator name"})
     */
    private $created_by;

    /**
     * @Gedmo\Blameable(on="update")
     * @ORM\Column(type="string", length=40, nullable=true, options={"comment" = "Last updater name"})
     */
    private $updated_by;

    /**
     * @ORM\Column(type="string",length=40,nullable=true, options={"comment" = "Unique reference of this record taken from legacy system"})
     */
    protected $legacy_mapping;

    /**
     * @ORM\Column(type="datetime",nullable=true, options={"comment" = "Last synchro timestamp"})
     */
    protected $legacy_synchro_at;

    public function getLineId()
    {
        return $this->line_id;
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function getOrderId()
    {
        return $this->order_id;
    }

    public function getStatusId()
    {
        return $this->status_id;
    }

    public function getProductId()
    {
        return $this->product_id;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getDiscount1()
    {
        return $this->discount_1;
    }

    public function getDiscount2()
    {
        return $this->discount_2;
    }

    public function getDiscount3()
    {
        return $this->discount_3;
    }

    public function getDiscount4()
    {
        return $this->discount_4;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    public function getCustomerReference()
    {
        return $this->customer_reference;
    }

    public function getCustomerComment()
    {
        return $this->customer_comment;
    }

    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
    }

    public function setStatusId($status_id)
    {
        $this->status_id = $status_id;
    }

    public function setProductId($product_id)
    {
        $this->product_id = $product_id;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    public function setPrice($price)
    {
        $this->price = $price;
    }

    public function setDiscount1($discount_1)
    {
        $this->discount_1 = $discount_1;
    }

    public function setDiscount2($discount_2)
    {
        $this->discount_2 = $discount_2;
    }

    public function setDiscount3($discount_3)
    {
        $this->discount_3 = $discount_3;
    }

    public function setDiscount4($discount_4)
    {
        $this->discount_4 = $discount_4;
    }

    public function setCustomerReference($customer_reference)
    {
        $this->customer_reference = $customer_reference;
    }

    public function setCustomerComment($customer_comment)
    {
        $this->customer_comment = $customer_comment;
    }

    /**
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     *
     * @param string $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     *
     * @param string $updated_at
     */
    public function setUpdatedAt($updated_at)
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getDeletedAt()
    {
        return $this->deleted_at;
    }

    /**
     *
     * @param string $updated_at
     */
    public function setDeletedAt($deleted_at)
    {
        $this->deleted_at = $deleted_at;
        return $this;
    }

    /**
     * Return creator username
     * @return string
     */
    public function getCreatedBy()
    {
        return $this->created_by;
    }

    /**
     * Set creator username
     * @param string $created_by
     */
    public function setCreatedBy($created_by)
    {
        $this->created_by = $created_by;
        return $this;
    }

    /**
     * Return last updater username
     * @return string
     */
    public function getUpdatedBy()
    {
        return $this->updated_by;
    }

    /**
     * Set the last updater username
     * @param string $updated_by
     */
    public function setUpdatedBy($updated_by)
    {
        $this->updated_by = $updated_by;
        return $this;
    }

    /**
     * Return legacy mapping
     * @return string $legacy_mapping
     */
    public function getLegacyMapping()
    {
        return $this->legacy_mapping;
    }

    /**
     * Set a legacy mapping for this record
     * @param string $legacy_mapping
     */
    public function setLegacyMapping($legacy_mapping)
    {
        $this->legacy_mapping = $legacy_mapping;
        return $this;
    }

    /**
     * Set legacy synchro time
     * @param string $legacy_mapping
     */
    public function setLegacySynchroAt($legacy_synchro_at)
    {
        $this->legacy_synchro_at = $legacy_synchro_at;
        return $this;
    }

    /**
     * Return legacy synchro timestamp
     * @return string
     */
    public function getLegacySynchroAt()
    {
        return $this->legacy_synchro_at;
    }
}
