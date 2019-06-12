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

class RegularMemberListCsvExportService extends CsvExportService
{
    /**
     * 正会員情報からCSVファイルを作成する.
     *
     * @param array $customerDatas
     *
     * @return bool
     */
    public function makeCsv(array $customerDatas, $anonymousCompanyEnabled = false)
    {
        $this->fopen();
        foreach ($customerDatas as $customerData) {
            $row = array();
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
            // 会員名(姓)
            $row[] = (is_null($customerData->getName01())?"":$customerData->getName01());
            // 会員名(名)
            $row[] = (is_null($customerData->getName02())?"":$customerData->getName02());
            // 会員名(セイ)
            $row[] = (is_null($customerData->getKana01())?"":$customerData->getKana01());
            // 会員名(メイ)
            $row[] = (is_null($customerData->getKana02())?"":$customerData->getKana02());
            // サポータ資格
            $row[] = ($customerData->getCustomerBasicInfo()->getSupporterType() == '非サポータ'?'':'サポータ');
            // インストラクタ資格
            $row[] = ($customerData->getCustomerBasicInfo()->getInstructorType() == '非インストラクタ'?'':$customerData->getCustomerBasicInfo()->getInstructorType());
            foreach ($customerData->getCustomerAddresses() as $AddresInfo) {
                if ($AddresInfo->getMailTo()->getId() == 2) {
                    // 都道府県
                    $row[] = (is_null($AddresInfo->getPref())?"":$AddresInfo->getPref());
                    // 市町村
                    $row[] = ((is_null($AddresInfo->getAddr01())||!$anonymousCompanyEnabled)?"":$AddresInfo->getAddr01());
                    $Out = true;
                    break;
                }
            }
            if (!$Out) {
                // 都道府県
                $row[] = (is_null($customerData->getPref())?"":$customerData->getPref());
                // 市町村
                $row[] = (is_null($customerData->getAddr01())?"":$customerData->getAddr01());
            }
            // 勤務先
            $row[] = (is_null($customerData->getCompanyName())?"":$customerData->getCompanyName());
            // PINコード
            $row[] = $customerData->getCustomerBasicInfo()->getCustomerPinCode();
            $this->fputcsv($row);
        }
        $this->fclose();
    }
}
