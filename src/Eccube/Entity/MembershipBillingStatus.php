<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MembershipBillingStatus
 */
class MembershipBillingStatus extends \Eccube\Entity\AbstractEntity
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
     * @var \Eccube\Entity\Master\BillingStatus
     */
    private $Status;

    /**
     * @var \Eccube\Entity\Customer
     */
    private $Customer;

    /**
     * @var \Eccube\Entity\ProductMembership
     */
    private $ProductMembership;


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
     * @return MembershipBillingStatus
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
     * @return MembershipBillingStatus
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
     * @param \Eccube\Entity\Master\BillingStatus $status
     * @return MembershipBillingStatus
     */
    public function setStatus(\Eccube\Entity\Master\BillingStatus $status = null)
    {
        $this->Status = $status;

        return $this;
    }

    /**
     * Get Status
     *
     * @return \Eccube\Entity\Master\BillingStatus 
     */
    public function getStatus()
    {
        return $this->Status;
    }

    /**
     * Set Customer
     *
     * @param \Eccube\Entity\Customer $customer
     * @return MembershipBillingStatus
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
     * Set ProductMembership
     *
     * @param \Eccube\Entity\ProductMembership $productMembership
     * @return MembershipBillingStatus
     */
    public function setProductMembership(\Eccube\Entity\ProductMembership $productMembership = null)
    {
        $this->ProductMembership = $productMembership;

        return $this;
    }

    /**
     * Get ProductMembership
     *
     * @return \Eccube\Entity\ProductMembership 
     */
    public function getProductMembership()
    {
        return $this->ProductMembership;
    }
}
