<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Flyer
 */
class Flyer extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $link_label;

    /**
     * @var integer
     */
    private $rank;

    /**
     * @var \DateTime
     */
    private $create_date;

    /**
     * @var \Eccube\Entity\Master\CustomerType
     */
    private $Target;

    /**
     * @var \Eccube\Entity\Member
     */
    private $Creator;


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
     * Get TrainingName
     *
     * @return string 
     */
    public function getTrainingName()
    {
        $trainingName = '';
        if (!is_null($this->ProductTraining)) {
            $trainingName = $this->ProductTraining->getProduct()->getName();
            if (strlen($trainingName) < 1) {
                $trainingName = $this->ProductTraining->getTrainingType()->getName();
            }
        }
        return $trainingName;
    }

    public function setTrainingName($trainingName)
    {
        return $this;
    }

    /**
     * Get ProductTrainingId
     *
     * @return integer 
     */
    public function getProductTrainingId()
    {
        $productTrainingId = 0;
        if (!is_null($this->ProductTraining)) {
            $productTrainingId = $this->ProductTraining->getId();
        }
        return $productTrainingId;
    }

    public function setProductTrainingId($productTrainingId)
    {
        return $this;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Flyer
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set link_label
     *
     * @param string $linkLabel
     * @return Flyer
     */
    public function setLinkLabel($linkLabel)
    {
        $this->link_label = $linkLabel;

        return $this;
    }

    /**
     * Get link_label
     *
     * @return string 
     */
    public function getLinkLabel()
    {
        return $this->link_label;
    }

    /**
     * Set create_date
     *
     * @param \DateTime $createDate
     * @return Flyer
     */
    public function setCreateDate($createDate)
    {
        $this->create_date = $createDate;

        return $this;
    }

    /**
     * Get create_date
     *
     * @return \DateTime 
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * Set Target
     *
     * @param \Eccube\Entity\Master\CustomerType $target
     * @return Flyer
     */
    public function setTarget(\Eccube\Entity\Master\CustomerType $target = null)
    {
        $this->Target = $target;

        return $this;
    }

    /**
     * Get Target
     *
     * @return \Eccube\Entity\Master\CustomerType 
     */
    public function getTarget()
    {
        return $this->Target;
    }

    /**
     * Set Creator
     *
     * @param \Eccube\Entity\Member $creator
     * @return Flyer
     */
    public function setCreator(\Eccube\Entity\Member $creator)
    {
        $this->Creator = $creator;

        return $this;
    }

    /**
     * Get Creator
     *
     * @return \Eccube\Entity\Member 
     */
    public function getCreator()
    {
        return $this->Creator;
    }
    /**
     * @var \DateTime
     */
    private $disp_from;

    /**
     * @var \DateTime
     */
    private $disp_to;

    /**
     * @var \Eccube\Entity\ProductTraining
     */
    private $ProductTraining;


    /**
     * Set disp_from
     *
     * @param \DateTime $dispFrom
     * @return Flyer
     */
    public function setDispFrom($dispFrom)
    {
        $this->disp_from = $dispFrom;

        return $this;
    }

    /**
     * Get disp_from
     *
     * @return \DateTime 
     */
    public function getDispFrom()
    {
        return (is_null($this->disp_from)?"":$this->disp_from->format('Y/m/d H:i'));
    }

    /**
     * Set disp_to
     *
     * @param \DateTime $dispTo
     * @return Flyer
     */
    public function setDispTo($dispTo)
    {
        $this->disp_to = $dispTo;

        return $this;
    }

    /**
     * Get disp_to
     *
     * @return \DateTime 
     */
    public function getDispTo()
    {
        return (is_null($this->disp_to)?"":$this->disp_to->format('Y/m/d H:i'));
    }

    /**
     * Set ProductTraining
     *
     * @param \Eccube\Entity\ProductTraining $productTraining
     * @return Flyer
     */
    public function setProductTraining(\Eccube\Entity\ProductTraining $productTraining)
    {
        $this->ProductTraining = $productTraining;

        return $this;
    }

    /**
     * Get ProductTraining
     *
     * @return \Eccube\Entity\ProductTraining 
     */
    public function getProductTraining()
    {
        return $this->ProductTraining;
    }
    /**
     * @var \DateTime
     */
    private $update_date;


    /**
     * Set update_date
     *
     * @param \DateTime $updateDate
     * @return Flyer
     */
    public function setUpdateDate($updateDate)
    {
        $this->update_date = $updateDate;

        return $this;
    }

    /**
     * Get update_date
     *
     * @return \DateTime 
     */
    public function getUpdateDate()
    {
        return $this->update_date;
    }

    /**
     * @var \Eccube\Entity\Member
     */
    private $Updater;

    /**
     * Set Updater
     *
     * @param \Eccube\Entity\Member $updater
     * @return Flyer
     */
    public function setUpdater(\Eccube\Entity\Member $updater)
    {
        $this->Updater = $updater;

        return $this;
    }

    /**
     * Get Updater
     *
     * @return \Eccube\Entity\Member 
     */
    public function getUpdater()
    {
        return $this->Updater;
    }
    /**
     * @var \Eccube\Entity\Master\Disp
     */
    private $Status;


    /**
     * Set Status
     *
     * @param \Eccube\Entity\Master\Disp $status
     * @return Flyer
     */
    public function setStatus(\Eccube\Entity\Master\Disp $status = null)
    {
        $this->Status = $status;

        return $this;
    }

    /**
     * Get Status
     *
     * @return \Eccube\Entity\Master\Disp 
     */
    public function getStatus()
    {
        return $this->Status;
    }
    /**
     * @var integer
     */
    private $del_flg = '0';


    /**
     * Set del_flg
     *
     * @param integer $delFlg
     * @return Flyer
     */
    public function setDelFlg($delFlg)
    {
        $this->del_flg = $delFlg;

        return $this;
    }

    /**
     * Get del_flg
     *
     * @return integer 
     */
    public function getDelFlg()
    {
        return $this->del_flg;
    }
}
