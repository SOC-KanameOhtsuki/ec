<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MembershipBillingTargetYear
 */
class MembershipBillingTargetYear extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Eccube\Entity\ProductMembership
     */
    private $ProductMembership;

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
     * Set ProductMembership
     *
     * @param \Eccube\Entity\ProductMembership $productMembership
     * @return MembershipBillingTargetYear
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

    /**
     * Set MembershipBilling
     *
     * @param \Eccube\Entity\MembershipBilling $membershipBilling
     * @return MembershipBillingTargetYear
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
}
