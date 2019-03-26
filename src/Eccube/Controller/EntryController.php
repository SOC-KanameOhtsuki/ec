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


namespace Eccube\Controller;

use Eccube\Application;
use Eccube\Entity\Master\CustomerStatus;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception as HttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Validator\Constraints as Assert;

class EntryController extends AbstractController
{

    /**
     * 会員登録画面.
     *
     * @param  Application $app
     * @param  Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {
        if ($app->isGranted('ROLE_USER')) {
            log_info('認証済のためログイン処理をスキップ');

            return $app->redirect($app->url('mypage'));
        }

        /** @var $Customer \Eccube\Entity\Customer */
        $Customer = $app['eccube.repository.customer']->newCustomer();
        $HomeCustomerAddress = new \Eccube\Entity\CustomerAddress();
        $OfficeAddress = new \Eccube\Entity\CustomerAddress();

        /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
        $builder = $app['form.factory']->createBuilder('entry', $Customer);

        $event = new EventArgs(
            array(
                'builder' => $builder,
                'Customer' => $Customer,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_ENTRY_INDEX_INITIALIZE, $event);

        /* @var $form \Symfony\Component\Form\FormInterface */
        $form = $builder->getForm();
        // 自宅住所
        $form['home_address']->setData($HomeCustomerAddress);
        // 勤務先住所
        $form['office_address']->setData($OfficeAddress);
        $form->get('office_address')->remove('name');
        $form->get('office_address')->remove('kana');
        $form->get('office_address')->remove('mobilephone');
        $form->get('office_address')->remove('email');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            switch ($request->get('mode')) {
                case 'confirm':
                    log_info('会員登録確認開始');
                    $builder->setAttribute('freeze', true);
                    $form = $builder->getForm();
                    $form->handleRequest($request);
                    log_info('会員登録確認完了');

                    return $app->render('Entry/confirm.twig', array(
                        'form' => $form->createView(),
                    ));

                case 'complete':
                    log_info('会員登録開始');
                    $Customer->setName01($HomeCustomerAddress->getName01())
                        ->setName02($HomeCustomerAddress->getName02())
                        ->setKana01($HomeCustomerAddress->getKana01())
                        ->setKana02($HomeCustomerAddress->getKana02())
                        ->setCompanyName($OfficeAddress->getCompanyName())
                        ->setZip01($HomeCustomerAddress->getZip01())
                        ->setZip02($HomeCustomerAddress->getZip02())
                        ->setZipcode($HomeCustomerAddress->getZip01() . $Customer->getZip02())
                        ->setPref($HomeCustomerAddress->getPref())
                        ->setAddr01($HomeCustomerAddress->getAddr01())
                        ->setAddr02($HomeCustomerAddress->getAddr02())
                        ->setTel01($HomeCustomerAddress->getTel01())
                        ->setTel02($HomeCustomerAddress->getTel02())
                        ->setTel03($HomeCustomerAddress->getTel03())
                        ->setFax01($HomeCustomerAddress->getFax01())
                        ->setFax02($HomeCustomerAddress->getFax02())
                        ->setFax03($HomeCustomerAddress->getFax03())
                        ->setMobilephone01($HomeCustomerAddress->getMobilephone01())
                        ->setMobilephone02($HomeCustomerAddress->getMobilephone02())
                        ->setMobilephone03($HomeCustomerAddress->getMobilephone03())
                        ->setEmail($HomeCustomerAddress->getEmail());
                    $CustomerBasicInfo = new \Eccube\Entity\CustomerBasicInfo();
                    $CustomerBasicInfo->setCustomer($Customer)
                                        ->setStatus($app['eccube.repository.customer_basic_info_status']->find($app['config']['initialize_customer_basicinfo_status']))
                                        ->setNobulletin($form->get('nobulletin')->getData())
                                        ->setAnonymous($form->get('anonymous')->getData())
                                        ->setAnonymousCompany($form->get('anonymous_company')->getData())
                                        ->setMembershipExemption($app['eccube.repository.master.exemption_type_type']->find($app['config']['initialize_exemption_type']))
                                        ->setInstructorType($app['eccube.repository.master.instructor_type']->find($app['config']['initialize_instructor_type']))
                                        ->setSupporterType($app['eccube.repository.master.supporter_type']->find($app['config']['initialize_supporter_type']))
                                        ->setCustomerPinCode($request->request->get('entry')['customer_pin_code']['first']);
                    $Customer->setSalt(
                            $app['eccube.repository.customer']->createSalt(5)
                        )
                        ->setPassword(
                            $app['eccube.repository.customer']->encryptPasswordFromParam($app, $Customer->getSalt(), $request->request->get('entry')['customer_pin_code']['first'])
                        )
                        ->setSecretKey(
                            $app['eccube.repository.customer']->getUniqueSecretKey($app)
                        );
                    $app['orm.em']->persist($Customer);
                    $HomeCustomerAddress->setCustomer($Customer)
                            ->setAddressType($app['orm.em']->getRepository('Eccube\Entity\Master\CustomerAddressType')->find(1))
                            ->setZipcode($HomeCustomerAddress->getZip01() . $HomeCustomerAddress->getZip02());
                    $app['orm.em']->persist($HomeCustomerAddress);
                    if ((1 < strlen($OfficeAddress->getCompanyName())) ||
                        (1 < strlen($OfficeAddress->getZip01())) ||
                        (1 < strlen($OfficeAddress->getZip02())) ||
                        (1 < strlen($OfficeAddress->getPref())) ||
                        (1 < strlen($OfficeAddress->getAddr01())) ||
                        (1 < strlen($OfficeAddress->getAddr02())) ||
                        (1 < strlen($OfficeAddress->getTel01())) ||
                        (1 < strlen($OfficeAddress->getTel02())) ||
                        (1 < strlen($OfficeAddress->getTel03())) ||
                        (1 < strlen($OfficeAddress->getFax01())) ||
                        (1 < strlen($OfficeAddress->getFax02())) ||
                        (1 < strlen($OfficeAddress->getFax03()))) {
                        $OfficeAddress->setCustomer($Customer)
                            ->setAddressType($app['orm.em']->getRepository('Eccube\Entity\Master\CustomerAddressType')->find(2))
                            ->setZipcode($OfficeAddress->getZip01() . $OfficeAddress->getZip02());
                        $app['orm.em']->persist($OfficeAddress);
                    } else if ($OfficeAddress->getId() !== null) {
                        $app['orm.em']->remove($OfficeAddress);
                    }
                    $customerNumber = sprintf($app['config']['temp_customer_number'], date('YmdHis'));
                    $existsCustomerBasicInfo = $app['eccube.repository.customer_basic_info']->findOneBy(array('customer_number' =>$customerNumber));
                    while (!is_null($existsCustomerBasicInfo)) {
                        // 仮会員IDには秒数まで入れるため競合時は数秒スリープ後に再試行
                        usleep(rand(1000000, 5000000));
                        $customerNumber = sprintf($app['config']['temp_customer_number'], date('YmdHis'));
                        $existsCustomerBasicInfo = $app['eccube.repository.customer_basic_info']->findOneBy(array('customer_number' =>$customerNumber));
                    }
                    $CustomerBasicInfo->setCustomerNumber($customerNumber);
                    $CustomerBasicInfo->setBureau($app['orm.em']->getRepository('Eccube\Entity\Master\Bureau')->find(18));
                    $app['orm.em']->persist($CustomerBasicInfo);
                    $app['orm.em']->flush();

                    log_info('会員登録完了');

                    $event = new EventArgs(
                        array(
                            'form' => $form,
                            'Customer' => $Customer,
                            'CustomerAddress' => $HomeCustomerAddress,
                        ),
                        $request
                    );
                    $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_ENTRY_INDEX_COMPLETE, $event);

                    $activateUrl = $app->url('entry_activate', array('secret_key' => $Customer->getSecretKey()));

                    /** @var $BaseInfo \Eccube\Entity\BaseInfo */
                    $BaseInfo = $app['eccube.repository.base_info']->get();
                    $activateFlg = $BaseInfo->getOptionCustomerActivate();

                    // 仮会員設定が有効な場合は、確認メールを送信し完了画面表示.
                    if (($activateFlg) && (!empty($Customer->getEmail()))) {
                        // メール送信
                        $app['eccube.service.mail']->sendCustomerConfirmMail($Customer, $activateUrl);

                        if ($event->hasResponse()) {
                            return $event->getResponse();
                        }

                        log_info('仮会員登録完了画面へリダイレクト');

                        return $app->redirect($app->url('entry_complete'));
                        // 仮会員設定が無効な場合は認証URLへ遷移させ、会員登録を完了させる.
                    } else {
                        log_info('本会員登録画面へリダイレクト');

                        return $app->redirect($activateUrl);
                    }
            }
        } else {
            foreach ($form->getErrors(true) as $Error) { 
                log_info('error:', array($Error->getOrigin()->getName(), $Error->getMessage()));
            }
        }

        return $app->render('Entry/index.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * 会員登録完了画面.
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function complete(Application $app)
    {
        return $app->render('Entry/complete.twig', array());
    }

    /**
     * 会員のアクティベート（本会員化）を行う.
     *
     * @param Application $app
     * @param Request $request
     * @param $secret_key
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function activate(Application $app, Request $request, $secret_key)
    {
        $errors = $app['validator']->validateValue($secret_key, array(
                new Assert\NotBlank(),
                new Assert\Regex(array(
                    'pattern' => '/^[a-zA-Z0-9]+$/',
                ))
            )
        );

        if ($request->getMethod() === 'GET' && count($errors) === 0) {
            log_info('本会員登録開始');
            try {
                $Customer = $app['eccube.repository.customer']
                    ->getNonActiveCustomerBySecretKey($secret_key);
            } catch (\Exception $e) {
                throw new HttpException\NotFoundHttpException('※ 既に会員登録が完了しているか、無効なURLです。');
            }

            $CustomerStatus = $app['eccube.repository.customer_status']->find(CustomerStatus::ACTIVE);
            $Customer->setStatus($CustomerStatus);
            $app['orm.em']->persist($Customer);
            $app['orm.em']->flush();

            log_info('本会員登録完了');

            $event = new EventArgs(
                array(
                    'Customer' => $Customer,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_ENTRY_ACTIVATE_COMPLETE, $event);

            // メール送信
            if (!empty($Customer->getEmail())) {
                $app['eccube.service.mail']->sendCustomerCompleteMail($Customer);
            } else {
                $Customer->setEmail(sprintf($app['config']['dummy_email'], date("YmdHis"), substr(explode(".", (microtime(true) . ""))[1], 0, 3)));
                $HomeCustomerAddress = null;
                $CustomerAddresses = $Customer->getCustomerAddresses();
                if ($CustomerAddresses) {
                    foreach($CustomerAddresses as $CustomerAddress) {
                        if (!is_null($CustomerAddress->getAddressType())) {
                            if ($CustomerAddress->getAddressType()->getId() == 1) {
                                $HomeCustomerAddress = $CustomerAddress;
                            }
                        }
                    }
                }
                if (is_null($HomeCustomerAddress)) {
                    $HomeCustomerAddress->setEmail($Customer->getEmail());
                    $app['orm.em']->persist($HomeCustomerAddress);
                }
                $app['orm.em']->persist($Customer);
                $app['orm.em']->flush();
            }

            // 本会員登録してログイン状態にする
            $token = new UsernamePasswordToken($Customer, null, 'customer', array('ROLE_USER'));
            $this->getSecurity($app)->setToken($token);
            $request->getSession()->migrate(true, $app['config']['cookie_lifetime']);

            log_info('ログイン済に変更', array($app->user()->getId()));

            return $app->render('Entry/activate.twig', array('member_id' => $Customer->getCustomerBasicInfo()->getCustomerNumber()));
        } else {
            throw new HttpException\AccessDeniedHttpException('不正なアクセスです。');
        }
    }
}
