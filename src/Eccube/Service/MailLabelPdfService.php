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
 * Class MailLabelPdfService.
 * Do export pdf function.
 */
class MailLabelPdfService extends AbstractFPDIService
{
    // ====================================
    // 定数宣言
    // ====================================
    /** ダウンロードするPDFファイル名 */
    const OUT_PDF_FILE_NAME = 'mail_label';

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

    /** 発行日 @var string */
    private $issueDate = '';

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
         $this->SetFont(self::FONT_SJIS);

        // PDFの余白(上左右)を設定
        $this->SetMargins(0, 0);

        // ヘッダーの出力を無効化
        $this->setPrintHeader(false);

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
    public function makePdf(array $customersData)
    {
        // データが空であれば終了
        if (count($customersData) < 1) {
            return false;
        }
        // 発行日の設定
        $this->issueDate = '作成日: ' . date('Y年m月d日');
        // ダウンロードファイル名の初期化
        $this->downloadFileName = null;

        $total = 0;
        $page_total = 0;
        $row_index = 0;
        $col_index = 0;
        $page = 0;
        foreach ($customersData as $customerData) {
            $Out = false;
            if (($total % 14) == 0) {
                // PDFにページを追加する
                $this->addPage('PORTRAIT', 'A4');
                $page_total = 0;
                $col_index = 0;
                $row_index = 0;
                ++$page;
            } else if (($page_total % 7) == 0) {
                ++$col_index;
                $row_index = 0;
            } else {
                ++$row_index;
            }
            ++$total;
            ++$page_total;
            $row_adjuster = (35.6 * $row_index);
            $col_adjuster = (85.1 * $col_index);
            $zip_code = '';
            $addr = '';
            $company = '';
            $name = '';
            foreach ($customerData->getCustomerAddresses() as $AddresInfo) {
                if ($AddresInfo->getMailTo()->getId() == 2) {
                    // 郵便番号
                    $zip_code = "〒" . (is_null($AddresInfo->getZip01())?"":$AddresInfo->getZip01()) . (is_null($AddresInfo->getZip02())?"":$AddresInfo->getZip02());
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
                $zip_code = "〒" . (is_null($customerData->getZip01())?"":$customerData->getZip01()) . (is_null($customerData->getZip02())?"":$customerData->getZip02());
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
            // 郵便番号
            $this->lfText(26.2 + $col_adjuster, 26.0 + $row_adjuster, $zip_code, 11, 'B');
            $current_row = 26.0 + $row_adjuster;
            // 住所
            if (strlen($addr) > 0) {
                $bakFontStyle = $this->FontStyle;
                $bakFontSize = $this->FontSizePt;
                $this->SetFont('', 'B', 9);
                $min_height = $this->getStringHeight(70.0, "北") + 0.3;
                $height = $this->getStringHeight(70.0, $addr) + 0.3;
                $this->SetXY(26.2 + $col_adjuster, $current_row);
                $this->MultiCell(70.0, $min_height, $addr, 0, "L", false, 0, "", "", true, 0, false, true, $height, "M");
                $this->SetFont('', $bakFontStyle, $bakFontSize);
                $current_row += $height;
            }
            // 会員名&勤務先
            if ((strlen($name) > 0) && (strlen($company) > 0)) {
                $bakFontStyle = $this->FontStyle;
                $bakFontSize = $this->FontSizePt;
                $this->SetFont('', 'B', 9);
                $min_height = $this->getStringHeight(70.0, "会") + 0.3;
                $height = $this->getStringHeight(70.0, "会") + 0.3;
                $this->SetXY(26.2 + $col_adjuster, $current_row);
                $current_row += $height;
                $this->MultiCell(70.0, $min_height, $company, 0, "L", false, 0, "", "", true, 0, false, true, $height, "M");
                $this->SetFont('', 'B', 14);
                $min_height = $this->getStringHeight(70.0, "あ") + 0.3;
                $height = $this->getStringHeight(70.0, "あ") + 0.3;
                $this->SetXY(26.2 + $col_adjuster, $current_row);
                $current_row += $height;
                $this->MultiCell(70.0, $min_height, $name, 0, "L", false, 0, "", "", true, 0, false, true, $height, "M");
                $this->SetFont('', $bakFontStyle, $bakFontSize);
            } else if (strlen($company) > 0) {
                $bakFontStyle = $this->FontStyle;
                $bakFontSize = $this->FontSizePt;
                $this->SetFont('', 'B', 14);
                $min_height = $this->getStringHeight(70.0, "会") + 0.3;
                $height = $this->getStringHeight(70.0, "会") + 0.3;
                $this->SetXY(26.2 + $col_adjuster, $current_row);
                $current_row += $height;
                $this->MultiCell(70.0, $min_height, $company, 0, "L", false, 0, "", "", true, 0, false, true, $height, "M");
                $this->SetFont('', $bakFontStyle, $bakFontSize);
            } else if (strlen($name) > 0) {
                $bakFontStyle = $this->FontStyle;
                $bakFontSize = $this->FontSizePt;
                $this->SetFont('', 'B', 14);
                $min_height = $this->getStringHeight(70.0, "あ") + 0.3;
                $height = $this->getStringHeight(70.0, "あ") + 0.3;
                $this->SetXY(26.2 + $col_adjuster, $current_row);
                $current_row += $height;
                $this->MultiCell(70.0, $min_height, $name, 0, "L", false, 0, "", "", true, 0, false, true, $height, "M");
                $this->SetFont('', $bakFontStyle, $bakFontSize);
            }
            // 会員番号
            if (!is_null($customerData->getCustomerBasicInfo()->getCustomerNumberOld()) ){
                $oldCustomerId = '';
                if (strlen($customerData->getCustomerBasicInfo()->getCustomerNumberOld()) < 6) {
                    $oldCustomerId = intval($customerData->getCustomerBasicInfo()->getCustomerNumberOld());
                } else {
                    $oldCustomerId = intval(substr($customerData->getCustomerBasicInfo()->getCustomerNumberOld(),
                                            strlen($customerData->getCustomerBasicInfo()->getCustomerNumberOld()) - 5));
                }
                $this->lfText(89.4 + $col_adjuster, 51.7 + $row_adjuster, "ID " . $oldCustomerId, 9, 'B');
            }
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

    /**
     * フッターに発行日を出力する.
     */
    public function Footer()
    {
        $this->Cell(0, 0, $this->issueDate, 0, 0, 'R');
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
