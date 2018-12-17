<?php

namespace Eccube\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * MembershipBillingDetailRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MembershipBillingDetailRepository extends EntityRepository
{
    public function insertAllTarget($targetProductId)
    {
        $em = $this->getEntityManager();
        $sql = "INSERT INTO dtb_membership_billing_detail (membership_billing, customer, status, create_date, update_date)";
        $sql .= "SELECT " . $targetProductId . ", customer_id, 1, NOW(), NOW() FROM dtb_customer WHERE customer_id NOT IN ";
        $sql .= "(SELECT dtb_customer.customer_id FROM dtb_customer INNER JOIN dtb_order ON dtb_customer.customer_id = dtb_order.customer_id ";
        $sql .= "INNER JOIN dtb_order_detail ON dtb_order.order_id = dtb_order_detail.order_id ";
        $sql .= "WHERE dtb_order_detail.product_id = :product_id ";
        $sql .= "GROUP BY dtb_customer.customer_id);";
        $result = $em->getConnection()->executeQuery($sql, array(':product_id' => $targetProductId));

        return $result;
    }
}