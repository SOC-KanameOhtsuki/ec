<?php
/*
 * This file is part of EC-CUBE Customize
 *
 */

namespace Eccube\Controller\Admin\Content;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;

class FormPrintingController extends AbstractController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * (non-PHPdoc)
     * @see \Eccube\Controller\Admin\Content\NewsController::index()
     * @deprecated 3.1 delete. use NewsController
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function payment(Application $app, Request $request = null)
    {
        return $app->render('Content/news.twig', array(
            'form' => $form->createView(),
            'NewsList' => $NewsList,
        ));
    }

    /**
     * (non-PHPdoc)
     * @see \Eccube\Controller\Admin\Content\NewsController::index()
     * @deprecated 3.1 delete. use NewsController
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function invoice(Application $app, Request $request = null)
    {
        return $app->render('Content/news.twig', array(
            'form' => $form->createView(),
            'NewsList' => $NewsList,
        ));
    }

    /**
     * (non-PHPdoc)
     * @see \Eccube\Controller\Admin\Content\NewsController::index()
     * @deprecated 3.1 delete. use NewsController
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delivery(Application $app, Request $request = null)
    {
        return $app->render('Content/news.twig', array(
            'form' => $form->createView(),
            'NewsList' => $NewsList,
        ));
    }

    /**
     * (non-PHPdoc)
     * @see \Eccube\Controller\Admin\Content\NewsController::index()
     * @deprecated 3.1 delete. use NewsController
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function businessCard(Application $app, Request $request = null)
    {
        return $app->render('Content/news.twig', array(
            'form' => $form->createView(),
            'NewsList' => $NewsList,
        ));
    }

    /**
     * (non-PHPdoc)
     * @see \Eccube\Controller\Admin\Content\NewsController::index()
     * @deprecated 3.1 delete. use NewsController
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function certification(Application $app, Request $request = null)
    {
        return $app->render('Content/news.twig', array(
            'form' => $form->createView(),
            'NewsList' => $NewsList,
        ));
    }

    /**
     * (non-PHPdoc)
     * @see \Eccube\Controller\Admin\Content\NewsController::index()
     * @deprecated 3.1 delete. use NewsController
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function regularMemberList(Application $app, Request $request = null)
    {
        return $app->render('Content/news.twig', array(
            'form' => $form->createView(),
            'NewsList' => $NewsList,
        ));
    }
}
