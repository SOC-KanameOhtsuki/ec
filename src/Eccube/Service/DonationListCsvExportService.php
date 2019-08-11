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

class DonationListCsvExportService extends CsvExportService
{
    /**
     * 受講者情報からCSVファイルを作成する.
     *
     * @param array $customerDatas
     *
     * @return bool
     */
    public function makeCsv(array $customerDatas, $searchData, $summarize)
    {
        $this->fopen();
        $getMaxDonationCountSql = 'SELECT';
        $getMaxDonationCountSql .= ' MAX(donation_count) AS max_donation_count';
        $getMaxDonationCountSql .= ' FROM';
        $getMaxDonationCountSql .= ' (SELECT';
        $getMaxDonationCountSql .= ' COUNT(dtb_order.order_id) AS donation_count';
        $getMaxDonationCountSql .= ' FROM';
        $getMaxDonationCountSql .= ' dtb_order';
        $getMaxDonationCountSql .= ' INNER JOIN dtb_order_detail ON dtb_order_detail.order_id = dtb_order.order_id';
        $getMaxDonationCountSql .= ' INNER JOIN dtb_product_category ON dtb_product_category.product_id = dtb_order_detail.product_id';
        $getMaxDonationCountSql .= ' WHERE';
        $getMaxDonationCountSql .= ' dtb_product_category.category_id = 2';
        $getDonationDetailSql = 'SELECT';
        $getDonationDetailSql .= ' dtb_order.customer_id AS customer_id,';
        $getDonationDetailSql .= ' dtb_order_detail.price AS price,';
        $getDonationDetailSql .= ' dtb_order.payment_date AS payment_date';
        $getDonationDetailSql .= ' FROM';
        $getDonationDetailSql .= ' dtb_order';
        $getDonationDetailSql .= ' INNER JOIN dtb_order_detail ON dtb_order_detail.order_id = dtb_order.order_id';
        $getDonationDetailSql .= ' INNER JOIN dtb_product_category ON dtb_product_category.product_id = dtb_order_detail.product_id';
        $getDonationDetailSql .= ' WHERE';
        $getDonationDetailSql .= ' dtb_product_category.category_id = 2';
        $getDonationSummarySql = 'SELECT';
        $getDonationSummarySql .= ' dtb_order.customer_id AS customer_id,';
        $getDonationSummarySql .= ' SUM(dtb_order_detail.price) AS total_donation';
        $getDonationSummarySql .= ' FROM';
        $getDonationSummarySql .= ' dtb_order';
        $getDonationSummarySql .= ' INNER JOIN dtb_order_detail ON dtb_order_detail.order_id = dtb_order.order_id';
        $getDonationSummarySql .= ' INNER JOIN dtb_product_category ON dtb_product_category.product_id = dtb_order_detail.product_id';
        $getDonationSummarySql .= ' WHERE';
        $getDonationSummarySql .= ' dtb_product_category.category_id = 2';
        if ($searchData['search_donation_type'] == 1) {
            if (isset($searchData['target_year'])) {
                $searchData['target_date_start'] = new \DateTime($searchData['target_year'] . '-01-01 00:00:00');
                $searchData['target_date_end'] = new \DateTime($searchData['target_year'] . '-12-31 23:59:59');
            }
        } else if ($searchData['search_donation_type'] == 2) {
            $TermInfo = null;
            if (isset($searchData['target_term'])) {
                $TermInfo = $this->em->getRepository('Eccube\Entity\Master\TermInfo')->find($searchData['target_term']);
            }
            if (!is_null($TermInfo)) {
                $searchTypeName = $TermInfo->getTermName();
                $searchData['target_date_start'] = new \DateTime($TermInfo->getTermStart()->format('Y-m-d 00:00:00'));
                $searchData['target_date_end'] = new \DateTime($TermInfo->getTermEnd()->format('Y-m-d 23:59:59'));
            }
        }

        if ((isset($searchData['target_date_start'])) || (isset($searchData['target_date_end']))) {
            $this->fputcsv(['出力期間' . (isset($searchData['target_date_start'])?$searchData['target_date_start']->format("Y年n月j日から"):"") . (isset($searchData['target_date_end'])?$searchData['target_date_end']->format("Y年n月j日まで"):"")]);
            if (isset($searchData['target_date_start'])) {
                $getMaxDonationCountSql .= " AND dtb_order.payment_date >= '" . $searchData['target_date_start']->format('Y-m-d 00:00:00') . "'";
                $getDonationDetailSql .= " AND dtb_order.payment_date >= '" . $searchData['target_date_start']->format('Y-m-d 00:00:00')  . "'";
                $getDonationSummarySql .= " AND dtb_order.payment_date >= '" . $searchData['target_date_start']->format('Y-m-d 00:00:00')  . "'";
            }
            if (isset($searchData['target_date_end'])) {
                $getMaxDonationCountSql .= " AND dtb_order.payment_date <= '" . $searchData['target_date_end']->format('Y-m-d 23:59:59') . "'";
                $getDonationDetailSql .= " AND dtb_order.payment_date <= '" . $searchData['target_date_end']->format('Y-m-d 23:59:59') . "'";
                $getDonationSummarySql .= " AND dtb_order.payment_date <= '" . $searchData['target_date_end']->format('Y-m-d 23:59:59') . "'";
            }
        } else {
            $this->fputcsv(['']);
        }
        $getMaxDonationCountSql .= ' GROUP BY dtb_order.customer_id) AS TEMP;';
        $getDonationDetailSql .= ' ORDER BY dtb_order.customer_id;';
        $getDonationSummarySql .= ' GROUP BY dtb_order.customer_id ORDER BY dtb_order.customer_id;';
        $donationDetailDatas = array();
        $donationDetails = $this->em->getConnection()->fetchAll($getDonationDetailSql);
        foreach ($donationDetails as $donationDetail) {
            if (!isset($donationDetailDatas[$donationDetail['customer_id']])) {
                $donationDetailDatas[$donationDetail['customer_id']] = array();
            }
            $donationDetailDatas[$donationDetail['customer_id']][] = $donationDetail;
        }

        if ($summarize == 1) {
            $maxDonationCount = $this->em->getConnection()->fetchColumn($getMaxDonationCountSql);
            $donationSummaryDatas = array();
            $donationSummaries = $this->em->getConnection()->fetchAll($getDonationSummarySql);
            foreach ($donationSummaries as $donationSummary) {
                $donationSummaryDatas[$donationSummary['customer_id']] = $donationSummary;
            }
            $idx = 1;
            $row = ['ID', 'ふりがな（姓）', 'ふりがな（名）', '姓', '名', '郵便番号', '都道府県', '市町村', '住所', '所属', '寄付の合計金額'];
            while ($idx <= $maxDonationCount) {
                $row[] = '受領年月日' . $idx;
                $row[] = '寄付金の額' . $idx;
                ++$idx;
            }
            $this->fputcsv($row);
            foreach ($customerDatas as $customerData) {
                $row = array();
                // ID
                $row[] = $customerData->getId();
                // ふりがな（姓）
                $row[] = (is_null($customerData->getKana01())?'':$customerData->getKana01());
                // ふりがな（名）
                $row[] = (is_null($customerData->getKana02())?'':$customerData->getKana02());
                // 姓
                $row[] = (is_null($customerData->getName01())?'':$customerData->getName01());
                // 名
                $row[] = (is_null($customerData->getName02())?'':$customerData->getName02());
                // 郵便番号
                $row[] = (is_null($customerData->getZip01())?'':$customerData->getZip01()) . (is_null($customerData->getZip02())?'':$customerData->getZip02());
                // 住所
                $row[] = (is_null($customerData->getPref())?'':$customerData->getPref()->getName());
                // 市町村
                $row[] = (is_null($customerData->getAddr01())?'':$customerData->getAddr01());
                // 住所
                $row[] = (is_null($customerData->getAddr02())?'':$customerData->getAddr02());
                // 所属
                $row[] = (is_null($customerData->getCompanyName())?'':$customerData->getCompanyName());
                // 寄付金の合計金額
                if (isset($donationSummaryDatas[$customerData->getId()])) {
                    $row[] = $donationSummaryDatas[$customerData->getId()]['total_donation'];
                } else {
                    $row[] = 0;
                }
                if (isset($donationDetailDatas[$customerData->getId()])) {
                    foreach($donationDetailDatas[$customerData->getId()] as $donationDetail) {
                        // 寄付金
                        $row[] = $donationDetail['payment_date'];
                        // 受領年月日
                        $row[] = $donationDetail['price'];
                    }
                }
                $this->fputcsv($row);
            }
        } else {
            $this->fputcsv(['ID', 'ふりがな（姓）', 'ふりがな（名）', '姓', '名', '郵便番号', '都道府県', '市町村', '住所', '所属', '寄付の金額', '受領年月日']);
            foreach ($customerDatas as $customerData) {
                if (isset($donationDetailDatas[$customerData->getId()])) {
                    foreach ($donationDetailDatas[$customerData->getId()] as $donationDetail) {
                        $row = array();
                        // ID
                        $oldCustomerId = '';
                        if (!is_null($customerData->getCustomerBasicInfo()->getCustomerNumber()) ){
                            if (strlen($customerData->getCustomerBasicInfo()->getCustomerNumber()) < 6) {
                                $oldCustomerId = intval($customerData->getCustomerBasicInfo()->getCustomerNumber());
                            } else {
                                $oldCustomerId = intval(substr($customerData->getCustomerBasicInfo()->getCustomerNumber(),
                                                        strlen($customerData->getCustomerBasicInfo()->getCustomerNumber()) - 5));
                            }
                        }
                        $row[] = $oldCustomerId;
                        // ふりがな（姓）
                        $row[] = (is_null($customerData->getKana01())?'':$customerData->getKana01());
                        // ふりがな（名）
                        $row[] = (is_null($customerData->getKana02())?'':$customerData->getKana02());
                        // 姓
                        $row[] = (is_null($customerData->getName01())?'':$customerData->getName01());
                        // 名
                        $row[] = (is_null($customerData->getName02())?'':$customerData->getName02());
                        // 郵便番号
                        $row[] = (is_null($customerData->getZip01())?'':$customerData->getZip01()) . (is_null($customerData->getZip02())?'':$customerData->getZip02());
                        // 住所
                        $row[] = (is_null($customerData->getPref())?'':$customerData->getPref()->getName());
                        // 市町村
                        $row[] = (is_null($customerData->getAddr01())?'':$customerData->getAddr01());
                        // 住所
                        $row[] = (is_null($customerData->getAddr02())?'':$customerData->getAddr02());
                        // 所属
                        $row[] = (is_null($customerData->getCompanyName())?'':$customerData->getCompanyName());
                        // 寄付金
                        $row[] = $donationDetail['payment_date'];
                        // 受領年月日
                        $row[] = $donationDetail['price'];
                        $this->fputcsv($row);
                    }
                }
            }
        }
        $this->fclose();
    }
}
