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
 * Class PaymentPdfService.
 * Do export pdf function.
 */
class PaymentPdfService extends AbstractFPDIService
{
    // ====================================
    // 定数宣言
    // ====================================
    /** ダウンロードするPDFファイル名 */
    const OUT_PDF_FILE_NAME = 'payment';

    /** FONT ゴシック */
    const FONT_GOTHIC = 'kozgopromedium';
    /** FONT 明朝 */
    const FONT_SJIS = 'kozminproregular';
    /** 1ページ最大行数 */
    const MAX_ROR_PER_PAGE = 1;

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
     * 受注情報からPDFファイルを作成する.
     *
     * @param array $ordersData
     *
     * @return bool
     */
    public function makePdf(array $ordersData)
    {
        // データが空であれば終了
        if (count($ordersData) < 1) {
            return false;
        }
        // 発行日の設定
        $this->issueDate = '作成日: ' . date('Y年m月d日');
        // ページ計算
        $this->pageMax = ((int) (count($ordersData) / self::MAX_ROR_PER_PAGE)) + (((count($ordersData) % self::MAX_ROR_PER_PAGE) == 0)?0:1);
        // ダウンロードファイル名の初期化
        $this->downloadFileName = null;

        // テンプレートファイルを読み込む
        $pdfFile = $this->app['config']['pdf_template_payment'];
        $templateFilePath = __DIR__.'/../Resource/pdf/'.$pdfFile;
        $this->setSourceFile($templateFilePath);
        $BaseInfo = $this->app['eccube.repository.base_info']->get();

        foreach ($ordersData as $order) {
            // PDFにページを追加する
            $this->addPdfPage();
            $detailData = array();
            $totalPrice = 0;
            foreach ($order->getOrderDetails() as $orderDetail) {
                $detailData[$orderDetail->getProductName()]['Price'] = $orderDetail->getPrice();
                $detailData[$orderDetail->getProductName()]['Quantity'] = $orderDetail->getQuantity();
                $totalPrice += ($orderDetail->getPrice() * $orderDetail->getQuantity());
            }
            // 口座
            $beforeSpacing = $this->getFontSpacing();
            $this->setFontSpacing(2.8);
            $this->lfText(19.6 + ((5 - strlen($this->app['config']['payment_pdf_account_symbol1'])) * 4.90), 58.7, $this->app['config']['payment_pdf_account_symbol1'], 10, 'B');
            $this->lfText(47.9, 58.7, $this->app['config']['payment_pdf_account_symbol2'], 10, 'B');
            $this->lfText(55.4 + ((7 - strlen($this->app['config']['payment_pdf_account_no'])) * 4.90), 58.7, $this->app['config']['payment_pdf_account_no'], 10, 'B');
            $this->lfText(151.4 + ((5 - strlen($this->app['config']['payment_pdf_account_symbol1'])) * 4.90), 58.7, $this->app['config']['payment_pdf_account_symbol1'], 10, 'B');
            $this->lfText(178.8, 58.7, $this->app['config']['payment_pdf_account_symbol2'], 10, 'B');
            $this->lfText(156.8 + ((7 - strlen($this->app['config']['payment_pdf_account_no'])) * 4.90), 69.2, $this->app['config']['payment_pdf_account_no'], 10, 'B');
            $this->setFontSpacing($beforeSpacing);
            // 加入者名
            $this->lfText(26.7, 67.8, $this->app['config']['payment_pdf_subscriber'], 10, 'B');
            $this->lfText(152.3, 79.1, $this->app['config']['payment_pdf_subscriber'], 10, 'B');
            // 通信欄
            $this->lfText(27.1, 74.7, "振込手数料はかかりません", 10, 'B');
            if (count($detailData) < 2) {
                foreach($detailData as $productName => $itemDetail) {
                    $this->lfText(26.2, 82.1, $productName . " " . number_format(round($itemDetail['Price'] * $itemDetail['Quantity'] * 0.08)) . "円", 10, 'B');
                    break;
                }
            } else {
                foreach($detailData as $productName => $itemDetail) {
                    $this->lfText(26.2, 82.1, $productName . " " . number_format(round($itemDetail['Price'] * $itemDetail['Quantity'] * 0.08)) . "円", 10, 'B');
                    break;
                }
                $this->lfText(26.2, 87.2, "他" . count($detailData) . "点", 10, 'B');
                $this->lfText(26.2, 92.2, "合計　　" . number_format($totalPrice + round($totalPrice * 0.08)) . "円", 10, 'B');
            }
            $this->lfText(90.0, 104.3, "会員番号(", 10, 'B');
            $this->lfText(106.0 + ((11 - strlen($order->getCustomer()->getId())) * 2.15), 104.3, $order->getCustomer()->getId(), 10, 'B');
            $this->lfText(130.0, 104.3, ")", 10, 'B');
            // 会員情報
            $this->lfText(33.4, 121.9, $order->getName01() . "　" . $order->getName02(), 12, 'B');
            $this->lfText(36.9, 110.5, $order->getZip01(), 9, 'B');
            $this->lfText(45.4, 110.5, $order->getZip02(), 9, 'B');
            $this->lfText(28.1, 116.0, $order->getAddr01() . $order->getAddr02(), 10, 'B');
            $this->lfText(69.8, 127.5, $order->getTel01(), 8, 'B');
            $this->lfText(79.7, 127.5, $order->getTel02(), 8, 'B');
            $this->lfText(91.0, 127.5, $order->getTel03(), 8, 'B');
            $this->lfText(152.4, 113.1, $order->getName01() . " " . $order->getName02() . " 様", 12, 'B');
            $this->lfText(153.6, 96.6, '〒' . $order->getZip01() . "-" . $order->getZip02(), 9, 'B');
            $this->lfText(151.7, 103.1, $order->getAddr01() . $order->getAddr02(), 10, 'B');
            // 金額
            $outputPrice = $totalPrice + round($totalPrice * 0.08);
            $beforeSpacing = $this->getFontSpacing();
            $this->setFontSpacing(2.8);
            $this->lfText(95.8 + ((8 - strlen($outputPrice)) * 4.90), 58.7, $outputPrice, 10, 'B');
            $this->lfText(151.4 + ((8 - strlen($outputPrice)) * 4.90), 90.9, $outputPrice, 10, 'B');
            $this->setFontSpacing($beforeSpacing);
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
