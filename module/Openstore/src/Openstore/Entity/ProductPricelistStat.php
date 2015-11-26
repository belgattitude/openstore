<?php

namespace Openstore\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="product_pricelist_stat",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_legacy_mapping_idx",columns={"legacy_mapping"}),
 *   },
 *   options={"comment" = "Product pricelist quick statistics"}
 * )
 */
class ProductPricelistStat
{
    
    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="ProductPricelist")
     * @ORM\JoinColumn(name="product_pricelist_stat_id", nullable=false, referencedColumnName="product_pricelist_id", onDelete="CASCADE")
     */
    private $product_pricelist_stat_id;
    
    
    /**
     * @ORM\Column(type="decimal", precision=16, scale=6, nullable=true, options={"comment"="Forecasted monthly sales quantity"})
     */
    private $forecasted_monthly_sales;


    /**
     * @ORM\Column(type="date", nullable=true, options={"comment" = "First recorded sale at"})
     */
    private $first_sale_recorded_at;
    

    /**
     * @ORM\Column(type="date", nullable=true, options={"comment" = "Latest recorded sale at"})
     */
    private $latest_sale_recorded_at;
    
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

    public function __construct()
    {
    }

    /**
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     *
     * @return integer
     */
    public function getId()
    {
        return $this->product_pricelist_stat_id;
    }

    /**
     *
     * @param integer $product_pricelist_id
     */
    public function setProduct_PricelistId($product_pricelist_id)
    {
        $this->product_pricelist_id = $product_pricelist_id;
        return $this;
    }

    /**
     *
     * @return integer
     */
    public function getProductPricelistId()
    {
        return $this->product_pricelist_id;
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
     * @param string $legacy_synchro_at
     */
    public function setLegacySynchroAt($legacy_synchro_at)
    {
        $this->legacy_synchro_at = $legacy_synchro_at;
        return $this;
    }

}
