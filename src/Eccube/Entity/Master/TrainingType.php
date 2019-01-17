<?php

namespace Eccube\Entity\Master;

use Doctrine\ORM\Mapping as ORM;

/**
 * TrainingType
 */
class TrainingType extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var integer
     */
    private $rank;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $ProductTraining;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ProductTraining = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return TrainingType
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

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
     * Set name
     *
     * @param string $name
     * @return TrainingType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set rank
     *
     * @param integer $rank
     * @return TrainingType
     */
    public function setRank($rank)
    {
        $this->rank = $rank;

        return $this;
    }

    /**
     * Get rank
     *
     * @return integer 
     */
    public function getRank()
    {
        return $this->rank;
    }

    /**
     * Add ProductTraining
     *
     * @param \Eccube\Entity\ProductTraining $productTraining
     * @return TrainingType
     */
    public function addProductTraining(\Eccube\Entity\ProductTraining $productTraining)
    {
        $this->ProductTraining[] = $productTraining;

        return $this;
    }

    /**
     * Remove ProductTraining
     *
     * @param \Eccube\Entity\ProductTraining $productTraining
     */
    public function removeProductTraining(\Eccube\Entity\ProductTraining $productTraining)
    {
        $this->ProductTraining->removeElement($productTraining);
    }

    /**
     * Get ProductTraining
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProductTraining()
    {
        return $this->ProductTraining;
    }
}
