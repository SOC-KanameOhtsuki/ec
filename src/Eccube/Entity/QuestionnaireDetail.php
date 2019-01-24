<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * QuestionnaireDetail
 */
class QuestionnaireDetail extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $detail_description;

    /**
     * @var integer
     */
    private $rank;

    /**
     * @var \DateTime
     */
    private $create_date;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $QuestionnaireDetailChoices;

    /**
     * @var \Eccube\Entity\QuestionnaireDetail
     */
    private $Questionnaire;

    /**
     * @var \Eccube\Entity\Member
     */
    private $Creator;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->QuestionnaireDetailChoices = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set detail_description
     *
     * @param string $detailDescription
     * @return QuestionnaireDetail
     */
    public function setDetailDescription($detailDescription)
    {
        $this->detail_description = $detailDescription;

        return $this;
    }

    /**
     * Get detail_description
     *
     * @return string 
     */
    public function getDetailDescription()
    {
        return $this->detail_description;
    }

    /**
     * Set rank
     *
     * @param integer $rank
     * @return QuestionnaireDetail
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
     * Set create_date
     *
     * @param \DateTime $createDate
     * @return QuestionnaireDetail
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
     * Add QuestionnaireDetailChoices
     *
     * @param \Eccube\Entity\QuestionnaireDetailChoice $questionnaireDetailChoices
     * @return QuestionnaireDetail
     */
    public function addQuestionnaireDetailChoice(\Eccube\Entity\QuestionnaireDetailChoice $questionnaireDetailChoices)
    {
        $this->QuestionnaireDetailChoices[] = $questionnaireDetailChoices;

        return $this;
    }

    /**
     * Remove QuestionnaireDetailChoices
     *
     * @param \Eccube\Entity\QuestionnaireDetailChoice $questionnaireDetailChoices
     */
    public function removeQuestionnaireDetailChoice(\Eccube\Entity\QuestionnaireDetailChoice $questionnaireDetailChoices)
    {
        $this->QuestionnaireDetailChoices->removeElement($questionnaireDetailChoices);
    }

    /**
     * Get QuestionnaireDetailChoices
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getQuestionnaireDetailChoices()
    {
        return $this->QuestionnaireDetailChoices;
    }

    /**
     * Set Questionnaire
     *
     * @param \Eccube\Entity\QuestionnaireDetail $questionnaire
     * @return QuestionnaireDetail
     */
    public function setQuestionnaire(\Eccube\Entity\QuestionnaireDetail $questionnaire)
    {
        $this->Questionnaire = $questionnaire;

        return $this;
    }

    /**
     * Get Questionnaire
     *
     * @return \Eccube\Entity\QuestionnaireDetail 
     */
    public function getQuestionnaire()
    {
        return $this->Questionnaire;
    }

    /**
     * Set Creator
     *
     * @param \Eccube\Entity\Member $creator
     * @return QuestionnaireDetail
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
}
