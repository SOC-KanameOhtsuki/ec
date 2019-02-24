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

namespace Eccube\Controller\Admin\Customer;

use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerEditController extends AbstractController
{
    public function index(Application $app, Request $request, $id = null)
    {
        $app['orm.em']->getFilters()->enable('incomplete_order_status_hidden');
        $isQrCodeRegisted = false;
        $QrCode = null;
        // 編集
        if ($id) {
            $Customer = $app['orm.em']
                ->getRepository('Eccube\Entity\Customer')
                ->find($id);
            if (is_null($Customer)) {
                throw new NotFoundHttpException();
            }
            $HomeCustomerAddress = new \Eccube\Entity\CustomerAddress();
            $OfficeAddress = new \Eccube\Entity\CustomerAddress();
            $CustomerAddresses = $Customer->getCustomerAddresses();
            if ($CustomerAddresses) {
                foreach($CustomerAddresses as $CustomerAddress) {
                    if (!is_null($CustomerAddress->getAddressType())) {
                        if ($CustomerAddress->getAddressType()->getId() == 1) {
                            $HomeCustomerAddress = $CustomerAddress;
                        } else if ($CustomerAddress->getAddressType()->getId() == 2) {
                            $OfficeAddress = $CustomerAddress;
                        }
                    } else {
                        log_info('NULL AddressType:', array($CustomerAddress->getId()));
                    }
                }
            }

            if (is_null($Customer->getCustomerGroup())) {
                $CustomerGroup = new \Eccube\Entity\CustomerGroup();
            } else {
                $CustomerGroup = $Customer->getCustomerGroup();
            }
            $CustomerImages = $Customer->getCustomerImages();
            if (!is_null($Customer->getCustomerBasicInfo())) {
                $customerId = $Customer->getCustomerBasicInfo()->getCustomerNumber();
                log_info('会員番号:' . $customerId, array($Customer->getId()));
                if ((0 < strlen($customerId)) && (!is_null($customerId))) {
                    if (!is_null($Customer->getCustomerQrs())) {
                        if (count($Customer->getCustomerQrs()) > 0) {
                            $QrCode = $Customer->getCustomerQrs()[0];
                        }
                    }
                    if (!is_null($QrCode)) {
                        if (file_exists($app['config']['customer_image_save_realdir'] . "/" . $QrCode->getFileName())) {
                            $isQrCodeRegisted = true;
                        }
                    }
                    if (!$isQrCodeRegisted) {
                        log_info('QRコード発行開始', array($Customer->getId()));
                        $qrCodeImg = file_get_contents($app['config']['qr_code_get_url'] . $customerId);
                        if ($qrCodeImg !== false) {
                            $fileName = date('mdHis').uniqid('_') . '.jpg';
                            if (file_put_contents($app['config']['customer_image_save_realdir'] . "/" . $fileName, $qrCodeImg) !== false) {
                                $QrCode = new \Eccube\Entity\CustomerQr();
                                $QrCode->setCustomer($Customer);
                                $QrCode->setFileName($fileName);
                                $QrCode->setRank(1);
                                $app['orm.em']->persist($QrCode);
                                $app['orm.em']->flush();
                                $isQrCodeRegisted = true;
                            };
                        }
                    }
                }
            }
            // 編集用にデフォルトパスワードをセット
            $previous_password = $Customer->getPassword();
            $Customer->setPassword($app['config']['default_password']);
            // 新規登録
        } else {
            $Customer = $app['eccube.repository.customer']->newCustomer();
            $HomeCustomerAddress = new \Eccube\Entity\CustomerAddress();
            $OfficeAddress = new \Eccube\Entity\CustomerAddress();
            $CustomerBasicInfo = new \Eccube\Entity\CustomerBasicInfo();
            $CustomerGroup = new \Eccube\Entity\CustomerGroup();
            $Customer->setBuyTimes(0);
            $Customer->setBuyTotal(0);
            $Customer->setCustomerBasicInfo($CustomerBasicInfo);
            $Customer->setCustomerGroup($CustomerGroup);
            $CustomerBasicInfo->setCustomer($Customer);
            $QrCode = null;
        }

        // 会員登録フォーム
        $builder = $app['form.factory']
            ->createBuilder('admin_customer', $Customer);

        $event = new EventArgs(
            array(
                'builder' => $builder,
                'Customer' => $Customer,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_CUSTOMER_EDIT_INDEX_INITIALIZE, $event);

        $form = $builder->getForm();
        $form['basic_info']->setData($Customer->getCustomerBasicInfo());
        // 自宅住所
        $form['home_address']->setData($HomeCustomerAddress);
        $form->get('home_address')->remove('company_name');
        // 勤務先住所
        $form['office_address']->setData($OfficeAddress);
        $form->get('office_address')->remove('name');
        $form->get('office_address')->remove('kana');
        $form->get('office_address')->remove('mobilephone');

        // ファイルの登録
        $images = array();
        $CustomerImages = $Customer->getCustomerImages();
        foreach ($CustomerImages as $CustomerImage) {
            $images[] = $CustomerImage->getFileName();
        }
        $form['images']->setData($images);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                log_info('会員登録開始', array($Customer->getId()));
                if ($Customer->getId() === null) {
                    $Customer->setSalt(
                        $app['eccube.repository.customer']->createSalt(5)
                    );
                    $Customer->setSecretKey(
                        $app['eccube.repository.customer']->getUniqueSecretKey($app)
                    );
                }

                $HomeCustomerAddress->setCustomer($Customer)
                        ->setAddressType($app['orm.em']->getRepository('Eccube\Entity\Master\CustomerAddressType')->find(1))
                        ->setZipcode($HomeCustomerAddress->getZip01() . $HomeCustomerAddress->getZip02())
                        ->setCompanyName($OfficeAddress->getCompanyName());

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
                    ->setMobilephone03($HomeCustomerAddress->getMobilephone03());

                if ($Customer->getPassword() === $app['config']['default_password']) {
                    $Customer->setPassword($previous_password);
                } else {
                    if ($Customer->getSalt() === null) {
                        $Customer->setSalt($app['eccube.repository.customer']->createSalt(5));
                    }
                    $Customer->setPassword(
                        $app['eccube.repository.customer']->encryptPassword($app, $Customer)
                    );
                }

                // 画像の登録
                $add_images = $form->get('add_images')->getData();
                foreach ($add_images as $add_image) {
                    $CustomerImage = new \Eccube\Entity\CustomerImage();
                    $CustomerImage
                        ->setFileName($add_image)
                        ->setCustomer($Customer)
                        ->setRank(1);
                    $Customer->addCustomerImages($CustomerImage);
                    $app['orm.em']->persist($CustomerImage);

                    // 移動
                    $file = new File($app['config']['image_temp_realdir'].'/'.$add_image);
                    $file->move($app['config']['customer_image_save_realdir']);
                }

                // 画像の削除
                $delete_images = $form->get('delete_images')->getData();
                foreach ($delete_images as $delete_image) {
                    $CustomerImage = $app['eccube.repository.product_image']
                        ->findOneBy(array('file_name' => $delete_image));

                    // 追加してすぐに削除した画像は、Entityに追加されない
                    if ($CustomerImage instanceof \Eccube\Entity\CustomerImage) {
                        $Customer->removeCustomerImages($CustomerImage);
                        $app['orm.em']->remove($CustomerImage);

                    }
                    $app['orm.em']->persist($Customer);

                    // 削除
                    if (!empty($delete_image)) {
                        $fs = new Filesystem();
                        $fs->remove($app['config']['customer_image_save_realdir'].'/'.$delete_image);
                    }
                }
                $CustomerBasicInfo = $form['basic_info']->getData();
                $CustomerBasicInfo->setCustomer($Customer);
                $app['orm.em']->persist($CustomerBasicInfo);
                if ($isQrCodeRegisted) {
                    log_info('(旧)QRコード削除', array($Customer->getId()));
                    $fs = new Filesystem();
                    $fs->remove($app['config']['customer_image_save_realdir'].'/'.$QrCode->getFileName());
                    $app['orm.em']->remove($QrCode);
                }
                $customerId = $CustomerBasicInfo->getCustomerNumber();
                if ((0 < strlen($customerId)) && (!is_null($customerId))) {
                    log_info('会員番号:' . $customerId, array($Customer->getId()));
                    log_info('QRコード発行開始', array($Customer->getId()));
                    $qrCodeImg = file_get_contents($app['config']['qr_code_get_url'] . $customerId);
                    if ($qrCodeImg !== false) {
                        $fileName = date('mdHis').uniqid('_') . '.jpg';
                        if (file_put_contents($app['config']['customer_image_save_realdir'] . "/" . $fileName, $qrCodeImg) !== false) {
                            $QrCode = new \Eccube\Entity\CustomerQr();
                            $QrCode->setCustomer($Customer);
                            $QrCode->setFileName($fileName);
                            $QrCode->setRank(1);
                            $app['orm.em']->persist($QrCode);
                            $app['orm.em']->flush();
                            $isQrCodeRegisted = true;
                        };
                    }
                }

                $request_data = $request->request->all();
                $CustomerGroup = null;
                if (isset($request_data['admin_customer']['belongs_group_id'])) {
                    $CustomerGroup = $app['eccube.repository.customer_group']
                        ->find($request_data['admin_customer']['belongs_group_id']);
                }
                $Customer->setCustomerGroup($CustomerGroup);

                $app['orm.em']->persist($Customer);
                $app['orm.em']->flush();

                log_info('会員登録完了', array($Customer->getId()));

                $event = new EventArgs(
                    array(
                        'form' => $form,
                        'Customer' => $Customer,
                    ),
                    $request
                );
                $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_CUSTOMER_EDIT_INDEX_COMPLETE, $event);

                $app->addSuccess('admin.customer.save.complete', 'admin');

                return $app->redirect($app->url('admin_customer_edit', array(
                    'id' => $Customer->getId(),
                )));
            } else {
                $app->addError('admin.customer.save.failed', 'admin');
            }
        }

        // 会員グループ検索フォーム
        $builder = $app['form.factory']
            ->createBuilder('admin_search_customer_group');

        $searchCustomerGroupModalForm = $builder->getForm();

        $CustomerCheckInfo = ['isRegister' => true];
        if ($Customer->getId() != null) {
            $CustomerCheckInfo['isRegister'] = false;
            if (sizeof($app['eccube.repository.order']->getProductTrainingOrders($app, $Customer)) > 0) {
                $CustomerCheckInfo['hasTrainingOrders'] = true;
            } else {
                $CustomerCheckInfo['hasTrainingOrders'] = false;
            }

            //dtb_category.id 2 == 寄付
            $ProductCategory = $app['eccube.repository.category']->find(2); 
            $Products = $app['eccube.repository.product_category']->getProductsForCategory($ProductCategory);

            if (sizeof($app['eccube.repository.order']->getContributionOrders($app, $Customer, $Products)) > 0) {
                $CustomerCheckInfo['hasContributionOrders'] = true;
            } else {
                $CustomerCheckInfo['hasContributionOrders'] = false;
            }
        }

        return $app->render('Customer/edit.twig', array(
            'form' => $form->createView(),
            'searchCustomerGroupModalForm' => $searchCustomerGroupModalForm->createView(),
            'Customer' => $Customer,
            'CustomerGroup' => $CustomerGroup,
            'CustomerCheckInfo' => $CustomerCheckInfo,
            'QrCode' => $QrCode,
        ));
    }

    public function addImage(Application $app, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException('リクエストが不正です');
        }

        $images = $request->files->get('admin_customer');

        $files = array();
        if (count($images) > 0) {
            foreach ($images as $img) {
                foreach ($img as $image) {
                    //ファイルフォーマット検証
                    $mimeType = $image->getMimeType();
                    if (0 !== strpos($mimeType, 'image')) {
                        throw new UnsupportedMediaTypeHttpException('ファイル形式が不正です');
                    }
                    $imageBinary = file_get_contents($image->getPathname());
                    $imageSize = getimagesizefromstring($imageBinary);
                    $imageResize = imagecreatefromstring($imageBinary);
                    $resizeRate = 1;
                    if ($app['config']['customer_max_width'] < $app['config']['customer_max_height']) {
                        if ($app['config']['customer_max_height'] < $imageSize[1]) {
                            $resizeRate = $app['config']['customer_max_height'] / $imageSize[1];
                        } else if ($app['config']['customer_max_width'] < $imageSize[0]) {
                            $resizeRate = $app['config']['customer_max_width'] / $imageSize[0];
                        } else if ($imageSize[0] < $imageSize[1]) {
                            $resizeRate = $app['config']['customer_max_height'] / $imageSize[1];
                        } else {
                            $resizeRate = $app['config']['customer_max_width'] / $imageSize[0];
                        }
                    } else {
                        if ($app['config']['customer_max_width'] < $imageSize[0]) {
                            $resizeRate = $app['config']['customer_max_width'] / $imageSize[0];
                        } else if ($app['config']['customer_max_height'] < $imageSize[1]) {
                            $resizeRate = $app['config']['customer_max_height'] / $imageSize[1];
                        } else if ($imageSize[1] < $imageSize[0]) {
                            $resizeRate = $app['config']['customer_max_width'] / $imageSize[0];
                        } else {
                            $resizeRate = $app['config']['customer_max_height'] / $imageSize[1];
                        }
                    }
                    $resizeWidth = round($imageSize[0] * $resizeRate);
                    $resizeHeight = round($imageSize[1] * $resizeRate);
                    $resizeImg = imagecreatetruecolor($resizeWidth, $resizeHeight);
                    imagecopyresampled($resizeImg, imagecreatefromstring($imageBinary), 0, 0, 0, 0, $resizeWidth, $resizeHeight, $imageSize[0], $imageSize[1]);
                    $extension = $image->getClientOriginalExtension();
                    $filename = date('mdHis').uniqid('_').'.'.$extension;
                    imagejpeg($resizeImg, $app['config']['image_temp_realdir'] . '/' . $filename);
                    $files[] = $filename;
                }
            }
        }

        $event = new EventArgs(
            array(
                'images' => $images,
                'files' => $files,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_PRODUCT_ADD_IMAGE_COMPLETE, $event);
        $files = $event->getArgument('files');

        return $app->json(array('files' => $files), 200);
    }

    /**
     * 顧客グループ情報を検索する.
     *
     * @param Application $app
     * @param Request $request
     * @param integer $page_no
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function searchCustomerGroupHtml(Application $app, Request $request, $page_no = null)
    {
        if ($request->isXmlHttpRequest()) {
            $app['monolog']->addDebug('search customer group start.');
            $page_count = $app['config']['default_page_count'];
            $session = $app['session'];

            if ('POST' === $request->getMethod()) {

                $page_no = 1;

                $searchData = array(
                    'multi' => $request->get('search_word'),
                );

                $session->set('eccube.admin.customer.search.group', $searchData);
                $session->set('eccube.admin.customer.search.group.page_no', $page_no);
            } else {
                $searchData = (array)$session->get('eccube.admin.customer.search.group');
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.customer.search.group.page_no'));
                } else {
                    $session->set('eccube.admin.customer.search.group.page_no', $page_no);
                }
            }

            $qb = $app['eccube.repository.customer_group']->getQueryBuilderBySearchData($searchData);
            $pagination = $app['paginator']()->paginate(
                $qb,
                $page_no,
                $page_count,
                array('wrap-queries' => true)
            );

            /** @var $Customers \Eccube\Entity\CustomerGroup[] */
            $CustomerGroups = $pagination->getItems();

            if (empty($CustomerGroups)) {
                $app['monolog']->addDebug('search customer group not found.');
            }

            $data = array();

            $formatName = '%s(%s)';
            foreach ($CustomerGroups as $CustomerGroup) {
                $data[] = array(
                    'id' => $CustomerGroup->getId(),
                    'name' => sprintf($formatName, $CustomerGroup->getName(), $CustomerGroup->getKana()),
                    'bill_to' => $CustomerGroup->getBillTo(),
                );
            }

            return $app->render('Customer/search_customer_group.twig', array(
                'data' => $data,
                'pagination' => $pagination,
            ));
        }
    }

    /**
     * 顧客グループ情報を検索する.
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function searchCustomerGroupById(Application $app, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $app['monolog']->addDebug('search customer group by id start.');

            /** @var $Customer \Eccube\Entity\CustomerGroup */
            $CustomerGroup = $app['eccube.repository.customer_group']
                ->find($request->get('id'));

            if (is_null($CustomerGroup)) {
                $app['monolog']->addDebug('search customer group by id not found.');

                return $app->json(array(), 404);
            }

            $app['monolog']->addDebug('search customer group by id found.');

            $data = array(
                'id' => $CustomerGroup->getId(),
                'name' => $CustomerGroup->getName(),
                'kana' => $CustomerGroup->getKana(),
                'bill_to' => $CustomerGroup->getBillTo(),
            );

            return $app->json($data);
        }
    }
}
