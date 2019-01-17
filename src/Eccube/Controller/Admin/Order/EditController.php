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

namespace Eccube\Controller\Admin\Order;

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Master\DeviceType;
use Eccube\Entity\OrderDetail;
use Eccube\Entity\ShipmentItem;
use Eccube\Entity\Shipping;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EditController extends AbstractController
{
    public function index(Application $app, Request $request, $id = null)
    {
        /* @var $softDeleteFilter \Eccube\Doctrine\Filter\SoftDeleteFilter */
        $softDeleteFilter = $app['orm.em']->getFilters()->getFilter('soft_delete');
        $softDeleteFilter->setExcludes(array(
            'Eccube\Entity\ProductClass',
            'Eccube\Entity\Product',
        ));

        $TargetOrder = null;
        $OriginOrder = null;

        if (is_null($id)) {
            // 空のエンティティを作成.
            $TargetOrder = $this->newOrder($app);
        } else {
            $TargetOrder = $app['eccube.repository.order']->find($id);
            if (is_null($TargetOrder)) {
                throw new NotFoundHttpException();
            }
        }

        // 編集前の受注情報を保持
        $OriginOrder = clone $TargetOrder;
        $OriginalOrderDetails = new ArrayCollection();
        // 編集前のお届け先情報を保持
        $OriginalShippings = new ArrayCollection();
        // 編集前のお届け先のアイテム情報を保持
        $OriginalShipmentItems = new ArrayCollection();

        // Save previous value before calculate
        $arrOldOrder = array();

        /** @var $OrderDetail OrderDetail*/
        foreach ($TargetOrder->getOrderDetails() as $OrderDetail) {
            $OriginalOrderDetails->add($OrderDetail);
            $arrOldOrder['OrderDetails'][$OrderDetail->getId()]['quantity'] = $OrderDetail->getQuantity();
        }

        // 編集前の情報を保持
        /** @var $tmpOriginalShippings Shipping*/
        foreach ($TargetOrder->getShippings() as $key => $tmpOriginalShippings) {
            $arrOldOrder['Shippings'][$key]['shipping_delivery_date'] = $tmpOriginalShippings->getShippingDeliveryDate();
            /** @var $tmpOriginalShipmentItem ShipmentItem*/
            foreach ($tmpOriginalShippings->getShipmentItems() as $tmpOriginalShipmentItem) {
                // アイテム情報
                $OriginalShipmentItems->add($tmpOriginalShipmentItem);
                $arrOldOrder['Shippings'][$key]['ShipmentItems'][$tmpOriginalShipmentItem->getId()]['quantity'] = $tmpOriginalShipmentItem->getQuantity();
            }
            // お届け先情報
            $OriginalShippings->add($tmpOriginalShippings);
        }

        $builder = $app['form.factory']
            ->createBuilder('order', $TargetOrder);

        $event = new EventArgs(
            array(
                'builder' => $builder,
                'OriginOrder' => $OriginOrder,
                'TargetOrder' => $TargetOrder,
                'OriginOrderDetails' => $OriginalOrderDetails,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_EDIT_INDEX_INITIALIZE, $event);

        $form = $builder->getForm();

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            $event = new EventArgs(
                array(
                    'builder' => $builder,
                    'OriginOrder' => $OriginOrder,
                    'TargetOrder' => $TargetOrder,
                    'OriginOrderDetails' => $OriginalOrderDetails,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_EDIT_INDEX_PROGRESS, $event);

            // 入力情報にもとづいて再計算.
            $this->calculate($app, $TargetOrder);

            // 登録ボタン押下
            switch ($request->get('mode')) {
                case 'register':
                    log_info('受注登録開始', array($TargetOrder->getId()));
                    $request_data = $request->request->all();
                    log_info('入力データ：', array(print_r($request_data, true)));

                    if ($TargetOrder->getTotal() > $app['config']['max_total_fee']) {
                        log_info('受注登録入力チェックエラー', array($TargetOrder->getId()));
                        $form['charge']->addError(new FormError('合計金額の上限を超えております。'));
                    } elseif ($form->isValid()) {

                        $BaseInfo = $app['eccube.repository.base_info']->get();

                        // お支払い方法の更新
                        $TargetOrder->setPaymentMethod($TargetOrder->getPayment()->getMethod());

                        // 配送業者・お届け時間の更新
                        $Shippings = $TargetOrder->getShippings();
                        foreach ($Shippings as $Shipping) {
                            $Shipping->setShippingDeliveryName($Shipping->getDelivery()->getName());
                            if (!is_null($Shipping->getDeliveryTime())) {
                                $Shipping->setShippingDeliveryTime($Shipping->getDeliveryTime()->getDeliveryTime());
                            } else {
                                $Shipping->setShippingDeliveryTime(null);
                            }
                        }


                        // 受注日/発送日/入金日の更新.
                        $this->updateDate($app, $TargetOrder, $OriginOrder);

                        // 受注明細で削除されているものをremove
                        foreach ($OriginalOrderDetails as $OrderDetail) {
                            if (false === $TargetOrder->getOrderDetails()->contains($OrderDetail)) {
                                $app['orm.em']->remove($OrderDetail);
                            }
                        }


                        if ($BaseInfo->getOptionMultipleShipping() == Constant::ENABLED) {
                            foreach ($TargetOrder->getOrderDetails() as $OrderDetail) {
                                /** @var $OrderDetail \Eccube\Entity\OrderDetail */
                                $OrderDetail->setOrder($TargetOrder);
                            }

                            /** @var \Eccube\Entity\Shipping $Shipping */
                            foreach ($Shippings as $Shipping) {
                                $shipmentItems = $Shipping->getShipmentItems();
                                /** @var \Eccube\Entity\ShipmentItem $ShipmentItem */
                                foreach ($shipmentItems as $ShipmentItem) {
                                    // 削除予定から商品アイテムを外す
                                    $OriginalShipmentItems->removeElement($ShipmentItem);
                                    $ShipmentItem->setOrder($TargetOrder);
                                    $ShipmentItem->setShipping($Shipping);
                                    $app['orm.em']->persist($ShipmentItem);
                                }
                                // 削除予定からお届け先情報を外す
                                $OriginalShippings->removeElement($Shipping);
                                $Shipping->setOrder($TargetOrder);
                                $app['orm.em']->persist($Shipping);
                            }
                            // 商品アイテムを削除する
                            foreach ($OriginalShipmentItems as $OriginalShipmentItem) {
                                $app['orm.em']->remove($OriginalShipmentItem);
                            }
                            // お届け先情報削除する
                            foreach ($OriginalShippings as $OriginalShipping) {
                                $app['orm.em']->remove($OriginalShipping);
                            }
                        } else {

                            $NewShipmentItems = new ArrayCollection();

                            foreach ($TargetOrder->getOrderDetails() as $OrderDetail) {
                                /** @var $OrderDetail \Eccube\Entity\OrderDetail */
                                $OrderDetail->setOrder($TargetOrder);

                                $NewShipmentItem = new ShipmentItem();
                                $NewShipmentItem
                                    ->setProduct($OrderDetail->getProduct())
                                    ->setProductClass($OrderDetail->getProductClass())
                                    ->setProductName($OrderDetail->getProduct()->getName())
                                    ->setProductCode($OrderDetail->getProductClass()->getCode())
                                    ->setClassCategoryName1($OrderDetail->getClassCategoryName1())
                                    ->setClassCategoryName2($OrderDetail->getClassCategoryName2())
                                    ->setClassName1($OrderDetail->getClassName1())
                                    ->setClassName2($OrderDetail->getClassName2())
                                    ->setPrice($OrderDetail->getPrice())
                                    ->setQuantity($OrderDetail->getQuantity())
                                    ->setOrder($TargetOrder);
                                $NewShipmentItems[] = $NewShipmentItem;

                            }
                            // 配送商品の更新. delete/insert.
                            $Shippings = $TargetOrder->getShippings();
                            foreach ($Shippings as $Shipping) {
                                $ShipmentItems = $Shipping->getShipmentItems();
                                foreach ($ShipmentItems as $ShipmentItem) {
                                    $app['orm.em']->remove($ShipmentItem);
                                }
                                $ShipmentItems->clear();
                                foreach ($NewShipmentItems as $NewShipmentItem) {
                                    $NewShipmentItem->setShipping($Shipping);
                                    $ShipmentItems->add($NewShipmentItem);
                                }
                            }
                        }

                        $Customer = $TargetOrder->getCustomer();
                        if ($Customer) {
                            // 受注情報の会員情報を更新
                            $TargetOrder->setSex($Customer->getSex());
                            $TargetOrder->setJob($Customer->getJob());
                            $TargetOrder->setBirth($Customer->getBirth());
                        }

                        $app['orm.em']->persist($TargetOrder);
                        $app['orm.em']->flush();

                        if ($Customer) {
                            // 会員の場合、購入回数、購入金額などを更新
                            $app['eccube.repository.customer']->updateBuyData($app, $Customer, $TargetOrder->getOrderStatus()->getId());
                        }

                        $event = new EventArgs(
                            array(
                                'form' => $form,
                                'OriginOrder' => $OriginOrder,
                                'TargetOrder' => $TargetOrder,
                                'OriginOrderDetails' => $OriginalOrderDetails,
                                'Customer' => $Customer,
                            ),
                            $request
                        );
                        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_EDIT_INDEX_COMPLETE, $event);

                        $app->addSuccess('admin.order.save.complete', 'admin');

                        log_info('受注登録完了', array($TargetOrder->getId()));

                        return $app->redirect($app->url('admin_order_edit', array('id' => $TargetOrder->getId())));
                    }

                    break;

                case 'add_delivery':
                    // お届け先情報の新規追加

                    $form = $builder->getForm();

                    $Shipping = new \Eccube\Entity\Shipping();
                    $Shipping->setDelFlg(Constant::DISABLED);

                    $TargetOrder->addShipping($Shipping);

                    $Shipping->setOrder($TargetOrder);

                    $form->setData($TargetOrder);

                    break;

                default:
                    break;
            }
        }

        // 会員検索フォーム
        $builder = $app['form.factory']
            ->createBuilder('admin_search_customer');

        $event = new EventArgs(
            array(
                'builder' => $builder,
                'OriginOrder' => $OriginOrder,
                'TargetOrder' => $TargetOrder,
                'OriginOrderDetails' => $OriginalOrderDetails,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_EDIT_SEARCH_CUSTOMER_INITIALIZE, $event);

        $searchCustomerModalForm = $builder->getForm();

        // 商品検索フォーム
        $builder = $app['form.factory']
            ->createBuilder('admin_search_product');

        $event = new EventArgs(
            array(
                'builder' => $builder,
                'OriginOrder' => $OriginOrder,
                'TargetOrder' => $TargetOrder,
                'OriginOrderDetails' => $OriginalOrderDetails,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_EDIT_SEARCH_PRODUCT_INITIALIZE, $event);

        $searchProductModalForm = $builder->getForm();

        // 配送業者のお届け時間
        $times = array();
        $deliveries = $app['eccube.repository.delivery']->findAll();
        foreach ($deliveries as $Delivery) {
            $deliveryTiems = $Delivery->getDeliveryTimes();
            foreach ($deliveryTiems as $DeliveryTime) {
                $times[$Delivery->getId()][$DeliveryTime->getId()] = $DeliveryTime->getDeliveryTime();
            }
        }

        return $app->render('Order/edit.twig', array(
            'form' => $form->createView(),
            'searchCustomerModalForm' => $searchCustomerModalForm->createView(),
            'searchProductModalForm' => $searchProductModalForm->createView(),
            'Order' => $TargetOrder,
            'id' => $id,
            'shippingDeliveryTimes' => $app['serializer']->serialize($times, 'json'),
            'arrOldOrder' => $arrOldOrder,
        ));
    }

    public function groupEdit(Application $app, Request $request, $id = null)
    {
        /* @var $softDeleteFilter \Eccube\Doctrine\Filter\SoftDeleteFilter */
        $softDeleteFilter = $app['orm.em']->getFilters()->getFilter('soft_delete');
        $softDeleteFilter->setExcludes(array(
            'Eccube\Entity\ProductClass',
            'Eccube\Entity\Product',
        ));

        $TargetOrder = null;
        $OriginOrder = null;
        $TargetCustomerIds = array();
        $TargetCustomersGroup = null;
        $TargetCustomers = null;

        if (is_null($id)) {
            // 空のエンティティを作成.
            $TargetGroupOrder = new \Eccube\Entity\GroupOrder();
            $TargetOrder = $this->newOrder($app);
        } else {
            $TargetOrder = $app['eccube.repository.order']->find($id);
            if (is_null($TargetOrder)) {
                throw new NotFoundHttpException();
            }
        }

        // 編集前の受注情報を保持
        $OriginOrder = clone $TargetOrder;
        $OriginalOrderDetails = new ArrayCollection();
        // 編集前のお届け先情報を保持
        $OriginalShippings = new ArrayCollection();
        // 編集前のお届け先のアイテム情報を保持
        $OriginalShipmentItems = new ArrayCollection();

        // Save previous value before calculate
        $arrOldOrder = array();

        /** @var $OrderDetail OrderDetail*/
        foreach ($TargetOrder->getOrderDetails() as $OrderDetail) {
            $OriginalOrderDetails->add($OrderDetail);
            $arrOldOrder['OrderDetails'][$OrderDetail->getId()]['quantity'] = $OrderDetail->getQuantity();
        }

        // 編集前の情報を保持
        /** @var $tmpOriginalShippings Shipping*/
        foreach ($TargetOrder->getShippings() as $key => $tmpOriginalShippings) {
            $arrOldOrder['Shippings'][$key]['shipping_delivery_date'] = $tmpOriginalShippings->getShippingDeliveryDate();
            /** @var $tmpOriginalShipmentItem ShipmentItem*/
            foreach ($tmpOriginalShippings->getShipmentItems() as $tmpOriginalShipmentItem) {
                // アイテム情報
                $OriginalShipmentItems->add($tmpOriginalShipmentItem);
                $arrOldOrder['Shippings'][$key]['ShipmentItems'][$tmpOriginalShipmentItem->getId()]['quantity'] = $tmpOriginalShipmentItem->getQuantity();
            }
            // お届け先情報
            $OriginalShippings->add($tmpOriginalShippings);
        }

        $builder = $app['form.factory']
            ->createBuilder('order', $TargetOrder);

        $event = new EventArgs(
            array(
                'builder' => $builder,
                'OriginOrder' => $OriginOrder,
                'TargetOrder' => $TargetOrder,
                'OriginOrderDetails' => $OriginalOrderDetails,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_EDIT_INDEX_INITIALIZE, $event);

        $form = $builder->getForm();

        if ('POST' === $request->getMethod()) {
            $request_data = $request->request->all();
            if (isset($request_data['order']['CustomerGroupId'])) {
                $TargetCustomersGroup = $app['eccube.repository.customer_group']->find($request_data['order']['CustomerGroupId']);
                $TargetCustomers = $app['eccube.repository.customer']->getQueryBuilderBySearchGroupId($request_data['order']['CustomerGroupId'])->getQuery()->getResult();
            }
            $request_data = $request->request->all();
            $customers = (isset($request_data['order']['CustomerIds'])?$request_data['order']['CustomerIds']:array());
            foreach ($customers as $customer) {
                $TargetCustomerIds[] = $customer;
            }
            // バリデーションパスのため、以下をダミー値とする
            $request_data['order']['name']['name01'] = 'ダミー';
            $request_data['order']['name']['name02'] = 'ダミー';
            $request_data['order']['kana']['kana01'] = 'ダミー';
            $request_data['order']['kana']['kana02'] = 'ダミー';
            $request_data['order']['email'] = 'abc@abc.com';
            $request_data['order']['zip']['zip01'] = '000';
            $request_data['order']['zip']['zip02'] = '0000';
            $request_data['order']['address']['pref'] = 1;
            $request_data['order']['address']['addr01'] = 'ダミー';
            $request_data['order']['address']['addr02'] = 'ダミー';
            $request_data['order']['tel']['tel01'] = '1234';
            $request_data['order']['tel']['tel02'] = '1234';
            $request_data['order']['tel']['tel03'] = '1234';
            $request_data['order']['fax']['fax01'] = '1234';
            $request_data['order']['fax']['fax02'] = '1234';
            $request_data['order']['fax']['fax03'] = '1234';
            $request_data['order']['Shippings'][0]['name']['name01'] = 'ダミー';
            $request_data['order']['Shippings'][0]['name']['name02'] = 'ダミー';
            $request_data['order']['Shippings'][0]['kana']['kana01'] = 'ダミ';
            $request_data['order']['Shippings'][0]['kana']['kana02'] = 'ダミ';
            $request_data['Shippings'][0]['name']['name01'] = 'ダミー';
            $request_data['Shippings'][0]['name']['name02'] = 'ダミー';
            $request_data['Shippings'][0]['kana']['kana01'] = 'ダミ';
            $request_data['Shippings'][0]['kana']['kana02'] = 'ダミ';
            $request->request->replace($request_data);
            $form->handleRequest($request);

            $event = new EventArgs(
                array(
                    'builder' => $builder,
                    'OriginOrder' => $OriginOrder,
                    'TargetOrder' => $TargetOrder,
                    'OriginOrderDetails' => $OriginalOrderDetails,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_EDIT_INDEX_PROGRESS, $event);

            // 入力情報にもとづいて再計算.
            $this->calculate($app, $TargetOrder);

            // 登録ボタン押下
            switch ($request->get('mode')) {
                case 'register':
                    log_info('受注登録開始', array($TargetOrder->getId()));
                    if ($TargetOrder->getTotal() > $app['config']['max_total_fee']) {
                        $form['charge']->addError(new FormError('合計金額の上限を超えております。'));
                    } elseif (count($customers) < 1) {
                        $app->addError('対象の会員が選択されていません', 'admin');
                    } elseif ($form->isValid()) {
                        // 会員グループ情報設定
                        $TargetGroupOrder->setCustomerGroup($TargetCustomersGroup);
                        $TargetGroupOrder->setName($TargetCustomersGroup->getName());
                        $TargetGroupOrder->setKana($TargetCustomersGroup->getKana());
                        $TargetGroupOrder->setSendToPref($TargetCustomersGroup->getSendToPref());
                        $TargetGroupOrder->setSendToZip01($TargetCustomersGroup->getSendToZip01());
                        $TargetGroupOrder->setSendToZip02($TargetCustomersGroup->getSendToZip02());
                        $TargetGroupOrder->setSendToZipcode($TargetCustomersGroup->getSendToZipcode());
                        $TargetGroupOrder->setSendToAddr01($TargetCustomersGroup->getSendToAddr01());
                        $TargetGroupOrder->setSendToAddr02($TargetCustomersGroup->getSendToAddr02());
                        $TargetGroupOrder->setSendToEmail($TargetCustomersGroup->getSendToEmail());
                        $TargetGroupOrder->setSendToTel01($TargetCustomersGroup->getSendToTel01());
                        $TargetGroupOrder->setSendToTel02($TargetCustomersGroup->getSendToTel02());
                        $TargetGroupOrder->setSendToTel03($TargetCustomersGroup->getSendToTel03());
                        $TargetGroupOrder->setSendToFax01($TargetCustomersGroup->getSendToFax01());
                        $TargetGroupOrder->setSendToFax02($TargetCustomersGroup->getSendToFax02());
                        $TargetGroupOrder->setSendToFax03($TargetCustomersGroup->getSendToFax03());
                        $TargetGroupOrder->setBillTo($TargetCustomersGroup->getBillTo());
                        $TargetGroupOrder->setBillToPref($TargetCustomersGroup->getBillToPref());
                        $TargetGroupOrder->setBillToZip01($TargetCustomersGroup->getBillToZip01());
                        $TargetGroupOrder->setBillToZip02($TargetCustomersGroup->getBillToZip02());
                        $TargetGroupOrder->setBillToZipcode($TargetCustomersGroup->getBillToZipcode());
                        $TargetGroupOrder->setBillToAddr01($TargetCustomersGroup->getBillToAddr01());
                        $TargetGroupOrder->setBillToAddr02($TargetCustomersGroup->getBillToAddr02());
                        $TargetGroupOrder->setBillToEmail($TargetCustomersGroup->getBillToEmail());
                        $TargetGroupOrder->setBillToTel01($TargetCustomersGroup->getBillToTel01());
                        $TargetGroupOrder->setBillToTel02($TargetCustomersGroup->getBillToTel02());
                        $TargetGroupOrder->setBillToTel03($TargetCustomersGroup->getBillToTel03());
                        $TargetGroupOrder->setBillToFax01($TargetCustomersGroup->getBillToFax01());
                        $TargetGroupOrder->setBillToFax02($TargetCustomersGroup->getBillToFax02());
                        $TargetGroupOrder->setBillToFax03($TargetCustomersGroup->getBillToFax03());
                        $TargetGroupOrder->setOrderDate(new \DateTime());
                        $app['orm.em']->persist($TargetGroupOrder);
                        $BaseInfo = $app['eccube.repository.base_info']->get();

                        foreach ($customers as $customer) {
                            $targetCustomer = $app['eccube.repository.customer']->find($customer);
                            if ($targetCustomer) {
                                // 空のエンティティを作成.
                                $registOrder = $this->newOrder($app);
                                // 受注情報をコピー
                                $registOrder->setCustomer($targetCustomer)
                                            ->setCustomerGroup($TargetCustomersGroup)
                                            ->setGroupOrder($TargetGroupOrder)
                                            ->setDiscount($TargetOrder->getDiscount())
                                            ->setSubtotal($TargetOrder->getSubtotal())
                                            ->setTotal($TargetOrder->getTotal())
                                            ->setPaymentTotal($TargetOrder->getPaymentTotal())
                                            ->setCharge($TargetOrder->getCharge())
                                            ->setTax($TargetOrder->getTax())
                                            ->setDeliveryFeeTotal($TargetOrder->getDeliveryFeeTotal())
                                            ->setOrderStatus($TargetOrder->getOrderStatus())
                                            ->setDelFlg(Constant::DISABLED)
                                            ->setName01($targetCustomer->getName01())
                                            ->setName02($targetCustomer->getName02())
                                            ->setKana01($targetCustomer->getKana01())
                                            ->setKana02($targetCustomer->getKana02())
                                            ->setPref($targetCustomer->getPref())
                                            ->setZip01($targetCustomer->getZip01())
                                            ->setZip02($targetCustomer->getZip02())
                                            ->setAddr01($targetCustomer->getAddr01())
                                            ->setAddr02($targetCustomer->getAddr02())
                                            ->setEmail($targetCustomer->getEmail())
                                            ->setTel01($targetCustomer->getTel01())
                                            ->setTel02($targetCustomer->getTel02())
                                            ->setTel03($targetCustomer->getTel03())
                                            ->setFax01($targetCustomer->getFax01())
                                            ->setFax02($targetCustomer->getFax02())
                                            ->setFax03($targetCustomer->getFax03())
                                            ->setSex($targetCustomer->getSex())
                                            ->setJob($targetCustomer->getJob())
                                            ->setBirth($targetCustomer->getBirth())
                                            ->setPayment($TargetOrder->getPayment())
                                            ->setPaymentMethod($TargetOrder->getPayment()->getMethod());

                                // 受注明細をコピー
                                foreach ($TargetOrder->getOrderDetails() as $OrderDetail) {
                                    $copyOrderDetail = new \Eccube\Entity\OrderDetail();
                                    $copyOrderDetail->setPriceIncTax($OrderDetail->getPriceIncTax());
                                    $copyOrderDetail->setProductName($OrderDetail->getProductName());
                                    $copyOrderDetail->setProductCode($OrderDetail->getProductCode());
                                    $copyOrderDetail->setClassCategoryName1($OrderDetail->getClassCategoryName1());
                                    $copyOrderDetail->setClassCategoryName2($OrderDetail->getClassCategoryName2());
                                    $copyOrderDetail->setPrice($OrderDetail->getPrice());
                                    $copyOrderDetail->setQuantity($OrderDetail->GetQuantity());
                                    $copyOrderDetail->setTaxRate($OrderDetail->getTaxRate());
                                    $copyOrderDetail->setTaxRule($OrderDetail->getTaxRule());
                                    $copyOrderDetail->setProduct($OrderDetail->getProduct());
                                    $copyOrderDetail->setProductClass($OrderDetail->getProductClass());
                                    $copyOrderDetail->setClassName1($OrderDetail->getClassName1());
                                    $copyOrderDetail->setClassName2($OrderDetail->getClassName2());
                                    $registOrder->addOrderDetail($copyOrderDetail);
                                }

                                // 会員の場合、購入回数、購入金額などを更新
                                $app['eccube.repository.customer']->updateBuyData($app, $targetCustomer, $registOrder->getOrderStatus()->getId());

                                // 配送業者・お届け時間の更新
                                $Shippings = $registOrder->getShippings();
                                foreach ($Shippings as $Shipping) {
                                    $Shipping->setName01($targetCustomer->getName01());
                                    $Shipping->setName02($targetCustomer->getName02());
                                    $Shipping->setKana01($targetCustomer->getKana01());
                                    $Shipping->setKana02($targetCustomer->getKana02());
                                    if (!is_null($Shipping->getDelivery())) {
                                        $Shipping->setShippingDeliveryName($Shipping->getDelivery()->getName());
                                    }
                                    if (!is_null($Shipping->getDeliveryTime())) {
                                        $Shipping->setShippingDeliveryTime($Shipping->getDeliveryTime()->getDeliveryTime());
                                    } else {
                                        $Shipping->setShippingDeliveryTime(null);
                                    }
                                }

                                // 受注日/発送日/入金日の更新.
                                $this->updateDate($app, $registOrder, $OriginOrder);

                                // 受注明細で削除されているものをremove
                                foreach ($OriginalOrderDetails as $OrderDetail) {
                                    if (false === $registOrder->getOrderDetails()->contains($OrderDetail)) {
                                        $app['orm.em']->remove($OrderDetail);
                                    }
                                }
                                $NewShipmentItems = new ArrayCollection();

                                foreach ($registOrder->getOrderDetails() as $OrderDetail) {
                                    /** @var $OrderDetail \Eccube\Entity\OrderDetail */
                                    $OrderDetail->setOrder($registOrder);

                                    $NewShipmentItem = new ShipmentItem();
                                    $NewShipmentItem
                                        ->setProduct($OrderDetail->getProduct())
                                        ->setProductClass($OrderDetail->getProductClass())
                                        ->setProductName($OrderDetail->getProduct()->getName())
                                        ->setProductCode($OrderDetail->getProductClass()->getCode())
                                        ->setClassCategoryName1($OrderDetail->getClassCategoryName1())
                                        ->setClassCategoryName2($OrderDetail->getClassCategoryName2())
                                        ->setClassName1($OrderDetail->getClassName1())
                                        ->setClassName2($OrderDetail->getClassName2())
                                        ->setPrice($OrderDetail->getPrice())
                                        ->setQuantity($OrderDetail->getQuantity())
                                        ->setOrder($registOrder);
                                    $NewShipmentItems[] = $NewShipmentItem;

                                }
                                // 配送商品の更新. delete/insert.
                                $Shippings = $registOrder->getShippings();
                                foreach ($Shippings as $Shipping) {
                                    $ShipmentItems = $Shipping->getShipmentItems();
                                    foreach ($ShipmentItems as $ShipmentItem) {
                                        $app['orm.em']->remove($ShipmentItem);
                                    }
                                    $ShipmentItems->clear();
                                    foreach ($NewShipmentItems as $NewShipmentItem) {
                                        $NewShipmentItem->setShipping($Shipping);
                                        $ShipmentItems->add($NewShipmentItem);
                                    }
                                }
                                $app['orm.em']->persist($registOrder);
                            }
                        }
                        $app['orm.em']->flush();
                        $app->addSuccess('admin.order.save.complete', 'admin');
                        log_info('受注登録完了', array($registOrder->getId()));
                        return $app->redirect($app->url('admin_group_order', array()));
                    } else {
                        log_info('isNotValid', array($TargetOrder->getId()));
                        foreach ($form->getErrors(true) as $Error) { 
                            log_info('error:', array($Error->getOrigin()->getName(), $Error->getMessage()));
                        }
                    }

                    break;

                case 'add_delivery':
                    // お届け先情報の新規追加

                    $form = $builder->getForm();

                    $Shipping = new \Eccube\Entity\Shipping();
                    $Shipping->setDelFlg(Constant::DISABLED);

                    $TargetOrder->addShipping($Shipping);

                    $Shipping->setOrder($TargetOrder);

                    $form->setData($TargetOrder);

                    break;

                default:
                    break;
            }
        }

        // 会員グループ検索フォーム
        $builder = $app['form.factory']
            ->createBuilder('admin_search_customer_group');
        $searchCustomerGroupModalForm = $builder->getForm();

        // 商品検索フォーム
        $builder = $app['form.factory']
            ->createBuilder('admin_search_product');

        $event = new EventArgs(
            array(
                'builder' => $builder,
                'OriginOrder' => $OriginOrder,
                'TargetOrder' => $TargetOrder,
                'OriginOrderDetails' => $OriginalOrderDetails,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_EDIT_SEARCH_PRODUCT_INITIALIZE, $event);

        $searchProductModalForm = $builder->getForm();

        // 配送業者のお届け時間
        $times = array();
        $deliveries = $app['eccube.repository.delivery']->findAll();
        foreach ($deliveries as $Delivery) {
            $deliveryTiems = $Delivery->getDeliveryTimes();
            foreach ($deliveryTiems as $DeliveryTime) {
                $times[$Delivery->getId()][$DeliveryTime->getId()] = $DeliveryTime->getDeliveryTime();
            }
        }

        return $app->render('Order/group_edit.twig', array(
            'form' => $form->createView(),
            'searchCustomerGroupModalForm' => $searchCustomerGroupModalForm->createView(),
            'searchProductModalForm' => $searchProductModalForm->createView(),
            'Order' => $TargetOrder,
            'CustomerGroup' => $TargetCustomersGroup,
            'Customers' => $TargetCustomers,
            'TargetCustomers' => $TargetCustomerIds,
            'id' => $id,
            'shippingDeliveryTimes' => $app['serializer']->serialize($times, 'json'),
            'arrOldOrder' => $arrOldOrder,
        ));
    }

    public function registMembership(Application $app, Request $request, $id = null)
    {
        $builder = $app['form.factory']
            ->createBuilder('admin_regist_membership');
        $form = $builder->getForm();
        // タイムアウトを無効にする.
        set_time_limit(0);

        if ('POST' === $request->getMethod()) {
            $membershipProducts = $app['eccube.repository.product']
                        ->getProductQueryBuilderByMembershipId($request->request->get('admin_regist_membership')['MembershipYear'])
                        ->getQuery()->getResult();
            if ($membershipProducts) {
                $membershipProduct = $membershipProducts[0];
                $processingMembershipBilling = $app['eccube.repository.membership_billing']->getProcessing($membershipProduct->getProductMembership()->getId());
                $taretCustomers = $app['eccube.repository.customer']
                        ->getCustomerByExclusionOrderProduct($membershipProduct->getId());
                if (1 < count($processingMembershipBilling)) {
                    $app->addError('登録済みの対象年度の年会費登録が完了していません', 'admin');
                } else if (count($taretCustomers) < 1) {
                    $app->addError('対象の会員が存在しません', 'admin');
                } else {
                    log_info('受注実行登録開始', array(count($taretCustomers)));
                    $membershipBillingStatus =  $app['eccube.repository.master.membership_billing_status']->find(1);
                    $membershipBilling = new \Eccube\Entity\MembershipBilling();
                    $membershipBilling->setStatus($membershipBillingStatus);
                    $membershipBilling->setProductMembership($membershipProduct->getProductMembership());
                    $app['orm.em']->persist($membershipBilling);
                    $app['orm.em']->flush();
                    $app['eccube.repository.membership_billing_detail']->insertAllTarget($membershipBilling->getId());
                    log_info('受注実行登録完了', array(count($taretCustomers)));

                    $domainSvPath = '';
                    $paths = explode('/', getcwd());
                    array_shift($paths);
                    array_pop($paths);
                    foreach($paths as $path) {
                        $domainSvPath .= '/' . $path;
                    }
                    $cmd = $domainSvPath . '/app/console membershipbilling:billing ' . $membershipBilling->getId() . ' > /dev/null &';
                    log_info('コマンド：' . $cmd, array());

                    exec($cmd);
                    $app->addSuccess('年会費登録の実行を開始しました', 'admin');
                    return $app->redirect($app->url('admin_membership_order_page', array('page_no' => 1, 'resume' => 1)));
                }
            } else {
                $app->addError('年会費商品情報の取得に失敗しました', 'admin');
            }
        }

        return $app->render('Order/regist_membership.twig', array(
            'form' => $form->createView(),
        ));
    }
    /**
     * 顧客情報を検索する.
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function searchCustomer(Application $app, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $app['monolog']->addDebug('search customer start.');

            $searchData = array(
                'multi' => $request->get('search_word'),
            );

            $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchData($searchData);

            $event = new EventArgs(
                array(
                    'qb' => $qb,
                    'data' => $searchData,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_EDIT_SEARCH_CUSTOMER_SEARCH, $event);

            $Customers = $qb->getQuery()->getResult();


            if (empty($Customers)) {
                $app['monolog']->addDebug('search customer not found.');
            }

            $data = array();

            $formatTel = '%s-%s-%s';
            $formatName = '%s%s(%s%s)';
            foreach ($Customers as $Customer) {
                $data[] = array(
                    'id' => $Customer->getId(),
                    'name' => sprintf($formatName, $Customer->getName01(), $Customer->getName02(), $Customer->getKana01(),
                        $Customer->getKana02()),
                    'tel' => sprintf($formatTel, $Customer->getTel01(), $Customer->getTel02(), $Customer->getTel03()),
                    'email' => $Customer->getEmail(),
                );
            }

            $event = new EventArgs(
                array(
                    'data' => $data,
                    'Customers' => $Customers,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_EDIT_SEARCH_CUSTOMER_COMPLETE, $event);
            $data = $event->getArgument('data');

            return $app->json($data);
        }
    }

    /**
     * 顧客情報を検索する.
     *
     * @param Application $app
     * @param Request $request
     * @param integer $page_no
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function searchCustomerHtml(Application $app, Request $request, $page_no = null)
    {
        if ($request->isXmlHttpRequest()) {
            $app['monolog']->addDebug('search customer start.');
            $page_count = $app['config']['default_page_count'];
            $session = $app['session'];

            if ('POST' === $request->getMethod()) {

                $page_no = 1;

                $searchData = array(
                    'multi' => $request->get('search_word'),
                );

                $session->set('eccube.admin.order.customer.search', $searchData);
                $session->set('eccube.admin.order.customer.search.page_no', $page_no);
            } else {
                $searchData = (array)$session->get('eccube.admin.order.customer.search');
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.order.customer.search.page_no'));
                } else {
                    $session->set('eccube.admin.order.customer.search.page_no', $page_no);
                }
            }

            $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchData($searchData);

            $event = new EventArgs(
                array(
                    'qb' => $qb,
                    'data' => $searchData,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_EDIT_SEARCH_CUSTOMER_SEARCH, $event);

            /** @var \Knp\Component\Pager\Pagination\SlidingPagination $pagination */
            $pagination = $app['paginator']()->paginate(
                $qb,
                $page_no,
                $page_count,
                array('wrap-queries' => true)
            );

            /** @var $Customers \Eccube\Entity\Customer[] */
            $Customers = $pagination->getItems();

            if (empty($Customers)) {
                $app['monolog']->addDebug('search customer not found.');
            }

            $data = array();

            $formatTel = '%s-%s-%s';
            $formatName = '%s%s(%s%s)';
            foreach ($Customers as $Customer) {
                $data[] = array(
                    'id' => $Customer->getId(),
                    'name' => sprintf($formatName, $Customer->getName01(), $Customer->getName02(), $Customer->getKana01(),
                        $Customer->getKana02()),
                    'tel' => sprintf($formatTel, $Customer->getTel01(), $Customer->getTel02(), $Customer->getTel03()),
                    'email' => $Customer->getEmail(),
                );
            }

            $event = new EventArgs(
                array(
                    'data' => $data,
                    'Customers' => $pagination,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_EDIT_SEARCH_CUSTOMER_COMPLETE, $event);
            $data = $event->getArgument('data');

            return $app->render('Order/search_customer.twig', array(
                'data' => $data,
                'pagination' => $pagination,
            ));
        }
    }

    /**
     * 顧客情報を検索する.
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function searchCustomerById(Application $app, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $app['monolog']->addDebug('search customer by id start.');

            /** @var $Customer \Eccube\Entity\Customer */
            $Customer = $app['eccube.repository.customer']
                ->find($request->get('id'));

            $event = new EventArgs(
                array(
                    'Customer' => $Customer,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_EDIT_SEARCH_CUSTOMER_BY_ID_INITIALIZE, $event);

            if (is_null($Customer)) {
                $app['monolog']->addDebug('search customer by id not found.');

                return $app->json(array(), 404);
            }

            $app['monolog']->addDebug('search customer by id found.');

            $data = array(
                'id' => $Customer->getId(),
                'name01' => $Customer->getName01(),
                'name02' => $Customer->getName02(),
                'kana01' => $Customer->getKana01(),
                'kana02' => $Customer->getKana02(),
                'zip01' => $Customer->getZip01(),
                'zip02' => $Customer->getZip02(),
                'pref' => is_null($Customer->getPref()) ? null : $Customer->getPref()->getId(),
                'addr01' => $Customer->getAddr01(),
                'addr02' => $Customer->getAddr02(),
                'email' => $Customer->getEmail(),
                'tel01' => $Customer->getTel01(),
                'tel02' => $Customer->getTel02(),
                'tel03' => $Customer->getTel03(),
                'fax01' => $Customer->getFax01(),
                'fax02' => $Customer->getFax02(),
                'fax03' => $Customer->getFax03(),
                'company_name' => $Customer->getCompanyName(),
            );

            $event = new EventArgs(
                array(
                    'data' => $data,
                    'Customer' => $Customer,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_EDIT_SEARCH_CUSTOMER_BY_ID_COMPLETE, $event);
            $data = $event->getArgument('data');

            return $app->json($data);
        }
    }


    /**
     * 顧客グループ情報を検索する.
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function searchCustomerGroupByGroupId(Application $app, Request $request)
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

            $app['monolog']->addDebug('search customer by id found.');

            $data = array(
                'id' => $CustomerGroup->getId(),
                'name' => $CustomerGroup->getName(),
                'kana' => $CustomerGroup->getKana(),
                'send_to_zip01' => $CustomerGroup->getSendToZip01(),
                'send_to_zip02' => $CustomerGroup->getSendToZip02(),
                'send_to_addr01' => $CustomerGroup->getSendToAddr01(),
                'send_to_addr02' => $CustomerGroup->getSendToAddr02(),
                'send_to_pref' => is_null($CustomerGroup->getSendToPref()) ? null : $CustomerGroup->getSendToPref()->getId(),
                'send_to_tel01' => $CustomerGroup->getSendToTel01(),
                'send_to_tel02' => $CustomerGroup->getSendToTel02(),
                'send_to_tel03' => $CustomerGroup->getSendToTel03(),
                'send_to_fax01' => $CustomerGroup->getSendToFax01(),
                'send_to_fax02' => $CustomerGroup->getSendToFax02(),
                'send_to_fax03' => $CustomerGroup->getSendToFax03(),
                'bill_to' => $CustomerGroup->getBillTo(),
                'bill_to_zip01' => $CustomerGroup->getBillToZip01(),
                'bill_to_zip02' => $CustomerGroup->getBillToZip02(),
                'bill_to_addr01' => $CustomerGroup->getBillToAddr01(),
                'bill_to_addr02' => $CustomerGroup->getBillToAddr02(),
                'bill_to_pref' => is_null($CustomerGroup->getBillToPref()) ? null : $CustomerGroup->getBillToPref()->getId(),
                'bill_to_tel01' => $CustomerGroup->getBillToTel01(),
                'bill_to_tel02' => $CustomerGroup->getBillToTel02(),
                'bill_to_tel03' => $CustomerGroup->getBillToTel03(),
                'bill_to_fax01' => $CustomerGroup->getBillToFax01(),
                'bill_to_fax02' => $CustomerGroup->getBillToFax02(),
                'bill_to_fax03' => $CustomerGroup->getBillToFax03(),
            );
            log_info('searchCustomerGroupByGroupId', array(print_r($data, true)));
            return $app->json($data);
        }
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

            return $app->render('Order/search_customer_group.twig', array(
                'data' => $data,
                'pagination' => $pagination,
            ));
        }
    }

    /**
     * 顧客グループ情報から顧客情報を検索する.
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function searchCustomerByGroupId(Application $app, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $app['monolog']->addDebug('search customer by id start.');

            /** @var $Customer \Eccube\Entity\Customer */
            $Customers = $app['eccube.repository.customer']
                ->getQueryBuilderBySearchGroupId($request->get('id'))
                ->getQuery()
                ->getResult();

            if (is_null($Customers)) {
                $app['monolog']->addDebug('search customer by id not found.');

                return $app->json(array(), 404);
            }

            $app['monolog']->addDebug('search customer by id found.');

            $data = array();
            foreach($Customers as $Customer) {
                $data[] = array(
                    'id' => $Customer->getId(),
                    'name01' => $Customer->getName01(),
                    'name02' => $Customer->getName02(),
                    'kana01' => $Customer->getKana01(),
                    'kana02' => $Customer->getKana02(),
                    'zip01' => $Customer->getZip01(),
                    'zip02' => $Customer->getZip02(),
                    'pref' => is_null($Customer->getPref()) ? null : $Customer->getPref()->getId(),
                    'addr01' => $Customer->getAddr01(),
                    'addr02' => $Customer->getAddr02(),
                    'email' => $Customer->getEmail(),
                    'tel01' => $Customer->getTel01(),
                    'tel02' => $Customer->getTel02(),
                    'tel03' => $Customer->getTel03(),
                    'fax01' => $Customer->getFax01(),
                    'fax02' => $Customer->getFax02(),
                    'fax03' => $Customer->getFax03(),
                    'company_name' => $Customer->getCompanyName(),
                );
            }
            return $app->json($data);
        }
    }

    public function searchProduct(Application $app, Request $request, $page_no = null)
    {
        if ($request->isXmlHttpRequest()) {
            $app['monolog']->addDebug('search product start.');
            $page_count = $app['config']['default_page_count'];
            $session = $app['session'];

            if ('POST' === $request->getMethod()) {

                $page_no = 1;

                $searchData = array(
                    'id' => $request->get('id'),
                );

                if ($categoryId = $request->get('category_id')) {
                    $Category = $app['eccube.repository.category']->find($categoryId);
                    $searchData['category_id'] = $Category;
                }

                $session->set('eccube.admin.order.product.search', $searchData);
                $session->set('eccube.admin.order.product.search.page_no', $page_no);
            } else {
                $searchData = (array)$session->get('eccube.admin.order.product.search');
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.order.product.search.page_no'));
                } else {
                    $session->set('eccube.admin.order.product.search.page_no', $page_no);
                }
            }

            $qb = $app['eccube.repository.product']
                ->getQueryBuilderBySearchDataForAdmin($searchData);

            $event = new EventArgs(
                array(
                    'qb' => $qb,
                    'searchData' => $searchData,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_EDIT_SEARCH_PRODUCT_SEARCH, $event);

            /** @var \Knp\Component\Pager\Pagination\SlidingPagination $pagination */
            $pagination = $app['paginator']()->paginate(
                $qb,
                $page_no,
                $page_count,
                array('wrap-queries' => true)
            );

            /** @var $Products \Eccube\Entity\Product[] */
            $Products = $pagination->getItems();

            if (empty($Products)) {
                $app['monolog']->addDebug('search product not found.');
            }

            $forms = array();
            foreach ($Products as $Product) {
                /* @var $builder \Symfony\Component\Form\FormBuilderInterface */
                $builder = $app['form.factory']->createNamedBuilder('', 'add_cart', null, array(
                    'product' => $Product,
                ));
                $addCartForm = $builder->getForm();
                $forms[$Product->getId()] = $addCartForm->createView();
            }

            $event = new EventArgs(
                array(
                    'forms' => $forms,
                    'Products' => $Products,
                    'pagination' => $pagination,
                ),
                $request
            );
            $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_EDIT_SEARCH_PRODUCT_COMPLETE, $event);

            return $app->render('Order/search_product.twig', array(
                'forms' => $forms,
                'Products' => $Products,
                'pagination' => $pagination,
            ));
        }
    }

    protected function newOrder(Application $app)
    {
        $Order = new \Eccube\Entity\Order();
        $Shipping = new \Eccube\Entity\Shipping();
        $Shipping->setDelFlg(0);
        $Order->addShipping($Shipping);
        $Shipping->setOrder($Order);

        // device type
        $DeviceType = $app['eccube.repository.master.device_type']->find(DeviceType::DEVICE_TYPE_ADMIN);
        $Order->setDeviceType($DeviceType);

        return $Order;
    }

    /**
     * フォームからの入直内容に基づいて、受注情報の再計算を行う
     *
     * @param $app
     * @param $Order
     */
    protected function calculate($app, \Eccube\Entity\Order $Order)
    {
        $taxtotal = 0;
        $subtotal = 0;

        // 受注明細データの税・小計を再計算
        /** @var $OrderDetails \Eccube\Entity\OrderDetail[] */
        $OrderDetails = $Order->getOrderDetails();
        foreach ($OrderDetails as $OrderDetail) {
            // 税
            $tax = $app['eccube.service.tax_rule']
                ->calcTax($OrderDetail->getPrice(), $OrderDetail->getTaxRate(), $OrderDetail->getTaxRule());
            $OrderDetail->setPriceIncTax($OrderDetail->getPrice() + $tax);

            $taxtotal += $tax * $OrderDetail->getQuantity();

            // 小計
            $subtotal += $OrderDetail->getTotalPrice();
        }

        $shippings = $Order->getShippings();
        /** @var \Eccube\Entity\Shipping $Shipping */
        foreach ($shippings as $Shipping) {
            $Shipping->setDelFlg(Constant::DISABLED);
        }

        // 受注データの税・小計・合計を再計算
        $Order->setTax($taxtotal);
        $Order->setSubtotal($subtotal);
        $Order->setTotal($subtotal + $Order->getCharge() + $Order->getDeliveryFeeTotal() - $Order->getDiscount());
        // お支払い合計は、totalと同一金額(2系ではtotal - point)
        $Order->setPaymentTotal($Order->getTotal());
    }

    /**
     * 受注ステータスに応じて, 受注日/入金日/発送日を更新する,
     * 発送済ステータスが設定された場合は, お届け先情報の発送日も更新を行う.
     *
     * 編集の場合
     * - 受注ステータスが他のステータスから発送済へ変更された場合に発送日を更新
     * - 受注ステータスが他のステータスから入金済へ変更された場合に入金日を更新
     *
     * 新規登録の場合
     * - 受注日を更新
     * - 受注ステータスが発送済に設定された場合に発送日を更新
     * - 受注ステータスが入金済に設定された場合に入金日を更新
     *
     *
     * @param $app
     * @param $TargetOrder
     * @param $OriginOrder
     */
    protected function updateDate($app, $TargetOrder, $OriginOrder)
    {
        $dateTime = new \DateTime();

        // 編集
        if ($TargetOrder->getId()) {
            // 発送済
            if ($TargetOrder->getOrderStatus()->getId() == $app['config']['order_deliv']) {
                // 編集前と異なる場合のみ更新
                if ($TargetOrder->getOrderStatus()->getId() != $OriginOrder->getOrderStatus()->getId()) {
                    $TargetOrder->setCommitDate($dateTime);
                    // お届け先情報の発送日も更新する.
                    $Shippings = $TargetOrder->getShippings();
                    foreach ($Shippings as $Shipping) {
                        $Shipping->setShippingCommitDate($dateTime);
                    }
                }
                // 入金済
            } elseif ($TargetOrder->getOrderStatus()->getId() == $app['config']['order_pre_end']) {
                // 編集前と異なる場合のみ更新
                if ($TargetOrder->getOrderStatus()->getId() != $OriginOrder->getOrderStatus()->getId()) {
                    $TargetOrder->setPaymentDate($dateTime);
                }
            }
            // 新規
        } else {
            // 発送済
            if ($TargetOrder->getOrderStatus()->getId() == $app['config']['order_deliv']) {
                $TargetOrder->setCommitDate($dateTime);
                // お届け先情報の発送日も更新する.
                $Shippings = $TargetOrder->getShippings();
                foreach ($Shippings as $Shipping) {
                    $Shipping->setShippingCommitDate($dateTime);
                }
                // 入金済
            } elseif ($TargetOrder->getOrderStatus()->getId() == $app['config']['order_pre_end']) {
                $TargetOrder->setPaymentDate($dateTime);
            }
            // 受注日時
            $TargetOrder->setOrderDate($dateTime);
        }
    }
}
