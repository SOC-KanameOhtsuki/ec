<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductTraining
 */
class ProductTraining extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $place;

    /**
     * @var string
     */
    private $zip01;

    /**
     * @var string
     */
    private $zip02;

    /**
     * @var string
     */
    private $zipcode;

    /**
     * @var string
     */
    private $addr01;

    /**
     * @var string
     */
    private $addr02;

    /**
     * @var string
     */
    private $target;

    /**
     * @var string
     */
    private $purpose;

    /**
     * @var string
     */
    private $item;

    /**
     * @var \DateTime
     */
    private $create_date;

    /**
     * @var \DateTime
     */
    private $update_date;

    /**
     * @var \Eccube\Entity\Product
     */
    private $Product;

    /**
     * @var \Eccube\Entity\Master\Pref
     */
    private $Pref;

    /**
     * @var \Eccube\Entity\AttendanceHistory
     */
    private $AttendanceHistories;

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
     * Set training_date(Date)
     *
     * @param \DateTime $day
     * @return ProductTraining
     */
    public function setDay($day)
    {
        return $this;
    }

    /**
     * Get training_date(Date)
     *
     * @return String
     */
    public function getDay()
    {
        return (is_null($this->training_date_start)?null:new \DateTime($this->training_date_start->format('Y/m/d')));
    }

    /**
     * Set training_date_start(Time)
     *
     * @param String $time
     * @return ProductTraining
     */
    public function setTimeStart($time)
    {
        return $this;
    }

    /**
     * Get training_date_start(Time)
     *
     * @return String 
     */
    public function getTimeStart()
    {
        return (is_null($this->training_date_start)?"":$this->training_date_start->format('H:i'));
    }

    /**
     * Set training_date_end(Time)
     *
     * @param String $time
     * @return ProductTraining
     */
    public function setTimeEnd($time)
    {
        return $this;
    }

    /**
     * Get training_date_end(Time)
     *
     * @return String 
     */
    public function getTimeEnd()
    {
        return (is_null($this->training_date_end)?"":$this->training_date_end->format('H:i'));
    }

    /**
     * Set place
     *
     * @param string $place
     * @return ProductTraining
     */
    public function setPlace($place)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return string 
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set zip01
     *
     * @param string $zip01
     * @return ProductTraining
     */
    public function setZip01($zip01)
    {
        $this->zip01 = $zip01;

        return $this;
    }

    /**
     * Get zip01
     *
     * @return string 
     */
    public function getZip01()
    {
        return $this->zip01;
    }

    /**
     * Set zip02
     *
     * @param string $zip02
     * @return ProductTraining
     */
    public function setZip02($zip02)
    {
        $this->zip02 = $zip02;

        return $this;
    }

    /**
     * Get zip02
     *
     * @return string 
     */
    public function getZip02()
    {
        return $this->zip02;
    }

    /**
     * Set zipcode
     *
     * @param string $zipcode
     * @return ProductTraining
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * Get zipcode
     *
     * @return string 
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Set addr01
     *
     * @param string $addr01
     * @return ProductTraining
     */
    public function setAddr01($addr01)
    {
        $this->addr01 = $addr01;

        return $this;
    }

    /**
     * Get addr01
     *
     * @return string 
     */
    public function getAddr01()
    {
        return $this->addr01;
    }

    /**
     * Set addr02
     *
     * @param string $addr02
     * @return ProductTraining
     */
    public function setAddr02($addr02)
    {
        $this->addr02 = $addr02;

        return $this;
    }

    /**
     * Get addr02
     *
     * @return string 
     */
    public function getAddr02()
    {
        return $this->addr02;
    }

    /**
     * Set target
     *
     * @param string $target
     * @return ProductTraining
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * Get target
     *
     * @return string 
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Set purpose
     *
     * @param string $purpose
     * @return ProductTraining
     */
    public function setPurpose($purpose)
    {
        $this->purpose = $purpose;

        return $this;
    }

    /**
     * Get purpose
     *
     * @return string 
     */
    public function getPurpose()
    {
        return $this->purpose;
    }

    /**
     * Set item
     *
     * @param string $item
     * @return ProductTraining
     */
    public function setItem($item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get item
     *
     * @return string 
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * Set create_date
     *
     * @param \DateTime $createDate
     * @return ProductTraining
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
     * @return ProductTraining
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
     * Set Product
     *
     * @param \Eccube\Entity\Product $product
     * @return ProductTraining
     */
    public function setProduct(\Eccube\Entity\Product $product = null)
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
     * Set Pref
     *
     * @param \Eccube\Entity\Master\Pref $pref
     * @return ProductTraining
     */
    public function setPref(\Eccube\Entity\Master\Pref $pref = null)
    {
        $this->Pref = $pref;

        return $this;
    }

    /**
     * Get Pref
     *
     * @return \Eccube\Entity\Master\Pref 
     */
    public function getPref()
    {
        return $this->Pref;
    }
    /**
     * @var \DateTime
     */
    private $training_date_start;

    /**
     * @var \DateTime
     */
    private $training_date_end;


    /**
     * Set training_date_start
     *
     * @param \DateTime $trainingDateStart
     * @return ProductTraining
     */
    public function setTrainingDateStart($trainingDateStart)
    {
        $this->training_date_start = $trainingDateStart;

        return $this;
    }

    /**
     * Get training_date_start
     *
     * @return \DateTime 
     */
    public function getTrainingDateStart()
    {
        return (is_null($this->training_date_start)?'':$this->training_date_start->format('Y/m/d H:i'));
    }

    /**
     * Set training_date_end
     *
     * @param \DateTime $trainingDateEnd
     * @return ProductTraining
     */
    public function setTrainingDateEnd($trainingDateEnd)
    {
        $this->training_date_end = $trainingDateEnd;

        return $this;
    }

    /**
     * Get training_date_end
     *
     * @return \DateTime 
     */
    public function getTrainingDateEnd()
    {
        return (is_null($this->training_date_end)?'':$this->training_date_end->format('Y/m/d H:i'));
    }
    /**
     * @var \Eccube\Entity\Master\TrainingType
     */
    private $TrainingType;


    /**
     * Set TrainingType
     *
     * @param \Eccube\Entity\Master\TrainingType $trainingType
     * @return ProductTraining
     */
    public function setTrainingType(\Eccube\Entity\Master\TrainingType $trainingType = null)
    {
        $this->TrainingType = $trainingType;

        return $this;
    }

    /**
     * Get TrainingType
     *
     * @return \Eccube\Entity\Master\TrainingType 
     */
    public function getTrainingType()
    {
        return $this->TrainingType;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->AttendanceHistories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->TrainingType = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add TrainingType
     *
     * @param \Eccube\Entity\Master\TrainingType $trainingType
     * @return ProductTraining
     */
    public function addTrainingType(\Eccube\Entity\Master\TrainingType $trainingType)
    {
        $this->TrainingType[] = $trainingType;

        return $this;
    }

    /**
     * Remove TrainingType
     *
     * @param \Eccube\Entity\Master\TrainingType $trainingType
     */
    public function removeTrainingType(\Eccube\Entity\Master\TrainingType $trainingType)
    {
        $this->TrainingType->removeElement($trainingType);
    }
    /**
     * @var string
     */
    private $collaborators;

    /**
     * @var string
     */
    private $area;

    /**
     * Set collaborators
     *
     * @param string $collaborators
     * @return ProductTraining
     */
    public function setCollaborators($collaborators)
    {
        $this->collaborators = $collaborators;

        return $this;
    }

    /**
     * Get collaborators
     *
     * @return string 
     */
    public function getCollaborators()
    {
        return $this->collaborators;
    }

    /**
     * Set area
     *
     * @param string $area
     * @return ProductTraining
     */
    public function setArea($area)
    {
        $this->area = $area;

        return $this;
    }

    /**
     * Get area
     *
     * @return string 
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @var \DateTime
     */
    private $accept_limit_date;

    /**
     * Set accept_limit_date
     *
     * @param \DateTime $acceptLimitDate
     * @return ProductTraining
     */
    public function setAcceptLimitDate($acceptLimitDate)
    {
        $this->accept_limit_date = $acceptLimitDate;

        return $this;
    }

    /**
     * Get accept_limit_date
     *
     * @return \DateTime 
     */
    public function getAcceptLimitDate()
    {
        return (is_null($this->accept_limit_date)?"":$this->accept_limit_date->format('Y/m/d H:i'));
    }

    /**
     * Add AttendanceHistory
     *
     * @param  \Eccube\Entity\AttendanceHistory $AttendanceHistory
     * @return Customer
     */
    public function addAttendanceHistory(\Eccube\Entity\AttendanceHistory $AttendanceHistory)
    {
        $this->AttendanceHistories[] = $AttendanceHistory;

        return $this;
    }

    /**
     * Remove AttendanceHistory
     *
     * @param \Eccube\Entity\AttendanceHistory $AttendanceHistory
     */
    public function removeAttendanceHistory(\Eccube\Entity\AttendanceHistory $AttendanceHistory)
    {
        $this->AttendanceHistories->removeElement($AttendanceHistory);
    }

    /**
     * Get AttendanceHistory
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAttendanceHistories()
    {
        return $this->AttendanceHistories;
    }
}
