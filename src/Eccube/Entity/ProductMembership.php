<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProductMembership
 */
class ProductMembership extends \Eccube\Entity\AbstractEntity
{
    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getMembershipYear();
    }

    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $membership_year;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set membership_year
     *
     * @param integer $membershipYear
     * @return ProductMembership
     */
    public function setMembershipYear($membershipYear)
    {
        $this->membership_year = $membershipYear;

        return $this;
    }

    /**
     * Get membership_year
     *
     * @return integer 
     */
    public function getMembershipYear()
    {
        return $this->membership_year;
    }

    /**
     * Set create_date
     *
     * @param \DateTime $createDate
     * @return ProductMembership
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
     * @return ProductMembership
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
     * @return ProductMembership
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
     * @var \Doctrine\Common\Collections\Collection
     */
    private $MembershipBilling;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->MembershipBilling = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add MembershipBilling
     *
     * @param \Eccube\Entity\MembershipBilling $membershipBilling
     * @return ProductMembership
     */
    public function addMembershipBilling(\Eccube\Entity\MembershipBilling $membershipBilling)
    {
        $this->MembershipBilling[] = $membershipBilling;

        return $this;
    }

    /**
     * Remove MembershipBilling
     *
     * @param \Eccube\Entity\MembershipBilling $membershipBilling
     */
    public function removeMembershipBilling(\Eccube\Entity\MembershipBilling $membershipBilling)
    {
        $this->MembershipBilling->removeElement($membershipBilling);
    }

    /**
     * Get MembershipBilling
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getMembershipBilling()
    {
        return $this->MembershipBilling;
    }
}
