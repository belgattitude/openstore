<?php

namespace OpenstoreSchema\Core\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="product_pricelist",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_product_price_idx",columns={"pricelist_id", "product_id"}),
 *     @ORM\UniqueConstraint(name="unique_legacy_mapping_idx",columns={"legacy_mapping"}),
 *   },
 *   indexes={
 *     @ORM\Index(name="price_idx", columns={"price"}),
 *   },
 *   options={"comment" = "Product pricelist"}
 * )
 */
class ProductPricelist
{
    /**
     * @ORM\Id
     * @ORM\Column(name="product_pricelist_id", type="bigint", nullable=false, options={"unsigned"=true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $product_pricelist_id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Pricelist", inversedBy="products", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="pricelist_id", referencedColumnName="pricelist_id", onDelete="CASCADE", nullable=false)
     */
    private $pricelist_id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="products", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id", onDelete="CASCADE", nullable=false)
     */
    private $product_id;

    /**
     *
     * @ORM\Column(type="boolean", nullable=false, options={"default"=1, "comment"="Whether the product is active in public website"})
     */
    private $flag_active;

    /**
     * Status id
     * @ORM\ManyToOne(targetEntity="ProductStatus", inversedBy="products", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="status_id", referencedColumnName="status_id", onDelete="CASCADE", nullable=true)
     */
    private $status_id;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=6, nullable=false, options={"comment"="Unit sales price discounts included"})
     */
    private $price;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=6, nullable=false, options={"comment"="Unit sales list price wihtout discounts"})
     */
    private $list_price;

    /**
     * @ORM\Column(type="decimal", precision=12, scale=6, nullable=true, options={"comment"="Unit public/msrp price"})
     */
    private $public_price;


    /**
     * @ORM\Column(type="decimal", precision=12, scale=6, nullable=true, options={"comment"="Minimum advertised price, only where regulation applies"})
     */
    private $map_price;

    /**
     * @ORM\Column(type="decimal", precision=16, scale=6, nullable=true, options={"comment"="Sales minimum quantity"})
     */
    private $sale_minimum_qty;

    /**
     * @ORM\Column(type="decimal", precision=16, scale=6, nullable=true, options={"comment"="Sales multiple quantity"})
     */
    private $sale_multiple_qty;

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
     * @ORM\Column(type="boolean", nullable=true, options={"comment"="Whether the product is considered as liquidation (stock clearance), , eventual discounts will appear in discount_1,_2..."})
     */
    private $is_liquidation;

    /**
     *
     * @ORM\Column(type="boolean", nullable=true, options={"comment"="Whether the product is considered as a new product in this pricelist"})
     */
    private $is_new;

    /**
     *
     * @ORM\Column(type="boolean", nullable=true, options={"comment"="Whether the product is considered as a best seller in this pricelist"})
     */
    private $is_bestseller;

    /**
     *
     * @ORM\Column(type="boolean", nullable=true, options={"comment"="Whether the product is considered as a best value in this pricelist"})
     */
    private $is_bestvalue;

    /**
     *
     * @ORM\Column(type="boolean", nullable=true, options={"comment"="Whether the product is considered as a hot product in this pricelist"})
     */
    private $is_hot;

    /**
     * @ORM\Column(type="boolean", nullable=true, options={"comment"="Whether the product is considered as promotional, eventual discounts will appear in discount_1,_2..."})
     */
    private $is_promotional;

    /**
     * @ORM\Column(type="date", nullable=true, options={"comment"="Discount started at"})
     */
    private $promo_start_at;

    /**
     * @ORM\Column(type="date", nullable=true, options={"comment"="Discount end at"})
     */
    private $promo_end_at;

    /**
     * @ORM\Column(type="date", nullable=true, options={"comment" = "Date on which product was made available, useful to display as new product"})
     */
    private $available_at;

    /**
     * @ORM\Column(type="date", nullable=true, options={"comment" = "Date on which product was/will be made unavailable"})
     */
    private $unavailable_at;


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

    public function __construct()
    {
    }

    /**
     *
     * @param integer $id
     */
    public function setId($id)
    {
        $this->product_pricelist_id = $id;
        return $this;
    }

    /**
     *
     * @return integer
     */
    public function getId()
    {
        return $this->product_pricelist_id;
    }

    /**
     *
     * @param integer $pricelist_id
     */
    public function setPricelistId($pricelist_id)
    {
        $this->pricelist_id = $pricelist_id;
        return $this;
    }

    /**
     *
     * @return integer
     */
    public function getPricelistId()
    {
        return $this->pricelist_id;
    }

    /**
     *
     * @param integer $product_id
     */
    public function setProductId($product_id)
    {
        $this->product_id = $product_id;
        return $this;
    }

    /**
     *
     * @return integer
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     *
     * @return boolean
     */
    public function getFlagActive()
    {
        return (boolean) $this->flag_active;
    }

    /**
     *
     */
    public function setFlagActive($flag_active)
    {
        $this->flag_active = $flag_active;
        return $this;
    }

    /**
     *
     * @return date
     */
    public function getUnavailableAt()
    {
        return $this->unavailable_at;
    }

    /**
     * @param string $unavailable_at date in Y-m-d H:i:s format
     */
    public function setUnavailableAt($unavailable_at)
    {
        $this->unavailable_at = $unavailable_at;
        return $this;
    }


    /**
     *
     * @return date
     */
    public function getAvailableAt()
    {
        return $this->available_at;
    }

    /**
     * @param string $available_at date in Y-m-d H:i:s format
     */
    public function setAvailableAt($available_at)
    {
        $this->available_at = $available_at;
        return $this;
    }

    /**
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     * @return ProductPricelist
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * @param float $public_price
     * @return ProductPricelist
     */
    public function setPublicPrice($public_price)
    {
        $this->public_price = $public_price;
        return $this;
    }

    /**
     * @return float
     */
    public function getPublicPrice()
    {
        return $this->public_price;
    }


    /**
     * @param float $map_price
     */
    public function setMapPrice($map_price)
    {
        $this->map_price = $map_price;
    }

    /**
     * @return float
     */
    public function getMapPrice()
    {
        return $this->map_price;
    }



    /**
     * @param string $promo_start_at date Y-m-d H:i:s
     */
    public function setPromoStartAt($promo_start_at)
    {
        $this->promo_start_at = $promo_start_at;
        return $this;
    }

    public function getPromoStartAt()
    {
        return $this->promo_start_at;
    }

    /**
     * @param string $promo_end_at date Y-m-d H:i:s
     */
    public function setPromoEndAt($promo_end_at)
    {
        $this->promo_end_at = $promo_end_at;
        return $this;
    }

    public function getPromoEndAt()
    {
        return $this->promo_end_at;
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

    /**
     * Convert the object to an array.
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getPrice();
    }

    /**
     * Magic getter to expose protected properties.
     *
     * @param string $property
     * @return mixed
     */
    public function __get($property)
    {
        return $this->$property;
    }

    /**
     * Magic setter to save protected properties.
     *
     * @param string $property
     * @param mixed $value
     */
    public function __set($property, $value)
    {
        $this->$property = $value;
    }
}
