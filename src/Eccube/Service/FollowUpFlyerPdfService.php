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
 * Class FollowUpFlyerPdfService.
 * Do export pdf function.
 */
class FollowUpFlyerPdfService extends AbstractFPDIService
{
    // ====================================
    // 定数宣言
    // ====================================
    /** ダウンロードするPDFファイル名 */
    const OUT_PDF_FILE_NAME = 'follow_up_flyer';

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
        $pdfFile = $this->app['config']['pdf_template_follow_up_flyer'];
        $templateFilePath = __DIR__.'/../Resource/pdf/'.$pdfFile;
        $this->setSourceFile($templateFilePath);
        // PDFにページを追加する
        $this->addPdfPage();

        $this->SetFont(self::FONT_GOTHIC);
        $this->SetTextColor(255, 255, 255);
        $bakFontStyle = $this->FontStyle;
        $bakFontSize = $this->FontSizePt;
        $fontSize = 22;
        $this->SetFont('', 'B', $fontSize);
        while (14.7 < $this->getStringHeight(23.6, $flyer_data->getProductTraining()->getAddr01())) {
            --$fontSize;
            $this->SetFont('', 'B', $fontSize);
        }
        $this->Rotate(-10.0, 151.5, 15.0);
        $this->lfText(151.5, 15.0, "in", 20, 'B');
        $this->Rotate(10.0, 151.5, 15.0);
        $this->Rotate(-10.0, 157.9, 12.6);
        $this->SetXY(157.9, 12.6);
        $this->MultiCell(29.0, 14.7, $flyer_data->getProductTraining()->getAddr01(), 0, "C", false, 0, "", "", true, 0, false, true, 14.7, "M");
        $this->Rotate(10.0, 157.9, 12.6);
        $this->SetFont('', $bakFontStyle, $bakFontSize);
        $this->SetTextColor(0, 0, 0);

        // 講習会日
        $this->lfText(55.6, 70.5, $flyer_data->getProductTraining()->getTrainingDateStart()->format('Y年n月j日(') . $this->WeekDay[$flyer_data->getProductTraining()->getTrainingDateStart()->format('w')] . ')', 28, 'B');
        $this->lfText(140.0, 73.5, $flyer_data->getProductTraining()->getTrainingDateStart()->format('H:i～') . $flyer_data->getProductTraining()->getTrainingDateEnd()->format('H:i'), 20, 'B');
        // 場所
        $this->lfText(55.6, 84.2, $flyer_data->getProductTraining()->getPlace(), 28, 'B');
        // 住所
        $this->lfText(55.6, 94.4, "(" . $flyer_data->getProductTraining()->getAddr01() . $flyer_data->getProductTraining()->getAddr02() . ")", 17, 'B');
        // 内容
        $this->lfMultiText(49.0, 135.3, 145.0, 20.0, str_replace("　", "", $flyer_data->getProductTraining()->getProduct()->getDescriptionDetail()), 17, 'B');
        // 受講料
        $this->lfText(49.0, 132.2, ((0 < $flyer_data->getProductTraining()->getProduct()->getPrice02IncTaxMax())?number_format($flyer_data->getProductTraining()->getProduct()->getPrice02IncTaxMax()) . '円':"無料"), 17, 'B');
        // 持ち物
        $this->lfMultiText(49.0, 135.8, 145.0, 11.3, $flyer_data->getProductTraining()->getItem(), 17, 'B');
        // 定員
        $ProductClasses = $flyer_data->getProductTraining()->getProduct()->getProductClasses();
        $ProductClass = $ProductClasses[0];
        if ($ProductClass->getStockUnlimited()) {
            $this->lfText(49.0, 155.1, $ProductClass->getStock() . '名', 17, 'B');
        }
        // 期限
        if (is_null($flyer_data->getProductTraining()->getAcceptLimitDate())) {
            $limit = date('Y/m/d', strtotime($flyer_data->getProductTraining()->getTrainingDateStart()->format('Y/m/d') . " -24 day"));
            $holidayRepository = new HolidayRepository();
            while($holidayRepository->isHoliday($limit)) {
                $limit = date('Y/m/d', strtotime($limit . " -1 day"));
            }
        } else {
            $limit = $flyer_data->getProductTraining()->getAcceptLimitDate()->format('Y/m/d');
        }
        // 主催
        $this->lfText(49.0, 189.2, "認定 NPO法人ふまねっと", 17, 'B');
        // 共催
        // 定員
        $bakFontStyle = $this->FontStyle;
        $bakFontSize = $this->FontSizePt;
        $this->SetFont('', "B", 13);
        $this->SetXY(49.0, 160.4);
        $line_height = $this->getStringHeight(28.0, "あ");
        $this->MultiCell(28.0, $line_height, date('n月j日(', strtotime($limit)) . $this->WeekDay[date('w', strtotime($limit))] . ")", 0, 'C', false, 0,  "", "", true, 0, false, true, $line_height, "M");
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
