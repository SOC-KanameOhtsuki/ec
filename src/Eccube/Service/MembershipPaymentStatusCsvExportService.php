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
    /** 曜日 @var array */
    private $WeekDay = ['0' => '日', '1' => '月', '2' => '火', '3' => '水', '4' => '木', '5' => '金', '6' => '土'];

    /**
     * 受講者情報からCSVファイルを作成する.
     *
     * @param array $customerDatas
     *
     * @return bool
     */
    public function makeCsv(array $customerDatas, $productTraining)
    {
        $this->fopen();
        $this->fputcsv(['講演会名簿']);
        $this->fputcsv(['会場 ' . $productTraining->getPlace()]);
        $startDate = $productTraining->getTrainingDateStart()->format('Y年n月j日(') . $this->WeekDay[$productTraining->getTrainingDateStart()->format('w')] . ')';
        $endDate = $productTraining->getTrainingDateEnd()->format('Y年n月j日(') . $this->WeekDay[$productTraining->getTrainingDateEnd()->format('w')] . ')';
        $this->fputcsv(['日時 ' . $startDate . $productTraining->getTrainingDateStart()->format(' G:i～') . (($startDate==$endDate)?'':$endDate . ' ') . $productTraining->getTrainingDateEnd()->format('G:i')]);
        $this->fputcsv(['No.','氏名','ふりがな','住所','電話番号','備考']);
        $no = 1;
        foreach ($customerDatas as $customerData) {
            $row = array();
            // No
            $row[] = $no;
            // 氏名
            $row[] = ((is_null($customerData->getName01())?'':$customerData->getName01() . ' ') . (is_null($customerData->getName02())?'':$customerData->getName02()));
            // ふりがな
            if (!is_null($customerData->getKana01()) || !is_null($customerData->getKana02())) {
                $row[] = mb_convert_kana(((is_null($customerData->getKana01())?'':$customerData->getKana01() . ' ') . (is_null($customerData->getKana02())?'':$customerData->getKana02())), 'c');
            } else {
                $row[] = '';
            }
            // 住所
            $row[] = (is_null($customerData->getAddr01())?'':$customerData->getAddr01());
            // 電話番号
            $row[] = $customerData->getTelNubmerAll();
            $this->fputcsv($row);
            ++$no;
        }
        $this->fclose();
    }
}
