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
 * Class DonationCertificatePdfService.
 * Do export pdf function.
 */
class DonationCertificatePdfService extends AbstractFPDIService
{
    // ====================================
    // 定数宣言
    // ====================================
    /** ダウンロードするPDFファイル名 */
    const OUT_PDF_FILE_NAME = 'donation_certificate';

    /** FONT ゴシック */
    const FONT_GOTHIC = 'kozgopromedium';
    /** FONT 明朝 */
    const FONT_SJIS = 'kozminproregular';
    /** 1ページ最大行数 */
    const MAX_ROR_PER_PAGE = 8;

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

    /** 発行日 @var string */
    private $issueDate = '';

    /** 最大ページ @var string */
    private $pageMax = '';

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
        $this->SetMargins(0, 0);

        // ヘッダーの出力を無効化
        $this->setPrintHeader(false);

        // フッターの出力を無効化
        $this->setPrintFooter(true);
        $this->setFooterMargin();
        $this->setFooterFont(array(self::FONT_GOTHIC, '', 8));
    }

    /**
     * 顧客情報からPDFファイルを作成する.
     *
     * @param array $customersData
     *
     * @return bool
     */
    public function makePdf(array $customersData, $searchData)
    {
        if ((!isset($searchData['target_year'])) || (strlen($searchData['target_year']) < 1)) {
            return false;
        }
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
        $getDonationDetailSql .= " AND dtb_order.payment_date >= '" . $searchData['target_year'] . "-01-01 00:00:00'";
        $getDonationDetailSql .= " AND dtb_order.payment_date <= '" . $searchData['target_year'] . "-12-31 23:59:59';";
        $donationDetailDatas = array();
        $donationDetails = $this->em->getConnection()->fetchAll($getDonationDetailSql);
        foreach ($donationDetails as $donationDetail) {
            if (!isset($donationDetailDatas[$donationDetail['customer_id']])) {
                $donationDetailDatas[$donationDetail['customer_id']] = array();
            }
            $donationDetailDatas[$donationDetail['customer_id']][] = $donationDetail;
        }
        // 発行日の設定
        $this->issueDate = '作成日: ' . date('Y年m月d日');
        // ダウンロードファイル名の初期化
        $this->downloadFileName = null;

        // テンプレートファイルを読み込む
        $pdfFile = $this->app['config']['pdf_template_donation_certificate'];
        $templateFilePath = __DIR__.'/../Resource/pdf/'. $pdfFile;
        $this->setSourceFile($templateFilePath);
        $BaseInfo = $this->app['eccube.repository.base_info']->get();

        // PDFにページを追加する
        $this->addPdfPage();
/*
        $this->SetFont(self::FONT_GOTHIC);
        $this->SetTextColor(53, 53, 53);
        foreach ($customersData as $customerData) {
            $zip_code = '';
            $addr = '';
            $company = '';
            $name = '';
            foreach ($customerData->getCustomerAddresses() as $AddresInfo) {
                if ($AddresInfo->getAddressType()->getId() == $to) {
                    // 郵便番号
                    $zip_code = "〒" . (is_null($AddresInfo->getZip01())?"":$AddresInfo->getZip01() . (is_null($AddresInfo->getZip02())?'':'-')) . (is_null($AddresInfo->getZip02())?"":$AddresInfo->getZip02());
                    // 住所
                    if (strlen((is_null($AddresInfo->getPref())?"":$AddresInfo->getPref())) > 0 && strlen((is_null($AddresInfo->getAddr01())?"":$AddresInfo->getAddr01())) > 0  && strlen((is_null($AddresInfo->getAddr02())?"":$AddresInfo->getAddr02())) > 0 ) {
                        $addr = (is_null($AddresInfo->getPref())?"":$AddresInfo->getPref()->getName()) . (is_null($AddresInfo->getAddr01())?"":$AddresInfo->getAddr01()) . (is_null($AddresInfo->getAddr02())?"":$AddresInfo->getAddr02());
                    }
                    // 勤務先
                    if (strlen((is_null($AddresInfo->getCompanyName())?"":$AddresInfo->getCompanyName())) > 0 ) {
                        $company = $AddresInfo->getCompanyName();
                    }
                    // 会員名
                    if (strlen((is_null($AddresInfo->getName01())?"":$AddresInfo->getName01())) > 0 && strlen((is_null($AddresInfo->getName02())?"":$AddresInfo->getName02())) > 0 ) {
                        $name = $AddresInfo->getName01() . " " . $AddresInfo->getName02() . ' 様';
                    }
                    $Out = true;
                    break;
                }
            }
            if (!$Out) {
                // 郵便番号
                $zip_code = "〒" . (is_null($customerData->getZip01())?"":$customerData->getZip01() . (is_null($customerData->getZip02())?'':'-')) . (is_null($customerData->getZip02())?"":$customerData->getZip02());
                // 住所
                if (strlen((is_null($customerData->getPref())?"":$customerData->getPref())) > 0 && strlen((is_null($customerData->getAddr01())?"":$customerData->getAddr01())) > 0  && strlen((is_null($customerData->getAddr02())?"":$customerData->getAddr02())) > 0 ) {
                    $addr = (is_null($customerData->getPref())?"":$customerData->getPref()->getName()) . (is_null($customerData->getAddr01())?"":$customerData->getAddr01()) . (is_null($customerData->getAddr02())?"":$customerData->getAddr02());
                }
                // 勤務先
                if (strlen((is_null($customerData->getCompanyName())?"":$customerData->getCompanyName())) > 0 ) {
                    $company = $customerData->getCompanyName();
                }
                // 会員名
                if (strlen((is_null($customerData->getName01())?"":$customerData->getName01())) > 0 && strlen((is_null($customerData->getName02())?"":$customerData->getName02())) > 0 ) {
                    $name = $customerData->getName01() . " " . $customerData->getName02() . ' 様';
                }
            }
            // 会員名&勤務先
            if ((strlen($name) > 0) && (strlen($company) > 0)) {
                foreach($donationDetailDatas[$customerData->getId()] as $donationDetail) {
                    // PDFにページを追加する
                    $this->addPdfPage();
                    // 寄付金
                    $row[] = $donationDetail['payment_date'];
                    // 受領年月日
                    $row[] = $donationDetail['price'];
                    // 郵便番号
                    $this->lfText(26.2, 26.0, $zip_code, 11, 'B');
                    $current_row = 26.0;
                    // 住所
                    if (strlen($addr) > 0) {
                        $bakFontStyle = $this->FontStyle;
                        $bakFontSize = $this->FontSizePt;
                        $this->SetFont('', 'B', 9);
                        $min_height = $this->getStringHeight(70.0, "北") + 0.3;
                        $height = $this->getStringHeight(70.0, $addr) + 0.3;
                        $this->SetXY(26.2, $current_row);
                        $this->MultiCell(70.0, $min_height, $addr, 0, "L", false, 0, "", "", true, 0, false, true, $height, "M");
                        $this->SetFont('', $bakFontStyle, $bakFontSize);
                        $current_row += $height;
                    }
                    $bakFontStyle = $this->FontStyle;
                    $bakFontSize = $this->FontSizePt;
                    $this->SetFont('', 'B', 9);
                    $min_height = $this->getStringHeight(70.0, "会") + 0.3;
                    $height = $this->getStringHeight(70.0, "会") + 0.3;
                    $this->SetXY(26.2, $current_row);
                    $current_row += $height;
                    $this->MultiCell(70.0, $min_height, $company, 0, "L", false, 0, "", "", true, 0, false, true, $height, "M");
                    $this->SetFont('', 'B', 14);
                    $min_height = $this->getStringHeight(70.0, "あ") + 0.3;
                    $height = $this->getStringHeight(70.0, "あ") + 0.3;
                    $this->SetXY(26.2, $current_row);
                    $current_row += $height;
                    $this->MultiCell(70.0, $min_height, $name, 0, "L", false, 0, "", "", true, 0, false, true, $height, "M");
                    $this->SetFont('', $bakFontStyle, $bakFontSize);
                }
            } else if (strlen($company) > 0) {
                foreach($donationDetailDatas[$customerData->getId()] as $donationDetail) {
                    // PDFにページを追加する
                    $this->addPdfPage();
                    // 寄付金
                    $row[] = $donationDetail['payment_date'];
                    // 受領年月日
                    $row[] = $donationDetail['price'];
                    // 郵便番号
                    $this->lfText(26.2, 26.0, $zip_code, 11, 'B');
                    $current_row = 26.0;
                    // 住所
                    if (strlen($addr) > 0) {
                        $bakFontStyle = $this->FontStyle;
                        $bakFontSize = $this->FontSizePt;
                        $this->SetFont('', 'B', 9);
                        $min_height = $this->getStringHeight(70.0, "北") + 0.3;
                        $height = $this->getStringHeight(70.0, $addr) + 0.3;
                        $this->SetXY(26.2, $current_row);
                        $this->MultiCell(70.0, $min_height, $addr, 0, "L", false, 0, "", "", true, 0, false, true, $height, "M");
                        $this->SetFont('', $bakFontStyle, $bakFontSize);
                        $current_row += $height;
                    }
                    $bakFontStyle = $this->FontStyle;
                    $bakFontSize = $this->FontSizePt;
                    $this->SetFont('', 'B', 14);
                    $min_height = $this->getStringHeight(70.0, "会") + 0.3;
                    $height = $this->getStringHeight(70.0, "会") + 0.3;
                    $this->SetXY(26.2, $current_row);
                    $current_row += $height;
                    $this->MultiCell(70.0, $min_height, $company, 0, "L", false, 0, "", "", true, 0, false, true, $height, "M");
                    $this->SetFont('', $bakFontStyle, $bakFontSize);
                }
            } else if (strlen($name) > 0) {
                foreach($donationDetailDatas[$customerData->getId()] as $donationDetail) {
                    // PDFにページを追加する
                    $this->addPdfPage();
                    // 寄付金
                    $row[] = $donationDetail['payment_date'];
                    // 受領年月日
                    $row[] = $donationDetail['price'];
                    // 郵便番号
                    $this->lfText(26.2, 26.0, $zip_code, 11, 'B');
                    $current_row = 26.0;
                    // 住所
                    if (strlen($addr) > 0) {
                        $bakFontStyle = $this->FontStyle;
                        $bakFontSize = $this->FontSizePt;
                        $this->SetFont('', 'B', 9);
                        $min_height = $this->getStringHeight(70.0, "北") + 0.3;
                        $height = $this->getStringHeight(70.0, $addr) + 0.3;
                        $this->SetXY(26.2, $current_row);
                        $this->MultiCell(70.0, $min_height, $addr, 0, "L", false, 0, "", "", true, 0, false, true, $height, "M");
                        $this->SetFont('', $bakFontStyle, $bakFontSize);
                        $current_row += $height;
                    }
                    $bakFontStyle = $this->FontStyle;
                    $bakFontSize = $this->FontSizePt;
                    $this->SetFont('', 'B', 14);
                    $min_height = $this->getStringHeight(70.0, "あ") + 0.3;
                    $height = $this->getStringHeight(70.0, "あ") + 0.3;
                    $this->SetXY(26.2, $current_row);
                    $current_row += $height;
                    $this->MultiCell(70.0, $min_height, $name, 0, "L", false, 0, "", "", true, 0, false, true, $height, "M");
                    $this->SetFont('', $bakFontStyle, $bakFontSize);
                }
            }
        }
*/
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

    /**
     * フッターに発行日を出力する.
     */
    public function Footer()
    {
        $this->Cell(0, 0, $this->issueDate, 0, 0, 'R');
    }

    /**
     * 作成するPDFのテンプレートファイルを指定する.
     */
    protected function addPdfPage()
    {
        // ページを追加
        $this->AddPage();

        // テンプレートに使うテンプレートファイルのページ番号を取得
        $tplIdx = $this->importPage(1);

        // テンプレートに使うテンプレートファイルのページ番号を指定
        $this->useTemplate($tplIdx, null, null, null, null, true);
    }

    /**
     * PDFへのテキスト書き込み
     *
     * @param int    $x     X座標
     * @param int    $y     Y座標
     * @param string $text  テキスト
     * @param int    $size  フォントサイズ
     * @param string $style フォントスタイル
     */
    protected function lfText($x, $y, $text, $size = 0, $style = '')
    {
        // 退避
        $bakFontStyle = $this->FontStyle;
        $bakFontSize = $this->FontSizePt;

        $this->SetFont('', $style, $size);
        $this->Text($x + $this->baseOffsetX, $y + $this->baseOffsetY, $text);

        // 復元
        $this->SetFont('', $bakFontStyle, $bakFontSize);
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
