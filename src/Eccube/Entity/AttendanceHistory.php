<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;
use Eccube\Util\EntityUtil;

/**
 * AttendanceHistory
 */
class AttendanceHistory extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Eccube\Entity\AttendanceStatus
     */
    // private $AttendanceStatus;

    /**
     * @var \Eccube\Entity\AttendanceDenialReason
     */
    // private $AttendanceDenialReason;

    /**
     * @var \DateTime
     */
    private $create_date;

    /**
     * @var \DateTime
     */
    private $update_date;

    private $reason;


    /**
     * @var \Eccube\Entity\ProductTraining
     */
    private $ProductTraining;

    /**
     * @var \Eccube\Entity\Customer
     */
    private $Customer;

    private $AttendanceStatus;

    private $AttendanceDenialReason;


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
     * Set create_date
     *
     * @param \DateTime $createDate
     * @return AuthorityRole
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
     * @return AuthorityRole
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

    public function setStatus($status)
    {
        $this->status = $status;
        
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    public function getReason()
    {
        return $this->reason;
    }


    /**
     * Set Customer
     *
     * @param \Eccube\Entity\ProductTraining $ProductTraining
     * @return CustomerImage
     */
    public function setCustomer(\Eccube\Entity\Customer $Customer)
    {
        $this->Customer = $Customer;

        return $this;
    }

    /**
     * Get Customer
     *
     * @return \Eccube\Entity\ProductTraining
     */
    public function getCustomer()
    {
        return $this->Customer;
    }

    /**
     * Set Customer
     *
     * @param \Eccube\Entity\ProductTraining $ProductTraining
     * @return CustomerImage
     */
    public function setProductTraining(\Eccube\Entity\ProductTraining $ProductTraining)
    {
        $this->ProductTraining = $ProductTraining;

        return $this;
    }

    /**
     * Get Customer
     *
     * @return \Eccube\Entity\ProductTraining
     */
    public function getProductTraining()
    {
        return $this->ProductTraining;
    }

    /**
     * Set Customer
     *
     * @param \Eccube\Entity\ProductTraining $ProductTraining
     * @return CustomerImage
     */
    public function setAttendanceStatus(\Eccube\Entity\Master\AttendanceStatus $AttendanceStatus)
    {
        $this->AttendanceStatus = $AttendanceStatus;

        return $this;
    }

    /**
     * Get Customer
     *
     * @return \Eccube\Entity\ProductTraining
     */
    public function getAttendanceStatus()
    {
        return $this->AttendanceStatus;
    }

    /**
     * Set Customer
     *
     * @param \Eccube\Entity\ProductTraining $ProductTraining
     * @return CustomerImage
     */
    public function setAttendanceDenialReason(\Eccube\Entity\Master\AttendanceDenialReason $AttendanceDenialReason)
    {
        $this->AttendanceDenialReason = $AttendanceDenialReason;

        return $this;
    }

    /**
     * Get Customer
     *
     * @return \Eccube\Entity\ProductTraining
     */
    public function getAttendanceDenialReason()
    {
        return $this->AttendanceDenialReason;
    }

}
