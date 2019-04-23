<?php

namespace Eccube\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * GroupOrderRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class GroupOrderRepository extends EntityRepository
{
    /**
     *
     * @param  array        $searchData
     * @return QueryBuilder
     */
    public function getQueryBuilderBySearchDataForAdmin($searchData)
    {
        $qb = $this->createQueryBuilder('go')
                    ->select('go.id, go.name, go.kana, go.bill_to, go.order_date')
                    ->addSelect('SUM(o.payment_total) AS payment_total')
                    ->addSelect('MAX(p.id) AS payment')
                    ->leftJoin('go.Order', 'o')
                    ->leftJoin('o.Payment', 'p');

        // order_id_start
        if (isset($searchData['order_id_start']) && Str::isNotBlank($searchData['order_id_start'])) {
            $qb
                ->andWhere('go.id >= :order_id_start')
                ->setParameter('order_id_start', $searchData['order_id_start']);
        }
        // multi
        if (isset( $searchData['multi']) && Str::isNotBlank($searchData['multi'])) {
            $multi = preg_match('/^\d+$/', $searchData['multi']) ? $searchData['multi'] : null;
            $qb
                ->andWhere('go.id = :multi OR go.name LIKE :likemulti go.kana LIKE :likemulti OR go.bill_to LIKE :likemulti')
                ->setParameter('multi', $multi)
                ->setParameter('likemulti', '%' . $searchData['multi'] . '%');
        }

        // order_id_end
        if (isset($searchData['order_id_end']) && Str::isNotBlank($searchData['order_id_end'])) {
            $qb
                ->andWhere('go.id <= :order_id_end')
                ->setParameter('order_id_end', $searchData['order_id_end']);
        }

        // name
        if (isset($searchData['name']) && Str::isNotBlank($searchData['name'])) {
            $qb
                ->andWhere('go.name) LIKE :name OR go.bill_to LIKE :name')
                ->setParameter('name', '%' . $searchData['name'] . '%');
        }

        // kana
        if (isset($searchData['kana']) && Str::isNotBlank($searchData['kana'])) {
            $qb
                ->andWhere('CONCAT(go.kana) LIKE :kana')
                ->setParameter('kana', '%' . $searchData['kana'] . '%');
        }

        // email
        if (isset($searchData['email']) && Str::isNotBlank($searchData['email'])) {
            $qb
                ->andWhere('go.send_to_email like :email OR go.bill_to_email like :email')
                ->setParameter('email', '%' . $searchData['email'] . '%');
        }

        // tel
        if (isset($searchData['tel']) && Str::isNotBlank($searchData['tel'])) {
            $qb
                ->andWhere('CONCAT(go.send_to_tel01, go.send_to_tel02, go.send_to_tel03) LIKE :tel OR CONCAT(go.bill_to_tel01, go.bill_to_tel02, go.bill_to_tel03) LIKE :tel ')
                ->setParameter('tel', '%' . $searchData['tel'] . '%');
        }

        // payment
        if (!empty($searchData['payment']) && count($searchData['payment'])) {
            $payments = array();
            foreach ($searchData['payment'] as $payment) {
                $payments[] = $payment->getId();
            }
            $qb
                ->leftJoin('o.Payment', 'p')
                ->andWhere($qb->expr()->in('p.id', ':payments'))
                ->setParameter('payments', $payments);
        }

        // oreder_date
        if (!empty($searchData['order_date_start']) && $searchData['order_date_start']) {
            $date = $searchData['order_date_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('go.order_date >= :order_date_start')
                ->setParameter('order_date_start', $date);
        }
        if (!empty($searchData['order_date_end']) && $searchData['order_date_end']) {
            $date = clone $searchData['order_date_end'];
            $date = $date
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('go.order_date < :order_date_end')
                ->setParameter('order_date_end', $date);
        }

        // payment_date
        if (!empty($searchData['payment_date_start']) && $searchData['payment_date_start']) {
            $date = $searchData['payment_date_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('o.payment_date >= :payment_date_start')
                ->setParameter('payment_date_start', $date);
        }
        if (!empty($searchData['payment_date_end']) && $searchData['payment_date_end']) {
            $date = clone $searchData['payment_date_end'];
            $date = $date
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('o.payment_date < :payment_date_end')
                ->setParameter('payment_date_end', $date);
        }

        // commit_date
        if (!empty($searchData['commit_date_start']) && $searchData['commit_date_start']) {
            $date = $searchData['commit_date_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('o.commit_date >= :commit_date_start')
                ->setParameter('commit_date_start', $date);
        }
        if (!empty($searchData['commit_date_end']) && $searchData['commit_date_end']) {
            $date = clone $searchData['commit_date_end'];
            $date = $date
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('o.commit_date < :commit_date_end')
                ->setParameter('commit_date_end', $date);
        }

        // update_date
        if (!empty($searchData['update_date_start']) && $searchData['update_date_start']) {
            $date = $searchData['update_date_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('go.update_date >= :update_date_start')
                ->setParameter('update_date_start', $date);
        }
        if (!empty($searchData['update_date_end']) && $searchData['update_date_end']) {
            $date = clone $searchData['update_date_end'];
            $date = $date
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('go.update_date < :update_date_end')
                ->setParameter('update_date_end', $date);
        }

        // payment_total
        if (isset($searchData['payment_total_start']) && Str::isNotBlank($searchData['payment_total_start'])) {
            $qb
                ->andWhere('o.payment_total >= :payment_total_start')
                ->setParameter('payment_total_start', $searchData['payment_total_start']);
        }
        if (isset($searchData['payment_total_end']) && Str::isNotBlank($searchData['payment_total_end'])) {
            $qb
                ->andWhere('o.payment_total <= :payment_total_end')
                ->setParameter('payment_total_end', $searchData['payment_total_end']);
        }

        // buy_product_name
        if (isset($searchData['buy_product_name']) && Str::isNotBlank($searchData['buy_product_name'])) {
            $qb
                ->leftJoin('o.OrderDetails', 'od')
                ->andWhere('od.product_name LIKE :buy_product_name')
                ->setParameter('buy_product_name', '%' . $searchData['buy_product_name'] . '%');
        }

        // Order By
        $qb->groupBy('go.id');
        $qb->orderBy('go.update_date', 'DESC');
        $qb->addorderBy('go.id', 'DESC');

        return $qb;
    }

    /**
     *
     * @param  array        $searchData
     * @return QueryBuilder
     */
    public function getOrderQueryBuilderBySearchDataForAdmin($searchData)
    {
        $qb = $this->createQueryBuilder('go')
                    ->leftJoin('go.Order', 'o')
                    ->leftJoin('o.OrderDetails', 'od')
                    ->leftJoin('o.Payment', 'p');

        // order_id_start
        if (isset($searchData['order_id_start']) && Str::isNotBlank($searchData['order_id_start'])) {
            $qb
                ->andWhere('go.id >= :order_id_start')
                ->setParameter('order_id_start', $searchData['order_id_start']);
        }
        // multi
        if (isset( $searchData['multi']) && Str::isNotBlank($searchData['multi'])) {
            $multi = preg_match('/^\d+$/', $searchData['multi']) ? $searchData['multi'] : null;
            $qb
                ->andWhere('go.id = :multi OR go.name LIKE :likemulti go.kana LIKE :likemulti OR go.bill_to LIKE :likemulti')
                ->setParameter('multi', $multi)
                ->setParameter('likemulti', '%' . $searchData['multi'] . '%');
        }

        // order_id_end
        if (isset($searchData['order_id_end']) && Str::isNotBlank($searchData['order_id_end'])) {
            $qb
                ->andWhere('go.id <= :order_id_end')
                ->setParameter('order_id_end', $searchData['order_id_end']);
        }

        // name
        if (isset($searchData['name']) && Str::isNotBlank($searchData['name'])) {
            $qb
                ->andWhere('go.name) LIKE :name OR go.bill_to LIKE :name')
                ->setParameter('name', '%' . $searchData['name'] . '%');
        }

        // kana
        if (isset($searchData['kana']) && Str::isNotBlank($searchData['kana'])) {
            $qb
                ->andWhere('CONCAT(go.kana) LIKE :kana')
                ->setParameter('kana', '%' . $searchData['kana'] . '%');
        }

        // email
        if (isset($searchData['email']) && Str::isNotBlank($searchData['email'])) {
            $qb
                ->andWhere('go.send_to_email like :email OR go.bill_to_email like :email')
                ->setParameter('email', '%' . $searchData['email'] . '%');
        }

        // tel
        if (isset($searchData['tel']) && Str::isNotBlank($searchData['tel'])) {
            $qb
                ->andWhere('CONCAT(go.send_to_tel01, go.send_to_tel02, go.send_to_tel03) LIKE :tel OR CONCAT(go.bill_to_tel01, go.bill_to_tel02, go.bill_to_tel03) LIKE :tel ')
                ->setParameter('tel', '%' . $searchData['tel'] . '%');
        }

        // payment
        if (!empty($searchData['payment']) && count($searchData['payment'])) {
            $payments = array();
            foreach ($searchData['payment'] as $payment) {
                $payments[] = $payment->getId();
            }
            $qb
                ->leftJoin('o.Payment', 'p')
                ->andWhere($qb->expr()->in('p.id', ':payments'))
                ->setParameter('payments', $payments);
        }

        // oreder_date
        if (!empty($searchData['order_date_start']) && $searchData['order_date_start']) {
            $date = $searchData['order_date_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('go.order_date >= :order_date_start')
                ->setParameter('order_date_start', $date);
        }
        if (!empty($searchData['order_date_end']) && $searchData['order_date_end']) {
            $date = clone $searchData['order_date_end'];
            $date = $date
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('go.order_date < :order_date_end')
                ->setParameter('order_date_end', $date);
        }

        // payment_date
        if (!empty($searchData['payment_date_start']) && $searchData['payment_date_start']) {
            $date = $searchData['payment_date_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('o.payment_date >= :payment_date_start')
                ->setParameter('payment_date_start', $date);
        }
        if (!empty($searchData['payment_date_end']) && $searchData['payment_date_end']) {
            $date = clone $searchData['payment_date_end'];
            $date = $date
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('o.payment_date < :payment_date_end')
                ->setParameter('payment_date_end', $date);
        }

        // commit_date
        if (!empty($searchData['commit_date_start']) && $searchData['commit_date_start']) {
            $date = $searchData['commit_date_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('o.commit_date >= :commit_date_start')
                ->setParameter('commit_date_start', $date);
        }
        if (!empty($searchData['commit_date_end']) && $searchData['commit_date_end']) {
            $date = clone $searchData['commit_date_end'];
            $date = $date
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('o.commit_date < :commit_date_end')
                ->setParameter('commit_date_end', $date);
        }

        // update_date
        if (!empty($searchData['update_date_start']) && $searchData['update_date_start']) {
            $date = $searchData['update_date_start']
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('go.update_date >= :update_date_start')
                ->setParameter('update_date_start', $date);
        }
        if (!empty($searchData['update_date_end']) && $searchData['update_date_end']) {
            $date = clone $searchData['update_date_end'];
            $date = $date
                ->modify('+1 days')
                ->format('Y-m-d H:i:s');
            $qb
                ->andWhere('go.update_date < :update_date_end')
                ->setParameter('update_date_end', $date);
        }

        // payment_total
        if (isset($searchData['payment_total_start']) && Str::isNotBlank($searchData['payment_total_start'])) {
            $qb
                ->andWhere('o.payment_total >= :payment_total_start')
                ->setParameter('payment_total_start', $searchData['payment_total_start']);
        }
        if (isset($searchData['payment_total_end']) && Str::isNotBlank($searchData['payment_total_end'])) {
            $qb
                ->andWhere('o.payment_total <= :payment_total_end')
                ->setParameter('payment_total_end', $searchData['payment_total_end']);
        }

        // buy_product_name
        if (isset($searchData['buy_product_name']) && Str::isNotBlank($searchData['buy_product_name'])) {
            $qb
                ->leftJoin('o.OrderDetails', 'od')
                ->andWhere('od.product_name LIKE :buy_product_name')
                ->setParameter('buy_product_name', '%' . $searchData['buy_product_name'] . '%');
        }

        // Order By
        $qb->orderBy('go.update_date', 'DESC');
        $qb->addorderBy('go.id', 'DESC');
        $qb->addorderBy('o.id', 'DESC');

        return $qb;
    }

    public function getBulkGroupOrder($BulkOrderId)
    {
        $serach_note = "membership_bulk_group_" . $BulkOrderId . "_%";
        $orders = $this->createQueryBuilder('go')
                ->where('go.del_flg = 0')
                ->andWhere('go.note LIKE :serach_note')
                ->setParameter('serach_note', $serach_note)
                ->orderBy('go.create_date', 'desc')
                ->getQuery()
                ->getResult();

        return $orders;
    }
}
