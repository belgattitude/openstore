<?php
namespace Openstore\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="discount_condition",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_legacy_mapping_idx",columns={"legacy_mapping"}),
 *     @ORM\UniqueConstraint(name="unique_condition",columns={"pricelist_id", "customer_id", "brand_id", "group_id", "category_id", "product_id", "valid_from", "valid_till"}),
 *   },
 *   indexes={
 *     @ORM\Index(name="valid_from_idx", columns={"valid_from"}),
 *     @ORM\Index(name="valid_till_idx", columns={"valid_till"}),
 *   },  
 *   options={"comment" = "Discount conditions table"}
 * )
 */
class DiscountCondition implements InputFilterAwareInterface
{
	
	/**
	 * @var \Zend\InputFilter\InputFilterInterface $inputFilter
	 */
	protected $inputFilter;

	
	/**
	 * @ORM\Id
	 * @ORM\Column(name="id", type="bigint", nullable=false, options={"unsigned"=true})
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;
	


	/**
	 * 
     * @ORM\ManyToOne(targetEntity="Pricelist", inversedBy="discounts", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="pricelist_id", referencedColumnName="pricelist_id", onDelete="CASCADE", nullable=true)
	 */
	private $pricelist_id;	
	
	/**
	 * 
     * @ORM\ManyToOne(targetEntity="Customer", inversedBy="discounts", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="customer_id", referencedColumnName="customer_id", onDelete="CASCADE", nullable=true)
	 */
	private $customer_id;


	/**
	 * 
     * @ORM\ManyToOne(targetEntity="ProductBrand", inversedBy="discounts", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="brand_id", referencedColumnName="brand_id", onDelete="CASCADE", nullable=true)
	 */
	private $brand_id;
	

	/**
	 * 
     * @ORM\ManyToOne(targetEntity="ProductGroup", inversedBy="discounts", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="group_id", referencedColumnName="group_id", onDelete="CASCADE", nullable=true)
	 */
	private $group_id;

	/**
	 * 
     * @ORM\ManyToOne(targetEntity="ProductCategory", inversedBy="discounts", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="category_id", referencedColumnName="category_id", onDelete="CASCADE", nullable=true)
	 */
	private $category_id;	
	
	/**
	 * 
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="discounts", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id", onDelete="CASCADE", nullable=true)
	 */
	private $product_id;	

	
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
	 * @ORM\Column(type="decimal", precision=12, scale=6, nullable=false, options={"comment"="Fixed price, only for products"})
	 */
	private $fixed_price;

	
	/**
	 * @ORM\Column(type="date", nullable=true, options={"comment" = "Discount valid from"})
	 */
	private $valid_from;

	/**
	 * @ORM\Column(type="date", nullable=true, options={"comment" = "Discount valid till"})
	 */
	private $valid_till;
	
	
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
		return $this->id;
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
	 * @param integer $customer_id
	 */
	public function setCustomerId($customer_id)
	{
		$this->customer_id = $customer_id;
		return $this;
	}	
	
	/**
	 * 
	 * @return integer
	 */
	public function getCustomerId()
	{
		return $this->customer_id;
	}
	
	
	/**
	 * 
	 * @param integer $brand_id
	 */
	public function setBrandId($brand_id)
	{
		$this->brand_id = $brand_id;
		return $this;
	}	
	
	/**
	 * 
	 * @return integer
	 */
	public function getBrandId()
	{
		return $this->brand_id;
	}

	/**
	 * 
	 * @param integer $category_id
	 */
	public function setCategoryId($category_id)
	{
		$this->category_id = $category_id;
		return $this;
	}	
	
	/**
	 * 
	 * @return integer
	 */
	public function getCategoryId()
	{
		return $this->category_id;
	}
	

	/**
	 * 
	 * @param integer $group_id
	 */
	public function setGroupId($group_id)
	{
		$this->group_id = $group_id;
		return $this;
	}	
	
	/**
	 * 
	 * @return integer
	 */
	public function getGroupId()
	{
		return $this->group_id;
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
	 * @return date
	 */
	public function getActivatedAt()
	{
		return $this->activated_at;
	}

	
	/**
	 * @param string $activated_at date in Y-m-d H:i:s format
	 */
	public function setActivatedAt($activated_at)
	{
		$this->activated_at = $activated_at;
		return $this;
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
	public function __get($property) {
		return $this->$property;
	}

	/**
	 * Magic setter to save protected properties.
	 *
	 * @param string $property
	 * @param mixed $value
	 */
	public function __set($property, $value) {
		$this->$property = $value;
	}	
	
	/**
	 * 
	 * @param \Zend\InputFilter\InputFilterInterface $inputFilter
	 */
	public function setInputFilter(InputFilterInterface $inputFilter) {
		$this->inputFiler = $inputFilter;
		return $this;
	}

	/**
	 * 
	 * @return \Zend\InputFilter\InputFilterInterface $inputFilter
	 */
	public function getInputFilter() {
		if (!$this->inputFilter) {
			$inputFilter = new InputFilter();
			$factory = new InputFactory();

			$inputFilter->add($factory->createInput(array(
						'name' => 'reference',
						'required' => true,
						'filters' => array(
							array('name' => 'StripTags'),
							array('name' => 'StringTrim'),
						),
						'validators' => array(
							array(
								'name' => 'StringLength',
								'options' => array(
									'encoding' => 'UTF-8',
									'min' => 1,
									'max' => 60,
								),
							),
						),
					)));

			$this->inputFilter = $inputFilter;
		}

		return $this->inputFilter;
	}
	
}

