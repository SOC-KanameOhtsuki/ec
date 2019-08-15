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
 * Class InstructorFlyerPdfService.
 * Do export pdf function.
 */
class InstructorFlyerPdfService extends AbstractFPDIService
{
    // ====================================
    // 定数宣言
    // ====================================
    /** ダウンロードするPDFファイル名 */
    const OUT_PDF_FILE_NAME = 'instructor_flyer';

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
        // ダウンロードファイル名の初期化
        $this->downloadFileName = null;
        $BaseInfo = $this->app['eccube.repository.base_info']->get();

        // テンプレートファイルを読み込む
        $pdfFile = $this->app['config']['pdf_template_instructor_flyer1'];
        $templateFilePath = __DIR__.'/../Resource/pdf/'.$pdfFile;
        $this->setSourceFile($templateFilePath);
        $this->SetFont(self::FONT_GOTHIC);
        // PDFにページを追加する
        $this->addPdfPage();
        $this->SetTextColor(35, 31, 32);
        // 講習会種別
        if ($flyer_data->getProductTraining()->getTrainingType()->getId() == 2) {
            $this->lfText(177.0, 31.2, "３", 39.5, 'B');
        } else if ($flyer_data->getProductTraining()->getTrainingType()->getId() == 3) {
            $this->lfText(177.0, 31.2, "２", 39.5, 'B');
        } else if ($flyer_data->getProductTraining()->getTrainingType()->getId() == 4) {
            $this->lfText(177.0, 31.2, "１", 39.5, 'B');
        }
        // 地域
        $this->SetTextColor(50, 50, 50);
        $fontSize = 22;
        $this->SetFont('', 'B', $fontSize);
        while (8.6 < $this->getStringHeight(26.9, $flyer_data->getProductTraining()->getPref())) {
            --$fontSize;
            $this->SetFont('', 'B', $fontSize);
        }
        $this->SetXY(15.5, 91.3);
        $this->MultiCell(26.9, 8.6, $flyer_data->getProductTraining()->getPref(), 0, "C", false, 0, "", "", true, 0, false, true, 8.7, "B");
        $fontSize = 22;
        $this->SetFont('', 'B', $fontSize);
        while (8.6 < $this->getStringHeight(26.9, $flyer_data->getProductTraining()->getAddr01())) {
            --$fontSize;
            $this->SetFont('', 'B', $fontSize);
        }
        $this->SetXY(15.5, 99.8);
        $this->MultiCell(26.9, 8.6, $flyer_data->getProductTraining()->getAddr01(), 0, "C", false, 0, "", "", true, 0, false, true, 8.7, "T");
        // 講習会日
        $this->SetFont('', '', 11.0);
        $this->SetXY(62.0, 90.0);
        $this->MultiCell(60.0, 5.1, mb_convert_kana($flyer_data->getProductTraining()->getTrainingDateStart()->format('Y年'), 'A', 'UTF-8'), 0, "L", false, 0, "", "", true, 0, false, true, 5.1, "M");
        $this->SetFont('', 'B', 19.0);
        $this->SetXY(62.0, 95.4);
        $this->MultiCell(60.0, 8.4, mb_convert_kana($flyer_data->getProductTraining()->getTrainingDateStart()->format('n月j日(') . $this->WeekDay[$flyer_data->getProductTraining()->getTrainingDateStart()->format('w')] . ')', 'A', 'UTF-8'), 0, "L", false, 0, "", "", true, 0, false, true, 8.4, "M");
        $this->SetFont('', 'B', 11.0);
        $this->SetXY(62.0, 103.6);
        $this->MultiCell(60.0, 5.1, mb_convert_kana($flyer_data->getProductTraining()->getTrainingDateStart()->format('H：i～') . $flyer_data->getProductTraining()->getTrainingDateEnd()->format('H：i'), 'A', 'UTF-8'), 0, "L", false, 0, "", "", true, 0, false, true, 5.1, "M");
        // 場所
        $font_size = 22.0;
        $this->SetFont('', 'B', $font_size);
        while (13.5 < $this->getStringHeight(65.0, $flyer_data->getProductTraining()->getPlace())) {
            $this->SetFont('', 'B', --$font_size);
        }
        $this->SetXY(131.5, 90.0);
        $this->MultiCell(65.0, 13.5, $flyer_data->getProductTraining()->getPlace(), 0, "L", false, 0, "", "", true, 0, false, true, 13.5, "M");
        // 住所
        $font_size = 18.0;
        $prace_addr = "(" . $flyer_data->getProductTraining()->getPref()->getName() . $flyer_data->getProductTraining()->getAddr01() . $flyer_data->getProductTraining()->getAddr02() . ")";
        $this->SetFont('', 'B', $font_size);
        while (5.1 < $this->getStringHeight(65.0, $prace_addr)) {
            $this->SetFont('', 'B', --$font_size);
        }
        $this->SetXY(131.5, 103.6);
        $this->MultiCell(65.0, 5.1, $prace_addr, 0, "L", false, 0, "", "", true, 0, false, true, 5.1, "M");
        // 内容
        $this->lfMultiText(34.6, 127.9, 92.0, 27.0, str_replace("　", "", $flyer_data->getProductTraining()->getProduct()->getDescriptionDetail()), 11, '', "L", "T");
        // 対象
        $this->lfMultiText(34.6, 155.7, 92.0, 16.0, $flyer_data->getProductTraining()->getTarget(), 11, '');
        // 受講料
        $this->lfText(34.6, 184.5, (0<($flyer_data->getProductTraining()->getProduct()->getPrice02IncTaxMax())?number_format($flyer_data->getProductTraining()->getProduct()->getPrice02IncTaxMax()) . '円':'無料'), 11, '');
        // 持ち物
        $this->lfMultiText(34.6, 212.6, 92.0, 10.4, $flyer_data->getProductTraining()->getItem(), 11, '');
        // 定員
        $ProductClasses = $flyer_data->getProductTraining()->getProduct()->getProductClasses();
        $ProductClass = $ProductClasses[0];
        $this->lfText(34.6, 231.7, $ProductClass->getStock() . '名', 11, '');
        // 協力
        $collaborator = "";
        if (!is_null($flyer_data->getProductTraining()->getCollaborators())) {
            $collaborator = $flyer_data->getProductTraining()->getCollaborators();
        }
        if (strlen($collaborator) > 0) {
            $this->lfText(12.5, 265.2, "【協　力】", 12, 'B');
            $this->lfText(33.0, 264.9, $collaborator, 15, 'B');
        }

        // テンプレートファイルを読み込む
        $pdfFile = $this->app['config']['pdf_template_instructor_flyer2'];
        $templateFilePath = __DIR__.'/../Resource/pdf/'.$pdfFile;
        $this->setSourceFile($templateFilePath);
        // PDFにページを追加する
        $this->addPdfPage();
        $this->SetTextColor(255, 255, 255);
        // 講習会種別
        if ($flyer_data->getProductTraining()->getTrainingType()->getId() == 2) {
            $this->lfText(107.6, 96.1, "３", 15.5, 'B');
        } else if ($flyer_data->getProductTraining()->getTrainingType()->getId() == 3) {
            $this->lfText(107.6, 96.1, "２", 15.5, 'B');
        } else if ($flyer_data->getProductTraining()->getTrainingType()->getId() == 4) {
            $this->lfText(107.6, 96.1, "１", 15.5, 'B');
        }
        $this->SetTextColor(50, 50, 50);
        // 記入日
        $this->SetFont('', '', 13.0);
        $this->SetXY(149.5, 126.4);
        $this->MultiCell(15.0, 6.0, date('Y'), 0, "R", false, 0, "", "", true, 0, false, true, 6.0, "M");
        $this->SetXY(169.3, 126.4);
        $this->MultiCell(7.0, 6.0, date('n'), 0, "R", false, 0, "", "", true, 0, false, true, 6.0, "M");
        $this->SetXY(180.2, 126.4);
        $this->MultiCell(7.0, 6.0, date('j'), 0, "R", false, 0, "", "", true, 0, false, true, 6.0, "M");
        // 受講日
        $trainingDate = $flyer_data->getProductTraining()->getTrainingDateStart()->format('Y-m-d H:i');
        $this->lfText(57.0, 225.8, date('Y', strtotime($trainingDate)), 15, '');
        $this->SetFont('', '', 15.0);
        $this->SetXY(74.4, 221.6);
        $this->MultiCell(12.4, 7.2, date('n', strtotime($trainingDate)), 0, "R", false, 0, "", "", true, 0, false, true, 7.2, "M");
        $this->SetXY(91.4, 221.6);
        $this->MultiCell(12.4, 7.2, date('j', strtotime($trainingDate)), 0, "R", false, 0, "", "", true, 0, false, true, 7.2, "M");
        $this->lfText(113.0, 225.4, $this->WeekDay[date('w', strtotime($trainingDate))], 15, '');
        // 場所
        $font_size = 15;
        $this->SetFont('', '', $font_size);
        while (7.2 < $this->getStringHeight(41.0, $flyer_data->getProductTraining()->getPlace())) {
            $this->SetFont('', '', --$font_size);
        }
        $this->SetXY(148.1, 221.6);
        $this->MultiCell(41.0, 7.2, $flyer_data->getProductTraining()->getPlace(), 0, "C", false, 0, "", "", true, 0, false, true, 7.2, "M");
        $this->SetFont('', $bakFontStyle, $bakFontSize);

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
    protected function lfMultiText($x, $y, $w, $h, $text, $size = 0, $style = '', $h_style = 'L', $v_style = 'M')
    {
        // 退避
        $bakFontStyle = $this->FontStyle;
        $bakFontSize = $this->FontSizePt;

        $this->SetFont('', $style, $size);
        $this->SetXY($x, $y);
        $this->MultiCell($w, $h, $text, 0, $h_style, false, 0,  "", "", true, 0, false, true, $h, $v_style);

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
