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
 * Class SupporterFlyerPdfService.
 * Do export pdf function.
 */
class SupporterFlyerPdfService extends AbstractFPDIService
{
    // ====================================
    // 定数宣言
    // ====================================
    /** ダウンロードするPDFファイル名 */
    const OUT_PDF_FILE_NAME = 'supporter_flyer';

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
        $pdfFile = $this->app['config']['pdf_template_supporter_flyer1'];
        $templateFilePath = __DIR__.'/../Resource/pdf/'.$pdfFile;
        $this->setSourceFile($templateFilePath);
        // PDFにページを追加する
        $this->addPdfPage();
        // 地域
        $idx = 0;
        $this->SetFont(self::FONT_GOTHIC);
        $this->SetTextColor(255, 255, 255);
        $bakFontStyle = $this->FontStyle;
        $bakFontSize = $this->FontSizePt;
        $fontSize = 22;
        $this->SetFont('', 'B', $fontSize);
        while (8.7 < $this->getStringHeight(27.0, $flyer_data->getProductTraining()->getPref())) {
            --$fontSize;
            $this->SetFont('', 'B', $fontSize);
        }
        $this->SetXY(16.0, 94.0);
        $this->MultiCell(29.0, 8.7, $flyer_data->getProductTraining()->getPref(), 0, "C", false, 0, "", "", true, 0, false, true, 8.7, "M");
        $fontSize = 22;
        $this->SetFont('', 'B', $fontSize);
        while (8.7 < $this->getStringHeight(27.0, $flyer_data->getProductTraining()->getAddr01())) {
            --$fontSize;
            $this->SetFont('', 'B', $fontSize);
        }
        $this->SetXY(16.0, 102.4);
        $this->MultiCell(29.0, 8.7, $flyer_data->getProductTraining()->getAddr01(), 0, "C", false, 0, "", "", true, 0, false, true, 8.7, "M");
        $this->SetFont('', $bakFontStyle, $bakFontSize);
        $this->SetTextColor(43, 37, 40);
        // 講習会日
        $this->lfText(65.0, 96.0, $flyer_data->getProductTraining()->getTrainingDateStart()->format('n月j日(') . $this->WeekDay[$flyer_data->getProductTraining()->getTrainingDateStart()->format('w')] . ')', 18, 'B');
        $this->lfText(65.0, 104.8, $flyer_data->getProductTraining()->getTrainingDateStart()->format('H:i～') . $flyer_data->getProductTraining()->getTrainingDateEnd()->format('H:i'), 12, 'B');
        // 場所
        $this->lfText(136.0, 96.0, $flyer_data->getProductTraining()->getPlace(), 15, 'B');
        // 住所
        $this->lfText(136.0, 104.8, $flyer_data->getProductTraining()->getPref()->getName() . $flyer_data->getProductTraining()->getAddr01() . $flyer_data->getProductTraining()->getAddr02(), 10, 'B');
        // 内容
        $this->lfMultiText(35.4, 122.3, 92.0, 10.0, str_replace("　", "", $flyer_data->getProductTraining()->getProduct()->getDescriptionDetail()), 11, '');
        // 対象
        $this->lfMultiText(35.4, 139.0, 92.0, 10.0, $flyer_data->getProductTraining()->getTarget(), 11, '');
        // 講師
        $this->lfText(35.4, 162.0, $flyer_data->getProductTraining()->getLecturer(), 11, '');
        // 受講料
        $this->lfText(35.4, 174.3, (0<$flyer_data->getProductTraining()->getProduct()->getPrice02IncTaxMax()?number_format($flyer_data->getProductTraining()->getProduct()->getPrice02IncTaxMax()) . '円':'無料'), 11, '');
        // 持ち物
        $this->lfMultiText(35.4, 197.7, 92.0, 10.0, $flyer_data->getProductTraining()->getItem(), 11, '');
        // 定員
        $ProductClasses = $flyer_data->getProductTraining()->getProduct()->getProductClasses();
        $ProductClass = $ProductClasses[0];
        $this->lfText(35.4, 217.4, $ProductClass->getStock() . '名', 11, '');
        // 協力
        $collaborator = "";
        if (!is_null($flyer_data->getProductTraining()->getCollaborators())) {
            $collaborator = $flyer_data->getProductTraining()->getCollaborators();
        }
        if (strlen($collaborator) > 0) {
            $this->lfText(13.3, 242.6, "【協　力】", 13, 'B');
            $this->lfText(35.4, 242.6, $collaborator, 13, 'B');
        }

        // テンプレートファイルを読み込む
        $pdfFile = $this->app['config']['pdf_template_supporter_flyer2'];
        $templateFilePath = __DIR__.'/../Resource/pdf/'.$pdfFile;
        $this->setSourceFile($templateFilePath);
        // PDFにページを追加する
        $this->addPdfPage();
        // 記入日
        $this->lfText(152.0, 130.6, date('Y'), 13, 'B');
        $this->lfText(169.5, 130.6, date('n'), 13, 'B');
        $this->lfText(180.5, 130.6, date('j'), 13, 'B');
        // 受講日
        $trainingDate = $flyer_data->getProductTraining()->getTrainingDateStart()->format('Y-m-d H:i');
        $this->lfText(57.0, 225.8, date('Y', strtotime($trainingDate)), 15, 'B');
        $this->lfText(78.0, 225.8, date('n', strtotime($trainingDate)), 15, 'B');
        $this->lfText(97.0, 225.8, date('j', strtotime($trainingDate)), 15, 'B');
        $this->lfText(113.0, 225.8, $this->WeekDay[date('w', strtotime($trainingDate))], 15, 'B');
        // 場所
        $this->lfText(148.1, 227.1, $flyer_data->getProductTraining()->getPlace(), 9, 'B');

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
        $this->SetXY($x, $y);
        $line_height = $this->getStringHeight($w, "あ");
        $this->MultiCell($w, $line_height, $text, 0, 'L', false, 0,  "", "", true, 0, false, true, $h, "M");

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
