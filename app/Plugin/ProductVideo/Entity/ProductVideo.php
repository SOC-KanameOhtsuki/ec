<?php

namespace Plugin\ProductVideo\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductVideo
 */
class ProductVideo extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $product_id;

    /**
     * @var string
     */
    private $Embed_main;

    /**
     * @var string
     */
    private $Embed_preview;

    /**
     * @var \Eccube\Entity\Product
     */
    private $Product;

    
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set Embed_main
     *
     * @param string $embedMain
     * @return ProductVideo
     */
    public function setEmbedMain($embedMain)
    {
        $this->Embed_main = $embedMain;

        return $this;
    }

    /**
     * Get Embed_main
     *
     * @return string 
     */
    public function getEmbedMain()
    {
        return $this->Embed_main;
    }

    /**
     * Set Embed_preview
     *
     * @param string $embedPreview
     * @return ProductVideo
     */
    public function setEmbedPreview($embedPreview)
    {
        $this->Embed_preview = $embedPreview;

        return $this;
    }

    /**
     * Get Embed_preview
     *
     * @return string 
     */
    public function getEmbedPreview()
    {
        return $this->Embed_preview;
    }

    /**
     * Set Product
     *
     * @param \Eccube\Entity\Product $product
     * @return ProductVideo
     */
    public function setProduct(\Eccube\Entity\Product $product)
    {
        $this->Product = $product;

        return $this;
    }

    /**
     * Get Product
     *
     * @return \Eccube\Entity\Product 
     */
    public function getProduct()
    {
        return $this->Product;
    }

    /**
     * Get Product
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->product_id;
    }

}
