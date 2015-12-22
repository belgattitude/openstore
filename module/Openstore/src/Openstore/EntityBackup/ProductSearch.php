<?php

namespace OpenstoreSchema\Core\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="product_search",
 *   uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_translation_idx",columns={"product_id", "lang"})
 *   },
 *   indexes={
 *     @ORM\Index(name="keywords_ft_idx", columns={"keywords"}, flags={"fulltext"})
 *   },
 *   options={"comment" = "Product search indexes", "engine":"Myisam"}
 * )
 *
 * NOTE THAT ON MYSQL 5.6+ / MariaDB 10+ fulltext index can be on INNODB table
 */
class ProductSearch
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id", type="bigint", nullable=false, options={"unsigned"=true, "comment" = "Primary key"})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     *
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="translations", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="product_id", referencedColumnName="product_id", onDelete="CASCADE", nullable=false)
     */
    private $product_id;

    /**
     * @ORM\ManyToOne(targetEntity="Language", inversedBy="product_translations", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="lang", referencedColumnName="lang", onDelete="RESTRICT", nullable=false)
     */
    private $lang;

    /**
     * @ORM\Column(type="string", length=1500, nullable=true)
     */
    private $keywords;

    /**
     * @ORM\Column(type="string", length=700, nullable=true)
     */
    private $tags;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true, options={"comment" = "Record last update timestamp"})
     */
    private $updated_at;

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
     * @param string $keywords
     */
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getKeywords()
    {
        return $this->keywords;
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
     * @param integer $lang_id
     */
    public function setLangId($lang_id)
    {
        $this->lang_id = $lang_id;
        return $this;
    }

    /**
     *
     * @return integer
     */
    public function getLangId()
    {
        return $this->lang_id;
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
}
