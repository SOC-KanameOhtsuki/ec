<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MembershipBillingTargetStatus
 */
class MembershipBillingTargetStatus extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var \Eccube\Entity\Master\CustomerBasicInfoStatus
     */
    private $TargetStatus;

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
     * Set TargetStatus
     *
     * @param \Eccube\Entity\Master\CustomerBasicInfoStatus $targetStatus
     * @return MembershipBillingTargetStatus
     */
    public function setTargetStatus(\Eccube\Entity\Master\CustomerBasicInfoStatus $targetStatus = null)
    {
        $this->TargetStatus = $targetStatus;

        return $this;
    }

    /**
     * Get TargetStatus
     *
     * @return \Eccube\Entity\Master\CustomerBasicInfoStatus 
     */
    public function getTargetStatus()
    {
        return $this->TargetStatus;
    }

    /**
     * Set MembershipBilling
     *
     * @param \Eccube\Entity\MembershipBilling $membershipBilling
     * @return MembershipBillingTargetStatus
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
