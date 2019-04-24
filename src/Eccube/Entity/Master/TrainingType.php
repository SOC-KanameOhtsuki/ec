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
    /**
     * @var integer
     */
    private $qualification;

    /**
     * @var \Eccube\Entity\Master\QualificationType
     */
    private $QualificationType;


    /**
     * Set qualification
     *
     * @param integer $qualification
     * @return TrainingType
     */
    public function setQualification($qualification)
    {
        $this->qualification = $qualification;

        return $this;
    }

    /**
     * Get qualification
     *
     * @return integer 
     */
    public function getQualification()
    {
        return $this->qualification;
    }

    /**
     * Set QualificationType
     *
     * @param \Eccube\Entity\Master\QualificationType $qualificationType
     * @return TrainingType
     */
    public function setQualificationType(\Eccube\Entity\Master\QualificationType $qualificationType = null)
    {
        $this->QualificationType = $qualificationType;

        return $this;
    }

    /**
     * Get QualificationType
     *
     * @return \Eccube\Entity\Master\QualificationType 
     */
    public function getQualificationType()
    {
        return $this->QualificationType;
    }
    /**
     * @var integer
     */
    private $rank_up;


    /**
     * Set rank_up
     *
     * @param integer $rankUp
     * @return TrainingType
     */
    public function setRankUp($rankUp)
    {
        $this->rank_up = $rankUp;

        return $this;
    }

    /**
     * Get rank_up
     *
     * @return integer 
     */
    public function getRankUp()
    {
        return $this->rank_up;
    }
}
