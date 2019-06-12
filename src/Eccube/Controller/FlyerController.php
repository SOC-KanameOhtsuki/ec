<?php
/*
 * This file is Customized file
 */


namespace Eccube\Controller;

use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class FlyerController
{

    private $title;

    public function __construct()
    {
        $this->title = '';
    }

    public function download(Application $app, Request $request, $id)
    {
        /* @var $Flyer \Eccube\Entity\Flyer */
        $Flyer = $app['eccube.repository.flyer']->find($id);
        if (!$Flyer) {
            throw new NotFoundHttpException();
        }

        // サービスの取得
        /* @var FaxAcceptPdfService $service */
        if ($Flyer->getProductTraining()->getTrainingType()->getId() == 1) {
            $service = $app['eccube.service.supporter_flyer_pdf'];
        } else if ($Flyer->getProductTraining()->getTrainingType()->getId() == 2 ||
                    $Flyer->getProductTraining()->getTrainingType()->getId() == 3 ||
                    $Flyer->getProductTraining()->getTrainingType()->getId() == 4 ) {
            $service = $app['eccube.service.instructor_flyer_pdf'];
        } else if ($Flyer->getProductTraining()->getTrainingType()->getId() == 5) {
            $service = $app['eccube.service.lecture_flyer_pdf'];
        } else {
            $service = $app['eccube.service.follow_up_flyer_pdf'];
        }
        $session = $request->getSession();

        // 顧客情報からPDFを作成する
        $status = $service->makePdf($Flyer);

        // 異常終了した場合の処理
        if (!$status) {
            throw new ServiceUnavailableHttpException();
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('FlyerDownload success!', array());
        return $response;
    }
}
