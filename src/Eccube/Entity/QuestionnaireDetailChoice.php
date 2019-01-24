<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * QuestionnaireDetailChoice
 */
class QuestionnaireDetailChoice extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $chice_name;

    /**
     * @var string
     */
    private $chice_description;

    /**
     * @var integer
     */
    private $rank;

    /**
     * @var \DateTime
     */
    private $create_date;

    /**
     * @var \Eccube\Entity\QuestionnaireDetail
     */
    private $QuestionnaireDetail;

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
     * Set chice_name
     *
     * @param string $chiceName
     * @return QuestionnaireDetailChoice
     */
    public function setChiceName($chiceName)
    {
        $this->chice_name = $chiceName;

        return $this;
    }

    /**
     * Get chice_name
     *
     * @return string 
     */
    public function getChiceName()
    {
        return $this->chice_name;
    }

    /**
     * Set chice_description
     *
     * @param string $chiceDescription
     * @return QuestionnaireDetailChoice
     */
    public function setChiceDescription($chiceDescription)
    {
        $this->chice_description = $chiceDescription;

        return $this;
    }

    /**
     * Get chice_description
     *
     * @return string 
     */
    public function getChiceDescription()
    {
        return $this->chice_description;
    }

    /**
     * Set rank
     *
     * @param integer $rank
     * @return QuestionnaireDetailChoice
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
     * @return QuestionnaireDetailChoice
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
     * Set QuestionnaireDetail
     *
     * @param \Eccube\Entity\QuestionnaireDetail $questionnaireDetail
     * @return QuestionnaireDetailChoice
     */
    public function setQuestionnaireDetail(\Eccube\Entity\QuestionnaireDetail $questionnaireDetail)
    {
        $this->QuestionnaireDetail = $questionnaireDetail;

        return $this;
    }

    /**
     * Get QuestionnaireDetail
     *
     * @return \Eccube\Entity\QuestionnaireDetail 
     */
    public function getQuestionnaireDetail()
    {
        return $this->QuestionnaireDetail;
    }

    /**
     * Set Creator
     *
     * @param \Eccube\Entity\Member $creator
     * @return QuestionnaireDetailChoice
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
