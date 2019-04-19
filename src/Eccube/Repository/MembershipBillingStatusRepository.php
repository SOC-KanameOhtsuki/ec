<?php

namespace Eccube\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * MembershipBillingStatusRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MembershipBillingStatusRepository extends EntityRepository
{
	public function getBillingStatus($Customer = null)
    {
        return $this->createQueryBuilder('h')
                ->where('h.Customer = :Customer')
                ->orderBy('h.ProductMembership')
                ->setParameter('Customer', $Customer)
                ->getQuery()
                ->getResult();
    }

    public function countTargetOrder($target_status_list)
    {
        $em = $this->getEntityManager();
        $sql = "SELECT";
        $sql .= " count(*)";
        $sql .= " FROM (";
        $sql .= " SELECT";
        $sql .= " dtb_order.order_id";
        $sql .= " FROM";
        $sql .= " dtb_order";
        $sql .= " LEFT JOIN dtb_order_detail ON dtb_order_detail.order_id = dtb_order.order_id";
        $sql .= " INNER JOIN dtb_product_membership ON dtb_product_membership.product_id = dtb_order_detail.product_id";
        $sql .= " WHERE";
        $cnt = 0;
        $sql .= " dtb_order.`status` IN (";
        foreach($target_status_list as $target_status) {
            $sql .= (($cnt>0)?",":"") . $target_status;
            ++$cnt;
        }
        $sql .= " )";
        $sql .= " AND CONCAT(LPAD('0', 11, dtb_order.customer_id), dtb_order.customer_id, LPAD('0', 11, dtb_order_detail.product_id), dtb_order_detail.product_id) NOT IN ";
        $sql .= " (";
        $sql .= " SELECT";
        $sql .= " CONCAT(LPAD('0', 11, dtb_membership_billing_status.customer), dtb_membership_billing_status.customer, LPAD('0', 11, dtb_product_membership.product_id), dtb_product_membership.product_id)";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " LEFT JOIN dtb_product_membership ON dtb_product_membership.product_membership_id = dtb_membership_billing_status.product_membership";
        $sql .= " ) ";
        $sql .= " AND dtb_order.`del_flg` <> 1";
        $sql .= " GROUP BY";
        $sql .= " dtb_order.order_id";
        $sql .= " ) TEMP;";
        $result = $em->getConnection()->fetchColumn($sql);
        return $result;
    }

    public function insertFromOrder($target_status_list)
    {
        $em = $this->getEntityManager();
        $sql = "INSERT dtb_membership_billing_status (`product_membership`, `customer`, `status`, `create_date`, `update_date`)";
        $sql .= " SELECT";
        $sql .= " dtb_product_membership.product_membership_id,";
        $sql .= " dtb_order.customer_id,";
        $sql .= " 1,";
        $sql .= " dtb_order.payment_date,";
        $sql .= " dtb_order.payment_date";
        $sql .= " FROM";
        $sql .= " dtb_order";
        $sql .= " LEFT JOIN dtb_order_detail ON dtb_order_detail.order_id = dtb_order.order_id";
        $sql .= " INNER JOIN dtb_product_membership ON dtb_product_membership.product_id = dtb_order_detail.product_id";
        $sql .= " WHERE";
        $cnt = 0;
        $sql .= " dtb_order.`status` IN (";
        foreach($target_status_list as $target_status) {
            $sql .= (($cnt>0)?",":"") . $target_status;
            ++$cnt;
        }
        $sql .= " )";
        $sql .= " AND CONCAT(LPAD('0', 11, dtb_order.customer_id), dtb_order.customer_id, LPAD('0', 11, dtb_order_detail.product_id), dtb_order_detail.product_id) NOT IN ";
        $sql .= " (";
        $sql .= " SELECT";
        $sql .= " CONCAT(LPAD('0', 11, dtb_membership_billing_status.customer), dtb_membership_billing_status.customer, LPAD('0', 11, dtb_product_membership.product_id), dtb_product_membership.product_id)";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " LEFT JOIN dtb_product_membership ON dtb_product_membership.product_membership_id = dtb_membership_billing_status.product_membership";
        $sql .= " )";
        $sql .= " AND dtb_order.`del_flg` <> 1";
        $sql .= " GROUP BY";
        $sql .= " dtb_order.order_id;";
        $result = $em->getConnection()->executeQuery($sql);
        return $result;
    }

    public function countNotPaymentStatus($target_status_list)
    {
        $em = $this->getEntityManager();
        $sql = "SELECT";
        $sql .= " count(*)";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " LEFT JOIN dtb_product_membership ON dtb_product_membership.product_membership_id = dtb_membership_billing_status.product_membership";
        $sql .= " WHERE";
        $sql .= " CONCAT(LPAD('0', 11, dtb_membership_billing_status.customer), dtb_membership_billing_status.customer, LPAD('0', 11, dtb_product_membership.product_id), dtb_product_membership.product_id) NOT IN (";
        $sql .= " SELECT";
        $sql .= " CONCAT(LPAD('0', 11, dtb_order.customer_id), dtb_order.customer_id, LPAD('0', 11, dtb_order_detail.product_id), dtb_order_detail.product_id)";
        $sql .= " FROM";
        $sql .= " dtb_order";
        $sql .= " LEFT JOIN dtb_order_detail ON dtb_order_detail.order_id = dtb_order.order_id";
        $sql .= " INNER JOIN dtb_product_membership ON dtb_product_membership.product_id = dtb_order_detail.product_id";
        $sql .= " WHERE";
        $sql .= " dtb_order.`status` IN (";
        $cnt = 0;
        foreach($target_status_list as $target_status) {
            $sql .= (($cnt>0)?",":"") . $target_status;
            ++$cnt;
        }
        $sql .= " )";
        $sql .= " AND dtb_order.`del_flg` <> 1";
        $sql .= " );";
        $result = $em->getConnection()->fetchColumn($sql);
        return $result;
    }

    public function deleteFromOrder($target_status_list)
    {
        $em = $this->getEntityManager();
        $sql = "DELETE FROM dtb_membership_billing_status WHERE membership_billing_status_id IN (";
        $sql .= " SELECT";
        $sql .= " membership_billing_status_id";
        $sql .= " FROM (";
        $sql .= " SELECT";
        $sql .= " dtb_membership_billing_status.membership_billing_status_id";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " LEFT JOIN dtb_product_membership ON dtb_product_membership.product_membership_id = dtb_membership_billing_status.product_membership";
        $sql .= " WHERE";
        $sql .= " CONCAT(LPAD('0', 11, dtb_membership_billing_status.customer), dtb_membership_billing_status.customer, LPAD('0', 11, dtb_product_membership.product_id), dtb_product_membership.product_id) NOT IN (";
        $sql .= " SELECT";
        $sql .= " CONCAT(LPAD('0', 11, dtb_order.customer_id), dtb_order.customer_id, LPAD('0', 11, dtb_order_detail.product_id), dtb_order_detail.product_id)";
        $sql .= " FROM";
        $sql .= " dtb_order";
        $sql .= " LEFT JOIN dtb_order_detail ON dtb_order_detail.order_id = dtb_order.order_id";
        $sql .= " INNER JOIN dtb_product_membership ON dtb_product_membership.product_id = dtb_order_detail.product_id";
        $sql .= " WHERE";
        $sql .= " dtb_order.`status` IN (";
        $cnt = 0;
        foreach($target_status_list as $target_status) {
            $sql .= (($cnt>0)?",":"") . $target_status;
            ++$cnt;
        }
        $sql .= " )";
        $sql .= " AND dtb_order.`del_flg` <> 1";
        $sql .= " )";
        $sql .= " ) AS TEMP";
        $sql .= " );";
        $result = $em->getConnection()->executeQuery($sql);
        return $result;
    }

    public function countPaymentMember($targetYear, $target_status_list)
    {
        $em = $this->getEntityManager();
        $sql = "SELECT";
        $sql .= " count(*)";
        $sql .= " FROM";
        $sql .= " dtb_customer";
        $sql .= " WHERE";
        $sql .= " dtb_customer.customer_id IN (";
        $sql .= " SELECT";
        $sql .= " dtb_membership_billing_status.customer";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " WHERE";
        $sql .= " dtb_membership_billing_status.customer IN (";
        $sql .= " SELECT";
        $sql .= " dtb_customer.customer_id";
        $sql .= " FROM";
        $sql .= " dtb_customer";
        $sql .= " LEFT JOIN dtb_customer_basic_info ON dtb_customer_basic_info.customer_id = dtb_customer.customer_id";
        $sql .= " WHERE";
        $sql .= " dtb_customer_basic_info.`status` IN (";
        $cnt = 0;
        foreach($target_status_list as $target_status) {
            $sql .= (($cnt>0)?",":"") . $target_status;
            ++$cnt;
        }
        $sql .= " )";
        $sql .= " )";
        $sql .= " AND dtb_membership_billing_status.product_membership = (";
        $sql .= " SELECT";
        $sql .= " dtb_product_membership.product_membership_id";
        $sql .= " FROM";
        $sql .= " dtb_product_membership";
        $sql .= " WHERE";
        $sql .= " dtb_product_membership.membership_year = " . $targetYear;
        $sql .= " )";
        $sql .= " );";
        $result = $em->getConnection()->fetchColumn($sql);
        return $result;
    }

    public function updatePaymentMember($targetYear, $target_status_list, $update_status)
    {
        $em = $this->getEntityManager();
        $sql = "UPDATE";
        $sql .= " dtb_customer_basic_info";
        $sql .= " SET";
        $sql .= " dtb_customer_basic_info.`status` = " . $update_status;
        $sql .= " WHERE";
        $sql .= " dtb_customer_basic_info.customer_id IN (";
        $sql .= " SELECT";
        $sql .= " dtb_membership_billing_status.customer";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " WHERE";
        $sql .= " dtb_membership_billing_status.customer IN (";
        $sql .= " SELECT";
        $sql .= " customer_id";
        $sql .= " FROM (";
        $sql .= " SELECT";
        $sql .= " dtb_customer.customer_id";
        $sql .= " FROM";
        $sql .= " dtb_customer";
        $sql .= " LEFT JOIN dtb_customer_basic_info ON dtb_customer_basic_info.customer_id = dtb_customer.customer_id";
        $sql .= " WHERE";
        $sql .= " dtb_customer_basic_info.`status` IN (";
        $cnt = 0;
        foreach($target_status_list as $target_status) {
            $sql .= (($cnt>0)?",":"") . $target_status;
            ++$cnt;
        }
        $sql .= " )";
        $sql .= " ) AS TEMP";
        $sql .= " )";
        $sql .= " AND dtb_membership_billing_status.product_membership = (";
        $sql .= " SELECT";
        $sql .= " dtb_product_membership.product_membership_id";
        $sql .= " FROM";
        $sql .= " dtb_product_membership";
        $sql .= " WHERE";
        $sql .= " dtb_product_membership.membership_year = " . $targetYear;
        $sql .= " )";
        $sql .= " );";
        $result = $em->getConnection()->executeQuery($sql);
        return $result;
    }

    public function countRealDelinquentMenber($nowTermYear)
    {
        $em = $this->getEntityManager();
        $sql = "SELECT";
        $sql .= " count(*)";
        $sql .= " FROM";
        $sql .= " dtb_customer_basic_info";
        $sql .= " WHERE";
        $sql .= " dtb_customer_basic_info.`status` NOT IN (6, 8)";
        $sql .= " AND dtb_customer_basic_info.regular_member_promoted IS NOT NULL";
        $sql .= " AND dtb_customer_basic_info.regular_member_promoted < '" . $nowTermYear . "-04-01 00:00:00'";
        $sql .= " AND dtb_customer_basic_info.customer_id NOT IN (";
        $sql .= " SELECT";
        $sql .= " dtb_membership_billing_status.customer";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " LEFT JOIN dtb_product_membership ON dtb_product_membership.product_membership_id = dtb_membership_billing_status.product_membership";
        $sql .= " WHERE";
        $sql .= " dtb_product_membership.membership_year = " . $nowTermYear . ")";
        $sql .= " AND dtb_customer_basic_info.customer_id IN (";
        $sql .= " SELECT";
        $sql .= " dtb_membership_billing_status.customer";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " LEFT JOIN dtb_product_membership ON dtb_product_membership.product_membership_id = dtb_membership_billing_status.product_membership";
        $sql .= " WHERE";
        $sql .= " dtb_product_membership.membership_year = " . ($nowTermYear - 1) . ");";
        $result = $em->getConnection()->fetchColumn($sql);
        return $result;
    }

    public function updateRealDelinquentMember($nowTermYear)
    {
        $em = $this->getEntityManager();
        $sql = "UPDATE";
        $sql .= " dtb_customer_basic_info";
        $sql .= " SET";
        $sql .= " dtb_customer_basic_info.`status` = 6";
        $sql .= " WHERE";
        $sql .= " dtb_customer_basic_info.`status` NOT IN (6, 8)";
        $sql .= " AND dtb_customer_basic_info.regular_member_promoted IS NOT NULL";
        $sql .= " AND dtb_customer_basic_info.regular_member_promoted < '" . $nowTermYear . "-04-01 00:00:00'";
        $sql .= " AND dtb_customer_basic_info.customer_id NOT IN (";
        $sql .= " SELECT";
        $sql .= " dtb_membership_billing_status.customer";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " LEFT JOIN dtb_product_membership ON dtb_product_membership.product_membership_id = dtb_membership_billing_status.product_membership";
        $sql .= " WHERE";
        $sql .= " dtb_product_membership.membership_year = " . $nowTermYear . ")";
        $sql .= " AND dtb_customer_basic_info.customer_id IN (";
        $sql .= " SELECT";
        $sql .= " dtb_membership_billing_status.customer";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " LEFT JOIN dtb_product_membership ON dtb_product_membership.product_membership_id = dtb_membership_billing_status.product_membership";
        $sql .= " WHERE";
        $sql .= " dtb_product_membership.membership_year = " . ($nowTermYear - 1) . ");";
        $result = $em->getConnection()->executeQuery($sql);
        return $result;
    }

    public function countRealDormantMenber($nowTermYear)
    {
        $em = $this->getEntityManager();
        $sql = "SELECT";
        $sql .= " count(*)";
        $sql .= " FROM";
        $sql .= " dtb_customer_basic_info";
        $sql .= " WHERE";
        $sql .= " dtb_customer_basic_info.`status` NOT IN (5, 8)";
        $sql .= " AND dtb_customer_basic_info.regular_member_promoted IS NOT NULL";
        $sql .= " AND dtb_customer_basic_info.regular_member_promoted < '" . $nowTermYear . "-04-01 00:00:00'";
        $sql .= " AND dtb_customer_basic_info.customer_id NOT IN (";
        $sql .= " SELECT";
        $sql .= " dtb_membership_billing_status.customer";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " LEFT JOIN dtb_product_membership ON dtb_product_membership.product_membership_id = dtb_membership_billing_status.product_membership";
        $sql .= " WHERE";
        $sql .= " dtb_product_membership.membership_year = " . $nowTermYear . ")";
        $sql .= " AND dtb_customer_basic_info.customer_id NOT IN (";
        $sql .= " SELECT";
        $sql .= " dtb_membership_billing_status.customer";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " LEFT JOIN dtb_product_membership ON dtb_product_membership.product_membership_id = dtb_membership_billing_status.product_membership";
        $sql .= " WHERE";
        $sql .= " dtb_product_membership.membership_year = " . ($nowTermYear - 1) . ")";
        $sql .= " AND dtb_customer_basic_info.customer_id IN (";
        $sql .= " SELECT";
        $sql .= " dtb_membership_billing_status.customer";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " LEFT JOIN dtb_product_membership ON dtb_product_membership.product_membership_id = dtb_membership_billing_status.product_membership";
        $sql .= " WHERE";
        $sql .= " dtb_product_membership.membership_year = " . ($nowTermYear - 2) . ");";
        $result = $em->getConnection()->fetchColumn($sql);
        return $result;
    }

    public function updateRealDormantMember($nowTermYear)
    {
        $em = $this->getEntityManager();
        $sql = "UPDATE";
        $sql .= " dtb_customer_basic_info";
        $sql .= " SET";
        $sql .= " dtb_customer_basic_info.`status` = 5";
        $sql .= " WHERE";
        $sql .= " dtb_customer_basic_info.`status` NOT IN (5, 8)";
        $sql .= " AND dtb_customer_basic_info.regular_member_promoted IS NOT NULL";
        $sql .= " AND dtb_customer_basic_info.regular_member_promoted < '" . $nowTermYear . "-04-01 00:00:00'";
        $sql .= " AND dtb_customer_basic_info.customer_id NOT IN (";
        $sql .= " SELECT";
        $sql .= " dtb_membership_billing_status.customer";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " LEFT JOIN dtb_product_membership ON dtb_product_membership.product_membership_id = dtb_membership_billing_status.product_membership";
        $sql .= " WHERE";
        $sql .= " dtb_product_membership.membership_year = " . $nowTermYear . ")";
        $sql .= " AND dtb_customer_basic_info.customer_id NOT IN (";
        $sql .= " SELECT";
        $sql .= " dtb_membership_billing_status.customer";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " LEFT JOIN dtb_product_membership ON dtb_product_membership.product_membership_id = dtb_membership_billing_status.product_membership";
        $sql .= " WHERE";
        $sql .= " dtb_product_membership.membership_year = " . ($nowTermYear - 1) . ")";
        $sql .= " AND dtb_customer_basic_info.customer_id IN (";
        $sql .= " SELECT";
        $sql .= " dtb_membership_billing_status.customer";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " LEFT JOIN dtb_product_membership ON dtb_product_membership.product_membership_id = dtb_membership_billing_status.product_membership";
        $sql .= " WHERE";
        $sql .= " dtb_product_membership.membership_year = " . ($nowTermYear - 2) . ");";
        $result = $em->getConnection()->executeQuery($sql);
        return $result;
    }

    public function countRealFormerMenber($nowTermYear)
    {
        $em = $this->getEntityManager();
        $sql = "SELECT";
        $sql .= " count(*)";
        $sql .= " FROM";
        $sql .= " dtb_customer_basic_info";
        $sql .= " WHERE";
        $sql .= " dtb_customer_basic_info.`status` NOT IN (7, 8)";
        $sql .= " AND dtb_customer_basic_info.regular_member_promoted IS NOT NULL";
        $sql .= " AND dtb_customer_basic_info.regular_member_promoted < '" . $nowTermYear . "-04-01 00:00:00'";
        $sql .= " AND dtb_customer_basic_info.customer_id NOT IN (";
        $sql .= " SELECT";
        $sql .= " dtb_membership_billing_status.customer";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " LEFT JOIN dtb_product_membership ON dtb_product_membership.product_membership_id = dtb_membership_billing_status.product_membership";
        $sql .= " WHERE";
        $sql .= " dtb_product_membership.membership_year = " . $nowTermYear . ")";
        $sql .= " AND dtb_customer_basic_info.customer_id NOT IN (";
        $sql .= " SELECT";
        $sql .= " dtb_membership_billing_status.customer";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " LEFT JOIN dtb_product_membership ON dtb_product_membership.product_membership_id = dtb_membership_billing_status.product_membership";
        $sql .= " WHERE";
        $sql .= " dtb_product_membership.membership_year = " . ($nowTermYear - 1) . ")";
        $sql .= " AND dtb_customer_basic_info.customer_id NOT IN (";
        $sql .= " SELECT";
        $sql .= " dtb_membership_billing_status.customer";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " LEFT JOIN dtb_product_membership ON dtb_product_membership.product_membership_id = dtb_membership_billing_status.product_membership";
        $sql .= " WHERE";
        $sql .= " dtb_product_membership.membership_year = " . ($nowTermYear - 2) . ")";
        $sql .= " AND dtb_customer_basic_info.customer_id IN (";
        $sql .= " SELECT";
        $sql .= " dtb_membership_billing_status.customer";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " LEFT JOIN dtb_product_membership ON dtb_product_membership.product_membership_id = dtb_membership_billing_status.product_membership";
        $sql .= " WHERE";
        $sql .= " dtb_product_membership.membership_year = " . ($nowTermYear - 3) . ");";
        $result = $em->getConnection()->fetchColumn($sql);
        return $result;
    }

    public function updateRealFormerMember($nowTermYear)
    {
        $em = $this->getEntityManager();
        $sql = "UPDATE";
        $sql .= " dtb_customer_basic_info";
        $sql .= " SET";
        $sql .= " dtb_customer_basic_info.`status` = 7";
        $sql .= " WHERE";
        $sql .= " dtb_customer_basic_info.`status` NOT IN (7, 8)";
        $sql .= " AND dtb_customer_basic_info.regular_member_promoted IS NOT NULL";
        $sql .= " AND dtb_customer_basic_info.regular_member_promoted < '" . $nowTermYear . "-04-01 00:00:00'";
        $sql .= " AND dtb_customer_basic_info.customer_id NOT IN (";
        $sql .= " SELECT";
        $sql .= " dtb_membership_billing_status.customer";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " LEFT JOIN dtb_product_membership ON dtb_product_membership.product_membership_id = dtb_membership_billing_status.product_membership";
        $sql .= " WHERE";
        $sql .= " dtb_product_membership.membership_year = " . $nowTermYear . ")";
        $sql .= " AND dtb_customer_basic_info.customer_id NOT IN (";
        $sql .= " SELECT";
        $sql .= " dtb_membership_billing_status.customer";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " LEFT JOIN dtb_product_membership ON dtb_product_membership.product_membership_id = dtb_membership_billing_status.product_membership";
        $sql .= " WHERE";
        $sql .= " dtb_product_membership.membership_year = " . ($nowTermYear - 1) . ")";
        $sql .= " AND dtb_customer_basic_info.customer_id NOT IN (";
        $sql .= " SELECT";
        $sql .= " dtb_membership_billing_status.customer";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " LEFT JOIN dtb_product_membership ON dtb_product_membership.product_membership_id = dtb_membership_billing_status.product_membership";
        $sql .= " WHERE";
        $sql .= " dtb_product_membership.membership_year = " . ($nowTermYear - 2) . ")";
        $sql .= " AND dtb_customer_basic_info.customer_id IN (";
        $sql .= " SELECT";
        $sql .= " dtb_membership_billing_status.customer";
        $sql .= " FROM";
        $sql .= " dtb_membership_billing_status";
        $sql .= " LEFT JOIN dtb_product_membership ON dtb_product_membership.product_membership_id = dtb_membership_billing_status.product_membership";
        $sql .= " WHERE";
        $sql .= " dtb_product_membership.membership_year = " . ($nowTermYear - 3) . ");";
        $result = $em->getConnection()->executeQuery($sql);
        return $result;
    }

    public function existsMembershipStatus($customerId, $membershipYear)
    {
        $recorde = $this->createQueryBuilder('ms')
                ->leftJoin('ms.Customer', 'c')
                ->leftJoin('ms.ProductMembership', 'pm')
                ->where('c.id = :Customer')
                ->andWhere('pm.membership_year = :MembershipYear')
                ->setParameter('Customer', $customerId)
                ->setParameter('MembershipYear', $membershipYear)
                ->getQuery()
                ->getResult();
        return (0 < count($recorde));
    }
}
