<?php

/*
 * This file is part of the FormDb
 *
 * Copyright (C) 2018 ふまねっと
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\FormDb\Controller;

use Eccube\Application;
use Symfony\Component\HttpFoundation\Request;

use Plugin\FormDb\Entity\FormContent;


class FormDbController
{

    /**
     * FormDb画面
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {

        // add code...

        return $app->render('FormDb/Resource/template/index.twig', array(
            // add parameter...
        ));
    }


    /**
     * FormDb 保存
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */

    public function saveform(Application $app, Request $request)
    {
 
        $save_name = $request->get('save_name');
        $urlpath = $request->get('urlpath');
        $formdata = $request->get('formdata');
		$message="";
		if( strlen($save_name)==0  ||strlen($urlpath)==0  || strlen($formdata)==0){
			$message="値が不正です。　登録できませんでした。";
		} else {
	         $FormContent = $app['eccube.plugin.formdb.repository.FormContent']->findOneBy(array('form_name' => $save_name,'urlpath' => $urlpath));
	         if(!$FormContent){
				$FormContent =new FormContent();
				$message=$save_name."は新規登録されました。";
	         } else {
				$message=$save_name."は再登録されました。";
	         }
	         $FormContent->setFormValue($formdata);
	         $FormContent->setFormName($save_name);
	         $FormContent->setUrlpath($urlpath);
	        $app['orm.em']->persist($FormContent);
	        $app['orm.em']->flush($FormContent);
		}

        return $app->render('FormDb/Resource/template/save.twig', array(
            // add parameter...
            "message"=>$message,
        ));
    }

    /**
     * FormDb 取得
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getlist(Application $app, Request $request)
    {

        $urlpath = $request->get('urlpath');

        $getlist = $app['orm.em']->createQueryBuilder('a')
			->select('a')
            ->from('Plugin\FormDb\Entity\FormContent', 'a')
            ->where('a.urlpath = :urlpath' )
			->setParameter('urlpath',  $urlpath )
            ->getQuery()
            ->execute();
///dump($getlist);
        return $app->render('FormDb/Resource/template/list.twig', array(
            // add parameter...
            'formlist'=>$getlist
        ));
    }


    /**
     * FormDb 取得
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getform(Application $app, Request $request)
    {

        // add code...
        $id = $request->get('id');
        $FormContent = $app['eccube.plugin.formdb.repository.FormContent']->findOneBy(array('id' =>$id));
		//dump($FormContent);
		$data=$FormContent->getFormValue();
		return $app->json($data, 200);

    }

    /**
     * FormDb 削除
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function deleteform(Application $app, Request $request)
    {
        // add code...
		$message="";
        $id = $request->get('id');
        $FormContent = $app['eccube.plugin.formdb.repository.FormContent']->findOneBy(array('id' =>$id));
		if($FormContent){
			$formName = $FormContent->getFormName();
				$message= $formName . "を削除しました。";
				$app['orm.em']->remove($FormContent);
		        $app['orm.em']->flush($FormContent);
		} else {
				$message="該当する。登録はありません。";
		}
        return $app->render('FormDb/Resource/template/delete.twig', array(
            // add parameter...
            "message"=>$message,
        ));
    }


}
