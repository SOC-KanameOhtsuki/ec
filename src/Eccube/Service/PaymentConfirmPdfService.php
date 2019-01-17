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
 * Class PaymentConfirmPdfService.
 * Do export pdf function.
 */
class PaymentConfirmPdfService extends AbstractFPDIService
{
    // ====================================
    // 定数宣言
    // ====================================
    /** ダウンロードするPDFファイル名 */
    const OUT_PDF_FILE_NAME = 'payment_confirm';

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
    public function makePdf(array $customersData, $product)
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
        $pdfFile = $this->app['config']['pdf_template_payment_confirm'];
        $templateFilePath = __DIR__.'/../Resource/pdf/'.$pdfFile;
        $this->setSourceFile($templateFilePath);
        $BaseInfo = $this->app['eccube.repository.base_info']->get();

        foreach ($customersData as $customerData) {
            // PDFにページを追加する
            $this->addPdfPage();
            // Fax番号
            $this->lfText(61.0, 13.7, $customerData->getFax01() . '-' . $customerData->getFax02() . '-' . $customerData->getFax03(), 15, 'B');
            // 会員名
            $this->lfText(19.4, 23.1, $customerData->getName01() . $customerData->getName02() . '様', 15, 'B');
            $this->lfText(40.4, 81.0, $customerData->getName01() . $customerData->getName02() . '様', 9, 'B');
            if ($product->hasProductTraining()) {
                // 講習会種別
                $this->lfText(39.9, 52.3, $product->getProductTraining()->getTrainingType()->getName(), 11, 'B');
                // 受講日
                $this->lfText(40.4, 86.4, date('Y年m月d日(', strtotime($product->getProductTraining()->getTrainingDateStart())) . $this->WeekDay[date('w', strtotime($product->getProductTraining()->getTrainingDateStart()))] . date(') H:i～', strtotime($product->getProductTraining()->getTrainingDateStart())) . date('H:i', strtotime($product->getProductTraining()->getTrainingDateEnd())), 9);
                // 場所
                $this->lfText(40.4, 91.7, $product->getProductTraining()->getPlace(), 9, 'B');
                // 住所
                $this->lfText(40.4, 97.5, $product->getProductTraining()->getPref()->getName() . $product->getProductTraining()->getAddr01() . $product->getProductTraining()->getAddr02(), 9, 'B');
                // 持ち物
                $this->lfText(40.4, 103.4, $product->getProductTraining()->getItem(), 9, 'B');
            }
            // 備考
            $this->lfText(40.4, 109.2, $product->getDescriptionDetail(), 9, 'B');
            // 会社情報電話番号
            $this->lfText(30.8, 170.4, $BaseInfo->getTel01() . '-' . $BaseInfo->getTel02() . '-' . $BaseInfo->getTel03(), 11, 'B');
            // 会社情報Fax
            $this->lfText(79.8, 170.4, $BaseInfo->getFax01() . '-' . $BaseInfo->getFax02() . '-' . $BaseInfo->getFax03(), 11, 'B');
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
