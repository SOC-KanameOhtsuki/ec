<?php

/*
 * This file is part of the ProductVideo
 *
 * Copyright (C) 2018 
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductVideo;

use Eccube\Application;
use Eccube\Event\EventArgs;
use Eccube\Entity\Product;
use Eccube\Entity\Order;
use Eccube\Entity\OrderDetail;
use Eccube\Entity\Customer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;
use Plugin\ProductVideo\Entity\ProductVideo;

class ProductVideoEvent
{

    /** @var  \Eccube\Application $app */
    private $app;

    /**
     * ProductVideoEvent constructor.
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
     * @param GetResponseEvent $event
     */
    public function onAppRequest(GetResponseEvent $event)
    {
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onAppResponse(FilterResponseEvent $event)
    {
    }

    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onAppException(GetResponseForExceptionEvent $event)
    {
    }

    /**
     * @param EventArgs $event
     */
    public function onAdminCustomerEditIndexInitialize(EventArgs $event)
    {
     //    dump("test");
    }




    /**
     * syo
     * @param EventArgs $event
     */
    public function onAdminProductEditIndexInitialize(EventArgs $event) {
        $Product= $event->getArgument('Product');
        $id = $Product->getId();
//        dump("onAdminProductEditIndexInitialize");

//      dump($this->app);
        $ProductVideo= $this->app['eccube.plugin.productvideo.repository.ProductVideo']->findOneBy(array('product_id' => $id));
        if (!$ProductVideo) {
            $ProductVideo= new ProductVideo();
            $ProductVideo
                    ->setEmbedMain("")
                    ->setEmbedPreview("")
                    ->setProduct($Product);
        }

//        dump($ProductVideo);
        $this->app['orm.em']->persist($ProductVideo);
 
        $builder = $event->getArgument('builder');
 
        $builder->add('plg_productvideo_embed_main', 'text', array(
                    'label' => '動画本編埋め込みタグ',
                    'required' => false,
                    'mapped' => false,
                    'data' => $ProductVideo->getEmbedMain() 
                        )
                )
                ->add('plg_productvideo_embed_preview', 'text', array(
                    'label' => '動画プレビュー埋め込みタグ',
                    'required' => false,
                    'mapped' => false,
                    'data' => $ProductVideo->getEmbedPreview()
                        )
                );

    }


    /**
     * 商品更新
     * @param EventArgs $event
     */
    public function onAdminProductEditComplete(EventArgs $event) {

        $form = $event->getArgument('form');
        $Product= $event->getArgument('Product');
//      dump($form);
        log_info('ビデオ商品登録開始', array($form));

                
        $id = $Product->getId();
//        dump("onAdminProductEditIndexInitialize");
        $ProductVideo= $this->app['eccube.plugin.productvideo.repository.ProductVideo']->findOneBy(array('product_id' => $id));
//        dump($ProductVideo);
 
        if (!$ProductVideo) {
            $ProductVideo= new ProductVideo();
        }
 
//          dump($id);
//          dump($ProductVideo);
        // エンティティを更新
        $ProductVideo->setEmbedMain($form['plg_productvideo_embed_main']->getData())
                  ->setEmbedPreview($form['plg_productvideo_embed_preview']->getData())
                  ->setProduct($Product);


        // DB更新
        $this->app['orm.em']->persist($ProductVideo);
        $this->app['orm.em']->flush($ProductVideo);


    }



    /**
     * @param EventArgs $event
     */
    public function onAdminCustomerEditIndexComplete(EventArgs $event)
    {
    }

    /**
     * @param EventArgs $event
     */
    public function onAdminProductProductClassEditComplete(EventArgs $event)
    {
    }

    /**
     * @param EventArgs $event
     */
    public function onAdminProductProductClassEditUpdate(EventArgs $event)
    {
    }

    /**
     * @param EventArgs $event
     */
    public function onAdminProductProductClassEditInitialize(EventArgs $event)
    {
    }

    /**
     * @param EventArgs $event
     */
    public function onAdminOrderEditIndexComplete(EventArgs $event)
    {
    }

    /**
     * @param EventArgs $event
     */
    public function onFrontMypageMypageHistoryInitialize(EventArgs $event)
    {
    }

    /**
     * @param EventArgs $event
     */
    public function onFrontCartBuystepComplete(EventArgs $event)
    {
    }

    /**
     * 商品一覧
     * @param EventArgs $event
     */
    public function onProductList(FilterResponseEvent  $event)
    {
    //  dump("onProductList");
    }

    /**
     * 商品詳細
     * @param EventArgs $event
     */
    public function onProductDetail(FilterResponseEvent  $event)
    {

       $request = $event->getRequest();
       $response = $event->getResponse();

       $id=$request->get('id');

        $ProductVideo= $this->app['eccube.plugin.productvideo.repository.ProductVideo']->findOneBy(array('product_id' => $id));
 
        if (!$ProductVideo) {
            return;
        }
        $embed_preview_tag=$ProductVideo->getEmbedPreview();
        $embed_main_tag=$ProductVideo->getEmbedMain();

	    $tag_arr = array("<!--VIDEO_MAIN_EMBED-->", "<!--VIDEO_PREVIEW_EMBED-->");
	    $embed_arr = array($embed_main_tag, $embed_preview_tag);

        $src_html = $response->getContent();
        $new_html = str_replace($tag_arr, $embed_arr, $src_html);

        $response->setContent($new_html);

        $event->setResponse($response);

    }


   /**
     *  マイページ
     *  - 利用ポイント・保有ポイント表示
     *
     * @param TemplateEvent $event
     */
    public function onRenderMyPageIndex($event) {
        //dump(12345);
        // ログイン判定
        $parameters = $event->getParameters();
        if ($this->isAuthRouteFront()) {
            $Customer = $this->app->user();
            $id = $Customer->GetId();
            $parameters = $event->getParameters();
            $parts = $this->app['twig']->getLoader()->getSource('ProductVideo/Resource/template/Mypage/navi.twig');
            // twigコードに挿入
            // 要素箇所の取得
            $search = "{% include 'Mypage/navi.twig' %}";
            $replace = $parts ;
            $source = str_replace($search, $replace, $event->getSource());
            $event->setSource($source);

        }
    }

    /**
     *  マイページ
     *
     * @param TemplateEvent $event
     */
    public function onRenderMyPageNavi( $event) {
        //dump("onRenderMyPageNavi");
        // ログイン判定
        if ($this->isAuthRouteFront()) {
            $Customer = $this->app->user();
            $id = $Customer->GetId();
            $parameters = $event->getParameters();
            $parts = $this->app['twig']->getLoader()->getSource('ProductVideo/Resource/template/Mypage/navi.twig');
            // twigコードに挿入
            // 要素箇所の取得
            $search = "{% include 'Mypage/navi.twig' %}";
            $replace = $parts ;
            $source = str_replace($search, $replace, $event->getSource());
            $event->setSource($source);
        }
    }

    /**
     *  マイページ
     *
     * @param TemplateEvent $event
     */
    public function onMypage_change_twig( $event) {
        //dump("onRenderMyPageNavi");
        // ログイン判定
        if ($this->isAuthRouteFront()) {
            $Customer = $this->app->user();
            $id = $Customer->GetId();
            $parameters = $event->getParameters();
            $parts = $this->app['twig']->getLoader()->getSource('ProductVideo/Resource/template/Mypage/navi.twig');
            // twigコードに挿入
            // 要素箇所の取得
            $search = "{% include 'Mypage/navi.twig' %}";
            $replace = $parts ;
            $source = str_replace($search, $replace, $event->getSource());
            $event->setSource($source);
        }
    }


    /**
     *  マイページ
     *
     * @param TemplateEvent $event
     */
    public function onMypage_change_complete_twig( $event) {
        //dump("onRenderMyPageNavi");
        // ログイン判定
        if ($this->isAuthRouteFront()) {
            $Customer = $this->app->user();
            $id = $Customer->GetId();
            $parameters = $event->getParameters();
            $parts = $this->app['twig']->getLoader()->getSource('ProductVideo/Resource/template/Mypage/navi.twig');
            // twigコードに挿入
            // 要素箇所の取得
            $search = "{% include 'Mypage/navi.twig' %}";
            $replace = $parts ;
            $source = str_replace($search, $replace, $event->getSource());
            $event->setSource($source);
        }
    }

    /**
     *  マイページ
     *
     * @param TemplateEvent $event
     */
    public function onMypage_delivery_twig( $event) {
        //dump("onRenderMyPageNavi");
        // ログイン判定
        if ($this->isAuthRouteFront()) {
            $Customer = $this->app->user();
            $id = $Customer->GetId();
            $parameters = $event->getParameters();
            $parts = $this->app['twig']->getLoader()->getSource('ProductVideo/Resource/template/Mypage/navi.twig');
            // twigコードに挿入
            // 要素箇所の取得
            $search = "{% include 'Mypage/navi.twig' %}";
            $replace = $parts ;
            $source = str_replace($search, $replace, $event->getSource());
            $event->setSource($source);
        }
    }


    /**
     *  マイページ
     *
     * @param TemplateEvent $event
     */
    public function onMypage_delivery_edit_twig( $event) {
        //dump("onRenderMyPageNavi");
        // ログイン判定
        if ($this->isAuthRouteFront()) {
            $Customer = $this->app->user();
            $id = $Customer->GetId();
            $parameters = $event->getParameters();
            $parts = $this->app['twig']->getLoader()->getSource('ProductVideo/Resource/template/Mypage/navi.twig');
            // twigコードに挿入
            // 要素箇所の取得
            $search = "{% include 'Mypage/navi.twig' %}";
            $replace = $parts ;
            $source = str_replace($search, $replace, $event->getSource());
            $event->setSource($source);
        }
    }

    /**
     *  マイページ
     *
     * @param TemplateEvent $event
     */
    public function onMypage_favorite_twig( $event) {
        //dump("onRenderMyPageNavi");
        // ログイン判定
        if ($this->isAuthRouteFront()) {
            $Customer = $this->app->user();
            $id = $Customer->GetId();
            $parameters = $event->getParameters();
            $parts = $this->app['twig']->getLoader()->getSource('ProductVideo/Resource/template/Mypage/navi.twig');
            // twigコードに挿入
            // 要素箇所の取得
            $search = "{% include 'Mypage/navi.twig' %}";
            $replace = $parts ;
            $source = str_replace($search, $replace, $event->getSource());
            $event->setSource($source);
        }
    }

    /**
     *  マイページ
     *
     * @param TemplateEvent $event
     */
    public function onMypage_login_twig( $event) {
        //dump("onRenderMyPageNavi");
        // ログイン判定
        if ($this->isAuthRouteFront()) {
            $Customer = $this->app->user();
            $id = $Customer->GetId();
            $parameters = $event->getParameters();
            $parts = $this->app['twig']->getLoader()->getSource('ProductVideo/Resource/template/Mypage/navi.twig');
            // twigコードに挿入
            // 要素箇所の取得
            $search = "{% include 'Mypage/navi.twig' %}";
            $replace = $parts ;
            $source = str_replace($search, $replace, $event->getSource());
            $event->setSource($source);
        }
    }

    /**
     *  マイページ
     *
     * @param TemplateEvent $event
     */
    public function onMypage_withdraw_twig( $event) {
        //dump("onRenderMyPageNavi");
        // ログイン判定
        if ($this->isAuthRouteFront()) {
            $Customer = $this->app->user();
            $id = $Customer->GetId();
            $parameters = $event->getParameters();
            $parts = $this->app['twig']->getLoader()->getSource('ProductVideo/Resource/template/Mypage/navi.twig');
            // twigコードに挿入
            // 要素箇所の取得
            $search = "{% include 'Mypage/navi.twig' %}";
            $replace = $parts ;
            $source = str_replace($search, $replace, $event->getSource());
            $event->setSource($source);
        }
    }

    /**
     *  マイページ
     *
     * @param TemplateEvent $event
     */
    public function onMypage_withdraw_complete_twig( $event) {
        //dump("onRenderMyPageNavi");
        // ログイン判定
        if ($this->isAuthRouteFront()) {
            $Customer = $this->app->user();
            $id = $Customer->GetId();
            $parameters = $event->getParameters();
            $parts = $this->app['twig']->getLoader()->getSource('ProductVideo/Resource/template/Mypage/navi.twig');
            // twigコードに挿入
            // 要素箇所の取得
            $search = "{% include 'Mypage/navi.twig' %}";
            $replace = $parts ;
            $source = str_replace($search, $replace, $event->getSource());
            $event->setSource($source);
        }
    }


    /**
     *  マイページ
     *
     * @param TemplateEvent $event
     */
    public function onMypage_withdraw_confirm_twig( $event) {
        //dump("onRenderMyPageNavi");
        // ログイン判定
        if ($this->isAuthRouteFront()) {
            $Customer = $this->app->user();
            $id = $Customer->GetId();
            $parameters = $event->getParameters();
            $parts = $this->app['twig']->getLoader()->getSource('ProductVideo/Resource/template/Mypage/navi.twig');
            // twigコードに挿入
            // 要素箇所の取得
            $search = "{% include 'Mypage/navi.twig' %}";
            $replace = $parts ;
            $source = str_replace($search, $replace, $event->getSource());
            $event->setSource($source);
        }
    }

    /**
     *  マイページ
     *
     * @param TemplateEvent $event
     */
    public function onRenderMypageHistoryTwigIndexRender( $event) {
        //dump("onRenderMyPageNavi");
        // ログイン判定
        if ($this->isAuthRouteFront()) {
            $Customer = $this->app->user();
            $id = $Customer->GetId();
            $parameters = $event->getParameters();
            $parts = $this->app['twig']->getLoader()->getSource('ProductVideo/Resource/template/Mypage/navi.twig');
            // twigコードに挿入
            // 要素箇所の取得
            $search = "{% include 'Mypage/navi.twig' %}";
            $replace = $parts ;
            $source = str_replace($search, $replace, $event->getSource());
            $event->setSource($source);
        }
    }

    /**
     * フロント画面権限確認
     *
     * @return bool
     */
    protected function isAuthRouteFront() {
        return $this->app->isGranted('ROLE_USER');
    }

}
