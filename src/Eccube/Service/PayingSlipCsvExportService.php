<?php
/*
 * This file is Customized file
 */


namespace Eccube\Service;

use Eccube\Common\Constant;
use Eccube\Util\EntityUtil;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\ArrayCollection;

class PayingSlipCsvExportService extends CsvExportService
{
    /**
     * 受注情報からCSVファイルを作成する.
     *
     * @param array $ordersData
     *
     * @return bool
     */
    public function makeCsv(array $ordersData)
    {
        $this->fopen();
        $row_count = 1;
        $row = [1,1,date('Ymd'), $this->config['paying_slip_csv_manufacturer_code'], $this->config['paying_slip_csv_company_code'],$this->config['paying_slip_csv_shop_code']];
        $this->fputcsv($row);
        $totalPayment = 0;
        foreach ($ordersData as $order) {
            $orderTotalPayment = 0;
            // 請求書データ
            $orderZip = $order->getZip01() . "-" . $order->getZip02();
            $orderAddr1 = $order->getAddr01();
            if (strlen($orderAddr1) < 1) {
                $orderAddr1 = '　　';
            }
            $orderAddr2 = $order->getAddr02();
            if (strlen($orderAddr2) < 1) {
                $orderAddr2 = '　　';
            }
            $orderName = $order->getName01(). " " .$order->getName02();
            $row = [3,1,$order->getId(),date('Ymd'),'',$orderZip,mb_convert_kana($orderAddr1, 'RASKV'),mb_convert_kana($orderAddr2, 'RASKV'),'',mb_convert_kana($orderName, 'RASKV'),'',''];
            $this->fputcsv($row);
            ++$row_count;
            foreach ($order->getOrderDetails() as $orderDetail) {
                // 請求明細
                $productName = $orderDetail->getProduct()->getName();
                $productPrice = $orderDetail->getPrice();
                $productQuantity = $orderDetail->getQuantity();
                $payment = $productPrice * $productQuantity;
                $orderTotalPayment += $payment;
                $row = [3,2,$orderDetail->getProduct()->getId(),mb_convert_kana($productName, 'RASKV'),$productQuantity,$productPrice,$payment,''];
                $this->fputcsv($row);
                ++$row_count;
            }
            // 請求合計
            $tax = round($orderTotalPayment * 0.08);
            $orderTotalPaymentAndTax = $orderTotalPayment + $tax;
            $row = [3,3,$orderTotalPayment,$tax,0,$orderTotalPaymentAndTax,$this->config['paying_slip_csv_printout_type'],'','','','','',''];
            $this->fputcsv($row);
            ++$row_count;
            $totalPayment += $orderTotalPaymentAndTax;
        }
        ++$row_count;
        $row = [9,$row_count,$totalPayment];
        $this->fputcsv($row);
        $this->fclose();
    }
}
