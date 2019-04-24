<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CustomerInstructor
 */
class CustomerInstructor extends \Eccube\Entity\AbstractEntity
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
     * @var \Eccube\Entity\Master\InstructorType
     */
    private $InstructorType;


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
     * @return CustomerInstructor
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
     * @return CustomerInstructor
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
     * @return CustomerInstructor
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
     * Set InstructorType
     *
     * @param \Eccube\Entity\Master\InstructorType $instructorType
     * @return CustomerInstructor
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
}
