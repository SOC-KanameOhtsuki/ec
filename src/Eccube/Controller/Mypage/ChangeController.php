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
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class ChangeController extends AbstractController
{
    /**
     * 会員情報編集画面.
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function index(Application $app, Request $request)
    {
        $Customer = $app->user();
        $LoginCustomer = clone $Customer;
        $app['orm.em']->detach($LoginCustomer);
        $HomeCustomerAddress = new \Eccube\Entity\CustomerAddress();
        $OfficeAddress = new \Eccube\Entity\CustomerAddress();
        $CustomerAddresses = $Customer->getCustomerAddresses();
        $MailTo = 1;
        if ($CustomerAddresses) {
            foreach($CustomerAddresses as $CustomerAddress) {
                if (!is_null($CustomerAddress->getAddressType())) {
                    if ($CustomerAddress->getAddressType()->getId() == 1) {
                        $HomeCustomerAddress = $CustomerAddress;
                        if (preg_match("/" . $app['config']['dummy_email_pattern'] . "/", $HomeCustomerAddress->getEmail())) {
                            $HomeCustomerAddress->setEmail(null);
                        }
                    } else if ($CustomerAddress->getAddressType()->getId() == 2) {
                        $OfficeAddress = $CustomerAddress;
                        if ($OfficeAddress->getMailTo()->getId() == 2) {
                            $MailTo = 2;
                        }
                    }
                } else {
                    log_info('NULL AddressType:', array($CustomerAddress->getId()));
                }
            }
        }

        $previous_password = $Customer->getPassword();
        $Customer->setPassword($app['config']['default_password']);

        /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
        $builder = $app['form.factory']->createBuilder('entry', $Customer);

        $event = new EventArgs(
            array(
                'builder' => $builder,
                'Customer' => $Customer,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_MYPAGE_CHANGE_INDEX_INITIALIZE, $event);

        /* @var $form \Symfony\Component\Form\FormInterface */
        $form = $builder->getForm();
        // PINコード
        // ※正会員の場合は、PINコードを変更不可にする
        if (in_array($Customer->getCustomerBasicInfo()->getStatus()->getId(), $app['config']['can_not_change_pincode_status'])) {
            $form->remove('customer_pin_code');
        } else {
            $form['customer_pin_code']->setData($Customer->getCustomerBasicInfo()->getCustomerPinCode());
        }
        // 郵送先
        $form['mail_to']->setData($MailTo);
        // 自宅住所
        $form['home_address']->setData($HomeCustomerAddress);
        // 勤務先住所
        $form['office_address']->setData($OfficeAddress);
        $form->get('office_address')->remove('name');
        $form->get('office_address')->remove('kana');
        $form->get('office_address')->remove('mobilephone');
        $form->get('office_address')->remove('email');
        $form['nobulletin']->setData($Customer->getCustomerBasicInfo()->getNobulletin());
        $form['anonymous']->setData($Customer->getCustomerBasicInfo()->getAnonymous());
        $form['anonymous_company']->setData($Customer->getCustomerBasicInfo()->getAnonymousCompany());
        $form->handleRequest($request);
        if ((strlen($OfficeAddress->getCompanyName()) < 1) &&
            (strlen($OfficeAddress->getZip01()) < 1) &&
            (strlen($OfficeAddress->getZip02()) < 1) &&
            (strlen($OfficeAddress->getPref()) < 1) &&
            (strlen($OfficeAddress->getAddr01()) < 1) &&
            (strlen($OfficeAddress->getAddr02()) < 1) &&
            (strlen($OfficeAddress->getTel01()) < 1) &&
            (strlen($OfficeAddress->getTel02()) < 1) &&
            (strlen($OfficeAddress->getTel03()) < 1) &&
            (strlen($OfficeAddress->getFax01()) < 1) &&
            (strlen($OfficeAddress->getFax02()) < 1) &&
            (strlen($OfficeAddress->getFax03()) < 1) && 
            ($form['mail_to']->getData() == 2)) {
            $form['mail_to']->addError(new FormError("郵送先を勤務先にする場合は勤務先を入力してください"));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            log_info('会員編集開始');
            if (in_array($Customer->getCustomerBasicInfo()->getStatus()->getId(), $app['config']['can_not_change_pincode_status'])) {
                if ($Customer->getPassword() === $app['config']['default_password']) {
                    $Customer->setPassword($previous_password);
                } else {
                    if ($Customer->getSalt() === null) {
                        $Customer->setSalt($app['eccube.repository.customer']->createSalt(5));
                    }
                    $Customer->setPassword(
                        $app['eccube.repository.customer']->encryptPasswordFromParam($app, $Customer->getSalt(), $request->request->get('entry')['customer_pin_code']['first'])
                    );
                }
            }
            log_info('input Email:' . $HomeCustomerAddress->getEmail());
            if (empty($HomeCustomerAddress->getEmail())) {
                if (preg_match("/" . $app['config']['dummy_email_pattern'] . "/", $Customer->getEmail())) {
                    $HomeCustomerAddress->setEmail($Customer->getEmail());
                } else {
                    $HomeCustomerAddress->setEmail(sprintf($app['config']['dummy_email'], date("YmdHis"), substr(explode(".", (microtime(true) . ""))[1], 0, 3)));
                }
            }
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
            $app['orm.em']->persist($Customer);
            $CustomerBasicInfo = $Customer->getCustomerBasicInfo();
            $CustomerBasicInfo->setNobulletin($form->get('nobulletin')->getData())
                                ->setAnonymous($form->get('anonymous')->getData())
                                ->setAnonymousCompany($form->get('anonymous_company')->getData());
            if (!in_array($CustomerBasicInfo->getStatus()->getId(), $app['config']['can_not_change_pincode_status'])) {
                $CustomerBasicInfo->setCustomerPinCode($request->request->get('entry')['customer_pin_code']['first']);
            }
            $app['orm.em']->persist($CustomerBasicInfo);
            $HomeCustomerAddress->setCustomer($Customer)
                    ->setAddressType($app['orm.em']->getRepository('Eccube\Entity\Master\CustomerAddressType')->find(1))
                    ->setZipcode($HomeCustomerAddress->getZip01() . $HomeCustomerAddress->getZip02());
            if ($form['mail_to']->getData() == 1) {
                $HomeCustomerAddress->setMailTo($app['orm.em']->getRepository('Eccube\Entity\Master\MailTo')->find(2));
            } else {
                $HomeCustomerAddress->setMailTo($app['orm.em']->getRepository('Eccube\Entity\Master\MailTo')->find(1));
            }
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
                if ($form['mail_to']->getData() == 2) {
                    $OfficeAddress->setMailTo($app['orm.em']->getRepository('Eccube\Entity\Master\MailTo')->find(2));
                } else {
                    $OfficeAddress->setMailTo($app['orm.em']->getRepository('Eccube\Entity\Master\MailTo')->find(1));
                }
                $app['orm.em']->persist($OfficeAddress);
            } else if ($OfficeAddress->getId() !== null) {
                $app['orm.em']->remove($OfficeAddress);
            }
            $app['orm.em']->flush();

            log_info('会員編集完了');

            $event = new EventArgs(
                array(
                    'form' => $form,
                    'Customer' => $Customer,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::FRONT_MYPAGE_CHANGE_INDEX_COMPLETE, $event);

            return $app->redirect($app->url('mypage_change_complete'));
        }

        $app['security']->getToken()->setUser($LoginCustomer);

        return $app->render('Mypage/change.twig', array(
            'form' => $form->createView(),
            'memberId' => $Customer->getCustomerBasicInfo()->getCustomerNumber(),
        ));
    }

    /**
     * 会員情報編集完了画面.
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function complete(Application $app, Request $request)
    {
        return $app->render('Mypage/change_complete.twig');
    }
}
