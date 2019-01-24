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

    public function getTimeStartYear()
    {
        return (is_null($this->training_date_start) ? "" : $this->training_date_start->format('Y'));
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

    public function getTrainingDateStartDay()
    {
        return (is_null($this->training_date_start)?'':$this->training_date_start->format('Y/m/d'));   
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
    /**
     * @var string
     */
    private $lecturer;

    /**
     * @var string
     */
    private $place_kana;

    /**
     * @var string
     */
    private $place_room;

    /**
     * @var string
     */
    private $tel01;

    /**
     * @var string
     */
    private $tel02;

    /**
     * @var string
     */
    private $tel03;

    /**
     * @var string
     */
    private $tel_second01;

    /**
     * @var string
     */
    private $tel_second02;

    /**
     * @var string
     */
    private $tel_second03;

    /**
     * @var string
     */
    private $fax01;

    /**
     * @var string
     */
    private $fax02;

    /**
     * @var string
     */
    private $fax03;

    /**
     * @var string
     */
    private $place_fee;


    /**
     * Set lecturer
     *
     * @param string $lecturer
     * @return ProductTraining
     */
    public function setLecturer($lecturer)
    {
        $this->lecturer = $lecturer;

        return $this;
    }

    /**
     * Get lecturer
     *
     * @return string 
     */
    public function getLecturer()
    {
        return $this->lecturer;
    }

    /**
     * Set place_kana
     *
     * @param string $placeKana
     * @return ProductTraining
     */
    public function setPlaceKana($placeKana)
    {
        $this->place_kana = $placeKana;

        return $this;
    }

    /**
     * Get place_kana
     *
     * @return string 
     */
    public function getPlaceKana()
    {
        return $this->place_kana;
    }

    /**
     * Set place_room
     *
     * @param string $placeRoom
     * @return ProductTraining
     */
    public function setPlaceRoom($placeRoom)
    {
        $this->place_room = $placeRoom;

        return $this;
    }

    /**
     * Get place_room
     *
     * @return string 
     */
    public function getPlaceRoom()
    {
        return $this->place_room;
    }

    /**
     * Set tel01
     *
     * @param string $tel01
     * @return ProductTraining
     */
    public function setTel01($tel01)
    {
        $this->tel01 = $tel01;

        return $this;
    }

    /**
     * Get tel01
     *
     * @return string 
     */
    public function getTel01()
    {
        return $this->tel01;
    }

    /**
     * Set tel02
     *
     * @param string $tel02
     * @return ProductTraining
     */
    public function setTel02($tel02)
    {
        $this->tel02 = $tel02;

        return $this;
    }

    /**
     * Get tel02
     *
     * @return string 
     */
    public function getTel02()
    {
        return $this->tel02;
    }

    /**
     * Set tel03
     *
     * @param string $tel03
     * @return ProductTraining
     */
    public function setTel03($tel03)
    {
        $this->tel03 = $tel03;

        return $this;
    }

    /**
     * Get tel03
     *
     * @return string 
     */
    public function getTel03()
    {
        return $this->tel03;
    }

    /**
     * Set tel_second01
     *
     * @param string $telSecond01
     * @return ProductTraining
     */
    public function setTelSecond01($telSecond01)
    {
        $this->tel_second01 = $telSecond01;

        return $this;
    }

    /**
     * Get tel_second01
     *
     * @return string 
     */
    public function getTelSecond01()
    {
        return $this->tel_second01;
    }

    /**
     * Set tel_second02
     *
     * @param string $telSecond02
     * @return ProductTraining
     */
    public function setTelSecond02($telSecond02)
    {
        $this->tel_second02 = $telSecond02;

        return $this;
    }

    /**
     * Get tel_second02
     *
     * @return string 
     */
    public function getTelSecond02()
    {
        return $this->tel_second02;
    }

    /**
     * Set tel_second03
     *
     * @param string $telSecond03
     * @return ProductTraining
     */
    public function setTelSecond03($telSecond03)
    {
        $this->tel_second03 = $telSecond03;

        return $this;
    }

    /**
     * Get tel_second03
     *
     * @return string 
     */
    public function getTelSecond03()
    {
        return $this->tel_second03;
    }

    /**
     * Set fax01
     *
     * @param string $fax01
     * @return ProductTraining
     */
    public function setFax01($fax01)
    {
        $this->fax01 = $fax01;

        return $this;
    }

    /**
     * Get fax01
     *
     * @return string 
     */
    public function getFax01()
    {
        return $this->fax01;
    }

    /**
     * Set fax02
     *
     * @param string $fax02
     * @return ProductTraining
     */
    public function setFax02($fax02)
    {
        $this->fax02 = $fax02;

        return $this;
    }

    /**
     * Get fax02
     *
     * @return string 
     */
    public function getFax02()
    {
        return $this->fax02;
    }

    /**
     * Set fax03
     *
     * @param string $fax03
     * @return ProductTraining
     */
    public function setFax03($fax03)
    {
        $this->fax03 = $fax03;

        return $this;
    }

    /**
     * Get fax03
     *
     * @return string 
     */
    public function getFax03()
    {
        return $this->fax03;
    }

    /**
     * Set place_fee
     *
     * @param string $placeFee
     * @return ProductTraining
     */
    public function setPlaceFee($placeFee)
    {
        $this->place_fee = $placeFee;

        return $this;
    }

    /**
     * Get place_fee
     *
     * @return string 
     */
    public function getPlaceFee()
    {
        return $this->place_fee;
    }
}
