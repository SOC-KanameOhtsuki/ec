<?php

/*
 * This file is part of the ProductVideo
 *
 * Copyright (C) 2018 ふまねっと
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductVideo\Controller;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Exception\CartException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\Query\ResultSetMapping;



class ProductVideoController
{




    /**
     * ProductVideo画面
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {

        // add code...

        return $app->render('ProductVideo/Resource/template/index.twig', array(
            // add parameter...
        ));
    }

    /**
     * ProductVideo画面
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function history(Application $app, Request $request, $page_no = null)
    {
        $Customer = $app['user'];

        /* @var $softDeleteFilter \Eccube\Doctrine\Filter\SoftDeleteFilter */
        $softDeleteFilter = $app['orm.em']->getFilters()->getFilter('soft_delete');
        $softDeleteFilter->setExcludes(array(
            'Eccube\Entity\ProductClass',
        ));

        // 購入処理中/決済処理中ステータスの受注を非表示にする.
        $app['orm.em']
            ->getFilters()
            ->enable('incomplete_order_status_hidden');

        // paginator
//        $qb = $app['eccube.repository.order']->getQueryBuilderByCustomer($Customer);
   
            $order_status=array();
            $order_status[]=4;//取り寄せ中
            $order_status[]=5;//発送済み
            $order_status[]=6;//入金済み

/*
|  1 | 新規受付        |    1 |
|  2 | 入金待ち        |    2 |
|  3 | キャンセル      |    4 |
|  4 | 取り寄せ中      |    5 |
|  5 | 発送済み        |    6 |
|  6 | 入金済み        |    3 |
|  7 | 決済処理中      |    0 |
|  8 | 購入処理中      |    7 |
*/
        $qb = $app['eccube.repository.order']->createQueryBuilder('o');
        $qb->addSelect(array('pt','od','pc','ptp'))
                ->innerJoin('o.OrderDetails', 'od')
                ->innerJoin('od.Product', 'pt')
                ->innerJoin('pt.ProductClasses', 'pc')
                ->innerJoin('pc.ProductType', 'ptp')
                ->where('o.Customer = :Customer')
                ->andWhere($qb->expr()->in('o.OrderStatus', ':statuses') )
                   ->andwhere('pc.ProductType = :TypeId')
                ->setParameter('statuses', $order_status)
                ->setParameter('Customer', $Customer)
                ->setParameter('TypeId', 6);
                // Order By
                $qb->addOrderBy('o.id', 'DESC');

        $event = new EventArgs(
            array(
                'qb' => $qb,
                'Customer' => $Customer,
            ),
            $request
        );

        $pagination = $app['paginator']()->paginate(
            $qb,
            $request->get('pageno', 1),
            $app['config']['search_pmax']
        );

        return $app->render('ProductVideo/Resource/template/Mypage/history.twig', array(
            'pagination' => $pagination,
        ));

//dump($page_no);
/*
        return $app->render( 'ProductVideo/Resource/template/Mypage/history.twig', array(
                    'pagination'  => $pagination,
                    'page_no'     => "1",
                    'disps'       => $disps,
                    'pageMaxis'   => $pageMaxis,
                    'page_status' => $page_status,
                    'page_count'  => $page_count,
                    'active'      => $active,
        ) );
*/

    }

    /**
     * ProductVideo画面
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function play(Application $app, Request $request,$product_id =null)
    {

        // add code...
//      dump($this->app);
        $ProductVideo= $app['eccube.plugin.productvideo.repository.ProductVideo']->findOneBy(array('product_id' => $product_id));
        $embed_tag="再生できる動画はありません。";
        if ($ProductVideo) {
            $embed_tag=$ProductVideo->getEmbedMain();

        }
        return $app->render('ProductVideo/Resource/template/Mypage/play.twig', array(
            // add parameter...
            'embed_tag'=> $embed_tag
        ));
    }


}
