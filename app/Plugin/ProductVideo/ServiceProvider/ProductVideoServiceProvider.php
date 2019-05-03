<?php

/*
 * This file is part of the ProductVideo
 *
 * Copyright (C) 2018 ふまねっと
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductVideo\ServiceProvider;

use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Plugin\ProductVideo\Form\Type\ProductVideoConfigType;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;

class ProductVideoServiceProvider implements ServiceProviderInterface
{

    public function register(BaseApplication $app)
    {
        // プラグイン用設定画面
        $app->match('/'.$app['config']['admin_route'].'/plugin/ProductVideo/config', 'Plugin\ProductVideo\Controller\ConfigController::index')->bind('plugin_ProductVideo_config');

        // 独自コントローラ
         //front
         $app->match('/plugin/productvideo/history', 'Plugin\ProductVideo\Controller\ProductVideoController::history')
                ->bind('plugin_ProductVideo_history');

         $app->match('/plugin/productvideo/history/page/{page_no}', 'Plugin\ProductVideo\Controller\ProductVideoController::history')
                ->assert('page_no', '\d+')
                ->bind('plugin_ProductVideo_history_page');


         //front
         $app->match('/plugin/productvideo/play/{product_id}', 'Plugin\ProductVideo\Controller\ProductVideoController::play')
            ->assert('product_id', '\d+')
            ->bind('plugin_ProductVideo_play');

        // sample
         $app->match('/plugin/productvideo/hello',   'Plugin\ProductVideo\Controller\ProductVideoController::index')->bind('plugin_ProductVideo_hello');




        // Form
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new ProductVideoConfigType();
            return $types;
        }));




        // Repository
        $app['eccube.plugin.productvideo.repository.ProductVideo'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\ProductVideo\Entity\ProductVideo');
        });

        // Service


        // メッセージ登録
        // $file = __DIR__ . '/../Resource/locale/message.' . $app['locale'] . '.yml';
        // $app['translator']->addResource('yaml', $file, $app['locale']);

        // load config
        // プラグイン独自の定数はconfig.ymlの「const」パラメータに対して定義し、$app['productvideoconfig']['定数名']で利用可能
        // if (isset($app['config']['ProductVideo']['const'])) {
        //     $config = $app['config'];
        //     $app['productvideoconfig'] = $app->share(function () use ($config) {
        //         return $config['ProductVideo']['const'];
        //     });
        // }

        // ログファイル設定
        $app['monolog.logger.プラグインコード'] = $app->share(function ($app) {
            $config = array(
                'name' => 'ProductVideo',
                'filename' => 'ProductVideo',
                'delimiter' => '_',
                'dateformat' => 'Y-m-d',
                'log_level' => 'INFO',
                'action_level' => 'ERROR',
                'passthru_level' => 'INFO',
                'max_files' => '90',
                'log_dateformat' => 'Y-m-d H:i:s,u',
                'log_format' => '[%datetime%] %channel%.%level_name% [%session_id%] [%uid%] [%user_id%] [%class%:%function%:%line%] - %message% %context% %extra% [%method%, %url%, %ip%, %referrer%, %user_agent%]',
            );
            return $app['eccube.monolog.factory']($config);
        });

    }

    public function boot(BaseApplication $app)
    {
    }

}
