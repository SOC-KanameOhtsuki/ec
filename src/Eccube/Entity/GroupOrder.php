<?php

namespace Eccube\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GroupOrder
 */
class GroupOrder extends \Eccube\Entity\AbstractEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $kana;

    /**
     * @var string
     */
    private $send_to_zip01;

    /**
     * @var string
     */
    private $send_to_zip02;

    /**
     * @var string
     */
    private $send_to_zipcode;

    /**
     * @var string
     */
    private $send_to_addr01;

    /**
     * @var string
     */
    private $send_to_addr02;

    /**
     * @var string
     */
    private $send_to_email;

    /**
     * @var string
     */
    private $send_to_tel01;

    /**
     * @var string
     */
    private $send_to_tel02;

    /**
     * @var string
     */
    private $send_to_tel03;

    /**
     * @var string
     */
    private $send_to_fax01;

    /**
     * @var string
     */
    private $send_to_fax02;

    /**
     * @var string
     */
    private $send_to_fax03;

    /**
     * @var string
     */
    private $bill_to;

    /**
     * @var string
     */
    private $bill_to_zip01;

    /**
     * @var string
     */
    private $bill_to_zip02;

    /**
     * @var string
     */
    private $bill_to_zipcode;

    /**
     * @var string
     */
    private $bill_to_addr01;

    /**
     * @var string
     */
    private $bill_to_addr02;

    /**
     * @var string
     */
    private $bill_to_email;

    /**
     * @var string
     */
    private $bill_to_tel01;

    /**
     * @var string
     */
    private $bill_to_tel02;

    /**
     * @var string
     */
    private $bill_to_tel03;

    /**
     * @var string
     */
    private $bill_to_fax01;

    /**
     * @var string
     */
    private $bill_to_fax02;

    /**
     * @var string
     */
    private $bill_to_fax03;

    /**
     * @var \DateTime
     */
    private $create_date;

    /**
     * @var \DateTime
     */
    private $update_date;

    /**
     * @var \DateTime
     */
    private $order_date;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $Order;

    /**
     * @var \Eccube\Entity\CustomerGroup
     */
    private $CustomerGroup;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->Order = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     * @return GroupOrder
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set kana
     *
     * @param string $kana
     * @return GroupOrder
     */
    public function setKana($kana)
    {
        $this->kana = $kana;

        return $this;
    }

    /**
     * Get kana
     *
     * @return string 
     */
    public function getKana()
    {
        return $this->kana;
    }

    /**
     * Set send_to_zip01
     *
     * @param string $sendToZip01
     * @return GroupOrder
     */
    public function setSendToZip01($sendToZip01)
    {
        $this->send_to_zip01 = $sendToZip01;

        return $this;
    }

    /**
     * Get send_to_zip01
     *
     * @return string 
     */
    public function getSendToZip01()
    {
        return $this->send_to_zip01;
    }

    /**
     * Set send_to_zip02
     *
     * @param string $sendToZip02
     * @return GroupOrder
     */
    public function setSendToZip02($sendToZip02)
    {
        $this->send_to_zip02 = $sendToZip02;

        return $this;
    }

    /**
     * Get send_to_zip02
     *
     * @return string 
     */
    public function getSendToZip02()
    {
        return $this->send_to_zip02;
    }

    /**
     * Set send_to_zipcode
     *
     * @param string $sendToZipcode
     * @return GroupOrder
     */
    public function setSendToZipcode($sendToZipcode)
    {
        $this->send_to_zipcode = $sendToZipcode;

        return $this;
    }

    /**
     * Get send_to_zipcode
     *
     * @return string 
     */
    public function getSendToZipcode()
    {
        return $this->send_to_zipcode;
    }

    /**
     * Set send_to_addr01
     *
     * @param string $sendToAddr01
     * @return GroupOrder
     */
    public function setSendToAddr01($sendToAddr01)
    {
        $this->send_to_addr01 = $sendToAddr01;

        return $this;
    }

    /**
     * Get send_to_addr01
     *
     * @return string 
     */
    public function getSendToAddr01()
    {
        return $this->send_to_addr01;
    }

    /**
     * Set send_to_addr02
     *
     * @param string $sendToAddr02
     * @return GroupOrder
     */
    public function setSendToAddr02($sendToAddr02)
    {
        $this->send_to_addr02 = $sendToAddr02;

        return $this;
    }

    /**
     * Get send_to_addr02
     *
     * @return string 
     */
    public function getSendToAddr02()
    {
        return $this->send_to_addr02;
    }

    /**
     * Set send_to_email
     *
     * @param string $sendToEmail
     * @return GroupOrder
     */
    public function setSendToEmail($sendToEmail)
    {
        $this->send_to_email = $sendToEmail;

        return $this;
    }

    /**
     * Get send_to_email
     *
     * @return string 
     */
    public function getSendToEmail()
    {
        return $this->send_to_email;
    }

    /**
     * Set send_to_tel01
     *
     * @param string $sendToTel01
     * @return GroupOrder
     */
    public function setSendToTel01($sendToTel01)
    {
        $this->send_to_tel01 = $sendToTel01;

        return $this;
    }

    /**
     * Get send_to_tel01
     *
     * @return string 
     */
    public function getSendToTel01()
    {
        return $this->send_to_tel01;
    }

    /**
     * Set send_to_tel02
     *
     * @param string $sendToTel02
     * @return GroupOrder
     */
    public function setSendToTel02($sendToTel02)
    {
        $this->send_to_tel02 = $sendToTel02;

        return $this;
    }

    /**
     * Get send_to_tel02
     *
     * @return string 
     */
    public function getSendToTel02()
    {
        return $this->send_to_tel02;
    }

    /**
     * Set send_to_tel03
     *
     * @param string $sendToTel03
     * @return GroupOrder
     */
    public function setSendToTel03($sendToTel03)
    {
        $this->send_to_tel03 = $sendToTel03;

        return $this;
    }

    /**
     * Get send_to_tel03
     *
     * @return string 
     */
    public function getSendToTel03()
    {
        return $this->send_to_tel03;
    }

    /**
     * Set send_to_fax01
     *
     * @param string $sendToFax01
     * @return GroupOrder
     */
    public function setSendToFax01($sendToFax01)
    {
        $this->send_to_fax01 = $sendToFax01;

        return $this;
    }

    /**
     * Get send_to_fax01
     *
     * @return string 
     */
    public function getSendToFax01()
    {
        return $this->send_to_fax01;
    }

    /**
     * Set send_to_fax02
     *
     * @param string $sendToFax02
     * @return GroupOrder
     */
    public function setSendToFax02($sendToFax02)
    {
        $this->send_to_fax02 = $sendToFax02;

        return $this;
    }

    /**
     * Get send_to_fax02
     *
     * @return string 
     */
    public function getSendToFax02()
    {
        return $this->send_to_fax02;
    }

    /**
     * Set send_to_fax03
     *
     * @param string $sendToFax03
     * @return GroupOrder
     */
    public function setSendToFax03($sendToFax03)
    {
        $this->send_to_fax03 = $sendToFax03;

        return $this;
    }

    /**
     * Get send_to_fax03
     *
     * @return string 
     */
    public function getSendToFax03()
    {
        return $this->send_to_fax03;
    }

    /**
     * Set bill_to
     *
     * @param string $billTo
     * @return GroupOrder
     */
    public function setBillTo($billTo)
    {
        $this->bill_to = $billTo;

        return $this;
    }

    /**
     * Get bill_to
     *
     * @return string 
     */
    public function getBillTo()
    {
        return $this->bill_to;
    }

    /**
     * Set bill_to_zip01
     *
     * @param string $billToZip01
     * @return GroupOrder
     */
    public function setBillToZip01($billToZip01)
    {
        $this->bill_to_zip01 = $billToZip01;

        return $this;
    }

    /**
     * Get bill_to_zip01
     *
     * @return string 
     */
    public function getBillToZip01()
    {
        return $this->bill_to_zip01;
    }

    /**
     * Set bill_to_zip02
     *
     * @param string $billToZip02
     * @return GroupOrder
     */
    public function setBillToZip02($billToZip02)
    {
        $this->bill_to_zip02 = $billToZip02;

        return $this;
    }

    /**
     * Get bill_to_zip02
     *
     * @return string 
     */
    public function getBillToZip02()
    {
        return $this->bill_to_zip02;
    }

    /**
     * Set bill_to_zipcode
     *
     * @param string $billToZipcode
     * @return GroupOrder
     */
    public function setBillToZipcode($billToZipcode)
    {
        $this->bill_to_zipcode = $billToZipcode;

        return $this;
    }

    /**
     * Get bill_to_zipcode
     *
     * @return string 
     */
    public function getBillToZipcode()
    {
        return $this->bill_to_zipcode;
    }

    /**
     * Set bill_to_addr01
     *
     * @param string $billToAddr01
     * @return GroupOrder
     */
    public function setBillToAddr01($billToAddr01)
    {
        $this->bill_to_addr01 = $billToAddr01;

        return $this;
    }

    /**
     * Get bill_to_addr01
     *
     * @return string 
     */
    public function getBillToAddr01()
    {
        return $this->bill_to_addr01;
    }

    /**
     * Set bill_to_addr02
     *
     * @param string $billToAddr02
     * @return GroupOrder
     */
    public function setBillToAddr02($billToAddr02)
    {
        $this->bill_to_addr02 = $billToAddr02;

        return $this;
    }

    /**
     * Get bill_to_addr02
     *
     * @return string 
     */
    public function getBillToAddr02()
    {
        return $this->bill_to_addr02;
    }

    /**
     * Set bill_to_email
     *
     * @param string $billToEmail
     * @return GroupOrder
     */
    public function setBillToEmail($billToEmail)
    {
        $this->bill_to_email = $billToEmail;

        return $this;
    }

    /**
     * Get bill_to_email
     *
     * @return string 
     */
    public function getBillToEmail()
    {
        return $this->bill_to_email;
    }

    /**
     * Set bill_to_tel01
     *
     * @param string $billToTel01
     * @return GroupOrder
     */
    public function setBillToTel01($billToTel01)
    {
        $this->bill_to_tel01 = $billToTel01;

        return $this;
    }

    /**
     * Get bill_to_tel01
     *
     * @return string 
     */
    public function getBillToTel01()
    {
        return $this->bill_to_tel01;
    }

    /**
     * Set bill_to_tel02
     *
     * @param string $billToTel02
     * @return GroupOrder
     */
    public function setBillToTel02($billToTel02)
    {
        $this->bill_to_tel02 = $billToTel02;

        return $this;
    }

    /**
     * Get bill_to_tel02
     *
     * @return string 
     */
    public function getBillToTel02()
    {
        return $this->bill_to_tel02;
    }

    /**
     * Set bill_to_tel03
     *
     * @param string $billToTel03
     * @return GroupOrder
     */
    public function setBillToTel03($billToTel03)
    {
        $this->bill_to_tel03 = $billToTel03;

        return $this;
    }

    /**
     * Get bill_to_tel03
     *
     * @return string 
     */
    public function getBillToTel03()
    {
        return $this->bill_to_tel03;
    }

    /**
     * Set bill_to_fax01
     *
     * @param string $billToFax01
     * @return GroupOrder
     */
    public function setBillToFax01($billToFax01)
    {
        $this->bill_to_fax01 = $billToFax01;

        return $this;
    }

    /**
     * Get bill_to_fax01
     *
     * @return string 
     */
    public function getBillToFax01()
    {
        return $this->bill_to_fax01;
    }

    /**
     * Set bill_to_fax02
     *
     * @param string $billToFax02
     * @return GroupOrder
     */
    public function setBillToFax02($billToFax02)
    {
        $this->bill_to_fax02 = $billToFax02;

        return $this;
    }

    /**
     * Get bill_to_fax02
     *
     * @return string 
     */
    public function getBillToFax02()
    {
        return $this->bill_to_fax02;
    }

    /**
     * Set bill_to_fax03
     *
     * @param string $billToFax03
     * @return GroupOrder
     */
    public function setBillToFax03($billToFax03)
    {
        $this->bill_to_fax03 = $billToFax03;

        return $this;
    }

    /**
     * Get bill_to_fax03
     *
     * @return string 
     */
    public function getBillToFax03()
    {
        return $this->bill_to_fax03;
    }

    /**
     * Set create_date
     *
     * @param \DateTime $createDate
     * @return GroupOrder
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
     * @return GroupOrder
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
     * Set order_date
     *
     * @param \DateTime $orderDate
     * @return GroupOrder
     */
    public function setOrderDate($orderDate)
    {
        $this->order_date = $orderDate;

        return $this;
    }

    /**
     * Get order_date
     *
     * @return \DateTime 
     */
    public function getOrderDate()
    {
        return $this->order_date;
    }

    /**
     * Add Order
     *
     * @param \Eccube\Entity\Order $order
     * @return GroupOrder
     */
    public function addOrder(\Eccube\Entity\Order $order)
    {
        $this->Order[] = $order;

        return $this;
    }

    /**
     * Remove Order
     *
     * @param \Eccube\Entity\Order $order
     */
    public function removeOrder(\Eccube\Entity\Order $order)
    {
        $this->Order->removeElement($order);
    }

    /**
     * Get Order
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getOrder()
    {
        return $this->Order;
    }

    /**
     * Set CustomerGroup
     *
     * @param \Eccube\Entity\CustomerGroup $customerGroup
     * @return GroupOrder
     */
    public function setCustomerGroup(\Eccube\Entity\CustomerGroup $customerGroup = null)
    {
        $this->CustomerGroup = $customerGroup;

        return $this;
    }

    /**
     * Get CustomerGroup
     *
     * @return \Eccube\Entity\CustomerGroup 
     */
    public function getCustomerGroup()
    {
        return $this->CustomerGroup;
    }
    /**
     * @var \Eccube\Entity\Master\Pref
     */
    private $SendToPref;

    /**
     * @var \Eccube\Entity\Master\Pref
     */
    private $BillToPref;


    /**
     * Set SendToPref
     *
     * @param \Eccube\Entity\Master\Pref $sendToPref
     * @return GroupOrder
     */
    public function setSendToPref(\Eccube\Entity\Master\Pref $sendToPref = null)
    {
        $this->SendToPref = $sendToPref;

        return $this;
    }

    /**
     * Get SendToPref
     *
     * @return \Eccube\Entity\Master\Pref 
     */
    public function getSendToPref()
    {
        return $this->SendToPref;
    }

    /**
     * Set BillToPref
     *
     * @param \Eccube\Entity\Master\Pref $billToPref
     * @return GroupOrder
     */
    public function setBillToPref(\Eccube\Entity\Master\Pref $billToPref = null)
    {
        $this->BillToPref = $billToPref;

        return $this;
    }

    /**
     * Get BillToPref
     *
     * @return \Eccube\Entity\Master\Pref 
     */
    public function getBillToPref()
    {
        return $this->BillToPref;
    }
    /**
     * @var integer
     */
    private $del_flg = '0';


    /**
     * Set del_flg
     *
     * @param integer $delFlg
     * @return GroupOrder
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
}
