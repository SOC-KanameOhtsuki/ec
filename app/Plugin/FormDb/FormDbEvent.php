<?php

/*
 * This file is part of the FormDb
 *
 * Copyright (C) 2018 ふまねっと
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\FormDb;

use Eccube\Application;
use Eccube\Event\EventArgs;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

class FormDbEvent
{

    /** @var  \Eccube\Application $app */
    private $app;

    /**
     * FormDbEvent constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param FilterControllerEvent $event
     */
    public function onAppController(FilterControllerEvent $event)
    {
    }

    /**
     * @param EventArgs $event
     */
    public function onAdminCustomerEditIndexInitialize(EventArgs $event)
    {
    }

    /**
     * @param EventArgs $event
     */
    public function onAdminCustomerIndexRender( $event)
    {
	 //dump("onAdminCustomerIndexRender");
	$src=$event->getSource();
	//dump($src);
        $parts = $this->app['twig']->getLoader()->getSource('FormDb/Resource/template/admin/formdb.twig');
        // twigコードに挿入
        // 要素箇所の取得
        $search = "<!--検索条件設定テーブルここまで-->";
        $replace = $search.$parts ;
        $source = str_replace($search, $replace, $src);
        $event->setSource($source);




    }





}
