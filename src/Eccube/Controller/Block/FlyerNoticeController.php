<?php
/*
 * This file is part of EC-CUBE
 *  Customized file
 */


namespace Eccube\Controller\Block;

use Eccube\Application;

class FlyerNoticeController
{
    public function index(Application $app)
    {

        $now = new \DateTime();
        $TrainingTypes = $app['eccube.repository.master.training_type']->getList();
        $qb = $app['eccube.repository.flyer']->createQueryBuilder('f')
        ->addSelect(array('pt','p'))
        ->innerJoin('f.ProductTraining', 'pt')
        ->innerJoin('pt.Product', 'p')
        ->where('f.Status = 1')
        ->andwhere('f.disp_from <= :DispFrom')
        ->andwhere('f.disp_to >= :DispTo')
        ->setParameter('DispFrom', $now)
        ->setParameter('DispTo', $now)
        // Order By
        ->addOrderBy('f.disp_from', 'DESC')
        ->getQuery()
        ->getResult();
//	dump($qb);
        return $app->render('Block/flyer_notice.twig', array(
            'FlyerList' => $qb,
        ));
    }
}
