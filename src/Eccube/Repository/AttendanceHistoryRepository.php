<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Eccube\Repository;

use Doctrine\ORM\EntityRepository;
use Eccube\Application;

/**
 * AttendanceHistoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AttendanceHistoryRepository extends EntityRepository
{
	public function getAttendanceHistoryByCustomerAndTraining($customerId, $trainingId)
    {
    	// var_dump($this->find(1));
    	// exit(0);
//         $qb = $this->createQueryBuilder('a')
//             ->where('a.customer_id = :customer_id')
//             ->andWhere('a.product_training_id = :product_training_id')
//             ->setParameter('customer_id', $customerId)
//             ->setParameter('product_training_id', $trainingId);
//         $query = $qb->getQuery();
// try {
//         var_dump($query->getSingleResult());

//     } catch (Exception $e) {
//     	var_dump($e);
//     }

//         exit(0);
//         return $query->getSingleResult();
    }
}
