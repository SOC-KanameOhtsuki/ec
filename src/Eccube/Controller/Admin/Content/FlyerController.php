<?php
/*
 * This file is Customize File
 */

namespace Eccube\Controller\Admin\Content;

use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * チラシ管理のコントローラクラス
 */
class FlyerController extends AbstractController
{
    /**
     * チラシ情報一覧を表示する。
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexFlyer(Application $app, Request $request)
    {
        $FlyerList = $app['eccube.repository.flyer']->findBy(array(), array('rank' => 'DESC'));

        $builder = $app->form();

        $form = $builder->getForm();
        return $app->render('Content/flyer.twig', array(
            'form' => $form->createView(),
            'FlyerList' => $FlyerList,
        ));
    }

    /**
     * チラシ情報を登録・編集する。
     *
     * @param Application $app
     * @param Request $request
     * @param integer $id
     * @throws NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editFlyer(Application $app, Request $request, $id = null)
    {
        if ($id) {
            $Flyer = $app['eccube.repository.flyer']->find($id);
            if (!$Flyer) {
                throw new NotFoundHttpException();
            }
            $Flyer->setLinkMethod((bool) $Flyer->getLinkMethod());
        } else {
            $Flyer = new \Eccube\Entity\Flyer();
        }

        $builder = $app['form.factory']
            ->createBuilder('admin_flyer', $Flyer);

        $form = $builder->getForm();

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $data = $form->getData();
                if (empty($data['url'])) {
                    $Flyer->setLinkMethod(Constant::DISABLED);
                }
                $status = $app['eccube.repository.flyer']->save($Flyer);

                if ($status) {
                    $app->addSuccess('admin.flyer.save.complete', 'admin');
                    return $app->redirect($app->url('admin_content_flyer'));
                }
                $app->addError('admin.flyer.save.error', 'admin');
            }
        }

        return $app->render('Content/flyer_edit.twig', array(
            'form' => $form->createView(),
            'Flyer' => $Flyer,
        ));
    }

    /**
     * 指定したチラシ情報の表示順を1つ上げる。
     *
     * @param Application $app
     * @param Request $request
     * @param integer $id
     * @throws NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function up(Application $app, Request $request, $id)
    {
        $this->isTokenValid($app);

        $TargetFlyer = $app['eccube.repository.flyer']->find($id);
        if (!$TargetFlyer) {
            throw new NotFoundHttpException();
        }

        $status = $app['eccube.repository.flyer']->up($TargetFlyer);

        if ($status) {
            $app->addSuccess('admin.flyer.up.complete', 'admin');
        } else {
            $app->addError('admin.flyer.up.error', 'admin');
        }

        return $app->redirect($app->url('admin_content_flyer'));
    }

    /**
     * 指定したチラシ情報の表示順を1つ下げる。
     *
     * @param Application $app
     * @param Request $request
     * @param integer $id
     * @throws NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function down(Application $app, Request $request, $id)
    {
        $this->isTokenValid($app);

        $TargetFlyer = $app['eccube.repository.flyer']->find($id);
        if (!$TargetFlyer) {
            throw new NotFoundHttpException();
        }

        $status = $app['eccube.repository.flyer']->down($TargetFlyer);

        if ($status) {
            $app->addSuccess('admin.flyer.down.complete', 'admin');
        } else {
            $app->addError('admin.flyer.down.error', 'admin');
        }

        return $app->redirect($app->url('admin_content_flyer'));
    }

    /**
     * 指定したチラシ情報を削除する。
     *
     * @param Application $app
     * @param Request $request
     * @param integer $id
     * @throws NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteFlyer(Application $app, Request $request, $id)
    {
        $this->isTokenValid($app);

        $TargetFlyer = $app['eccube.repository.flyer']->find($id);
        if (!$TargetFlyer) {
            throw new NotFoundHttpException();
        }

        $status = $app['eccube.repository.flyer']->delete($TargetFlyer);

        $event = new EventArgs(
            array(
                'TargetFlyer' => $TargetFlyer,
                'status' => $status,
            ),
            $request
        );

        if ($status) {
            $app->addSuccess('admin.flyer.delete.complete', 'admin');
        } else {
            $app->addSuccess('admin.flyer.delete.error', 'admin');
        }

        return $app->redirect($app->url('admin_content_flyer'));
    }
}