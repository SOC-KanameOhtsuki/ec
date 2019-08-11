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
 * Class FaxAcceptPdfService.
 * Do export pdf function.
 */
class FaxAcceptPdfService extends AbstractFPDIService
{
    // ====================================
    // 定数宣言
    // ====================================
    /** ダウンロードするPDFファイル名 */
    const OUT_PDF_FILE_NAME = 'fax_accept';

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
    public function makePdf(array $customersData, array $orders, $product)
    {
        // データが空であれば終了
        if (count($customersData) < 1) {
            return false;
        }
        // 発行日の設定
        $this->issueDate = '作成日: ' . date('Y年m月d日');
        // ダウンロードファイル名の初期化
        $this->downloadFileName = null;

        // テンプレートファイルを読み込む
        $pdfFile = $this->app['config']['pdf_template_fax_accept'];
        $templateFilePath = __DIR__.'/../Resource/pdf/'. $pdfFile;
        $this->setSourceFile($templateFilePath);
        $BaseInfo = $this->app['eccube.repository.base_info']->get();

        $this->SetFont(self::FONT_GOTHIC);
        $this->SetTextColor(53, 53, 53);
        foreach ($customersData as $customerData) {
            // PDFにページを追加する
            $this->addPdfPage();
            // Fax番号
            $fax = '';
            foreach ($customerData->getCustomerAddresses() as $AddresInfo) {
                if ($AddresInfo->getMailTo()->getId() == 2) {
                    $fax = ((is_null($AddresInfo->getFax01())&&is_null($AddresInfo->getFax02())&&is_null($AddresInfo->getFax02()))?"":$AddresInfo->getFax01() . '-' . $AddresInfo->getFax02() . '-' . $AddresInfo->getFax03());
                    break;
                }
            }
            $this->lfText(36.1, 19.0, $fax, 15, '');
            // 会員名
            $this->lfText(18.3, 28.1, $customerData->getName01() . $customerData->getName02() . '様', 15, '');
            $this->lfText(33.8, 76.7, $customerData->getName01() . $customerData->getName02() . '様', 12, '');
            if ($product->hasProductTraining()) {
                // 講習会種別
                $bakFontStyle = $this->FontStyle;
                $bakFontSize = $this->FontSizePt;
                $this->SetFont('', '', 12);
                $this->SetXY(38.6, 45.8);
                $this->MultiCell(67.8, 7.0, $product->getProductTraining()->getTrainingType()->getName(), 0, "C", false, 0, "", "", true, 0, false, true, 7.0, "T");
                $this->SetFont('', $bakFontStyle, $bakFontSize);
                // 受講日
                $startDate = $product->getProductTraining()->getTrainingDateStart()->format('Y年n月j日(') . $this->WeekDay[$product->getProductTraining()->getTrainingDateStart()->format('w')] . ')';
                $endDate = $product->getProductTraining()->getTrainingDateEnd()->format('Y年n月j日(') . $this->WeekDay[$product->getProductTraining()->getTrainingDateEnd()->format('w')] . ')';
                $this->lfText(33.8, 82.2, $startDate . $product->getProductTraining()->getTrainingDateStart()->format(' G:i～') . (($startDate==$endDate)?'':$endDate . ' ') . $product->getProductTraining()->getTrainingDateEnd()->format('G:i'), 12, '');
                // 受付開始時間
                $this->lfText(143.3, 82.2, date('G:i', strtotime($product->getProductTraining()->getTrainingDateStart()->format('Y-m-d H:i:s') . " -30 minute")), 12, '');
                // 場所
                $this->lfText(33.8, 87.7, $product->getProductTraining()->getPlace(), 12, '');
                // 住所
                $this->lfText(33.8, 93.2, $product->getProductTraining()->getPref()->getName() . $product->getProductTraining()->getAddr01() . $product->getProductTraining()->getAddr02(), 12, '');
                // 持ち物
                $this->lfText(33.8, 98.7, $product->getProductTraining()->getItem(), 12, '');
            }
            // 備考
            $bakFontStyle = $this->FontStyle;
            $bakFontSize = $this->FontSizePt;
            $this->SetFont('', '', 12);
            $this->SetXY(33.8, 100.2);
            $this->MultiCell(88.4, 5.5, str_replace("　", "", $product->getDescriptionDetail()), 0, "L", false, 0, "", "", true, 0, false, true, 17.0, "T");
            $this->SetFont('', $bakFontStyle, $bakFontSize);
            // 受講料
            $price = 0;
            if (isset($orders[$customerData->getId()])) {
                $price = $orders[$customerData->getId()]->getPaymentTotal();
            } else {
                $price = $product->getPrice02IncTaxMax();
            }
            if (0 < $price) {
                $bakFontStyle = $this->FontStyle;
                $bakFontSize = $this->FontSizePt;
                $this->SetFont('', '', 12);
                $this->SetXY(32.5, 157.0);
                $this->MultiCell(24.4, 7.0, number_format($price), 0, "R", false, 0, "", "", true, 0, false, true, 7.0, "T");
                $this->SetFont('', $bakFontStyle, $bakFontSize);
            } else {
                $this->lfText(33.8, 161.0, '無料', 12, '');
                $this->Rect(58.7, 157.7, 4.3, 5.0, 'F', null, array(255, 255, 255));
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
