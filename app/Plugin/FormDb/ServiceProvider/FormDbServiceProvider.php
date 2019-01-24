<?php

/*
 * This file is part of the FormDb
 *
 * Copyright (C) 2018 ふまねっと
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\FormDb\ServiceProvider;

use Monolog\Handler\FingersCrossed\ErrorLevelActivationStrategy;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Plugin\FormDb\Form\Type\FormDbConfigType;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;

class FormDbServiceProvider implements ServiceProviderInterface
{

    public function register(BaseApplication $app)
    {
        // プラグイン用設定画面
        $app->match('/'.$app['config']['admin_route'].'/plugin/FormDb/config', 'Plugin\FormDb\Controller\ConfigController::index')->bind('plugin_FormDb_config');

        // 独自コントローラ
        $app->match('/plugin/formdb/hello', 'Plugin\FormDb\Controller\FormDbController::index')->bind('plugin_FormDb_hello');

        //Form保存
        /*
        POST
         save_name: 保存名
         urlpath: URLPATH
         formdata: シリアライズしたformデータr
        */
        $app->match('/plugin/formdb/saveform', 'Plugin\FormDb\Controller\FormDbController::saveform')->bind('plugin_FormDb_save');



        //Form取得 一意
        /*
        POST
         urlpath: URLPATH
        ret HTML のリスト一覧
        */
        $app->match('/plugin/formdb/getlist', 'Plugin\FormDb\Controller\FormDbController::getlist')->bind('plugin_FormDb_getList');
        
        //Form取得 一意
        /*
        POST
        id: ID
        formdata: JSONデータr
        */
        $app->match('/plugin/formdb/getform', 'Plugin\FormDb\Controller\FormDbController::getform')->bind('plugin_FormDb_getform');

        //Form削除
        /*
        POST
        id: ID
        */
        $app->match('/plugin/formdb/deleteform', 'Plugin\FormDb\Controller\FormDbController::deleteform')->bind('plugin_FormDb_deleteform');


        // Form
        $app['form.types'] = $app->share($app->extend('form.types', function ($types) use ($app) {
            $types[] = new FormDbConfigType();

            return $types;
        }));

        // Repository
        $app['eccube.plugin.formdb.repository.FormContent'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\FormDb\Entity\FormContent');
        });

        // Service

        // メッセージ登録
        // $file = __DIR__ . '/../Resource/locale/message.' . $app['locale'] . '.yml';
        // $app['translator']->addResource('yaml', $file, $app['locale']);

        // load config
        // プラグイン独自の定数はconfig.ymlの「const」パラメータに対して定義し、$app['formdbconfig']['定数名']で利用可能
        // if (isset($app['config']['FormDb']['const'])) {
        //     $config = $app['config'];
        //     $app['formdbconfig'] = $app->share(function () use ($config) {
        //         return $config['FormDb']['const'];
        //     });
        // }

        // ログファイル設定
        $app['monolog.logger.formdb'] = $app->share(function ($app) {

            $logger = new $app['monolog.logger.class']('formdb');

            $filename = $app['config']['root_dir'].'/app/log/formdb.log';
            $RotateHandler = new RotatingFileHandler($filename, $app['config']['log']['max_files'], Logger::INFO);
            $RotateHandler->setFilenameFormat(
                'formdb_{date}',
                'Y-m-d'
            );

            $logger->pushHandler(
                new FingersCrossedHandler(
                    $RotateHandler,
                    new ErrorLevelActivationStrategy(Logger::ERROR),
                    0,
                    true,
                    true,
                    Logger::INFO
                )
            );

            return $logger;
        });

    }

    public function boot(BaseApplication $app)
    {
    }

}
