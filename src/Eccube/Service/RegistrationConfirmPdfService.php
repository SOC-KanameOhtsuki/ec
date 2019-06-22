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
 * Class RegistrationConfirmPdfService.
 * Do export pdf function.
 */
class RegistrationConfirmPdfService extends AbstractFPDIService
{
    // ====================================
    // 定数宣言
    // ====================================
    /** ダウンロードするPDFファイル名 */
    const OUT_PDF_FILE_NAME = 'registration_confirm';

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
    public function makePdf(array $customersData, $anonymousCompanyEnabled = false)
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
        $pdfFile = $this->app['config']['pdf_template_registration_confirm'];
        $templateFilePath = __DIR__.'/../Resource/pdf/'.$pdfFile;
        $this->setSourceFile($templateFilePath);

        $this->SetFont(self::FONT_GOTHIC);
        foreach ($customersData as $customerData) {
            // PDFにページを追加する
            $this->addPdfPage();
            $birth = (is_null($customerData->getBirth())?'':$customerData->getBirth()->format('Y/n/j'));
            $home_zip_code = '';
            $home_addr = '';
            $home_tel = '';
            $home_fax = '';
            $home_mobile_phone = '';
            $company = '';
            $company_zip_code = '';
            $company_addr = '';
            $company_tel = '';
            $company_fax = '';
            $mail_address = '';
            $select = '自宅';
            foreach ($customerData->getCustomerAddresses() as $AddresInfo) {
                if ($AddresInfo->getAddressType()->getId() == 1) {
                    if (strlen((is_null($AddresInfo->getPref())?"":$AddresInfo->getPref())) > 0 && strlen((is_null($AddresInfo->getAddr01())?"":$AddresInfo->getAddr01())) > 0  && strlen((is_null($AddresInfo->getAddr02())?"":$AddresInfo->getAddr02())) > 0 ) {
                        $home_addr = (is_null($AddresInfo->getPref())?"":$AddresInfo->getPref()->getName()) . (is_null($AddresInfo->getAddr01())?"":$AddresInfo->getAddr01()) . (is_null($AddresInfo->getAddr02())?"":$AddresInfo->getAddr02());
                    }
                    $home_zip_code = (is_null($AddresInfo->getZip01())?"":$AddresInfo->getZip01()) . (is_null($AddresInfo->getZip02())?"":$AddresInfo->getZip02());
                    if (!is_null($AddresInfo->getTel01()) && !is_null($AddresInfo->getTel02()) && !is_null($AddresInfo->getTel03())) {
                        $home_tel = $customerData->getTel01() . "-" . $AddresInfo->getTel02() . "-" . $AddresInfo->getTel03();
                    }
                    if (!is_null($AddresInfo->getFax01()) && !is_null($AddresInfo->getFax02()) && !is_null($AddresInfo->getFax03())) {
                        $home_fax = $customerData->getFax01() . "-" . $AddresInfo->getFax02() . "-" . $AddresInfo->getFax03();
                    }
                    if (!is_null($AddresInfo->getMobilephone01()) && !is_null($AddresInfo->getMobilephone02()) && !is_null($AddresInfo->getMobilephone03())) {
                        $home_mobile_phone = $customerData->getMobilephone01() . "-" . $AddresInfo->getMobilephone02() . "-" . $AddresInfo->getMobilephone03();
                    }
                    if (!is_null($AddresInfo->getEmail())) {
                        $mail_address = $AddresInfo->getEmail();
                    }
                } else if ($AddresInfo->getAddressType()->getId() == 2) {
                    if (strlen((is_null($AddresInfo->getPref())?"":$AddresInfo->getPref())) > 0 && strlen((is_null($AddresInfo->getAddr01())?"":$AddresInfo->getAddr01())) > 0  && strlen((is_null($AddresInfo->getAddr02())?"":$AddresInfo->getAddr02())) > 0 ) {
                        $company_addr = (is_null($AddresInfo->getPref())?"":$AddresInfo->getPref()->getName()) . (is_null($AddresInfo->getAddr01())?"":$AddresInfo->getAddr01()) . (is_null($AddresInfo->getAddr02())?"":$AddresInfo->getAddr02());
                    }
                    $company_zip_code = (is_null($AddresInfo->getZip01())?"":$AddresInfo->getZip01()) . (is_null($AddresInfo->getZip02())?"":$AddresInfo->getZip02());
                    if (!is_null($AddresInfo->getTel01()) && !is_null($AddresInfo->getTel02()) && !is_null($AddresInfo->getTel03())) {
                        $company_tel = $customerData->getTel01() . "-" . $AddresInfo->getTel02() . "-" . $AddresInfo->getTel03();
                    }
                    if (!is_null($AddresInfo->getFax01()) && !is_null($AddresInfo->getFax02()) && !is_null($AddresInfo->getFax03())) {
                        $company_fax = $customerData->getFax01() . "-" . $AddresInfo->getFax02() . "-" . $AddresInfo->getFax03();
                    }
                    if (($anonymousCompanyEnabled) && strlen((is_null($AddresInfo->getName01())?"":$AddresInfo->getName01())) > 0 ) {
                        $company = $AddresInfo->getName01();
                    }
                    if ($AddresInfo->getMailTo()->getId() == 2) {
                        $select = '勤務先';
                    }
                }
            }
            if (($anonymousCompanyEnabled) && (strlen($company) < 1)) {
                $company = $customerData->getCompanyName();
            }
            if ((strlen($home_addr) < 1) && strlen((is_null($AddresInfo->getPref())?"":$AddresInfo->getPref())) > 0 && strlen((is_null($AddresInfo->getAddr01())?"":$AddresInfo->getAddr01())) > 0  && strlen((is_null($AddresInfo->getAddr02())?"":$AddresInfo->getAddr02())) > 0 ) {
                $home_addr = (is_null($AddresInfo->getPref())?"":$AddresInfo->getPref()->getName()) . (is_null($AddresInfo->getAddr01())?"":$AddresInfo->getAddr01()) . (is_null($AddresInfo->getAddr02())?"":$AddresInfo->getAddr02());
            }
            if (strlen($home_zip_code) < 1) {
                $home_zip_code = (is_null($customerData->getZip01())?"":$customerData->getZip01()) . (is_null($customerData->getZip02())?"":$customerData->getZip02());
            }
            if ((strlen($home_tel) < 1) && !is_null($AddresInfo->getTel01()) && !is_null($AddresInfo->getTel02()) && !is_null($AddresInfo->getTel03())) {
                $home_tel = $customerData->getTel01() . "-" . $AddresInfo->getTel02() . "-" . $AddresInfo->getTel03();
            }
            if ((strlen($home_fax) < 1) && !is_null($AddresInfo->getFax01()) && !is_null($AddresInfo->getFax02()) && !is_null($AddresInfo->getFax03())) {
                $home_fax = $customerData->getFax01() . "-" . $AddresInfo->getFax02() . "-" . $AddresInfo->getFax03();
            }
            if ((strlen($home_mobile_phone) < 1) && !is_null($AddresInfo->getMobilephone01()) && !is_null($AddresInfo->getMobilephone02()) && !is_null($AddresInfo->getMobilephone03())) {
                $home_mobile_phone = $customerData->getMobilephone01() . "-" . $AddresInfo->getMobilephone02() . "-" . $AddresInfo->getMobilephone03();
            }
            if ((strlen($mail_address) < 1) && (!is_null($customerData->getEmail()))) {
                $mail_address = $customerData->getEmail();
            }
            if (preg_match("/" . $this->app['config']['dummy_email_pattern'] . "/", $mail_address)) {
                $mail_address = '';
            }
            // 会員名(ふりがな)
            $this->lfMultiText(56.1, 57.1, 48.0, 11.0, mb_convert_kana((is_null($customerData->getKana01())?'':$customerData->getKana01() . (is_null($customerData->getKana02())?'':' ')) . (is_null($customerData->getKana02())?'':$customerData->getKana02()), 'c', 'UTF-8'), 12);
            // 会員名
            $this->lfMultiText(56.1, 69.1, 48.0, 11.0, $customerData->getName01() . " " . $customerData->getName02(), 12);
            // 生年月日
            $this->lfMultiText(56.1, 81.2, 48.0, 11.0, $birth, 12);
            // 希望送付先
            $this->lfMultiText(56.1, 93.4, 48.0, 11.0, $select, 12);
            // 自宅郵便番号
            $this->lfMultiText(56.1, 105.5, 48.0, 11.0, $home_zip_code, 12);
            // 自宅住所
            $this->lfMultiText(56.1, 117.8, 48.0, 23.3, $home_addr, 12);
            // 自宅電話番号
            $this->lfMultiText(56.1, 142.5, 48.0, 11.0, $home_tel, 12);
            // 自宅Fax番号
            $this->lfMultiText(56.1, 155.0, 48.0, 11.0, $home_fax, 12);
            // 自宅携帯番号
            $this->lfMultiText(56.1, 167.0, 48.0, 11.0, $home_mobile_phone, 12);
            // 所属先名(勤務先名称)
            $this->lfMultiText(56.1, 179.1, 48.0, 16.9, $company, 12);
            // 勤務先郵便番号
            $this->lfMultiText(56.1, 198.1, 48.0, 11.0, $company_zip_code, 12);
            // 勤務先住所
            $this->lfMultiText(56.1, 210.1, 48.0, 23.3, $company_addr, 12);
            // 勤務先電話番号
            $this->lfMultiText(56.1, 234.7, 48.0, 11.0, $company_tel, 12);
            // 勤務先Fax番号
            $this->lfMultiText(56.1, 246.8, 48.0, 11.0, $company_fax, 12);
            // メールアドレス
            $this->lfMultiText(56.1, 259.0, 48.0, 16.9, $mail_address, 12);
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
        $this->MultiCell($w, $h, $text, 0, "L", false, 0, "", "", true, 0, false, true, $h, "M");
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
