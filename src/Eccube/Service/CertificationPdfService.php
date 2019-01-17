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
 * Class CertificationPdfService.
 * Do export pdf function.
 */
class CertificationPdfService extends AbstractFPDIService
{
    // ====================================
    // 定数宣言
    // ====================================
    /** ダウンロードするPDFファイル名 */
    const OUT_PDF_FILE_NAME = 'certification';

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
        $this->SetMargins(15, 20);

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
    public function makePdf(array $customersData, $product = NULL)
    {
        // データが空であれば終了
        if (count($customersData) < 1) {
            return false;
        }
        // 発行日の設定
        $this->issueDate = '作成日: ' . date('Y年m月d日');
        // ページ計算
        $this->pageMax = ((int) (count($customersData) / self::MAX_ROR_PER_PAGE)) + (((count($customersData) % self::MAX_ROR_PER_PAGE) == 0)?0:1);
        // ダウンロードファイル名の初期化
        $this->downloadFileName = null;

        // テンプレートファイルを読み込む
        $pdfFile = $this->app['config']['pdf_template_certification'];
        $templateFilePath = __DIR__.'/../Resource/pdf/'.$pdfFile;
        $this->setSourceFile($templateFilePath);

        $row = 1;
        $col = 1;
        foreach ($customersData as $customerData) {
            if (($row == 1) && ($col == 1)) {
                // PDFにページを追加する
                $this->addPdfPage();
            }
            // サポーター
            if ($customerData->getCustomerBasicInfo()->getSupporterType() != '非サポータ') {
                $beforeSpacing = $this->getFontSpacing();
                $this->SetTextColor(255, 255, 255);
                $this->setFontSpacing(1.0);
                $this->lfText(10.5 + (102.9 * ($col - 1)), 19.0 + (60.5 * ($row - 1)), 'Supporter', 10);
                $this->SetTextColor(0);
                $this->setFontSpacing($beforeSpacing);
            }
            // 会員番号
            $this->lfText(42.9 + (102.9 * ($col - 1)), 30.2 + (60.5 * ($row - 1)), $customerData->getId(), 12, 'B');
            // プロフィール写真
            if (!is_null($customerData->getCustomerImages())) {
                if (0 < count($customerData->getCustomerImages())) {
                    $photoFile = $this->app['config']['image_save_realdir'].'/'.$customerData->getCustomerImages()[0]->getFileName();
                    if (file_exists($photoFile)) {
                        $this->Image($photoFile, 11.0 + (102.9 * ($col - 1)), 24.4 + (60.5 * ($row - 1)), 20.3);
                    }
                }
            }
            // 会員名
            $this->lfText(39.5 + (102.9 * ($col - 1)), 37.0 + (60.5 * ($row - 1)), $customerData->getName01() . " " . $customerData->getName02(), 22, 'B');
            // PINコード
            $this->lfText(72.4 + (102.9 * ($col - 1)), 51.4 + (60.5 * ($row - 1)), $customerData->getCustomerBasicInfo()->getCustomerPinCode(), 10);

            if (($row * $col) < self::MAX_ROR_PER_PAGE) {
                if ($col < 2) {
                    ++$col;
                } else {
                    ++$row;
                    $col = 1;
                }
            } else {
                $row = 1;
                $col = 1;
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

        // ページ情報
        $this->lfText(194.3, 7.6, '(' . $this->PageNo() . '/' . $this->pageMax . ')', 8);
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
