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
 * Class LectureFlyerPdfService.
 * Do export pdf function.
 */
class LectureFlyerPdfService extends AbstractFPDIService
{
    // ====================================
    // 定数宣言
    // ====================================
    /** ダウンロードするPDFファイル名 */
    const OUT_PDF_FILE_NAME = 'lecture_flyer';

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
        $pdfFile = $this->app['config']['pdf_template_lecture_flyer'];
        $templateFilePath = __DIR__.'/../Resource/pdf/'.$pdfFile;
        $this->setSourceFile($templateFilePath);
        // PDFにページを追加する
        $this->addPdfPage();
        // 講習会日
        $this->SetTextColor(6, 50, 29);
        $this->lfText(15.5, 79.0, $flyer_data->getProductTraining()->getTrainingDateStart()->format('Y年n月j日(') . $this->WeekDay[$flyer_data->getProductTraining()->getTrainingDateStart()->format('w')] . ')', 40);
        $this->lfText(24.5, 97.4, $flyer_data->getProductTraining()->getTrainingDateStart()->format('G時i分～') . $flyer_data->getProductTraining()->getTrainingDateEnd()->format('G時i分') . "(受付開始：" .  date('G時i分', strtotime($flyer_data->getProductTraining()->getTrainingDateStart()->format('Y-m-d H:i:s') . " -30 minute")) . ")", 18, 'B');
        // 場所
        $bakFontStyle = $this->FontStyle;
        $bakFontSize = $this->FontSizePt;
        $fontSize = 60;
        $this->SetFont('', 'B', $fontSize);
        while (12.0 < $this->getStringHeight(130.0, $flyer_data->getProductTraining()->getAddr01())) {
            --$fontSize;
            $this->SetFont('', 'B', $fontSize);
        }
        $this->SetXY(34.8, 106.5);
        $this->MultiCell(130.0, 12.0, $flyer_data->getProductTraining()->getPlace(), 0, "L", false, 0, "", "", true, 0, false, true, 14.7, "T");
        // 住所
        $this->lfText(57.7, 133.6, $flyer_data->getProductTraining()->getAddr01() . $flyer_data->getProductTraining()->getAddr02(), 20, 'B');
        $this->SetTextColor(33, 72, 53);
        // 講師
        $this->lfText(34.8, 143.6, $flyer_data->getProductTraining()->getLecturer(), 17, 'B');
        // 受講料
        $this->lfText(34.8, 185.4, ((0 <$flyer_data->getProductTraining()->getProduct()->getPrice02IncTaxMax())?number_format($flyer_data->getProductTraining()->getProduct()->getPrice02IncTaxMax()) . '円':"無料"), 13, 'B');
        // 定員
        $ProductClasses = $flyer_data->getProductTraining()->getProduct()->getProductClasses();
        if ($ProductClasses) {
            if ($ProductClasses[0]->getStockUnlimited()) {
                $ProductClass = $ProductClasses[0];
            }
        }
        if ($ProductClass) {
            $this->lfText(34.8, 192.8, $ProductClass->getStock() . '名', 13, 'B');
        }
        // 持ち物
        $this->lfMultiText(34.8, 193.8, 88.0, 10.0, $flyer_data->getProductTraining()->getItem(), 13, 'B');
        // 期限
        if (is_null($flyer_data->getProductTraining()->getAcceptLimitDate())) {
            $holidayRepository = new HolidayRepository();
            while($holidayRepository->isHoliday($limit)) {
                $limit = date('Y/m/d', strtotime($limit . " -1 day"));
            }
        } else {
            $limit = $flyer_data->getProductTraining()->getAcceptLimitDate()->format('Y/m/d');
        }
        $this->lfText(165.8, 252.0, date('n月j日(', strtotime($limit)) . $this->WeekDay[date('w', strtotime($limit))] . ")", 13, 'B');
        // 受講料
        $this->SetTextColor(255, 255, 255);
        $this->Rotate(-5.0, 156.2, 60.5);
        $this->lfText(156.2, 60.5, "参加料", 28, 'B');
        $this->Rotate(5.0, 156.2, 60.5);
        $this->Rotate(-5.0, 155.2, 69.2);
        $this->lfText(155.2, 69.2, ((0 <$flyer_data->getProductTraining()->getProduct()->getPrice02IncTaxMax())?number_format($flyer_data->getProductTraining()->getProduct()->getPrice02IncTaxMax()) . '円':"無料"), 24, 'B');
        $this->Rotate(5.0, 155.2, 69.2);
        // 定員
        if ($ProductClass) {
            $ProductClass = $ProductClasses[0];
            $this->Rotate(-5.0, 169.0, 135.0);
            $this->lfText(169.0, 135.0, '定員', 22, 'B');
            $this->Rotate(5.0, 169.0, 135.0);
            $this->Rotate(-5.0, 167.0, 143.8);
            $this->lfText(167.0, 143.8, $ProductClass->getStock() . '人', 22, 'B');
            $this->Rotate(5.0, 167.0, 143.8);
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
