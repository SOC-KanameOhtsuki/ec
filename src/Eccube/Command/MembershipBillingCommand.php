<?php

namespace Eccube\Command;

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Common\Constant;
use Eccube\Entity\Master\DeviceType;
use Eccube\Entity\ShipmentItem;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\TableHelper;

class MembershipBillingCommand extends \Knp\Command\Command
{

    protected $app;

    protected function configure() {
        $this
            ->setName('membershipbilling:billing')
            ->setDescription('Membership Billing For All Target Customer')
            ->addArgument('BillingId', InputArgument::REQUIRED, 'What invoice do you give?');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

        $this->app = $this->getSilexApplication();
        $this->app->initialize();
        $this->app->boot();

        $console = new Application();

        $BillingId = $input->getArgument('BillingId');
        $membershipBilling = $this->app['eccube.repository.membership_billing']
                    ->find($BillingId);
        if ($membershipBilling) {
            if ($membershipBilling->getStatus()->getId() == 1) {
                log_info('受注登録開始', array(count($membershipBilling->getMembershipBillingDetail())));
                // 処理状態更新
                $membershipBilling->setStatus($this->app['eccube.repository.master.membership_billing_status']->find(2));
                $this->app['orm.em']->persist($membershipBilling);
                $this->app['orm.em']->flush();
                try {
                    $membershipProduct = $membershipBilling->getProductMembership()->getProduct();
                    $CommonTaxRule = $this->app['eccube.repository.tax_rule']->getByRule($membershipProduct, $membershipProduct->getProductClasses()[0]);
                    $OrderStatus = $this->app['eccube.repository.master.order_status']->find(1);
                    $Payment = $this->app['eccube.repository.payment']->find(3);
                    if (0 < count($membershipBilling->getMembershipBillingDetail())) {
                        foreach($membershipBilling->getMembershipBillingDetail() as $membershipBillingDetail) {
                            $success = true;
                            $info = '';
                            $order_id = null;
                            if ($membershipBillingDetail->getStatus()->getId() != 1) {
                                log_info('年会費受注取り込み詳細が既に処理済み', array($membershipBillingDetail->getId()));
                                continue;
                            }
                            // 詳細処理状態更新
                            $membershipBillingDetail->setStatus($this->app['eccube.repository.master.membership_billing_detail_status']->find(2));
                            $this->app['orm.em']->persist($membershipBillingDetail);
                            $this->app['orm.em']->flush();
                            try {
                                // 会員エンティティを取得.
                                $customer = $membershipBillingDetail->getCustomer();
                                if (is_null($customer)) {
                                    throw new Exception('処理時、会員情報取得失敗');
                                } else if ($customer->getDelFlg() != 0) {
                                    throw new Exception('処理時、会員情報削除済み');
                                }
                                // 空のエンティティを作成.
                                $order = $this->newOrder($this->app);
                                // 受注情報を設定
                                $order->setCustomer($customer)
                                            ->setDiscount(0)
                                            ->setSubtotal($membershipProduct->getPrice02IncTaxMax())
                                            ->setTotal($membershipProduct->getPrice02IncTaxMax())
                                            ->setPaymentTotal($membershipProduct->getPrice02IncTaxMax())
                                            ->setCharge(0)
                                            ->setTax($membershipProduct->getPrice02IncTaxMax() - $membershipProduct->getPrice02Min())
                                            ->setDeliveryFeeTotal(0)
                                            ->setOrderStatus($OrderStatus)
                                            ->setDelFlg(Constant::DISABLED)
                                            ->setName01($customer->getName01())
                                            ->setName02($customer->getName02())
                                            ->setKana01($customer->getKana01())
                                            ->setKana02($customer->getKana02())
                                            ->setPref($customer->getPref())
                                            ->setZip01($customer->getZip01())
                                            ->setZip02($customer->getZip02())
                                            ->setAddr01($customer->getAddr01())
                                            ->setAddr02($customer->getAddr02())
                                            ->setEmail($customer->getEmail())
                                            ->setTel01($customer->getTel01())
                                            ->setTel02($customer->getTel02())
                                            ->setTel03($customer->getTel03())
                                            ->setFax01($customer->getFax01())
                                            ->setFax02($customer->getFax02())
                                            ->setFax03($customer->getFax03())
                                            ->setSex($customer->getSex())
                                            ->setJob($customer->getJob())
                                            ->setBirth($customer->getBirth())
                                            ->setPayment($Payment)
                                            ->setPaymentMethod($Payment->getMethod());
                                // 受注明細を作成
                                $OrderDetail = new \Eccube\Entity\OrderDetail();
                                $OrderDetail->setPriceIncTax($membershipProduct->getPrice02IncTaxMax());
                                $OrderDetail->setProductName($membershipProduct->getName());
                                $OrderDetail->setProductCode($membershipProduct->getProductClasses()[0]->getCode());
                                $OrderDetail->setPrice($membershipProduct->getPrice02Min());
                                $OrderDetail->setQuantity(1);
                                $OrderDetail->setTaxRate($CommonTaxRule->getTaxRate());
                                $OrderDetail->setTaxRule($CommonTaxRule->getId());
                                $OrderDetail->setProduct($membershipProduct);
                                $OrderDetail->setProductClass($membershipProduct->getProductClasses()[0]);
                                $OrderDetail->setClassName1($membershipProduct->getClassName1());
                                $OrderDetail->setClassName2($membershipProduct->getClassName2());
                                $OrderDetail->setOrder($order);
                                $order->addOrderDetail($OrderDetail);

                                // 会員の場合、購入回数、購入金額などを更新
                                $this->app['eccube.repository.customer']->updateBuyData($this->app, $customer, 1);

                                // 配送業者・お届け時間の更新
                                $Shippings = $order->getShippings();
                                foreach ($Shippings as $Shipping) {
                                    $Shipping->setName01($customer->getName01());
                                    $Shipping->setName02($customer->getName02());
                                    $Shipping->setKana01($customer->getKana01());
                                    $Shipping->setKana02($customer->getKana02());
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
                                $order->setOrderDate(new \DateTime());

                                $NewShipmentItems = new ArrayCollection();
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
                                    ->setOrder($order);
                                $NewShipmentItems[] = $NewShipmentItem;

                                // 配送商品の更新. delete/insert.
                                $Shippings = $order->getShippings();
                                foreach ($Shippings as $Shipping) {
                                    $ShipmentItems = $Shipping->getShipmentItems();
                                    foreach ($ShipmentItems as $ShipmentItem) {
                                        $this->app['orm.em']->remove($ShipmentItem);
                                    }
                                    $ShipmentItems->clear();
                                    foreach ($NewShipmentItems as $NewShipmentItem) {
                                        $NewShipmentItem->setShipping($Shipping);
                                        $ShipmentItems->add($NewShipmentItem);
                                    }
                                }
                                $this->app['orm.em']->persist($order);
                                $this->app['orm.em']->flush();
                                $order_id = $order->getId();
                            } catch (\Exception $e) {
                                $success = false;
                                $info = $e->getMessage();
                            } finally {
                                // 詳細処理状態更新
                                if ($success) {
                                    $membershipBillingDetail->setStatus($this->app['eccube.repository.master.membership_billing_detail_status']->find(3));
                                } else {
                                    $membershipBillingDetail->setStatus($this->app['eccube.repository.master.membership_billing_detail_status']->find(4));
                                    $membershipBillingDetail->setInfo($info);
                                }
                                if (!is_null($order_id)) {
                                    $membershipBillingDetail->setOrder($this->app['eccube.repository.order']->find($order_id));
                                }
                                $this->app['orm.em']->persist($membershipBillingDetail);
                                $this->app['orm.em']->flush();
                            }
                        }
                    }
                } catch (\Exception $e) {
                    echo "予期せぬエラー:" . $e->getMessage() . "\n";
                } finally {
                    // 処理状態更新
                    $membershipBilling->setStatus($this->app['eccube.repository.master.membership_billing_status']->find(3));
                    $this->app['orm.em']->persist($membershipBilling);
                    $this->app['orm.em']->flush();
                }
                log_info('受注処理登録完了', array(count($taretCustomers)));
            }
        }
    }

    protected function newOrder($app)
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
}
