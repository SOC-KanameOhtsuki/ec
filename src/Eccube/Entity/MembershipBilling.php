<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MembershipBilling
 */
class MembershipBilling extends \Eccube\Entity\AbstractEntity
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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $MembershipBillingDetail;

    /**
     * @var \Eccube\Entity\Master\MembershipBillingStatus
     */
    private $Status;

    /**
     * @var \Eccube\Entity\MembershipBillingTargetYear
     */
    private $TargetYear;

    /**
     * @var \Eccube\Entity\MembershipBillingTargetStatus
     */
    private $TargetStatus;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->MembershipBillingDetail = new \Doctrine\Common\Collections\ArrayCollection();
        $this->TargetYear = new \Doctrine\Common\Collections\ArrayCollection();
        $this->TargetStatus = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * @return MembershipBilling
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
     * @return MembershipBilling
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
     * Add MembershipBillingDetail
     *
     * @param \Eccube\Entity\MembershipBillingDetail $membershipBillingDetail
     * @return MembershipBilling
     */
    public function addMembershipBillingDetail(\Eccube\Entity\MembershipBillingDetail $membershipBillingDetail)
    {
        $this->MembershipBillingDetail[] = $membershipBillingDetail;

        return $this;
    }

    /**
     * Remove MembershipBillingDetail
     *
     * @param \Eccube\Entity\MembershipBillingDetail $membershipBillingDetail
     */
    public function removeMembershipBillingDetail(\Eccube\Entity\MembershipBillingDetail $membershipBillingDetail)
    {
        $this->MembershipBillingDetail->removeElement($membershipBillingDetail);
    }

    /**
     * Get MembershipBillingDetail
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMembershipBillingDetail()
    {
        return $this->MembershipBillingDetail;
    }

    /**
     * Add TargetYear
     *
     * @param \Eccube\Entity\MembershipBillingTargetYear $targetYear
     * @return MembershipBilling
     */
    public function addTargetYear(\Eccube\Entity\MembershipBillingTargetYear $targetYear)
    {
        $this->TargetYear[] = $targetYear;

        return $this;
    }

    /**
     * Remove TargetYear
     *
     * @param \Eccube\Entity\MembershipBillingTargetYear $targetYear
     */
    public function removeTargetYear(\Eccube\Entity\MembershipBillingTargetYear $targetYear)
    {
        $this->TargetYear->removeElement($targetYear);
    }

    /**
     * Get TargetYear
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTargetYear()
    {
        return $this->TargetYear;
    }

    /**
     * Add TargetStatus
     *
     * @param \Eccube\Entity\MembershipBillingTargetStatus $targetStatus
     * @return MembershipBilling
     */
    public function addTargetStatus(\Eccube\Entity\MembershipBillingTargetStatus $targetStatus)
    {
        $this->TargetStatus[] = $targetStatus;

        return $this;
    }

    /**
     * Remove TargetStatus
     *
     * @param \Eccube\Entity\MembershipBillingTargetStatus $targetStatus
     */
    public function removeTargetStatus(\Eccube\Entity\MembershipBillingTargetStatus $targetStatus)
    {
        $this->TargetStatus->removeElement($targetStatus);
    }

    /**
     * Get TargetStatus
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTargetStatus()
    {
        return $this->TargetStatus;
    }

    /**
     * Set Status
     *
     * @param \Eccube\Entity\Master\MembershipBillingStatus $status
     * @return MembershipBilling
     */
    public function setStatus(\Eccube\Entity\Master\MembershipBillingStatus $status = null)
    {
        $this->Status = $status;

        return $this;
    }

    /**
     * Get Status
     *
     * @return \Eccube\Entity\Master\MembershipBillingStatus 
     */
    public function getStatus()
    {
        return $this->Status;
    }
}
