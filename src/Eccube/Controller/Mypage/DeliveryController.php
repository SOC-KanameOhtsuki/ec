<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Eccube\Controller\Mypage;

use Eccube\Application;
use Eccube\Controller\AbstractController;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeliveryController extends AbstractController
{
    /**
     * お届け先一覧画面.
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {
        $Customer = $app['user'];

        return $app->render('Mypage/delivery.twig', array(
            'Customer' => $Customer,
        ));
    }

    /**
     * お届け先編集画面.
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function edit(Application $app, Request $request, $id = null)
    {
        $Customer = $app['user'];

        // 配送先住所最大値判定
        // $idが存在する際は、追加処理ではなく、編集の処理ため本ロジックスキップ
        if (is_null($id)) {
            $addressCurrNum = count($Customer->getCustomerAddresses());
            $addressMax = $app['config']['deliv_addr_max'];
            if ($addressCurrNum >= $addressMax) {
                throw new NotFoundHttpException('お届け先の登録数の上限を超えています');
            }
        }

        $CustomerAddress = $app['eccube.repository.customer_address']->findOrCreateByCustomerAndId($Customer, $id);
        $hasName = false;
        $hasKana = false;
        $hasMobilePhone = false;
        $hasMail = false;
        if ($CustomerAddress->getAddressType()->getId() == 1) {
            $hasName = true;
            $hasKana = true;
            $hasMobilePhone = true;
            $hasMail = true;
            if (preg_match("/" . $app['config']['dummy_email_pattern'] . "/", $CustomerAddress->getEmail())) {
                $CustomerAddress->setEmail(null);
            }
        } else if ($CustomerAddress->getAddressType()->getId() == 2) {
            $form->remove('name');
            $form->remove('kana');
            $form->remove('mobilephone');
            $form->remove('email');
        } else {
            $hasName = true;
            $hasKana = true;
            $form->remove('mobilephone');
            $form->remove('email');
        }

        $parentPage = $request->get('parent_page', null);

        // 正しい遷移かをチェック
        $allowdParents = array(
            $app->url('mypage_delivery'),
            $app->url('shopping_delivery'),
        );

        // 遷移が正しくない場合、デフォルトであるマイページの配送先追加の画面を設定する
        if (!in_array($parentPage, $allowdParents)) {
            // @deprecated 使用されていないコード
            $parentPage  = $app->url('mypage_delivery');
        }

        $builder = $app['form.factory']
            ->createBuilder('customer_address', $CustomerAddress);

        $event = new EventArgs(
            array(
                'builder' => $builder,
                'Customer' => $Customer,
                'CustomerAddress' => $CustomerAddress,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_MYPAGE_DELIVERY_EDIT_INITIALIZE, $event);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            log_info('お届け先登録開始', array($id));

            if ($CustomerAddress->getAddressType()->getId() == 1) {
                if (empty($CustomerAddress->getEmail())) {
                    if (preg_match("/" . $app['config']['dummy_email_pattern'] . "/", $Customer->getEmail())) {
                        $CustomerAddress->setEmail($Customer->getEmail());
                    } else {
                        $CustomerAddress->setEmail(sprintf($app['config']['dummy_email'], date("YmdHis"), substr(explode(".", (microtime(true) . ""))[1], 0, 3)));
                    }
                }
            }

            $app['orm.em']->persist($CustomerAddress);
            $app['orm.em']->flush();

            log_info('お届け先登録完了', array($id));

            $event = new EventArgs(
                array(
                    'form' => $form,
                    'Customer' => $Customer,
                    'CustomerAddress' => $CustomerAddress,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_MYPAGE_DELIVERY_EDIT_COMPLETE, $event);

            $app->addSuccess('mypage.delivery.add.complete');

            return $app->redirect($app->url('mypage_delivery'));
        }

        $BaseInfo = $app['eccube.repository.base_info']->get();

        return $app->render('Mypage/delivery_edit.twig', array(
            'form' => $form->createView(),
            'parentPage' => $parentPage,
            'BaseInfo' => $BaseInfo,
            'hasName' => $hasName,
            'hasKana' => $hasKana,
            'hasMobilePhone' => $hasMobilePhone,
            'hasMail' => $hasMail,
        ));
    }

    /**
     * お届け先を削除する.
     *
     * @param Application $app
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function delete(Application $app, Request $request, $id)
    {
        $this->isTokenValid($app);

        log_info('お届け先削除開始', array($id));

        $Customer = $app['user'];

        $status = $app['eccube.repository.customer_address']->deleteByCustomerAndId($Customer, $id);

        if ($status) {
            $event = new EventArgs(
                array(
                    'id' => $id,
                    'Customer' => $Customer,
                ), $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_MYPAGE_DELIVERY_DELETE_COMPLETE, $event);

            $app->addSuccess('mypage.address.delete.complete');

            log_info('お届け先削除完了', array($id));

        } else {
            $app->addError('mypage.address.delete.failed');

            log_info('お届け先削除失敗', array($id));
        }

        return $app->redirect($app->url('mypage_delivery'));
    }
}
