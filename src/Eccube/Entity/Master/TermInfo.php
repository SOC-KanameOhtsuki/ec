<?php

namespace Eccube\Entity\Master;

use Doctrine\ORM\Mapping as ORM;

/**
 * TermInfo
 */
class TermInfo extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $term_name;

    /**
     * @var \DateTime
     */
    private $term_start;

    /**
     * @var \DateTime
     */
    private $term_end;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set term_name
     *
     * @param string $termName
     * @return TermInfo
     */
    public function setTermName($termName)
    {
        $this->term_name = $termName;

        return $this;
    }

    /**
     * Get term_name
     *
     * @return string 
     */
    public function getTermName()
    {
        return $this->term_name;
    }

    /**
     * Set term_start
     *
     * @param \DateTime $termStart
     * @return TermInfo
     */
    public function setTermStart($termStart)
    {
        $this->term_start = $termStart;

        return $this;
    }

    /**
     * Get term_start
     *
     * @return \DateTime 
     */
    public function getTermStart()
    {
        return $this->term_start;
    }

    /**
     * Set term_end
     *
     * @param \DateTime $termEnd
     * @return TermInfo
     */
    public function setTermEnd($termEnd)
    {
        $this->term_end = $termEnd;

        return $this;
    }

    /**
     * Get term_end
     *
     * @return \DateTime 
     */
    public function getTermEnd()
    {
        return $this->term_end;
    }

    /**
     * Set del_flg
     *
     * @param integer $delFlg
     * @return TermInfo
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
     * @return TermInfo
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
     * @return TermInfo
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
     * @var integer
     */
    private $term_year;

    /**
     * @var integer
     */
    private $japanese_year;

    /**
     * Set term_year
     *
     * @param integer $termYear
     * @return TermInfo
     */
    public function setTermYear($termYear)
    {
        $this->term_year = $termYear;

        return $this;
    }

    /**
     * Get term_year
     *
     * @return integer 
     */
    public function getTermYear()
    {
        return $this->term_year;
    }

    /**
     * Set japanese_year
     *
     * @param integer $japaneseYear
     * @return TermInfo
     */
    public function setJapaneseYear($japaneseYear)
    {
        $this->japanese_year = $japaneseYear;

        return $this;
    }

    /**
     * Get japanese_year
     *
     * @return integer 
     */
    public function getJapaneseYear()
    {
        return $this->japanese_year;
    }
    /**
     * @var \DateTime
     */
    private $valid_period_start;

    /**
     * @var \DateTime
     */
    private $valid_period_end;

    /**
     * @var integer
     */
    private $valid_flg = '0';


    /**
     * Set valid_period_start
     *
     * @param \DateTime $validPeriodStart
     * @return TermInfo
     */
    public function setValidPeriodStart($validPeriodStart)
    {
        $this->valid_period_start = $validPeriodStart;

        return $this;
    }

    /**
     * Get valid_period_start
     *
     * @return \DateTime 
     */
    public function getValidPeriodStart()
    {
        return $this->valid_period_start;
    }

    /**
     * Set valid_period_end
     *
     * @param \DateTime $validPeriodEnd
     * @return TermInfo
     */
    public function setValidPeriodEnd($validPeriodEnd)
    {
        $this->valid_period_end = $validPeriodEnd;

        return $this;
    }

    /**
     * Get valid_period_end
     *
     * @return \DateTime 
     */
    public function getValidPeriodEnd()
    {
        return $this->valid_period_end;
    }

    /**
     * Set valid_flg
     *
     * @param integer $validFlg
     * @return TermInfo
     */
    public function setValidFlg($validFlg)
    {
        $this->valid_flg = $validFlg;

        return $this;
    }

    /**
     * Get valid_flg
     *
     * @return integer 
     */
    public function getValidFlg()
    {
        return $this->valid_flg;
    }
}
