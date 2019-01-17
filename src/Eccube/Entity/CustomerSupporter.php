<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CustomerSupporter
 */
class CustomerSupporter extends \Eccube\Entity\AbstractEntity
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
     * @var \Eccube\Entity\Customer
     */
    private $Customer;

    /**
     * @var \Eccube\Entity\Member
     */
    private $Creator;

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
     * Set create_date
     *
     * @param \DateTime $createDate
     * @return CustomerSupporter
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
     * Set Customer
     *
     * @param \Eccube\Entity\Customer $customer
     * @return CustomerSupporter
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
     * Set Creator
     *
     * @param \Eccube\Entity\Member $creator
     * @return CustomerSupporter
     */
    public function setCreator(\Eccube\Entity\Member $creator)
    {
        $this->Creator = $creator;

        return $this;
    }

    /**
     * Get Creator
     *
     * @return \Eccube\Entity\Member 
     */
    public function getCreator()
    {
        return $this->Creator;
    }

    /**
     * Set SupporterType
     *
     * @param \Eccube\Entity\Master\SupporterType $supporterType
     * @return CustomerSupporter
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
}
