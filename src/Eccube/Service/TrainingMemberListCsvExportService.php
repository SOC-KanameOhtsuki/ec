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

class TrainingMemberListCsvExportService extends CsvExportService
{
    /** 曜日 @var array */
    private $WeekDay = ['0' => '日', '1' => '月', '2' => '火', '3' => '水', '4' => '木', '5' => '金', '6' => '土'];

    /**
     * 受講者情報からCSVファイルを作成する.
     *
     * @param array $customerDatas
     *
     * @return bool
     */
    public function makeCsv(array $customerDatas, array $orders, $productTraining)
    {
        $this->fopen();
        $this->fputcsv([$productTraining->getTrainingDateStart()->format('Y年n月j日(') . $this->WeekDay[$productTraining->getTrainingDateStart()->format('w')] . ')' . (is_null($productTraining->getPlace())?'':' '.$productTraining->getPlace()) . ' ' . $productTraining->getTrainingType()->getName()]);
        $this->fputcsv([]);
        $this->fputcsv(['','ふりがな','氏名','住所','所属先名称','資格','年齢','請求金額','支払金額','領収日','TEL']);
        $no = 1;
        $nowDateTime = new \DateTime();
        foreach ($customerDatas as $customerData) {
            $row = array();
            // No
            $row[] = $no;
            // ふりがな
            if (!is_null($customerData->getKana01()) || !is_null($customerData->getKana02())) {
                $row[] = mb_convert_kana(((is_null($customerData->getKana01())?'':$customerData->getKana01() . ' ') . (is_null($customerData->getKana02())?'':$customerData->getKana02())), 'c');
            } else {
                $row[] = '';
            }
            // 氏名
            $row[] = ((is_null($customerData->getName01())?'':$customerData->getName01() . ' ') . (is_null($customerData->getName02())?'':$customerData->getName02()));
            // 住所
            $row[] = ((is_null($customerData->getPref())?'':$customerData->getPref()->getName()) . (is_null($customerData->getAddr01())?'':$customerData->getAddr01()));
            // 所属先
            $row[] = (is_null($customerData->getCompanyName())?'':$customerData->getCompanyName());
            // 資格
            $row[] = $customerData->getCustomerBasicInfo()->getQualification();
            // 年齢
            if (is_null($customerData->getBirth())) {
                $row[] = '';
            } else {
                $row[] = $nowDateTime->diff($customerData->getBirth())->format('%y');
            }
            if (isset($orders[$customerData->getId()])) {
                // 請求金額
                $row[] = '\\' . number_format($orders[$customerData->getId()]->getPaymentTotal());
                if (is_null($orders[$customerData->getId()]->getPaymentDate())) {
                    // 支払金額
                    $row[] = 0;
                    // 領収日
                    $row[] = '';
                } else {
                    // 支払金額
                    $row[] = '\\' . number_format($orders[$customerData->getId()]->getPaymentTotal());
                    // 領収日
                    $row[] = $orders[$customerData->getId()]->getPaymentDate()->format('Y年n月j日');
                }
            } else {

                // 請求金額・支払金額・領収日
                $row[] = '';
                $row[] = '';
                $row[] = '';
            }
            // TEL
            $row[] = $customerData->getTelNubmerAll();
            $this->fputcsv($row);
            ++$no;
        }
        $this->fclose();
    }
}
