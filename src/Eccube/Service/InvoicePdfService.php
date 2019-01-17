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
 * Class InvoicePdfService.
 * Do export pdf function.
 */
class InvoicePdfService extends AbstractFPDIService
{
    // ====================================
    // 定数宣言
    // ====================================
    /** ダウンロードするPDFファイル名 */
    const OUT_PDF_FILE_NAME = 'invoice';

    /** FONT ゴシック */
    const FONT_GOTHIC = 'kozgopromedium';
    /** FONT 明朝 */
    const FONT_SJIS = 'kozminproregular';
    /** 1ページ最大行数 */
    const MAX_ROR_PER_PAGE = 21;

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
        $pdfFile = $this->app['config']['pdf_template_invoice'];
        $templateFilePath = __DIR__.'/../Resource/pdf/'.$pdfFile;
        $this->setSourceFile($templateFilePath);
        $BaseInfo = $this->app['eccube.repository.base_info']->get();

        foreach ($ordersData as $order) {
            $detailData = array();
            $totalPrice = 0;
            foreach ($order->getOrderDetails() as $orderDetail) {
                $detailData[$orderDetail->getProductName()]['Price'] = $orderDetail->getPrice();
                $detailData[$orderDetail->getProductName()]['Quantity'] = $orderDetail->getQuantity();
                $totalPrice += ($orderDetail->getPrice() * $orderDetail->getQuantity());
            }
            $row = 1;
            // ページ計算
            $this->pageMax = ((int) (count($detailData) / self::MAX_ROR_PER_PAGE)) + (((count($detailData) % self::MAX_ROR_PER_PAGE) == 0)?0:1);
            foreach ($detailData as $ProductName => $detail) {
                if ($row == 1) {
                    // PDFにページを追加する
                    $this->addPdfPage();
                    // 会員名
                    $this->lfText(23.6, 57.6, $order->getName01() . " " . $order->getName02(), 12);
                    // 請求年
                    $this->lfText(148.1, 42.2, date('Y'), 12);
                    // 請求月
                    $this->lfText(167.6, 42.2, date('m'), 12);
                    // 請求日
                    $this->lfText(180.3, 42.2, date('d'), 12);
                    // 請求元郵便番号
                    $this->lfText(122.2, 50.8, $BaseInfo->getZip01() . "-" . $BaseInfo->getZip02(), 12);
                    // 請求元住所
                    $this->lfText(117.9, 56.1, $BaseInfo->getAddr01() . $BaseInfo->getAddr02(), 12);
                    // 請求元会社名
                    $this->lfText(117.9, 63.5, $BaseInfo->getCompanyName(), 12);
                }
                // 商品名
                $this->lfText(23.9, 117.3 + (6.41 * ($row - 1)), $ProductName, 8);
                // 数量
                $this->lfText(99.8, 117.3 + (6.41 * ($row - 1)), number_format($detail['Price']), 8);
                // 単価
                $this->lfText(124.7, 117.3 + (6.41 * ($row - 1)), number_format($detail['Quantity']), 8);
                // 金額
                $this->lfText(144.3, 117.3 + (6.41 * ($row - 1)), number_format(($detail['Price'] * $detail['Quantity'])), 8);
                if ($row < self::MAX_ROR_PER_PAGE) {
                    ++$row;
                } else {
                    $row = 1;
                }
            }
            // 小計
            $this->lfText(144.3, 243.3, number_format($totalPrice), 12);
            // 消費税率
            $this->lfText(101.9, 248.9, number_format(8), 12);
            // 消費税
            $this->lfText(144.3, 248.9, number_format(round($totalPrice * 0.08)), 12);
            // 合計
            $this->lfText(144.3, 255.0, number_format($totalPrice + round($totalPrice * 0.08)), 12);
            // 合計金額
            $this->lfText(84.1, 100.2, number_format($totalPrice + round($totalPrice * 0.08)), 16);
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
     * グループ受注情報からPDFファイルを作成する.
     *
     * @param array $ordersData
     *
     * @return bool
     */
    public function makeGroupPdf(array $groupOrdersData)
    {
        // データが空であれば終了
        if (count($groupOrdersData) < 1) {
            return false;
        }
        // 発行日の設定
        $this->issueDate = '作成日: ' . date('Y年m月d日');
        // ダウンロードファイル名の初期化
        $this->downloadFileName = null;

        // テンプレートファイルを読み込む
        $pdfFile = $this->app['config']['pdf_template_invoice'];
        $templateFilePath = __DIR__.'/../Resource/pdf/'.$pdfFile;
        $this->setSourceFile($templateFilePath);
        $BaseInfo = $this->app['eccube.repository.base_info']->get();

        foreach ($groupOrdersData as $groupOrder) {
            $detailData = array();
            foreach ($groupOrder->getOrder() as $order) {
                foreach ($order->getOrderDetails() as $orderDetail) {
                    if (!isset($detailData[$orderDetail->getProductName()])) {
                        $detailData[$orderDetail->getProductName()]['Quantity'] = 0;
                        $detailData[$orderDetail->getProductName()]['Price'] = $orderDetail->getPrice();
                    }
                    $detailData[$orderDetail->getProductName()]['Quantity'] += $orderDetail->getQuantity();
                }
            }
            $row = 1;
            $totalPrice = 0;
            // ページ計算
            $this->pageMax = ((int) (count($detailData) / self::MAX_ROR_PER_PAGE)) + (((count($detailData) % self::MAX_ROR_PER_PAGE) == 0)?0:1);
            foreach ($detailData as $ProductName => $detail) {
                if ($row == 1) {
                    // PDFにページを追加する
                    $this->addPdfPage();
                    // 会員名
                    $this->lfText(23.6, 57.6, $groupOrder->getBillTo(), 12);
                    // 請求年
                    $this->lfText(148.1, 42.2, date('Y'), 12);
                    // 請求月
                    $this->lfText(167.6, 42.2, date('m'), 12);
                    // 請求日
                    $this->lfText(180.3, 42.2, date('d'), 12);
                    // 請求元郵便番号
                    $this->lfText(122.2, 50.8, $BaseInfo->getZip01() . "-" . $BaseInfo->getZip02(), 12);
                    // 請求元住所
                    $this->lfText(117.9, 56.1, $BaseInfo->getAddr01() . $BaseInfo->getAddr02(), 12);
                    // 請求元会社名
                    $this->lfText(117.9, 63.5, $BaseInfo->getCompanyName(), 12);
                }
                // 商品名
                $this->lfText(23.9, 117.3 + (6.41 * ($row - 1)), $ProductName, 8);
                // 数量
                $this->lfText(99.8, 117.3 + (6.41 * ($row - 1)), number_format($detail['Price']), 8);
                // 単価
                $this->lfText(124.7, 117.3 + (6.41 * ($row - 1)), number_format($detail['Quantity']), 8);
                // 金額
                $this->lfText(144.3, 117.3 + (6.41 * ($row - 1)), number_format(($detail['Price'] * $detail['Quantity'])), 8);
                $totalPrice += ($detail['Price'] * $detail['Quantity']);
                if ($row < self::MAX_ROR_PER_PAGE) {
                    ++$row;
                } else {
                    $row = 1;
                }
            }
            // 小計
            $this->lfText(144.3, 243.3, number_format($totalPrice), 12);
            // 消費税率
            $this->lfText(101.9, 248.9, number_format(8), 12);
            // 消費税
            $this->lfText(144.3, 248.9, number_format(round($totalPrice * 0.08)), 12);
            // 合計
            $this->lfText(144.3, 255.0, number_format($totalPrice + round($totalPrice * 0.08)), 12);
            // 合計金額
            $this->lfText(84.1, 100.2, number_format($totalPrice + round($totalPrice * 0.08)), 16);
        }

        return true;
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
