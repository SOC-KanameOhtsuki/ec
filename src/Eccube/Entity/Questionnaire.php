<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Questionnaire
 */
class Questionnaire extends \Eccube\Entity\AbstractEntity
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
     * @var string
     */
    private $description;

    /**
     * @var integer
     */
    private $del_flg = '0';

    /**
     * @var \DateTime
     */
    private $create_date;

    /**
     * @var \DateTime
     */
    private $update_date;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $QuestionnaireAttachments;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $QuestionnaireDetails;

    /**
     * @var \Eccube\Entity\Member
     */
    private $Creator;

    /**
     * @var \Eccube\Entity\Master\Disp
     */
    private $Status;

    /**
     * @var \Eccube\Entity\Master\CustomerType
     */
    private $Target;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->QuestionnaireAttachments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->QuestionnaireDetails = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Questionnaire
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
     * Set description
     *
     * @param string $description
     * @return Questionnaire
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
     * Set del_flg
     *
     * @param integer $delFlg
     * @return Questionnaire
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

    /**
     * Set create_date
     *
     * @param \DateTime $createDate
     * @return Questionnaire
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
     * Set update_date
     *
     * @param \DateTime $updateDate
     * @return Questionnaire
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
     * Add QuestionnaireAttachments
     *
     * @param \Eccube\Entity\QuestionnaireAttachment $questionnaireAttachments
     * @return Questionnaire
     */
    public function addQuestionnaireAttachment(\Eccube\Entity\QuestionnaireAttachment $questionnaireAttachments)
    {
        $this->QuestionnaireAttachments[] = $questionnaireAttachments;

        return $this;
    }

    /**
     * Remove QuestionnaireAttachments
     *
     * @param \Eccube\Entity\QuestionnaireAttachment $questionnaireAttachments
     */
    public function removeQuestionnaireAttachment(\Eccube\Entity\QuestionnaireAttachment $questionnaireAttachments)
    {
        $this->QuestionnaireAttachments->removeElement($questionnaireAttachments);
    }

    /**
     * Get QuestionnaireAttachments
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getQuestionnaireAttachments()
    {
        return $this->QuestionnaireAttachments;
    }

    /**
     * Add QuestionnaireDetails
     *
     * @param \Eccube\Entity\QuestionnaireDetail $questionnaireDetails
     * @return Questionnaire
     */
    public function addQuestionnaireDetail(\Eccube\Entity\QuestionnaireDetail $questionnaireDetails)
    {
        $this->QuestionnaireDetails[] = $questionnaireDetails;

        return $this;
    }

    /**
     * Remove QuestionnaireDetails
     *
     * @param \Eccube\Entity\QuestionnaireDetail $questionnaireDetails
     */
    public function removeQuestionnaireDetail(\Eccube\Entity\QuestionnaireDetail $questionnaireDetails)
    {
        $this->QuestionnaireDetails->removeElement($questionnaireDetails);
    }

    /**
     * Get QuestionnaireDetails
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getQuestionnaireDetails()
    {
        return $this->QuestionnaireDetails;
    }

    /**
     * Set Creator
     *
     * @param \Eccube\Entity\Member $creator
     * @return Questionnaire
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
     * Set Status
     *
     * @param \Eccube\Entity\Master\Disp $status
     * @return Questionnaire
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
     * Set Target
     *
     * @param \Eccube\Entity\Master\CustomerType $target
     * @return Questionnaire
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
}
