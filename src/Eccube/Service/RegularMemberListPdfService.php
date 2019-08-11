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
 * Class RegularMemberListPdfService.
 * Do export pdf function.
 */
class RegularMemberListPdfService extends AbstractFPDIService
{
    // ====================================
    // 定数宣言
    // ====================================
    /** ダウンロードするPDFファイル名 */
    const OUT_PDF_FILE_NAME = 'regular_member_list';

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
        $this->SetMargins(5, 22);
        $this->SetAutoPageBreak(true, 10);

        $this->setHeaderMargin(10);
        $this->setHeaderFont(array(self::FONT_SJIS, '', 8));
        $this->setPrintHeader(true);

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
    public function makePdf(array $customersData, $anonymousEnabled = false)
    {
        // データが空であれば終了
        if (count($customersData) < 1) {
            return false;
        }
        // 発行日の設定
        $this->issueDate = '作成日: ' . date('Y年m月d日');
       // ダウンロードファイル名の初期化
        $this->downloadFileName = null;

        // PDFにページを追加する
        $this->AddPage('LANDSCAPE', 'A4');
        foreach ($customersData as $customerData) {
            // サポータ資格
            $supporter_type = ($customerData->getCustomerBasicInfo()->getSupporterType() == '非サポータ'?'':'サポータ');
            // インストラクタ資格
            $instructor_type = ($customerData->getCustomerBasicInfo()->getInstructorType() == '非インストラクタ'?'':$customerData->getCustomerBasicInfo()->getInstructorType());
            $Out = false;
            foreach ($customerData->getCustomerAddresses() as $AddresInfo) {
                if ($AddresInfo->getMailTo()->getId() == 2) {
                    // 都道府県
                    $pref = (is_null($AddresInfo->getAddr01())?"":$AddresInfo->getPref());
                    // 市町村
                    $addr = (is_null($AddresInfo->getAddr01())?"":$AddresInfo->getAddr01());
                    $Out = true;
                    break;
                }
            }
            if (!$Out) {
                // 都道府県
                $pref = (is_null($customerData->getAddr01())?"":$customerData->getPref());
                // 市町村
                $addr = (is_null($customerData->getAddr01())?"":$customerData->getAddr01());
            }
            // 勤務先
            if ($customerData->getCustomerBasicInfo()->getAnonymousCompany()->getId() == 2 || !$anonymousEnabled) {
                $company = (is_null($customerData->getCompanyName())?"":$customerData->getCompanyName());
            } else {
                $company = '';
            }

            $height = $this->getStringHeight(20, $customerData->getName01()) + 0.3;
            if ($height < $this->getStringHeight(20, $customerData->getName02()) + 0.3) {
                $height = $this->getStringHeight(20, $customerData->getName02()) + 0.3;
            }
            if ($height < $this->getStringHeight(25, $customerData->getKana01()) + 0.3) {
                $height = $this->getStringHeight(25, $customerData->getKana01()) + 0.3;
            }
            if ($height < $this->getStringHeight(25, $customerData->getKana02()) + 0.3) {
                $height = $this->getStringHeight(25, $customerData->getKana02()) + 0.3;
            }
            if ($height < $this->getStringHeight(35, $addr) + 0.3) {
                $height = $this->getStringHeight(35, $addr) + 0.3;
            }
            if ($height < $this->getStringHeight(40, $company) + 0.3) {
                $height = $this->getStringHeight(40, $company) + 0.3;
            }

            // 会員番号
            $this->Cell(25.0, $height, $customerData->getId(), 1, 0, "C", false, "", 0, false, "T", "M");
            // 会員名(姓)
            $this->MultiCell(20.0, $height, $customerData->getName01(), 1, "L", false, 0, "", "", true, 0, false, true, $height, "M");
            // 会員名(名)
            $this->MultiCell(20.0, $height, $customerData->getName02(), 1, "L", false, 0, "", "", true, 0, false, true, $height, "M");
            // 会員名(セイ)
            $this->MultiCell(25.0, $height, $customerData->getKana01(), 1, "L", false, 0, "", "", true, 0, false, true, $height, "M");
            // 会員名(メイ)
            $this->MultiCell(25.0, $height, $customerData->getKana02(), 1, "L", false, 0, "", "", true, 0, false, true, $height, "M");
            // サポータ資格
            $this->Cell(25.0, $height, $supporter_type, 1, 0, "C", false, "", 0, false, "T", "M");
            // インストラクタ資格
            $this->Cell(25.0, $height, $instructor_type, 1, 0, "C", false, "", 0, false, "T", "M");
            // 都道府県
            $this->Cell(20.0, $height, $pref, 1, 0, "C", false, "", 0, false, "T", "M");
            // 市町村
            $this->MultiCell(35.0, $height, $addr, 1, "L", false, 0, "", "", true, 0, false, true, $height, "M");
            // 勤務先
            $this->MultiCell(40.0, $height, $company, 1, "L", false, 0, "", "", true, 0, false, true, $height, "M");
            // PINコード
            $this->Cell(25.0, $height, (is_null($customerData->getCustomerBasicInfo()->getCustomerPinCode())?"":$customerData->getCustomerBasicInfo()->getCustomerPinCode()), 1, 0, "C", false, "", 0, false, "T", "M");
            $this->Ln();
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
        // 会員番号
        $this->Cell(25.0, 12, "会員番号", 1, 0, "C", false, "", 0, false, "T", "M");
        // 会員名(姓)
        $this->Cell(20.0, 12, "姓", 1, 0, "C", false, "", 0, false, "T", "M");
        // 会員名(名)
        $this->Cell(20.0, 12, "名", 1, 0, "C", false, "", 0, false, "T", "M");
        // 会員名(セイ)
        $this->Cell(25.0, 12, "セイ", 1, 0, "C", false, "", 0, false, "T", "M");
        // 会員名(メイ)
        $this->Cell(25.0, 12, "メイ", 1, 0, "C", false, "", 0, false, "T", "M");
        // サポータ資格
        $this->MultiCell(25.0, 12, "サポータ資格", 1, "C", false, 0, "", "", true, 0, false, true, 12, "M");
        // インストラクタ資格
        $this->MultiCell(25.0, 12, "インストラクタ資格", 1, "L", false, 0, "", "", true, 0, false, true, 12, "M");
        // 都道府県
        $this->Cell(20.0, 12, "都道府県", 1, 0, "C", false, "", 0, false, "T", "M");
        // 市町村
        $this->Cell(35.0, 12, "市町村", 1, 0, "C", false, "", 0, false, "T", "M");
        // 勤務先
        $this->Cell(40.0, 12, "勤務先", 1, 0, "C", false, "", 0, false, "T", "M");
        // PINコード
        $this->Cell(25.0, 12, "PINコード", 1, 0, "C", false, "", 0, false, "T", "M");
        $this->Ln();
        // ページ情報
        $this->lfText(285.0, 7.6, $this->PageNo() . ' Page', 8);
        $this->restoreFont();
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
