<?php
/*
 * This file is Cutomized File.
 */

namespace Eccube\Controller;

use Eccube\Application;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\Validator\Constraints as Assert;

class SecondLoginController extends AbstractController
{
    /**
     * 2段階ログイン.
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {
        $Customer = null;
        $session = $request->getSession();
        // 2段階ログインは会員認証必須
        if (!$app->isGranted('IS_AUTHENTICATED_FULLY')) {
            if ($session->has('eccube.second_login.fail_redirect.url')) {
                $targetFailPath = $session->set('eccube.second_login.fail_redirect.url');
            } else {
                $targetFailPath = $app['config']['second_login_def_fail_target_path'];
            }
            return $app->redirect($targetFailPath);
        } else {
            $Customer = $app->user();
        }

        $builder = $app['form.factory']
            ->createNamedBuilder('', 'second_login');

        $form = $builder->getForm();
        $form->handleRequest($request);
        $error = null;

        if ($form->isSubmitted()) {
            $frame = $form->get('_form_frame')->getData();
            $body = $form->get('_form_body')->getData();
            $targetPath = $form->get('_target_path')->getData();
            if ($form->isValid()) {
                $CustomerBasicInfo = $Customer->getCustomerBasicInfo();
                if (($CustomerBasicInfo->getCustomerNumber() == $form->get('login_menber_id')->getData())
                    && ($CustomerBasicInfo->getCustomerPinCode() == $form->get('login_pin')->getData())) {
                    $app->setCustomerType($app['config']['customer_type_fumanet']);
                    return $app->redirect($targetPath);
                } else {
                    log_error('2段階認証エラー');
                    $error = 'ふまねっと会員IDまたはPINコードに誤りがあります。';
                }
            } else {
                foreach ($form->getErrors(true) as $Error) { 
                    log_error('error:', array($Error->getOrigin()->getName(), $Error->getMessage()));
                }
                log_error('Validエラー');
                $error = 'ふまねっと会員IDまたはPINコードに誤りがあります。';
            }
        } else {
            $session = $request->getSession();
            if ($session->has('eccube.second_login.form.frame')) {
                $frame = $session->get('eccube.second_login.form.frame');
            } else {
                $frame = $app['config']['second_login_def_frame'];
            }
            if ($session->has('eccube.second_login.form.body')) {
                $body = $session->get('eccube.second_login.form.body');
            } else {
                $body = $app['config']['second_login_def_body'];
            }
            if ($session->has('eccube.second_login.redirect.url')) {
                $targetPath = $session->get('eccube.second_login.redirect.url');
            } else {
                $targetPath = $app['config']['second_login_def_target_path'];
            }
        }

        return $app->render('SecondLogin/index.twig', array(
            'form' => $form->createView(),
            'targetPath' => $targetPath,
            'frame' => $frame,
            'error' => $error,
            'body' => $body,
        ));
    }

    /**
     * 2段階ログインキャンセル.
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function cancel(Application $app, Request $request)
    {
        $app->setCustomerType($app['config']['customer_type_normal']);
        return $app->redirect("/" . $request->request->get('_target_path'));
    }
}
