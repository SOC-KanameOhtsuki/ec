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

        $this->SetFont(self::FONT_GOTHIC);
        foreach ($customersData as $customerData) {
            // PDFにページを追加する
            $this->addPdfPage();
            // サポーター
            if ($customerData->getCustomerBasicInfo()->getSupporterType()->getId() == 2) {
                $imgFile = __DIR__.'/../Resource/pdf/supporter_mark.jpg';
                if (file_exists($imgFile)) {
                    $this->Image($imgFile, 10.0, 10.4, 27.5);
                }
            }
            // インストラクタ
            if ($customerData->getCustomerBasicInfo()->getInstructorType()->getId() == 1) {
                $imgFile = __DIR__.'/../Resource/pdf/instructor_3_mark.jpg';
                if (file_exists($imgFile)) {
                    $this->Image($imgFile, 39.1, 10.4, 27.5);
                }
            } else if ($customerData->getCustomerBasicInfo()->getInstructorType()->getId() == 2) {
                $imgFile = __DIR__.'/../Resource/pdf/instructor_2_mark.jpg';
                if (file_exists($imgFile)) {
                    $this->Image($imgFile, 39.1, 10.4, 27.5);
                }
            }
            // 年度
            $termInfos = $this->app['eccube.repository.master.term_info']->createQueryBuilder('t')
                    ->andWhere("t.valid_period_start <= '" . date('Y-m-d H:i:s') . "'")
                    ->andWhere("t.valid_period_end >= '" . date('Y-m-d H:i:s') . "'")
                    ->andWhere('t.del_flg = 0')
                    ->andWhere('t.valid_flg = 1')
                    ->addOrderBy('t.term_year', 'desc')
                    ->getQuery()
                    ->getResult();
            if ((!is_null($termInfos)) && (0 < count($termInfos))) {
                $currentTermYear = $termInfos[0]->getTermYear();
            } else if (date('m') < 4) {
                $currentTermYear = date('Y') - 1;
            } else {
                $currentTermYear = date('Y');
            }
            $bakFontStyle = $this->FontStyle;
            $bakFontSize = $this->FontSizePt;
            $this->SetFont('', 'B', 17);
            $this->SetXY(72.4, 14.8);
            $this->MultiCell(18.0, 8.0, $currentTermYear, 0, "C", false, 0, "", "", true, 2, false, true, 8.0, "T");
            $this->SetFont('', $bakFontStyle, $bakFontSize);
            // 会員番号
            $this->lfText(42.9, 28.0, $customerData->getId(), 12, 'B');
            // プロフィール写真
            if (!is_null($customerData->getCustomerImages())) {
                if (0 < count($customerData->getCustomerImages())) {
                    $photoFile = $this->app['config']['customer_image_save_realdir'].'/'.$customerData->getCustomerImages()[0]->getFileName();
                    if (file_exists($photoFile)) {
                        $this->Image($photoFile, 11.0, 23.8, 19.5);
                    }
                }
            }
            // QRコード
            $customerId = $customerData->getCustomerBasicInfo()->getCustomerNumber();
            $QrCode = null;
            if ((0 < strlen($customerId)) && (!is_null($customerId))) {
                $isQrCodeRegisted = false;
                if (!is_null($customerData->getCustomerQrs())) {
                    if (count($customerData->getCustomerQrs()) > 0) {
                        $QrCode = $customerData->getCustomerQrs()[0];
                    }
                }
                if (!is_null($QrCode)) {
                    if (file_exists($this->app['config']['customer_image_save_realdir'] . "/" . $QrCode->getFileName())) {
                        $isQrCodeRegisted = true;
                    }
                }
                if (!$isQrCodeRegisted) {
                    $qrCodeImg = file_get_contents($this->app['config']['qr_code_get_url'] . $customerId);
                    if ($qrCodeImg !== false) {
                        $fileName = date('mdHis').uniqid('_') . '.jpg';
                        if (file_put_contents($this->app['config']['customer_image_save_realdir'] . "/" . $fileName, $qrCodeImg) !== false) {
                            $QrCode = new \Eccube\Entity\CustomerQr();
                            $QrCode->setCustomer($customerData);
                            $QrCode->setFileName($fileName);
                            $QrCode->setRank(1);
                            $this->app['orm.em']->persist($QrCode);
                            $this->app['orm.em']->flush();
                            $isQrCodeRegisted = true;
                        };
                    }
                }
            }
            if ($isQrCodeRegisted) {
                $photoFile = $this->app['config']['customer_image_save_realdir'] . "/" . $QrCode->getFileName();
                $this->Image($photoFile, 82.3, 23.8, 9.0);
            }
            // 会員名
            $bakFontStyle = $this->FontStyle;
            $bakFontSize = $this->FontSizePt;
            $fontSize = 22;
            $this->SetFont('', 'B', $fontSize);
            while (11.5 < $this->getStringHeight(64.0, $customerData->getName01() . " " . $customerData->getName02())) {
                $this->SetFont('', 'B', --$fontSize);
            }
            $this->SetXY(30.5, 32.3);
            $this->MultiCell(64.0, 11.5, $customerData->getName01() . " " . $customerData->getName02(), 1, "C", false, 0, "", "", true, 0, false, true, 11.5, "M");
            // PINコード
            $this->lfText(72.4, 49.7, $customerData->getCustomerBasicInfo()->getCustomerPinCode(), 10);
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
