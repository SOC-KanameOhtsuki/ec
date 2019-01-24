<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * QuestionnaireAttachment
 */
class QuestionnaireAttachment extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $file_name;

    /**
     * @var integer
     */
    private $rank;

    /**
     * @var \DateTime
     */
    private $create_date;

    /**
     * @var \Eccube\Entity\Questionnaire
     */
    private $Questionnaire;

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
     * Set file_name
     *
     * @param string $fileName
     * @return QuestionnaireAttachment
     */
    public function setFileName($fileName)
    {
        $this->file_name = $fileName;

        return $this;
    }

    /**
     * Get file_name
     *
     * @return string 
     */
    public function getFileName()
    {
        return $this->file_name;
    }

    /**
     * Set rank
     *
     * @param integer $rank
     * @return QuestionnaireAttachment
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
     * @return QuestionnaireAttachment
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
     * Set Questionnaire
     *
     * @param \Eccube\Entity\Questionnaire $questionnaire
     * @return QuestionnaireAttachment
     */
    public function setQuestionnaire(\Eccube\Entity\Questionnaire $questionnaire)
    {
        $this->Questionnaire = $questionnaire;

        return $this;
    }

    /**
     * Get Questionnaire
     *
     * @return \Eccube\Entity\Questionnaire 
     */
    public function getQuestionnaire()
    {
        return $this->Questionnaire;
    }

    /**
     * Set Creator
     *
     * @param \Eccube\Entity\Member $creator
     * @return QuestionnaireAttachment
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
     * @var string
     */
    private $label;


    /**
     * Set label
     *
     * @param string $label
     * @return QuestionnaireAttachment
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label
     *
     * @return string 
     */
    public function getLabel()
    {
        return $this->label;
    }
}
