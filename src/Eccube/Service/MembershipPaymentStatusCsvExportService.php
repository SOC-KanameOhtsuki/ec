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

class MembershipPaymentStatusCsvExportService extends CsvExportService
{
    /**
     * 会員情報からCSVファイルを作成する.
     *
     * @param array $customerDatas
     *
     * @return bool
     */
    public function makeCsv(array $customerDatas, array $orderDatas, array $membershipBillingStatus, $productMemberShip)
    {
        $this->fopen();
        $this->fputcsv(['年会費支払状況名簿']);
        $this->fputcsv(['No.','年度','会員番号','氏名','Status','支払方法','支払状況','都道府県','住所1','住所2','ID']);
        $no = 1;
        foreach ($customerDatas as $customerData) {
            $row = array();
            // No
            $row[] = $no;
            // 年度
            $row[] = $productMemberShip->getMembershipYear();
            // 会員番号
            $oldCustomerId = '';
            if (!is_null($customerData->getCustomerBasicInfo()->getCustomerNumberOld()) ){
                if (strlen($customerData->getCustomerBasicInfo()->getCustomerNumberOld()) < 6) {
                    $oldCustomerId = intval($customerData->getCustomerBasicInfo()->getCustomerNumberOld());
                } else {
                    $oldCustomerId = intval(substr($customerData->getCustomerBasicInfo()->getCustomerNumberOld(),
                                            strlen($customerData->getCustomerBasicInfo()->getCustomerNumberOld()) - 5));
                }
            }
            $row[] = $oldCustomerId;
            // 氏名
            $row[] = ((is_null($customerData->getName01())?'':$customerData->getName01()) . (is_null($customerData->getName02())?'':$customerData->getName02()));
            // Status
            $row[] = $customerData->getCustomerBasicInfo()->getStatus()->getName();
            // 支払状況
            $row[] = (isset($membershipBillingStatus[$customerData->getId()])?'支払済み':'未納');
            // 支払方法
            $row[] = (isset($order[$customerData->getId()])?(!is_null($order[$customerData->getId()]->getPaymentMethod())?$order[$customerData->getId()]->getPaymentMethod():''):'');
            // 郵便番号
            $row[] = (is_null($customerData->getZip01())?"":$customerData->getZip01()) . (is_null($customerData->getZip02())?"":$customerData->getZip02());
            // 都道府県
            $row[] = (is_null($customerData->getPref())?'':$customerData->getPref()->getName());
            // 住所1
            $row[] = (is_null($customerData->getAddr01())?'':$customerData->getAddr01());
            // 住所2
            $row[] = (is_null($customerData->getAddr02())?'':$customerData->getAddr02());
            // ID
            $row[] = (is_null($customerData->getEmail())?'':$customerData->getEmail());
            $this->fputcsv($row);
            ++$no;
        }
        $this->fclose();
    }
}
