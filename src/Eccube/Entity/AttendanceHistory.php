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
    /**
     * @var integer
     */
    private $del_flg = '0';


    /**
     * Set del_flg
     *
     * @param integer $delFlg
     * @return AttendanceHistory
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
     * @var integer
     */
    private $before_qualification;


    /**
     * Set before_qualification
     *
     * @param integer $beforeQualification
     * @return AttendanceHistory
     */
    public function setBeforeQualification($beforeQualification)
    {
        $this->before_qualification = $beforeQualification;

        return $this;
    }

    /**
     * Get before_qualification
     *
     * @return integer 
     */
    public function getBeforeQualification()
    {
        return $this->before_qualification;
    }

    /**
     * @var integer
     */
    private $before_status;

    /**
     * @var string
     */
    private $before_customer_number;

    /**
     * @var integer
     */
    private $before_customer_pin_code;

    /**
     * @var integer
     */
    private $before_last_pay_membership_year;

    /**
     * @var \DateTime
     */
    private $before_membership_expired;

    /**
     * @var \DateTime
     */
    private $before_regular_member_promoted;


    /**
     * Set before_status
     *
     * @param integer $beforeStatus
     * @return AttendanceHistory
     */
    public function setBeforeStatus($beforeStatus)
    {
        $this->before_status = $beforeStatus;

        return $this;
    }

    /**
     * Get before_status
     *
     * @return integer 
     */
    public function getBeforeStatus()
    {
        return $this->before_status;
    }

    /**
     * Set before_customer_number
     *
     * @param string $beforeCustomerNumber
     * @return AttendanceHistory
     */
    public function setBeforeCustomerNumber($beforeCustomerNumber)
    {
        $this->before_customer_number = $beforeCustomerNumber;

        return $this;
    }

    /**
     * Get before_customer_number
     *
     * @return string 
     */
    public function getBeforeCustomerNumber()
    {
        return $this->before_customer_number;
    }

    /**
     * Set before_customer_pin_code
     *
     * @param integer $beforeCustomerPinCode
     * @return AttendanceHistory
     */
    public function setBeforeCustomerPinCode($beforeCustomerPinCode)
    {
        $this->before_customer_pin_code = $beforeCustomerPinCode;

        return $this;
    }

    /**
     * Get before_customer_pin_code
     *
     * @return integer 
     */
    public function getBeforeCustomerPinCode()
    {
        return $this->before_customer_pin_code;
    }

    /**
     * Set before_last_pay_membership_year
     *
     * @param integer $beforeLastPayMembershipYear
     * @return AttendanceHistory
     */
    public function setBeforeLastPayMembershipYear($beforeLastPayMembershipYear)
    {
        $this->before_last_pay_membership_year = $beforeLastPayMembershipYear;

        return $this;
    }

    /**
     * Get before_last_pay_membership_year
     *
     * @return integer 
     */
    public function getBeforeLastPayMembershipYear()
    {
        return $this->before_last_pay_membership_year;
    }

    /**
     * Set before_membership_expired
     *
     * @param \DateTime $beforeMembershipExpired
     * @return AttendanceHistory
     */
    public function setBeforeMembershipExpired($beforeMembershipExpired)
    {
        $this->before_membership_expired = $beforeMembershipExpired;

        return $this;
    }

    /**
     * Get before_membership_expired
     *
     * @return \DateTime 
     */
    public function getBeforeMembershipExpired()
    {
        return $this->before_membership_expired;
    }

    /**
     * Set before_regular_member_promoted
     *
     * @param \DateTime $beforeRegularMemberPromoted
     * @return AttendanceHistory
     */
    public function setBeforeRegularMemberPromoted($beforeRegularMemberPromoted)
    {
        $this->before_regular_member_promoted = $beforeRegularMemberPromoted;

        return $this;
    }

    /**
     * Get before_regular_member_promoted
     *
     * @return \DateTime 
     */
    public function getBeforeRegularMemberPromoted()
    {
        return $this->before_regular_member_promoted;
    }
    /**
     * @var integer
     */
    private $before_membership_exemption;


    /**
     * Set before_membership_exemption
     *
     * @param integer $beforeMembershipExemption
     * @return AttendanceHistory
     */
    public function setBeforeMembershipExemption($beforeMembershipExemption)
    {
        $this->before_membership_exemption = $beforeMembershipExemption;

        return $this;
    }

    /**
     * Get before_membership_exemption
     *
     * @return integer 
     */
    public function getBeforeMembershipExemption()
    {
        return $this->before_membership_exemption;
    }
}
