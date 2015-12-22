<?php

namespace OpenstoreSchema\Core\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="OpenstoreSchema\Core\Entity\Repository\SaleOrderRepository")
 * @ORM\Table(
 *   name="sale_order",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_legacy_mapping_idx",columns={"legacy_mapping"}),
 *   },
 *   indexes={
 *   },
 *   options={"comment" = "Sales order table"}
 * )
 */
class SaleOrder
{
    /**
     * @ORM\Id
     * @ORM\Column(name="order_id", type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $order_id;

    /**
     * @ORM\Column(type="string", length=60, nullable=true, options={"comment" = "Reference"})
     */
    private $reference;

    /**
     * @ORM\ManyToOne(targetEntity="SaleOrderType", inversedBy="orders")
     * @ORM\JoinColumn(name="type_id", referencedColumnName="type_id", nullable=false, onDelete="CASCADE")
     */
    private $type_id;

    /**
     * @ORM\ManyToOne(targetEntity="SaleOrderStatus", inversedBy="orders")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="status_id", nullable=true, onDelete="CASCADE")
     */
    private $status_id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="orders", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="customer_id", nullable=false)
     */
    private $customer_id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="SaleRep", inversedBy="orders", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="rep_id", referencedColumnName="rep_id", nullable=true)
     */
    private $rep_id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="orders", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="user_id", nullable=true)
     */
    private $user_id;

    /**
     * @ORM\ManyToOne(targetEntity="SaleOrder", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="order_id", onDelete="CASCADE", nullable=true)
     */
    private $parent_id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Pricelist", inversedBy="orders", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="pricelist_id", referencedColumnName="pricelist_id", nullable=false)
     */
    private $pricelist_id;

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
     * @ORM\Column(type="datetime", nullable=true, options={"comment" = "Order/Quote document date"})
     */
    private $document_date;

    /**
     * @ORM\Column(type="datetime", nullable=true, options={"comment" = "When in quote, make an expiry date"})
     */
    private $expires_at;

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

    /**
     *
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customer_id;
    }

    /**
     *
     * @param string $customer_id
     */
    public function setCustomerId($customer_id)
    {
        //$customer = ->getRepository('OpenstoreSchema\Core\Entity\Customer')->find($customer_id);
        $this->customer_id = $customer_id;
        return $this;
    }

    /**
     * @param string $customer_reference
     */
    public function setCustomerReference($customer_reference)
    {
        $this->customer_reference = $customer_reference;
    }

    public function getOrderId()
    {
        return $this->order_id;
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function getTypeId()
    {
        return $this->type_id;
    }

    public function getStatusId()
    {
        return $this->status_id;
    }

    public function getUser_id()
    {
        return $this->user_id;
    }

    public function getPricelistId()
    {
        return $this->pricelist_id;
    }

    public function getDocumentDate()
    {
        return $this->document_date;
    }

    public function getExpiresAt()
    {
        return $this->expires_at;
    }

    public function setOrderId($order_id)
    {
        $this->order_id = $order_id;
    }

    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    public function setTypeId($type_id)
    {
        $this->type_id = $type_id;
    }

    public function setStatusId($status_id)
    {
        $this->status_id = $status_id;
    }

    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    public function setPricelistId($pricelist_id)
    {
        $this->pricelist_id = $pricelist_id;
    }

    public function setDocumentDate($document_date)
    {
        $this->document_date = $document_date;
    }

    public function setExpiresAt($expires_at)
    {
        $this->expires_at = $expires_at;
    }

    /**
     * @return string
     */
    public function getCustomerReference()
    {
        return $this->customer_reference;
    }

    /**
     * @param string $customer_comment
     */
    public function setCustomerComment($customer_comment)
    {
        $this->customer_comment = $customer_comment;
    }

    /**
     * @return string
     */
    public function getCustomerComment()
    {
        return $this->customer_comment;
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
