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
use Japanese\Holiday\Repository as HolidayRepository;

/**
 * Class FaxAcceptPdfService.
 * Do export pdf function.
 */
class FlyerPdfService extends AbstractFPDIService
{
    // ====================================
    // 定数宣言
    // ====================================
    /** ダウンロードするPDFファイル名 */
    const OUT_PDF_FILE_NAME = 'flyer';

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
    public function makePdf($flyer_data)
    {
        // データが空であれば終了
        if (is_null($flyer_data)) {
            return false;
        }
        // 発行日の設定
        $this->issueDate = '作成日: ' . date('Y年m月d日');
        // ダウンロードファイル名の初期化
        $this->downloadFileName = null;
        $BaseInfo = $this->app['eccube.repository.base_info']->get();

        // テンプレートファイルを読み込む
        $pdfFile = $this->app['config']['pdf_template_flyer1'];
        $templateFilePath = __DIR__.'/../Resource/pdf/'.$pdfFile;
        $this->setSourceFile($templateFilePath);
        // PDFにページを追加する
        $this->addPdfPage();
        // 講習会種別
        $this->lfMultiText(14.5, 28.2, 121.0, 20.5, $flyer_data->getProductTraining()->getTrainingType()->getName(), 30, 'B');
        $this->lfText(14.5, 52.0, "のご案内", 29, 'B');
        // 講習会日
        $this->lfText(48.4, 92.5, date('m月d日(', strtotime($flyer_data->getProductTraining()->getTrainingDateStart())) . $this->WeekDay[date('w', strtotime($flyer_data->getProductTraining()->getTrainingDateStart()))] . ')', 18, 'B');
        $this->lfText(48.4, 102.3, date('H:i～', strtotime($flyer_data->getProductTraining()->getTrainingDateStart())) . date('H:i', strtotime($flyer_data->getProductTraining()->getTrainingDateEnd())), 12, 'B');
        // 場所
        $this->lfText(122.3, 92.5, $flyer_data->getProductTraining()->getPlace(), 18, 'B');
        // 住所
        $this->lfText(122.3, 102.3, $flyer_data->getProductTraining()->getPref()->getName() . $flyer_data->getProductTraining()->getAddr01() . $flyer_data->getProductTraining()->getAddr02(), 12, 'B');
        // 対象
        $this->lfMultiText(34.8, 122.3, 88.0, 10.0, $flyer_data->getProductTraining()->getTarget(), 13, 'B');
        // 内容
        $this->lfMultiText(34.8, 135.3, 88.0, 10.0, $flyer_data->getProductTraining()->getProduct()->getDescriptionDetail(), 13, 'B');
        // 受講料
        $this->lfText(34.8, 148.6, number_format($flyer_data->getProductTraining()->getProduct()->getPrice02IncTaxMax()) . '円', 13, 'B');
        // 年会費
        $this->lfText(41.7, 177.2, date('Y'), 11, 'B');
        $membership = $this->app['config']['default_membership'];
        try {
            $membershipInfo = $this->app['eccube.repository.product']->getQueryBuilderBySearchDataForAdmin(array('membership_year' => date('Y')))->getQuery()->getSingleResult();
            $membership = $membershipInfo->getPrice02IncTaxMax();
        } catch (\Exception $e) {
        }
        $this->lfText(69.0, 177.1, number_format($membership), 11, 'B');
        // 持ち物
        $this->lfMultiText(34.8, 188.8, 88.0, 10.0, $flyer_data->getProductTraining()->getItem(), 13, 'B');
        // 期限
        $limit = date('Y/m/d', strtotime($flyer_data->getProductTraining()->getTrainingDateStart() . " -24 day"));
        file_put_contents("/var/www/ec_trial2/app/log/debug.log", "limit:" . $limit . "\n", FILE_APPEND);
        $holidayRepository = new HolidayRepository();
        while($holidayRepository->isHoliday($limit)) {
            $limit = date('Y/m/d', strtotime($limit . " -1 day"));
        }
        file_put_contents("/var/www/ec_trial2/app/log/debug.log", "limit:" . $limit . "\n", FILE_APPEND);
        $this->lfText(81.8, 202.5, date('m月d日', strtotime($limit)), 13, 'B');
        // 定員
        $ProductClasses = $flyer_data->getProductTraining()->getProduct()->getProductClasses();
        $ProductClass = $ProductClasses[0];
        if ($ProductClass->getStockUnlimited()) {
            $this->lfText(72.6, 213.9, '※定員' . $ProductClass->getStock() . '名(先着順)', 11, 'B');
        }
        // 会社情報電話番号
        $this->lfText(39.5, 236.4, $BaseInfo->getTel01() . '-' . $BaseInfo->getTel02() . '-' . $BaseInfo->getTel03(), 14, 'B');
        // 会社情報Fax
        $this->lfText(85.9, 236.4, $BaseInfo->getFax01() . '-' . $BaseInfo->getFax02() . '-' . $BaseInfo->getFax03(), 14, 'B');
        // 会社情報郵便番号住所
        $this->lfText(33.3, 248.4, $BaseInfo->getZip01() . '-' . $BaseInfo->getZip02() . ' ' . $BaseInfo->getPref()->getName() . $BaseInfo->getAddr01() . $BaseInfo->getAddr02(), 11, 'B');

        // テンプレートファイルを読み込む
        $pdfFile = $this->app['config']['pdf_template_flyer2'];
        $templateFilePath = __DIR__.'/../Resource/pdf/'.$pdfFile;
        $this->setSourceFile($templateFilePath);
        // PDFにページを追加する
        $this->addPdfPage();
        // 年会費
        $this->lfText(163.0, 36.2, date('Y'), 11, 'B');
        $this->lfText(190.0, 36.2, number_format($membership), 11, 'B');
        // 記入日
        $this->lfText(152.0, 123.5, date('Y'), 13, 'B');
        $this->lfText(169.5, 123.5, date('m'), 13, 'B');
        $this->lfText(181.0, 123.5, date('d'), 13, 'B');
        // 受講日
        $this->lfText(50.0, 218.5, date('m月d日(', strtotime($flyer_data->getProductTraining()->getTrainingDateStart())) . $this->WeekDay[date('w', strtotime($flyer_data->getProductTraining()->getTrainingDateStart()))] . ')', 15, 'B');
        // 場所
        $this->lfText(125.3, 218.5, $flyer_data->getProductTraining()->getPlace(), 15, 'B');
        // 会員数
        $this->lfText(79.2, 252.1, date('Y'), 11, 'B');
        $this->lfText(95.9, 252.1, '3', 11, 'B');
        $this->lfText(106.8, 252.1, '31', 11, 'B');
        $this->lfText(127.2, 252.1, '200', 11, 'B');
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
     * PDFへの折り返しテキスト書き込み
     *
     * @param int    $x     X座標
     * @param int    $y     Y座標
     * @param int    $w     幅
     * @param int    $h     高さ
     * @param string $text  テキスト
     * @param int    $size  フォントサイズ
     * @param string $style フォントスタイル
     */
    protected function lfMultiText($x, $y, $w, $h, $text, $size = 0, $style = '')
    {
        // 退避
        $bakFontStyle = $this->FontStyle;
        $bakFontSize = $this->FontSizePt;

        $this->SetFont('', $style, $size);
        $this->MultiCell($w, $h, $text, 0, 'J',false, 1, $x + $this->baseOffsetX, $y + $this->baseOffsetY);

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
