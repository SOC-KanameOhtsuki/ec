<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MembershipBillingDetail
 */
class MembershipBillingDetail extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $info;

    /**
     * @var \DateTime
     */
    private $create_date;

    /**
     * @var \DateTime
     */
    private $update_date;

    /**
     * @var \Eccube\Entity\Master\MembershipBillingDetailStatus
     */
    private $Status;

    /**
     * @var \Eccube\Entity\Customer
     */
    private $Customer;

    /**
     * @var \Eccube\Entity\MembershipBilling
     */
    private $MembershipBilling;


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
     * Set info
     *
     * @param string $info
     * @return MembershipBillingDetail
     */
    public function setInfo($info)
    {
        $this->info = $info;

        return $this;
    }

    /**
     * Get info
     *
     * @return string 
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * Set create_date
     *
     * @param \DateTime $createDate
     * @return MembershipBillingDetail
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
     * @return MembershipBillingDetail
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
     * Set Status
     *
     * @param \Eccube\Entity\Master\MembershipBillingDetailStatus $status
     * @return MembershipBillingDetail
     */
    public function setStatus(\Eccube\Entity\Master\MembershipBillingDetailStatus $status = null)
    {
        $this->Status = $status;

        return $this;
    }

    /**
     * Get Status
     *
     * @return \Eccube\Entity\Master\MembershipBillingDetailStatus 
     */
    public function getStatus()
    {
        return $this->Status;
    }

    /**
     * Set Customer
     *
     * @param \Eccube\Entity\Customer $customer
     * @return MembershipBillingDetail
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
     * Set MembershipBilling
     *
     * @param \Eccube\Entity\MembershipBilling $membershipBilling
     * @return MembershipBillingDetail
     */
    public function setMembershipBilling(\Eccube\Entity\MembershipBilling $membershipBilling = null)
    {
        $this->MembershipBilling = $membershipBilling;

        return $this;
    }

    /**
     * Get MembershipBilling
     *
     * @return \Eccube\Entity\MembershipBilling 
     */
    public function getMembershipBilling()
    {
        return $this->MembershipBilling;
    }
    /**
     * @var \Eccube\Entity\Order
     */
    private $Order;


    /**
     * Set Order
     *
     * @param \Eccube\Entity\Order $order
     * @return MembershipBillingDetail
     */
    public function setOrder(\Eccube\Entity\Order $order = null)
    {
        $this->Order = $order;

        return $this;
    }

    /**
     * Get Order
     *
     * @return \Eccube\Entity\Order 
     */
    public function getOrder()
    {
        return $this->Order;
    }
}
