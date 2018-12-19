<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CustomerBasicInfo
 */
class CustomerBasicInfo extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $customer_number;

    /**
     * @var integer
     */
    private $customer_pin_code;

    /**
     * @var \DateTime
     */
    private $create_date;

    /**
     * @var \DateTime
     */
    private $update_date;

    /**
     * @var \Eccube\Entity\Customer
     */
    private $Customer;

    /**
     * @var \Eccube\Entity\Master\CustomerBasicInfoStatus
     */
    private $Status;

    /**
     * @var \Eccube\Entity\Master\InstructorType
     */
    private $InstructorType;

    /**
     * @var \Eccube\Entity\Master\SupporterType
     */
    private $SupporterType;


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
     * Set customer_number
     *
     * @param string $customerNumber
     * @return CustomerBasicInfo
     */
    public function setCustomerNumber($customerNumber)
    {
        $this->customer_number = $customerNumber;

        return $this;
    }

    /**
     * Get customer_number
     *
     * @return string 
     */
    public function getCustomerNumber()
    {
        return $this->customer_number;
    }

    /**
     * Set customer_pin_code
     *
     * @param integer $customerPinCode
     * @return CustomerBasicInfo
     */
    public function setCustomerPinCode($customerPinCode)
    {
        $this->customer_pin_code = $customerPinCode;

        return $this;
    }

    /**
     * Get customer_pin_code
     *
     * @return integer 
     */
    public function getCustomerPinCode()
    {
        return $this->customer_pin_code;
    }

    /**
     * Set create_date
     *
     * @param \DateTime $createDate
     * @return CustomerBasicInfo
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
     * @return CustomerBasicInfo
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
     * @param \Eccube\Entity\Customer $customer
     * @return CustomerBasicInfo
     */
    public function setCustomer(\Eccube\Entity\Customer $customer = null)
    {
        $this->Customer = $customer;

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
     * Set Status
     *
     * @param \Eccube\Entity\Master\CustomerBasicInfoStatus $status
     * @return CustomerBasicInfo
     */
    public function setStatus(\Eccube\Entity\Master\CustomerBasicInfoStatus $status = null)
    {
        $this->Status = $status;

        return $this;
    }

    /**
     * Get Status
     *
     * @return \Eccube\Entity\Master\CustomerBasicInfoStatus 
     */
    public function getStatus()
    {
        return $this->Status;
    }

    /**
     * Set InstructorType
     *
     * @param \Eccube\Entity\Master\InstructorType $instructorType
     * @return CustomerBasicInfo
     */
    public function setInstructorType(\Eccube\Entity\Master\InstructorType $instructorType = null)
    {
        $this->InstructorType = $instructorType;

        return $this;
    }

    /**
     * Get InstructorType
     *
     * @return \Eccube\Entity\Master\InstructorType 
     */
    public function getInstructorType()
    {
        return $this->InstructorType;
    }

    /**
     * Set SupporterType
     *
     * @param \Eccube\Entity\Master\SupporterType $supporterType
     * @return CustomerBasicInfo
     */
    public function setSupporterType(\Eccube\Entity\Master\SupporterType $supporterType = null)
    {
        $this->SupporterType = $supporterType;

        return $this;
    }

    /**
     * Get SupporterType
     *
     * @return \Eccube\Entity\Master\SupporterType 
     */
    public function getSupporterType()
    {
        return $this->SupporterType;
    }
    /**
     * @var string
     */
    private $qualification;

    /**
     * @var integer
     */
    private $last_pay_membership_year;

    /**
     * @var \DateTime
     */
    private $membership_expired;

    /**
     * @var \DateTime
     */
    private $regular_member_promoted;

    /**
     * @var \Eccube\Entity\Master\Bureau
     */
    private $Bureau;

    /**
     * @var \Eccube\Entity\Master\ExemptionType
     */
    private $ExemptionType;


    /**
     * Set qualification
     *
     * @param string $qualification
     * @return CustomerBasicInfo
     */
    public function setQualification($qualification)
    {
        $this->qualification = $qualification;

        return $this;
    }

    /**
     * Get qualification
     *
     * @return string 
     */
    public function getQualification()
    {
        return $this->qualification;
    }

    /**
     * Set last_pay_membership_year
     *
     * @param integer $lastPayMembershipYear
     * @return CustomerBasicInfo
     */
    public function setLastPayMembershipYear($lastPayMembershipYear)
    {
        $this->last_pay_membership_year = $lastPayMembershipYear;

        return $this;
    }

    /**
     * Get last_pay_membership_year
     *
     * @return integer 
     */
    public function getLastPayMembershipYear()
    {
        return $this->last_pay_membership_year;
    }

    /**
     * Set membership_expired
     *
     * @param \DateTime $membershipExpired
     * @return CustomerBasicInfo
     */
    public function setMembershipExpired($membershipExpired)
    {
        $this->membership_expired = $membershipExpired;

        return $this;
    }

    /**
     * Get membership_expired
     *
     * @return \DateTime 
     */
    public function getMembershipExpired()
    {
        return (is_null($this->membership_expired)?'':$this->membership_expired->format('Y/m/d'));
    }

    /**
     * Set regular_member_promoted
     *
     * @param \DateTime $regularMemberPromoted
     * @return CustomerBasicInfo
     */
    public function setRegularMemberPromoted($regularMemberPromoted)
    {
        $this->regular_member_promoted = $regularMemberPromoted;

        return $this;
    }

    /**
     * Get regular_member_promoted
     *
     * @return \DateTime 
     */
    public function getRegularMemberPromoted()
    {
        return (is_null($this->regular_member_promoted)?'':$this->regular_member_promoted->format('Y/m/d'));
    }

    /**
     * Set Bureau
     *
     * @param \Eccube\Entity\Master\Bureau $bureau
     * @return CustomerBasicInfo
     */
    public function setBureau(\Eccube\Entity\Master\Bureau $bureau = null)
    {
        $this->Bureau = $bureau;

        return $this;
    }

    /**
     * Get Bureau
     *
     * @return \Eccube\Entity\Master\Bureau 
     */
    public function getBureau()
    {
        return $this->Bureau;
    }

    /**
     * Set ExemptionType
     *
     * @param \Eccube\Entity\Master\ExemptionType $exemptionType
     * @return CustomerBasicInfo
     */
    public function setExemptionType(\Eccube\Entity\Master\ExemptionType $exemptionType = null)
    {
        $this->ExemptionType = $exemptionType;

        return $this;
    }

    /**
     * Get ExemptionType
     *
     * @return \Eccube\Entity\Master\ExemptionType 
     */
    public function getExemptionType()
    {
        return $this->ExemptionType;
    }
}
