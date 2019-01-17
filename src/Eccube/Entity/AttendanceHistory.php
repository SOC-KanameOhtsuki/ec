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
     * @var \DateTime
     */
    private $create_date;

    /**
     * @var \DateTime
     */
    private $update_date;

    /**
     * @var \Eccube\Entity\ProductTraining
     */
    private $ProductTraining;

    /**
     * @var \Eccube\Entity\Customer
     */
    private $Customer;

    /**
     * @var \Eccube\Entity\Master\AttendanceStatus
     */
    private $AttendanceStatus;

    /**
     * @var \Eccube\Entity\Master\AttendanceDenialReason
     */
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
     * @return \Eccube\Entity\Customer
     */
    public function getCustomer()
    {
        return $this->Customer;
    }

    /**
     * Set ProductTraining
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
     * Get ProductTraining
     *
     * @return \Eccube\Entity\ProductTraining
     */
    public function getProductTraining()
    {
        return $this->ProductTraining;
    }

    /**
     * Set AttendanceStatus
     *
     * @param \Eccube\Entity\Master\AttendanceStatus $AttendanceStatus
     * @return CustomerImage
     */
    public function setAttendanceStatus(\Eccube\Entity\Master\AttendanceStatus $AttendanceStatus)
    {
        $this->AttendanceStatus = $AttendanceStatus;

        return $this;
    }

    /**
     * Get AttendanceStatus
     *
     * @return \Eccube\Entity\Master\AttendanceStatus
     */
    public function getAttendanceStatus()
    {
        return $this->AttendanceStatus;
    }

    /**
     * Set AttendanceDenialReason
     *
     * @param \Eccube\Entity\Master\AttendanceDenialReason $AttendanceDenialReason
     * @return CustomerImage
     */
    public function setAttendanceDenialReason(\Eccube\Entity\Master\AttendanceDenialReason $AttendanceDenialReason)
    {
        $this->AttendanceDenialReason = $AttendanceDenialReason;

        return $this;
    }

    /**
     * Get AttendanceDenialReason
     *
     * @return \Eccube\Entity\Master\AttendanceDenialReason
     */
    public function getAttendanceDenialReason()
    {
        return $this->AttendanceDenialReason;
    }
}
