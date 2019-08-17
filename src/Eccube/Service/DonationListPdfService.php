<?php
/*
 * This file is part of the Order Pdf plugin
 *
 * Copyright (C) 2016 LOCKON CO.,LTD. All Rights Reserved.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eccube\Service;

use Eccube\Application;
use Eccube\Entity\BaseInfo;
use Eccube\Entity\Help;
use Eccube\Entity\Order;
use Eccube\Entity\OrderDetail;

/**
 * Class DonationListPdfService.
 * Do export pdf function.
 */
class DonationListPdfService extends AbstractFPDIService
{
    // ====================================
    // 定数宣言
    // ====================================
    /** ダウンロードするPDFファイル名 */
    const OUT_PDF_FILE_NAME = 'donation_list';

    /** FONT ゴシック */
    const FONT_GOTHIC = 'kozgopromedium';
    /** FONT 明朝 */
    const FONT_SJIS = 'kozminproregular';

    // ====================================
    // 変数宣言
    // ====================================
    /** @var Application */
    public $app;

    // --------------------------------------
    // Font情報のバックアップデータ
    /** @var string フォント名 */
    private $bakFontFamily;
    /** @var string フォントスタイル */
    private $bakFontStyle;
    /** @var string フォントサイズ */
    private $bakFontSize;
    // --------------------------------------

    // lfTextのoffset
    private $baseOffsetX = 0;
    private $baseOffsetY = -4;

    /** ダウンロードファイル名 @var string */
    private $downloadFileName = null;

    /** 対象年度(西暦) @var string */
    private $targetYear = '';

    /** 曜日 @var array */
    private $WeekDay = ['0' => '日', '1' => '月', '2' => '火', '3' => '水', '4' => '木', '5' => '金', '6' => '土'];

    /**
     * コンストラクタ.
     *
     * @param object $app
     */
    public function __construct($app)
    {
        $this->app = $app;
        parent::__construct();

        // Fontの設定しておかないと文字化けを起こす
        $this->SetFont(self::FONT_GOTHIC);

        // PDFの余白(上左右)を設定
        $this->SetMargins(18.0, 29.0, 19.0);
        $this->SetAutoPageBreak(true, 22.0);

        $this->setHeaderMargin(29.0);
        $this->setHeaderFont(array(self::FONT_GOTHIC, '', 8));
        $this->setPrintHeader(true);

        // フッターの出力を無効化
        $this->setPrintFooter(true);
        $this->setFooterMargin();
        $this->setFooterFont(array(self::FONT_SJIS, '', 8));
    }

    /**
     * 顧客情報からPDFファイルを作成する.
     *
     * @param array $customersData
     *
     * @return bool
     */
    public function makePdf(array $customersData, $searchData, $TermInfo)
    {
        if (is_null($TermInfo)) {
            return false;
        }
        $anonymous = 0;
        $this->targetYear = $TermInfo->getTermYear();
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
        $getDonationDetailSql .= " AND dtb_order.payment_date >= '" . $TermInfo->getTermStart()->format('Y-m-d 00:00:00') . "'";
        $getDonationDetailSql .= " AND dtb_order.payment_date <= '" . $TermInfo->getTermEnd()->format('Y-m-d 23:59:59') . "'";
        $getDonationDetailSql .= ' AND dtb_order_detail.kifu_no_pub = 0;';
        $getAnonymouDonationCountSql = 'SELECT COUNT(*) FROM (SELECT dtb_order.customer_id FROM dtb_order';
        $getAnonymouDonationCountSql .= ' INNER JOIN dtb_order_detail ON dtb_order_detail.order_id = dtb_order.order_id';
        $getAnonymouDonationCountSql .= ' INNER JOIN dtb_product_category ON dtb_product_category.product_id = dtb_order_detail.product_id';
        $getAnonymouDonationCountSql .= ' WHERE';
        $getAnonymouDonationCountSql .= ' dtb_product_category.category_id = 2';
        $getAnonymouDonationCountSql .= " AND dtb_order.payment_date >= '" . $TermInfo->getTermStart()->format('Y-m-d 00:00:00') . "'";
        $getAnonymouDonationCountSql .= " AND dtb_order.payment_date <= '" . $TermInfo->getTermEnd()->format('Y-m-d 23:59:59') . "'";
        $getAnonymouDonationCountSql .= ' AND dtb_order_detail.kifu_no_pub = 1';
        $getAnonymouDonationCountSql .= ' GROUP BY dtb_order.customer_id) AS TEMP;';
        $donationDetailDatas = array();
        $donationDetails = $this->app['orm.em']->getConnection()->fetchAll($getDonationDetailSql);
        foreach ($donationDetails as $donationDetail) {
            if (!isset($donationDetailDatas[$donationDetail['customer_id']])) {
                $donationDetailDatas[$donationDetail['customer_id']] = array();
            }
            $donationDetailDatas[$donationDetail['customer_id']][] = $donationDetail;
        }
        $anonymous = $this->app['orm.em']->getConnection()->fetchColumn($getAnonymouDonationCountSql);

        // ダウンロードファイル名の初期化
        $this->downloadFileName = null;

        $this->SetFont(self::FONT_GOTHIC);

        // PDFにページを追加する
        $this->AddPage('PORTRAIT', 'A4');
        $this->SetFont('', '', 9);
        $this->SetTextColor(0, 0, 0);
        $no = 1;
        $count = 1;
        foreach ($customersData as $customerData) {
            $Out = false;
            foreach ($customerData->getCustomerAddresses() as $AddresInfo) {
                if ($AddresInfo->getMailTo()->getId() == 2) {
                    // 都道府県
                    $pref = (is_null($AddresInfo->getPref())?"":$AddresInfo->getPref());
                    // 市町村
                    $addr = (is_null($AddresInfo->getAddr01())?"":$AddresInfo->getAddr01());
                    $Out = true;
                    break;
                }
            }
            if (!$Out) {
                // 都道府県
                $pref = (is_null($customerData->getPref())?"":$customerData->getPref());
                // 市町村
                $addr = (is_null($customerData->getAddr01())?"":$customerData->getAddr01());
            }
            $name = $customerData->getName01() . ((0<strlen($customerData->getName02()))?$customerData->getName02():"");
            $height = 5.0;
            if ($height < $this->getStringHeight(72.0, $name, false, true, 0)) {
                $height = $this->getStringHeight(72.0, $name, false, true, 0);
            }
            if ($height < $this->getStringHeight(23.4, $pref, false, true, 0)) {
                $height = $this->getStringHeight(23.4, $pref, false, true, 0);
            }
            if ($height < $this->getStringHeight(34.5, $addr, false, true, 0)) {
                $height = $this->getStringHeight(34.5, $addr, false, true, 0);
            }
            if (isset($donationDetailDatas[$customerData->getId()])) {
                foreach($donationDetailDatas[$customerData->getId()] as $donationDetail) {
                    // No
                    $this->Cell(10.2, $height, $no, 1, 0, "R", false, "", 0, false, "T", "M");
                    // 氏名
                    $this->MultiCell(72.0, $height, $name, 1, "L", false, 0, "", "", true, 0, false, true, $height, "M");
                    // 都道府県
                    $this->MultiCell(23.4, $height, $pref, 1, "L", false, 0, "", "", true, 0, false, true, $height, "M");
                    // 市町村
                    $this->MultiCell(34.5, $height, $addr, 1, "L", false, 0, "", "", true, 0, false, true, $height, "M");
                    // 寄付日
                    $this->MultiCell(32.0, $height, date('Y年n月j日', strtotime($donationDetail['payment_date'])), 1, "R", false, 0, "", "", true, 0, false, true, $height, "M");
                    $this->Ln();
                    ++$no;
                }
            }
            ++$count;
            if ($count > 137) {
                break;
            }
        }
        if ($this->GetY() < 250) {
            $baseY = $this->GetY();
            $this->Text(29.0, $baseY + 6.0, $this->targetYear . "年度寄付者名簿について");
            $this->Text(29.0, $baseY + 11.0, "①上に上げた一覧の寄付者の他に、匿名希望の方が" . $anonymous . "名いらっしゃいます");
            $this->Text(29.0, $baseY + 16.0, "②名簿の作成には最新の注意を払っておりますが、万が一の訂正がありましたら");
            $this->Text(29.0, $baseY + 21.0, "事務局までご一報くださいますようお願い申し上げます。");
            $this->Text(29.0, $baseY + 31.0, "認定NPO法人ふまねっと");
            $this->Text(29.0, $baseY + 36.0, "TEL 011-807-4667");
            $this->Text(29.0, $baseY + 41.0, "Mail info@1to3.jp");
        } else {
            $this->setPrintHeader(false);
            $this->AddPage('PORTRAIT', 'A4');
            $this->Text(29.0, 6.0, $this->targetYear . "年度寄付者名簿について");
            $this->Text(29.0, 11.0, "①上に上げた一覧の寄付者の他に、匿名希望の方が" . $anonymous . "名いらっしゃいます");
            $this->Text(29.0, 16.0, "②名簿の作成には最新の注意を払っておりますが、万が一の訂正がありましたら");
            $this->Text(29.0, 21.0, "事務局までご一報くださいますようお願い申し上げます。");
            $this->Text(29.0, 31.0, "認定NPO法人ふまねっと");
            $this->Text(29.0, 36.0, "TEL 011-807-4667");
            $this->Text(29.0, 41.0, "Mail info@1to3.jp");
        }

        return true;
    }

    /**
     * PDFファイルを出力する.
     *
     * @return string|mixed
     */
    public function outputPdf()
    {
        return $this->Output($this->getPdfFileName(), 'S');
    }

    /**
     * PDFファイル名を取得する
     *
     * @return string ファイル名
     */
    public function getPdfFileName()
    {
        if (!is_null($this->downloadFileName)) {
            return $this->downloadFileName;
        }
        $this->downloadFileName = self::OUT_PDF_FILE_NAME . Date('YmdHis') . ".pdf";

        return $this->downloadFileName;
    }

    public function Header()
    {
        $this->backupFont();
        $this->SetFont('', '', 10);
        $this->Text(18.0, 19.6, $this->targetYear . "年寄付者名簿(氏名順)敬称略");
        $this->Ln();
        $this->SetFont('', '', 9);
        // No
        $this->Cell(10.2, 4.8, "", 1, 0, "C", false, "", 0, false, "T", "M");
        // 氏名
        $this->Cell(72.0, 4.8, "氏名", 1, 0, "C", false, "", 0, false, "T", "M");
        // 都道府県
        $this->Cell(23.4, 4.8, "都道府県", 1, 0, "C", false, "", 0, false, "T", "M");
        // 市町村
        $this->Cell(34.5, 4.8, "市町村", 1, 0, "C", false, "", 0, false, "T", "M");
        // 寄付日
        $this->Cell(32.0, 4.8, "寄付日", 1, 0, "C", false, "", 0, false, "T", "M");
        $this->Ln();
        $this->restoreFont();
    }

    /**
     * フッターに発行日を出力する.
     */
    public function Footer()
    {
    }

    /**
     * 基準座標を設定する.
     *
     * @param int $x
     * @param int $y
     */
    protected function setBasePosition($x = null, $y = null)
    {
        // 現在のマージンを取得する
        $result = $this->getMargins();

        // 基準座標を指定する
        $actualX = is_null($x) ? $result['left'] : $x;
        $this->SetX($actualX);
        $actualY = is_null($y) ? $result['top'] : $y;
        $this->SetY($actualY);
    }

    /**
     * Font情報のバックアップ.
     */
    protected function backupFont()
    {
        // フォント情報のバックアップ
        $this->bakFontFamily = $this->FontFamily;
        $this->bakFontStyle = $this->FontStyle;
        $this->bakFontSize = $this->FontSizePt;
    }

    /**
     * Font情報の復元.
     */
    protected function restoreFont()
    {
        $this->SetFont($this->bakFontFamily, $this->bakFontStyle, $this->bakFontSize);
    }
}
