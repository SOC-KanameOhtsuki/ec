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
        $this->app['orm.em']->getConnection()->getConfiguration()->setSQLLogger(null);
        $logfile_path = $this->app['config']['root_dir'].'/app/log/MembershipBilling_' . $BillingId . '.log';

        $BillingId = $input->getArgument('BillingId');
        file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . '年会費受注登録バッチ起動 BillingId:' . $BillingId . "\n", FILE_APPEND);
        $membershipBilling = $this->app['eccube.repository.membership_billing']
                    ->find($BillingId);
        if ($membershipBilling) {
            if ($membershipBilling->getStatus()->getId() == 1) {
                $total = count($membershipBilling->getMembershipBillingDetail());
                file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . '受注登録開始:' . $total . "\n", FILE_APPEND);
                // 処理状態更新
                $membershipBilling->setStatus($this->app['eccube.repository.master.membership_billing_status']->find(2));
                $this->app['orm.em']->persist($membershipBilling);
                $this->app['orm.em']->flush();
                try {
                    $membershipProduct = $membershipBilling->getProductMembership()->getProduct();
                    $price = $membershipProduct->getPrice02IncTaxMax();
                    $productClass = $membershipProduct->getProductClasses()[0];
                    $deviceType = $this->app['eccube.repository.master.device_type']->find(DeviceType::DEVICE_TYPE_ADMIN);
                    $CommonTaxRule = $this->app['eccube.repository.tax_rule']->getByRule($membershipProduct, $membershipProduct->getProductClasses()[0]);
                    $taxRate = $CommonTaxRule->getTaxRate();
                    $taxRuleId = $CommonTaxRule->getId();
                    $OrderStatus = $this->app['eccube.repository.master.order_status']->find(1);
                    $Payment = $this->app['eccube.repository.payment']->find(3);
                    $membershipBillingProcessing = $this->app['eccube.repository.master.membership_billing_detail_status']->find(3);
                    $membershipBillingSuccess = $this->app['eccube.repository.master.membership_billing_detail_status']->find(3);
                    $membershipBillingFail = $this->app['eccube.repository.master.membership_billing_detail_status']->find(4);
                    $keepAliveTime = date('Y-m-d H:i:s');
                    if (0 < count($membershipBilling->getMembershipBillingDetail())) {
                        $countSkip = 0;
                        $countRegist = 0;
                        foreach($membershipBilling->getMembershipBillingDetail() as $membershipBillingDetail) {
                            if (strtotime("now") >= strtotime("+" . $this->app['config']['keep_alive_seconds'] . " seconds", strtotime($keepAliveTime))) {
                                file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . $total . '件中 登録:' . $countRegist . '件 処理済みスキップ:' . $countSkip. "件\n", FILE_APPEND);
                                $keepAliveTime = date('Y-m-d H:i:s');
                            }
                            $success = true;
                            $info = '';
                            $order = null;
                            if ($membershipBillingDetail->getStatus()->getId() != 1) {
                                ++$countSkip;
                                continue;
                            } else {
                                ++$countRegist;
                            }
                            // 詳細処理状態更新
                            $membershipBillingDetail->setStatus($membershipBillingProcessing);
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
                                $order = new \Eccube\Entity\Order();
                                $order->setDeviceType($deviceType);
                                // 受注情報を設定
                                $order->setCustomer($customer)
                                            ->setDiscount(0)
                                            ->setSubtotal($price)
                                            ->setTotal($price)
                                            ->setPaymentTotal($price)
                                            ->setCharge(0)
                                            ->setTax($price - $membershipProduct->getPrice02Min())
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
                                $OrderDetail->setPriceIncTax($price);
                                $OrderDetail->setProductName($membershipProduct->getName());
                                $OrderDetail->setProductCode($productClass->getCode());
                                $OrderDetail->setPrice($membershipProduct->getPrice02Min());
                                $OrderDetail->setQuantity(1);
                                $OrderDetail->setTaxRate($taxRate);
                                $OrderDetail->setTaxRule($taxRuleId);
                                $OrderDetail->setProduct($membershipProduct);
                                $OrderDetail->setProductClass($productClass);
                                $OrderDetail->setClassName1($membershipProduct->getClassName1());
                                $OrderDetail->setClassName2($membershipProduct->getClassName2());
                                $OrderDetail->setOrder($order);
                                $order->addOrderDetail($OrderDetail);

                                // 会員の場合、購入回数、購入金額などを更新
                                $this->app['eccube.repository.customer']->updateBuyData($this->app, $customer, 1);

                                // 配送業者・お届け時間の更新
                                $NewShipmentItem = new ShipmentItem();
                                $NewShipmentItem
                                    ->setProduct($membershipProduct)
                                    ->setProductClass($productClass)
                                    ->setProductName($membershipProduct->getName())
                                    ->setProductCode($productClass->getCode())
                                    ->setClassCategoryName1($OrderDetail->getClassCategoryName1())
                                    ->setClassCategoryName2($OrderDetail->getClassCategoryName2())
                                    ->setClassName1($membershipProduct->getClassName1())
                                    ->setClassName2($membershipProduct->getClassName2())
                                    ->setPrice($membershipProduct->getPrice02Min())
                                    ->setQuantity(1)
                                    ->setOrder($order);

                                // 配送商品の設定.
                                $Shipping = new \Eccube\Entity\Shipping();
                                $Shipping->setDelFlg(0);
                                $Shipping->setName01($customer->getName01());
                                $Shipping->setName02($customer->getName02());
                                $Shipping->setKana01($customer->getKana01());
                                $Shipping->setKana02($customer->getKana02());
                                $NewShipmentItem->setShipping($Shipping);
                                $Shipping->getShipmentItems()->add($NewShipmentItem);
                                $order->addShipping($Shipping);
                                $Shipping->setOrder($order);

                                // 受注日/発送日/入金日の更新.
                                $order->setOrderDate(new \DateTime());

                                $this->app['orm.em']->persist($order);
                            } catch (\Exception $e) {
                                $success = false;
                                $info = $e->getMessage();
                                $order = null;
                            } finally {
                                // 詳細処理状態更新
                                if ($success) {
                                    $membershipBillingDetail->setStatus($membershipBillingSuccess);
                                } else {
                                    $membershipBillingDetail->setStatus($membershipBillingFail);
                                    $membershipBillingDetail->setInfo($info);
                                }
                                if (!is_null($order)) {
                                    $membershipBillingDetail->setOrder($order);
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
                file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . '受注処理登録完了:' . count($taretCustomers). "\n", FILE_APPEND);
            }
        }
        file_put_contents($logfile_path, date('Y-m-d H:i:s: ') . '年会費受注登録バッチ終了 BillingId:' . $BillingId . "\n", FILE_APPEND);
    }
}
