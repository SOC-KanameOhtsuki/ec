<?php
/*
 * This file is Customized file
 */

namespace Eccube\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Util\EntityUtil;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\HttpFoundation\Request;

class YamatoLabelCsvExportService extends CsvExportService
{
    /**
     * 受注情報からCSVファイルを作成する.
     *
     * @param Application $app
     * @param array $orders
     *
     * @return bool
     */
    public function makeCsv(Application $app, array $orders)
    {
        $BaseInfo = $app['eccube.repository.base_info']->get();
        $this->fopen();
        $this->fputcsv(['No.','お届け先電話番号','お届け先郵便番号','お届け先住所1','お届け先住所2','届け先氏名','依頼主電話番号','依頼主郵便番号','依頼主住所1','依頼主住所2','依頼主氏名','品名','受付日','お届け予定日','時間帯お届け']);
        $no = 1;
        foreach ($orders as $order) {
            $row = array();
            // No
            $row[] = $no;
            // お届け先電話番号
            if (!is_null($order->getTel01()) && !is_null($order->getTel02()) && !is_null($order->getTel03())) {
                $row[] = $order->getTel01() . '-' . $order->getTel02() . '-' . $order->getTel03();
            } else {
                $row[] = '';
            }
            // お届け先郵便番号
            $row[] = (is_null($order->getZip01())?"":$order->getZip01() . (is_null($order->getZip02())?'':'-')) . (is_null($order->getZip02())?"":$order->getZip02());
            // お届け先住所1
            $row[] = (is_null($order->getPref())?"":$order->getPref()->getName()) . (is_null($order->getAddr01())?"":$order->getAddr01());
            // お届け先住所2
            $row[] = (is_null($order->getAddr02())?'':$order->getAddr02());
            // お届け先氏名
            $row[] = ((is_null($order->getName01())?'':$order->getName01() . ' ') . (is_null($order->getName02())?'':$order->getName02()));
            // 依頼主電話番号
            if (!is_null($BaseInfo->getTel01()) && !is_null($BaseInfo->getTel02()) && !is_null($BaseInfo->getTel03())) {
                $row[] = $BaseInfo->getTel01() . '-' . $BaseInfo->getTel02() . '-' . $BaseInfo->getTel03();
            } else {
                $row[] = '';
            }
            // 依頼主郵便番号
            $row[] = (is_null($BaseInfo->getZip01())?"":$BaseInfo->getZip01() . (is_null($BaseInfo->getZip02())?'':'-')) . (is_null($BaseInfo->getZip02())?"":$BaseInfo->getZip02());
            // 依頼主住所1
            $row[] = (is_null($BaseInfo->getPref())?"":$BaseInfo->getPref()->getName()) . (is_null($BaseInfo->getAddr01())?"":$BaseInfo->getAddr01());
            // 依頼主住所2
            $row[] = (is_null($BaseInfo->getAddr02())?'':$BaseInfo->getAddr02());
            // 依頼主氏名
            $row[] = (is_null($BaseInfo->getCompanyName())?'':$BaseInfo->getCompanyName());
            // 品名
            $row[] = "ふまねっと";
            // 受付日
            $row[] = date('Y/n/j');
            // お届け予定日
            $row[] = "";
            // 時間帯お届け
            $row[] = "指定なし";
            $this->fputcsv($row);
            ++$no;
        }
        $this->fclose();
    }
}
