<?php
/*
 * This file is part of EC-CUBE Customize
 *
 */

namespace Eccube\Controller\Admin\FormPrinting;

use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FormPrintingController extends AbstractController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function payment(Application $app, Request $request = null, $page_no = null)
    {
        $session = $request->getSession();

        $builder = $app['form.factory']
            ->createBuilder('admin_search_order');

        $searchForm = $builder->getForm();

        $pagination = array();

        $disps = $app['eccube.repository.master.disp']->findAll();
        $pageMaxis = $app['eccube.repository.master.page_max']->findAll();

        // 表示件数は順番で取得する、1.SESSION 2.設定ファイル
        $page_count = $session->get('eccube.admin.order.search.page_count', $app['config']['default_page_count']);

        $page_count_param = $request->get('page_count');
        // 表示件数はURLパラメターから取得する
        if($page_count_param && is_numeric($page_count_param)){
            foreach($pageMaxis as $pageMax){
                if($page_count_param == $pageMax->getName()){
                    $page_count = $pageMax->getName();
                    // 表示件数入力値正し場合はSESSIONに保存する
                    $session->set('eccube.admin.order.search.page_count', $page_count);
                    break;
                }
            }
        }

        $active = false;

        if ('POST' === $request->getMethod()) {
            $searchForm->handleRequest($request);
            if ($searchForm->isValid()) {
                $searchData = $searchForm->getData();

                // paginator
                $qb = $app['eccube.repository.order']->getQueryBuilderBySearchDataForAdmin($searchData);

                $page_no = 1;
                $pagination = $app['paginator']()->paginate(
                    $qb,
                    $page_no,
                    $page_count
                );

                // sessionに検索条件を保持.
                $viewData = \Eccube\Util\FormUtil::getViewData($searchForm);
                $session->set('eccube.admin.order.search', $viewData);
                $session->set('eccube.admin.order.search.page_no', $page_no);
            }
        } else {
            if (is_null($page_no) && $request->get('resume') != Constant::ENABLED) {
                // sessionを削除
                $session->remove('eccube.admin.order.search');
                $session->remove('eccube.admin.order.search.page_no');
                $session->remove('eccube.admin.order.search.page_count');
            } else {
                // pagingなどの処理
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.order.search.page_no'));
                } else {
                    $session->set('eccube.admin.order.search.page_no', $page_no);
                }
                $viewData = $session->get('eccube.admin.order.search');
                if (!is_null($viewData)) {
                    // sessionに保持されている検索条件を復元.
                    $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);
                }
                if (!is_null($searchData)) {
                    // 表示件数
                    $pcount = $request->get('page_count');

                    $page_count = empty($pcount) ? $page_count : $pcount;

                    $qb = $app['eccube.repository.order']->getQueryBuilderBySearchDataForAdmin($searchData);

                    $event = new EventArgs(
                        array(
                            'form' => $searchForm,
                            'qb' => $qb,
                        ),
                        $request
                    );
                    $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_INDEX_SEARCH, $event);

                    $pagination = $app['paginator']()->paginate(
                        $qb,
                        $page_no,
                        $page_count
                    );
                        }
                    }
                        }

        return $app->render('FormPrinting/payment.twig', array(
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'disps' => $disps,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_count' => $page_count,
            'active' => $active,
        ));
    }

    public function paymentAllExport(Application $app, Request $request = null)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        $session = $request->getSession();
        $viewData = $session->get('eccube.admin.order.search');
        if (is_null($viewData)) {
            $app->addError('admin.payment_pdf.parameter.notfound', 'admin');
            log_info('The Order cannot found!');
            return $app->redirect($app->url('admin_form_printing_payment'));
        }

        // sessionに保持されている検索条件を復元.
        $builder = $app['form.factory']
            ->createBuilder('admin_search_order');
        $searchForm = $builder->getForm();
        $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);

        // サービスの取得
        /* @var PaymentPdfService $service */
        $service = $app['eccube.service.payment_pdf'];

        // 受注情報取得
        $orders = $app['eccube.repository.order']->getQueryBuilderBySearchDataForAdmin($searchData)
                ->getQuery()
                ->getResult();

        // 受注情報からPDFを作成する
        $status = $service->makePdf($orders);

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('admin.payment_pdf.download.failure', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_form_printing_payment'));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('PaymentPdf download success!', array('Order:' => count($orders)));
        return $response;
    }

    public function paymentSelectExport(Application $app, Request $request = null)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        // requestから対象顧客IDの一覧を取得する.
        $ids = $this->getIds($request);
        if (count($ids) == 0) {
            $app->addError('admin.payment_pdf.parameter.notfound', 'admin');
            log_info('The Order cannot found!');
            return $app->redirect($app->url('admin_form_printing_payment'));
        }

        // サービスの取得
        /* @var PaymentPdfService $service */
        $service = $app['eccube.service.payment_pdf'];

        // 受注情報取得
        $orders = $app['eccube.repository.order']->getQueryBuilderBySearchOrderIds($ids)
                ->getQuery()
                ->getResult();

        // 受注情報からPDFを作成する
        $status = $service->makePdf($orders);

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('admin.payment_pdf.download.failure', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_form_printing_payment'));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('PaymentPdf download success!', array('Order ID' => implode(',', $this->getIds($request))));
        return $response;
    }

    public function paymentAllCsvExport(Application $app, Request $request = null)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        $session = $request->getSession();
        $viewData = $session->get('eccube.admin.order.search');
        if (is_null($viewData)) {
            $app->addError('admin.payment_pdf.parameter.notfound', 'admin');
            log_info('The Order cannot found!');
            return $app->redirect($app->url('admin_form_printing_payment'));
        }

        // sessionに保持されている検索条件を復元.
        $builder = $app['form.factory']
            ->createBuilder('admin_search_order');
        $searchForm = $builder->getForm();
        $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);

        $response = new StreamedResponse();
        $response->setCallback(function () use ($app, $request, $searchData) {

            // 受注情報取得
            $orders = $app['eccube.repository.order']->getQueryBuilderBySearchDataForAdmin($searchData)
                    ->getQuery()
                    ->getResult();

            // サービスの取得
            /* @var PaymentPdfService $service */
            $service = $app['eccube.service.csv.paying_slip.export'];

            // 受注情報からCSVを作成する
            $service->makeCsv($orders);
        });

        $now = new \DateTime();
        $filename = 'payment_' . $now->format('YmdHis') . '.csv';
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);
        $response->send();

        log_info("CSVファイル名", array($filename));
        return $response;
    }

    public function paymentSelectCsvExport(Application $app, Request $request = null)
    {
        log_info("paymentSelectCsvExport Start");
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        // requestから対象顧客IDの一覧を取得する.
        $ids = $this->getIds($request);
        if (count($ids) == 0) {
            $app->addError('admin.payment_pdf.parameter.notfound', 'admin');
            log_info('The Order cannot found!');
            return $app->redirect($app->url('admin_form_printing_payment'));
        }

        $response = new StreamedResponse();
        $response->setCallback(function () use ($app, $request, $ids) {

            // 受注情報取得
            $orders = $app['eccube.repository.order']->getQueryBuilderBySearchOrderIds($ids)
                    ->getQuery()
                    ->getResult();

            log_info('ids:' . print_r($ids, true));
            log_info('orders:' . count($orders));

            // サービスの取得
            /* @var PaymentPdfService $service */
            $service = $app['eccube.service.csv.paying_slip.export'];

            // 受注情報からCSVを作成する
            $service->makeCsv($orders);
        });

        $now = new \DateTime();
        $filename = 'payment_' . $now->format('YmdHis') . '.csv';
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);
        $response->send();

        log_info("CSVファイル名", array($filename));
        return $response;
    }

    public function invoice(Application $app, Request $request = null, $page_no = null)
    {
        $session = $request->getSession();

        $builder = $app['form.factory']
            ->createBuilder('admin_search_order');

        $searchForm = $builder->getForm();

        $pagination = array();

        $disps = $app['eccube.repository.master.disp']->findAll();
        $pageMaxis = $app['eccube.repository.master.page_max']->findAll();

        // 表示件数は順番で取得する、1.SESSION 2.設定ファイル
        $page_count = $session->get('eccube.admin.order.search.page_count', $app['config']['default_page_count']);

        $page_count_param = $request->get('page_count');
        // 表示件数はURLパラメターから取得する
        if($page_count_param && is_numeric($page_count_param)){
            foreach($pageMaxis as $pageMax){
                if($page_count_param == $pageMax->getName()){
                    $page_count = $pageMax->getName();
                    // 表示件数入力値正し場合はSESSIONに保存する
                    $session->set('eccube.admin.order.search.page_count', $page_count);
                    break;
                }
            }
        }

        $active = false;

        if ('POST' === $request->getMethod()) {
            $searchForm->handleRequest($request);
            if ($searchForm->isValid()) {
                $searchData = $searchForm->getData();

                // paginator
                $qb = $app['eccube.repository.order']->getQueryBuilderBySearchDataForAdmin($searchData);

                $page_no = 1;
                $pagination = $app['paginator']()->paginate(
                    $qb,
                    $page_no,
                    $page_count
                );

                // sessionに検索条件を保持.
                $viewData = \Eccube\Util\FormUtil::getViewData($searchForm);
                $session->set('eccube.admin.order.search', $viewData);
                $session->set('eccube.admin.order.search.page_no', $page_no);
            }
        } else {
            if (is_null($page_no) && $request->get('resume') != Constant::ENABLED) {
                // sessionを削除
                $session->remove('eccube.admin.order.search');
                $session->remove('eccube.admin.order.search.page_no');
                $session->remove('eccube.admin.order.search.page_count');
            } else {
                // pagingなどの処理
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.order.search.page_no'));
                } else {
                    $session->set('eccube.admin.order.search.page_no', $page_no);
                }
                $viewData = $session->get('eccube.admin.order.search');
                if (!is_null($viewData)) {
                    // sessionに保持されている検索条件を復元.
                    $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);
                }
                if (!is_null($searchData)) {
                    // 表示件数
                    $pcount = $request->get('page_count');

                    $page_count = empty($pcount) ? $page_count : $pcount;

                    $qb = $app['eccube.repository.order']->getQueryBuilderBySearchDataForAdmin($searchData);

                    $event = new EventArgs(
                        array(
                            'form' => $searchForm,
                            'qb' => $qb,
                        ),
                        $request
                    );
                    $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_INDEX_SEARCH, $event);

                    $pagination = $app['paginator']()->paginate(
                        $qb,
                        $page_no,
                        $page_count
                    );
                        }
                    }
                        }

        return $app->render('FormPrinting/invoice.twig', array(
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'disps' => $disps,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_count' => $page_count,
            'active' => $active,
        ));
    }

    public function invoiceAllExport(Application $app, Request $request = null)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        $session = $request->getSession();
        $viewData = $session->get('eccube.admin.order.search');
        if (is_null($viewData)) {
            $app->addError('admin.invoice_pdf.parameter.notfound', 'admin');
            log_info('The Order cannot found!');
            return $app->redirect($app->url('admin_form_printing_invoice'));
        }

        // sessionに保持されている検索条件を復元.
        $builder = $app['form.factory']
            ->createBuilder('admin_search_order');
        $searchForm = $builder->getForm();
        $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);

        // サービスの取得
        /* @var InvoicePdfService $service */
        $service = $app['eccube.service.invoice_pdf'];

        // 受注情報取得
        $orders = $app['eccube.repository.order']->getQueryBuilderBySearchDataForAdmin($searchData)
                ->getQuery()
                ->getResult();

        // 受注情報からPDFを作成する
        $status = $service->makePdf($orders);

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('admin.invoice_pdf.download.failure', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_form_printing_invoice'));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('InvoicePdf download success!', array('Order:' => count($orders)));
        return $response;
    }

    public function invoiceSelectExport(Application $app, Request $request = null)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        // requestから対象顧客IDの一覧を取得する.
        $ids = $this->getIds($request);
        if (count($ids) == 0) {
            $app->addError('admin.invoice_pdf.parameter.notfound', 'admin');
            log_info('The Order cannot found!');
            return $app->redirect($app->url('admin_form_printing_invoice'));
        }

        // サービスの取得
        /* @var InvoicePdfService $service */
        $service = $app['eccube.service.invoice_pdf'];

        // 受注情報取得
        $orders = $app['eccube.repository.order']->getQueryBuilderBySearchOrderIds($ids)
                ->getQuery()
                ->getResult();

        // 受注情報からPDFを作成する
        $status = $service->makePdf($orders);

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('admin.invoice_pdf.download.failure', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_form_printing_invoice'));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('InvoicePdf download success!', array('Order ID' => implode(',', $this->getIds($request))));
        return $response;
    }

    public function delivery(Application $app, Request $request = null, $page_no = null)
    {
        $session = $request->getSession();

        $builder = $app['form.factory']
            ->createBuilder('admin_search_order');

        $searchForm = $builder->getForm();

        $pagination = array();

        $disps = $app['eccube.repository.master.disp']->findAll();
        $pageMaxis = $app['eccube.repository.master.page_max']->findAll();

        // 表示件数は順番で取得する、1.SESSION 2.設定ファイル
        $page_count = $session->get('eccube.admin.order.search.page_count', $app['config']['default_page_count']);

        $page_count_param = $request->get('page_count');
        // 表示件数はURLパラメターから取得する
        if($page_count_param && is_numeric($page_count_param)){
            foreach($pageMaxis as $pageMax){
                if($page_count_param == $pageMax->getName()){
                    $page_count = $pageMax->getName();
                    // 表示件数入力値正し場合はSESSIONに保存する
                    $session->set('eccube.admin.order.search.page_count', $page_count);
                    break;
                }
            }
        }

        $active = false;

        if ('POST' === $request->getMethod()) {
            $searchForm->handleRequest($request);
            if ($searchForm->isValid()) {
                $searchData = $searchForm->getData();

                // paginator
                $qb = $app['eccube.repository.order']->getQueryBuilderBySearchDataForAdmin($searchData);

                $page_no = 1;
                $pagination = $app['paginator']()->paginate(
                    $qb,
                    $page_no,
                    $page_count
                );

                // sessionに検索条件を保持.
                $viewData = \Eccube\Util\FormUtil::getViewData($searchForm);
                $session->set('eccube.admin.order.search', $viewData);
                $session->set('eccube.admin.order.search.page_no', $page_no);
            }
        } else {
            if (is_null($page_no) && $request->get('resume') != Constant::ENABLED) {
                // sessionを削除
                $session->remove('eccube.admin.order.search');
                $session->remove('eccube.admin.order.search.page_no');
                $session->remove('eccube.admin.order.search.page_count');
            } else {
                // pagingなどの処理
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.order.search.page_no'));
                } else {
                    $session->set('eccube.admin.order.search.page_no', $page_no);
                }
                $viewData = $session->get('eccube.admin.order.search');
                if (!is_null($viewData)) {
                    // sessionに保持されている検索条件を復元.
                    $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);
                }
                if (!is_null($searchData)) {
                    // 表示件数
                    $pcount = $request->get('page_count');

                    $page_count = empty($pcount) ? $page_count : $pcount;

                    $qb = $app['eccube.repository.order']->getQueryBuilderBySearchDataForAdmin($searchData);

                    $event = new EventArgs(
                        array(
                            'form' => $searchForm,
                            'qb' => $qb,
                        ),
                        $request
                    );
                    $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_ORDER_INDEX_SEARCH, $event);

                    $pagination = $app['paginator']()->paginate(
                        $qb,
                        $page_no,
                        $page_count
                    );
                        }
                    }
                        }

        return $app->render('FormPrinting/delivery.twig', array(
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'disps' => $disps,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_count' => $page_count,
            'active' => $active,
        ));
    }

    public function deliveryAllExport(Application $app, Request $request = null)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        $session = $request->getSession();
        $viewData = $session->get('eccube.admin.order.search');
        if (is_null($viewData)) {
            $app->addError('admin.delevery_pdf.parameter.notfound', 'admin');
            log_info('The Order cannot found!');
            return $app->redirect($app->url('admin_form_printing_delivery'));
        }

        // sessionに保持されている検索条件を復元.
        $builder = $app['form.factory']
            ->createBuilder('admin_search_order');
        $searchForm = $builder->getForm();
        $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);

        // サービスの取得
        /* @var DeliveryPdfService $service */
        $service = $app['eccube.service.delivery_pdf'];

        // 受注情報取得
        $orders = $app['eccube.repository.order']->getQueryBuilderBySearchDataForAdmin($searchData)
                ->getQuery()
                ->getResult();

        // 受注情報からPDFを作成する
        $status = $service->makePdf($orders);

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('admin.delevery_pdf.download.failure', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_form_printing_delivery'));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('DeliveryPdf download success!', array('Order:' => count($orders)));
        return $response;
    }

    public function deliverySelectExport(Application $app, Request $request = null)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        // requestから対象顧客IDの一覧を取得する.
        $ids = $this->getIds($request);
        if (count($ids) == 0) {
            $app->addError('admin.delevery_pdf.parameter.notfound', 'admin');
            log_info('The Order cannot found!');
            return $app->redirect($app->url('admin_form_printing_delivery'));
        }

        // サービスの取得
        /* @var DeliveryPdfService $service */
        $service = $app['eccube.service.delivery_pdf'];

        // 受注情報取得
        $orders = $app['eccube.repository.order']->getQueryBuilderBySearchOrderIds($ids)
                ->getQuery()
                ->getResult();

        // 受注情報からPDFを作成する
        $status = $service->makePdf($orders);

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('admin.delevery_pdf.download.failure', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_form_printing_delivery'));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('DeliveryPdf download success!', array('Order ID' => implode(',', $this->getIds($request))));
        return $response;
    }

    public function businessCard(Application $app, Request $request = null, $page_no = null)
    {
        $session = $request->getSession();
        $pagination = array();
        $builder = $app['form.factory']
            ->createBuilder('admin_search_regular_member');

        $searchForm = $builder->getForm();

        //アコーディオンの制御初期化( デフォルトでは閉じる )
        $active = false;

        $pageMaxis = $app['eccube.repository.master.page_max']->findAll();

        // 表示件数は順番で取得する、1.SESSION 2.設定ファイル
        $page_count = $session->get('eccube.admin.customer.search.page_count', $app['config']['default_page_count']);

        $page_count_param = $request->get('page_count');
        // 表示件数はURLパラメターから取得する
        if($page_count_param && is_numeric($page_count_param)){
            foreach($pageMaxis as $pageMax){
                if($page_count_param == $pageMax->getName()){
                    $page_count = $pageMax->getName();
                    // 表示件数入力値正し場合はSESSIONに保存する
                    $session->set('eccube.admin.customer.search.page_count', $page_count);
                    break;
                }
            }
        }

        if ('POST' === $request->getMethod()) {

            $searchForm->handleRequest($request);

            if ($searchForm->isValid()) {
                $searchData = $searchForm->getData();

                // paginator
                $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchRegularMemberData($searchData);
                $page_no = 1;

                $pagination = $app['paginator']()->paginate(
                    $qb,
                    $page_no,
                    $page_count
                );

                // sessionに検索条件を保持.
                $viewData = \Eccube\Util\FormUtil::getViewData($searchForm);
                $session->set('eccube.admin.customer.search', $viewData);
                $session->set('eccube.admin.customer.search.page_no', $page_no);
            }
        } else {
            if (is_null($page_no) && $request->get('resume') != Constant::ENABLED) {
                // sessionを削除
                $session->remove('eccube.admin.customer.search');
                $session->remove('eccube.admin.customer.search.page_no');
                $session->remove('eccube.admin.customer.search.page_count');
            } else {
                // pagingなどの処理
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.customer.search.page_no'));
                } else {
                    $session->set('eccube.admin.customer.search.page_no', $page_no);
                }
                $viewData = $session->get('eccube.admin.customer.search');
                if (!is_null($viewData)) {
                    // sessionに保持されている検索条件を復元.
                    $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);

                    // 表示件数
                    $page_count = $request->get('page_count', $page_count);

                    $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchRegularMemberData($searchData);

                    $event = new EventArgs(
                        array(
                            'form' => $searchForm,
                            'qb' => $qb,
                        ),
                        $request
                    );
                    $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_REGULAR_MEMBER_INDEX_SEARCH, $event);

                    $pagination = $app['paginator']()->paginate(
                        $qb,
                        $page_no,
                        $page_count
                    );
                }
            }
        }
        return $app->render('FormPrinting/business_card_list.twig', array(
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_count' => $page_count,
            'active' => $active,
        ));
    }

    public function businessCardAllExport(Application $app, Request $request = null)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        $session = $request->getSession();
        $viewData = $session->get('eccube.admin.customer.search');
        if (is_null($viewData)) {
            $app->addError('admin.business_card_pdf.parameter.notfound', 'admin');
            log_info('The Customer cannot found!');
            return $app->redirect($app->url('admin_form_printing_business_card'));
        }

        // sessionに保持されている検索条件を復元.
        $builder = $app['form.factory']
            ->createBuilder('admin_search_regular_member');
        $searchForm = $builder->getForm();
        $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);

        // サービスの取得
        /* @var BusinessCardPdfService $service */
        $service = $app['eccube.service.business_card_pdf'];

        // 顧客情報取得
        $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchRegularMemberData($searchData)
                ->getQuery()
                ->getResult();

        // 顧客情報からPDFを作成する
        $status = $service->makePdf($customers);

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('admin.business_card_pdf.download.failure', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_form_printing_business_card'));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('BusinessCardPdf download success!', array('Customer:' => count($customers)));
        return $response;
    }

    public function businessCardSelectExport(Application $app, Request $request = null)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        // requestから対象顧客IDの一覧を取得する.
        $ids = $this->getIds($request);
        if (count($ids) == 0) {
            $app->addError('admin.business_card_pdf.parameter.notfound', 'admin');
            log_info('The Customer cannot found!');
            return $app->redirect($app->url('admin_form_printing_business_card'));
        }

        // サービスの取得
        /* @var BusinessCardPdfService $service */
        $service = $app['eccube.service.business_card_pdf'];

        // 顧客情報取得
        $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchRegularMemberIds($ids)
                ->getQuery()
                ->getResult();

        // 顧客情報からPDFを作成する
        $status = $service->makePdf($customers);

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('admin.business_card_pdf.download.failure', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_form_printing_business_card'));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('BusinessCardPdf download success!', array('Customer ID' => implode(',', $this->getIds($request))));
        return $response;
    }

    public function certification(Application $app, Request $request = null, $page_no = null)
    {
        $session = $request->getSession();
        $pagination = array();
        $builder = $app['form.factory']
            ->createBuilder('admin_search_regular_member');

        $searchForm = $builder->getForm();

        //アコーディオンの制御初期化( デフォルトでは閉じる )
        $active = false;

        $pageMaxis = $app['eccube.repository.master.page_max']->findAll();

        // 表示件数は順番で取得する、1.SESSION 2.設定ファイル
        $page_count = $session->get('eccube.admin.customer.search.page_count', $app['config']['default_page_count']);

        $page_count_param = $request->get('page_count');
        // 表示件数はURLパラメターから取得する
        if($page_count_param && is_numeric($page_count_param)){
            foreach($pageMaxis as $pageMax){
                if($page_count_param == $pageMax->getName()){
                    $page_count = $pageMax->getName();
                    // 表示件数入力値正し場合はSESSIONに保存する
                    $session->set('eccube.admin.customer.search.page_count', $page_count);
                    break;
                }
            }
        }

        if ('POST' === $request->getMethod()) {

            $searchForm->handleRequest($request);

            if ($searchForm->isValid()) {
                $searchData = $searchForm->getData();

                // paginator
                $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchRegularMemberData($searchData);
                $page_no = 1;

                $pagination = $app['paginator']()->paginate(
                    $qb,
                    $page_no,
                    $page_count
                );

                // sessionに検索条件を保持.
                $viewData = \Eccube\Util\FormUtil::getViewData($searchForm);
                $session->set('eccube.admin.customer.search', $viewData);
                $session->set('eccube.admin.customer.search.page_no', $page_no);
            }
        } else {
            if (is_null($page_no) && $request->get('resume') != Constant::ENABLED) {
                // sessionを削除
                $session->remove('eccube.admin.customer.search');
                $session->remove('eccube.admin.customer.search.page_no');
                $session->remove('eccube.admin.customer.search.page_count');
            } else {
                // pagingなどの処理
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.customer.search.page_no'));
                } else {
                    $session->set('eccube.admin.customer.search.page_no', $page_no);
                }
                $viewData = $session->get('eccube.admin.customer.search');
                if (!is_null($viewData)) {
                    // sessionに保持されている検索条件を復元.
                    $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);

                    // 表示件数
                    $page_count = $request->get('page_count', $page_count);

                    $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchRegularMemberData($searchData);

                    $event = new EventArgs(
                        array(
                            'form' => $searchForm,
                            'qb' => $qb,
                        ),
                        $request
                    );
                    $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_REGULAR_MEMBER_INDEX_SEARCH, $event);

                    $pagination = $app['paginator']()->paginate(
                        $qb,
                        $page_no,
                        $page_count
                    );
                }
            }
        }
        return $app->render('FormPrinting/certification.twig', array(
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_count' => $page_count,
            'active' => $active,
        ));
    }

    public function certificationAllExport(Application $app, Request $request = null)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        $session = $request->getSession();
        $viewData = $session->get('eccube.admin.customer.search');
        if (is_null($viewData)) {
            $app->addError('admin.certification_pdf.parameter.notfound', 'admin');
            log_info('The Customer cannot found!');
            return $app->redirect($app->url('admin_form_printing_certification'));
        }

        // sessionに保持されている検索条件を復元.
        $builder = $app['form.factory']
            ->createBuilder('admin_search_regular_member');
        $searchForm = $builder->getForm();
        $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);

        // サービスの取得
        /* @var CertificationPdfService $service */
        $service = $app['eccube.service.certification_pdf'];

        // 顧客情報取得
        $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchRegularMemberData($searchData)
                ->getQuery()
                ->getResult();

        // 顧客情報からPDFを作成する
        $status = $service->makePdf($customers);

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('admin.certification_pdf.download.failure', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_form_printing_certification'));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('CertificationPdf download success!', array('Customer:' => count($customers)));
        return $response;
    }

    public function certificationSelectExport(Application $app, Request $request = null)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        // requestから対象顧客IDの一覧を取得する.
        $ids = $this->getIds($request);
        if (count($ids) == 0) {
            $app->addError('admin.certification_pdf.parameter.notfound', 'admin');
            log_info('The Customer cannot found!');
            return $app->redirect($app->url('admin_form_printing_certification'));
        }

        // サービスの取得
        /* @var CertificationPdfService $service */
        $service = $app['eccube.service.certification_pdf'];

        // 顧客情報取得
        $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchRegularMemberIds($ids)
                ->getQuery()
                ->getResult();

        // 顧客情報からPDFを作成する
        $status = $service->makePdf($customers);

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('admin.certification_pdf.download.failure', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_form_printing_certification'));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('CertificationPdf download success!', array('Customer ID' => implode(',', $this->getIds($request))));
        return $response;
    }

    public function mailLabel(Application $app, Request $request = null, $page_no = null)
    {
        $session = $request->getSession();
        $pagination = array();
        $builder = $app['form.factory']
            ->createBuilder('admin_search_customer');
        $searchForm = $builder->getForm();

        //アコーディオンの制御初期化( デフォルトでは閉じる )
        $active = false;

        $pageMaxis = $app['eccube.repository.master.page_max']->findAll();

        // 表示件数は順番で取得する、1.SESSION 2.設定ファイル
        $page_count = $session->get('eccube.admin.customer.search.page_count', $app['config']['default_page_count']);

        $page_count_param = $request->get('page_count');
        // 表示件数はURLパラメターから取得する
        if($page_count_param && is_numeric($page_count_param)){
            foreach($pageMaxis as $pageMax){
                if($page_count_param == $pageMax->getName()){
                    $page_count = $pageMax->getName();
                    // 表示件数入力値正し場合はSESSIONに保存する
                    $session->set('eccube.admin.customer.search.page_count', $page_count);
                    break;
                }
            }
        }

        if ('POST' === $request->getMethod()) {

            $searchForm->handleRequest($request);

            if ($searchForm->isValid()) {
                $searchData = $searchForm->getData();

                // paginator
                $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchData($searchData)
                                                        ->OrderBy('c.id', 'ASC');
                $page_no = 1;

                $pagination = $app['paginator']()->paginate(
                    $qb,
                    $page_no,
                    $page_count
                );

                // sessionに検索条件を保持.
                $viewData = \Eccube\Util\FormUtil::getViewData($searchForm);
                $session->set('eccube.admin.customer.search', $viewData);
                $session->set('eccube.admin.customer.search.page_no', $page_no);
            }
        } else {
            if (is_null($page_no) && $request->get('resume') != Constant::ENABLED) {
                // sessionを削除
                $session->remove('eccube.admin.customer.search');
                $session->remove('eccube.admin.customer.search.page_no');
                $session->remove('eccube.admin.customer.search.page_count');
            } else {
                // pagingなどの処理
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.customer.search.page_no'));
                } else {
                    $session->set('eccube.admin.customer.search.page_no', $page_no);
                }
                $viewData = $session->get('eccube.admin.customer.search');
                if (!is_null($viewData)) {
                    // sessionに保持されている検索条件を復元.
                    $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);

                    // 表示件数
                    $page_count = $request->get('page_count', $page_count);

                    $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchData($searchData)
                                                            ->OrderBy('c.id', 'ASC');

                    $event = new EventArgs(
                        array(
                            'form' => $searchForm,
                            'qb' => $qb,
                        ),
                        $request
                    );

                    $pagination = $app['paginator']()->paginate(
                        $qb,
                        $page_no,
                        $page_count
                    );
                }
            }
        }
        return $app->render('FormPrinting/mail_label.twig', array(
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_count' => $page_count,
            'active' => $active,
        ));
    }

    public function mailLabelAllExport(Application $app, Request $request = null)
    {
        $to = 0;
        $existsId = 0;
        $queryString = $request->getQueryString();
        if (!empty($queryString)) {
            // クエリーをparseする
            parse_str($queryString, $ary);
            foreach ($ary as $key => $val) {
                // キーが一致
                if (preg_match('/^to$/', $key)) {
                    $to = $val;
                } else if (preg_match('/^existsId$/', $key)) {
                    $existsId = $val;
                }
            }
        }

        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        $session = $request->getSession();
        $viewData = $session->get('eccube.admin.customer.search');
        if (is_null($viewData)) {
            $app->addError('admin.mail_label_pdf.parameter.notfound', 'admin');
            log_info('The Customer cannot found!');
            return $app->redirect($app->url('admin_form_printing_mail_label'));
        }

        // sessionに保持されている検索条件を復元.
        $builder = $app['form.factory']
            ->createBuilder('admin_search_regular_member');
        $searchForm = $builder->getForm();
        $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);

        // サービスの取得
        /* @var RegularMemberListPdfService $service */
        $service = $app['eccube.service.mail_label_pdf'];

        // 顧客情報取得
        $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchData($searchData)
                ->OrderBy('c.id', 'ASC')
                ->getQuery()
                ->getResult();

        // 顧客情報からPDFを作成する
        $status = $service->makePdf($customers, null, $to, $existsId);

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('admin.mail_label.download.failure', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_form_printing_mail_label'));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('MailLabelPdf download success!', array('Customer:' => count($customers)));
        return $response;
    }

    public function mailLabelSelectExport(Application $app, Request $request = null)
    {
        $to = 0;
        $existsId = 0;
        $queryString = $request->getQueryString();
        if (!empty($queryString)) {
            // クエリーをparseする
            parse_str($queryString, $ary);
            foreach ($ary as $key => $val) {
                // キーが一致
                if (preg_match('/^to$/', $key)) {
                    $to = $val;
                } else if (preg_match('/^existsId$/', $key)) {
                    $existsId = $val;
                }
            }
        }

        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        // requestから対象顧客IDの一覧を取得する.
        $ids = $this->getIds($request);
        if (count($ids) == 0) {
            $app->addError('admin.mail_label_pdf.parameter.notfound', 'admin');
            log_info('The Customer cannot found!');
            return $app->redirect($app->url('admin_form_printing_mail_label'));
        }

        // サービスの取得
        /* @var RegularMemberListPdfService $service */
        $service = $app['eccube.service.mail_label_pdf'];

        // 顧客情報取得
        $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchData(array('customer_ids' => $ids))
                ->OrderBy('c.id', 'ASC')
                ->getQuery()
                ->getResult();

        // 顧客情報からPDFを作成する
        $status = $service->makePdf($customers, null, $to, $existsId);

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('admin.mail_label_pdf.download.failure', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_form_printing_mail_label'));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('MailLabelPdf download success!', array('Customer ID' => implode(',', $this->getIds($request))));
        return $response;
    }

    public function membershipPaymentStatusList(Application $app, Request $request = null, $page_no = null)
    {
        $session = $request->getSession();
        $pagination = array();
        $builder = $app['form.factory']
            ->createBuilder('admin_search_membership_payment_customer');
        $searchForm = $builder->getForm();

        //アコーディオンの制御初期化( デフォルトでは閉じる )
        $active = false;

        $pageMaxis = $app['eccube.repository.master.page_max']->findAll();

        // 表示件数は順番で取得する、1.SESSION 2.設定ファイル
        $page_count = $session->get('eccube.admin.membership_payment_customer.search.page_count', $app['config']['default_page_count']);

        $page_count_param = $request->get('page_count');
        // 表示件数はURLパラメターから取得する
        if($page_count_param && is_numeric($page_count_param)){
            foreach($pageMaxis as $pageMax){
                if($page_count_param == $pageMax->getName()){
                    $page_count = $pageMax->getName();
                    // 表示件数入力値正し場合はSESSIONに保存する
                    $session->set('eccube.admin.membership_payment_customer.search.page_count', $page_count);
                    break;
                }
            }
        }

        if ('POST' === $request->getMethod()) {

            $searchForm->handleRequest($request);
            if (isset($request->get('admin_search_membership_payment_customer')['membership_year'])) {
                $searchData = array('membership_pay_' . $request->get('admin_search_membership_payment_customer')['membership_year'] => array(1, 3, 4),
                                    'target_membership_product_id' => $request->get('admin_search_membership_payment_customer')['membership_year']);
                // paginator
                $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchData($searchData) 
                                                       ->OrderBy('c.id', 'ASC');
                $page_no = 1;
                $pagination = $app['paginator']()->paginate(
                    $qb,
                    $page_no,
                    $page_count
                );

                // sessionに検索条件を保持.
                $session->set('eccube.admin.membership_payment_customer.search', $searchData);
                $session->set('eccube.admin.membership_payment_customer.search.page_no', $page_no);
            }
        } else {
            if (is_null($page_no) && $request->get('resume') != Constant::ENABLED) {
                // sessionを削除
                $session->remove('eccube.admin.membership_payment_customer.search');
                $session->remove('eccube.admin.membership_payment_customer.search.page_no');
                $session->remove('eccube.admin.membership_payment_customer.search.page_count');
            } else {
                // pagingなどの処理
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.membership_payment_customer.search.page_no'));
                } else {
                    $session->set('eccube.admin.membership_payment_customer.search.page_no', $page_no);
                }
                $searchData = $session->get('eccube.admin.membership_payment_customer.search');
                if (!is_null($searchData)) {
                    // 表示件数
                    $page_count = $request->get('page_count', $page_count);
                    $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchData($searchData)
                                                            ->OrderBy('c.id', 'ASC');
                    $event = new EventArgs(
                        array(
                            'form' => $searchForm,
                            'qb' => $qb,
                        ),
                        $request
                    );

                    $pagination = $app['paginator']()->paginate(
                        $qb,
                        $page_no,
                        $page_count
                    );
                }
            }
        }
        return $app->render('FormPrinting/membership_payment_status_list.twig', array(
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_count' => $page_count,
            'active' => $active,
        ));
    }

    public function membershipPaymentStatusListExport(Application $app, Request $request = null)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        // sessionに保持されている検索条件を復元.
        $session = $request->getSession();
        $searchData = $session->get('eccube.admin.membership_payment_customer.search');
        $productMemberShip = null;
        // 年会費商品情報取得
        if (isset($searchData['target_membership_product_id'])) {
            $productMemberShip = $app['eccube.repository.product_membership']->find($searchData['target_membership_product_id']);
        }
        if (is_null($productMemberShip)) {
            $app->addError('admin.membership_payment_status_list.parameter.notfound', 'admin');
            log_info('The Customer cannot found!');
            return $app->redirect($app->url('admin_form_printing_membership_payment_status_list'));
        }
        // 受注情報取得
        $orders = array();
        $orderDatas = $app['eccube.repository.order']->getQueryBuilderBySearchDataForAdmin(array('product_id' => $productMemberShip->getProduct()->getId()))
                                                                                        ->getQuery()
                                                                                        ->getResult();
        foreach ($orderDatas as $orderData) {
            $orders[$orderData->getCustomer()->getId()] = $orderData;
        }
        // 年会費支払い状況取得
        $membershipBillingStatus = array();
        $membershipBillingStatusDatas = $app['eccube.repository.membership_billing_status']->createQueryBuilder('ms')
                                                                                        ->leftJoin('ms.ProductMembership', 'pm')
                                                                                        ->where('pm.id = :ProductMembershipId')
                                                                                        ->setParameter('ProductMembershipId', $productMemberShip->getId())
                                                                                        ->getQuery()
                                                                                        ->getResult();
        foreach ($membershipBillingStatusDatas as $membershipBillingStatusData) {
            $membershipBillingStatus[$membershipBillingStatusData->getCustomer()->getId()] = $membershipBillingStatusData;
        }

        // サービスの取得
        /* @var RegularMemberListPdfService $service */
        $service = $app['eccube.service.csv.membership_payment_status.export'];

        // 顧客情報取得
        $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchData(array())
                ->OrderBy('c.id', 'ASC')
                ->getQuery()
                ->getResult();

        $response = new StreamedResponse();
        $response->setCallback(function () use ($app, $request, $customers, $orders, $membershipBillingStatus, $productMemberShip) {

            log_info('customers:' . count($customers));
            log_info('orders:' . count($orders));

            // サービスの取得
            /* @var TrainingMemberListCsvExportService $service */
            $service = $app['eccube.service.csv.membership_payment_status.export'];

            // 顧客情報から年会費支払状況名簿CSVを作成する
            $service->makeCsv($customers, $orders, $membershipBillingStatus, $productMemberShip);
        });

        $now = new \DateTime();
        $filename = 'MemberPaymentCustomerList_' . $now->format('YmdHis') . '.csv';
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);

        $response->send();

        log_info("CSVファイル名", array($filename));

        return $response;
    }

    public function donationList(Application $app, Request $request = null, $page_no = null)
    {
        $session = $request->getSession();
        $pagination = array();
        $builder = $app['form.factory']
            ->createBuilder('admin_search_donation_payment_customer');
        $searchForm = $builder->getForm();
        $pageMaxis = $app['eccube.repository.master.page_max']->findAll();

        $searchType = 0;
        $searchTypeName = '';
        $searchDateStart = '';
        $searchDateEnd = '';

        // 表示件数は順番で取得する、1.SESSION 2.設定ファイル
        $page_count = $session->get('eccube.admin.donation_payment_customer.search.page_count', $app['config']['default_page_count']);
        $page_count_param = $request->get('page_count');
        // 表示件数はURLパラメターから取得する
        if($page_count_param && is_numeric($page_count_param)){
            foreach($pageMaxis as $pageMax){
                if($page_count_param == $pageMax->getName()){
                    $page_count = $pageMax->getName();
                    // 表示件数入力値正し場合はSESSIONに保存する
                    $session->set('eccube.admin.donation_payment_customer.search.page_count', $page_count);
                    break;
                }
            }
        }

        if ('POST' === $request->getMethod()) {
            $searchForm->handleRequest($request);
            $isValid = false;
            if ($searchForm->isValid()) {
                $searchData = $searchForm->getData();
                $searchType = $searchData['search_donation_type'];
                if ($searchData['search_donation_type'] == 1) {
                    if (isset($searchData['target_year'])) {
                        $searchTypeName = $searchData['target_year'] . '年';
                        $searchDateStart = new \DateTime($searchData['target_year'] . '-01-01 00:00:00');
                        $searchDateEnd = new \DateTime($searchData['target_year'] . '-12-31 23:59:59');
                        $isValid = true;
                    } else {
                        $form['target_year']->addError(new FormError("正しい西暦を入力してください"));
                    }
                } else if ($searchData['search_donation_type'] == 2) {
                    $TermInfo = null;
                    if (isset($searchData['target_term'])) {
                        $TermInfo = $app['eccube.repository.master.term_info']->find($searchData['target_term']);
                    }
                    if (!is_null($TermInfo)) {
                        $searchTypeName = $TermInfo->getTermName();
                        $searchDateStart = new \DateTime($TermInfo->getTermStart()->format('Y-m-d 00:00:00'));
                        $searchDateEnd = new \DateTime($TermInfo->getTermEnd()->format('Y-m-d 23:59:59'));
                        $isValid = true;
                    } else {
                        $form['target_term']->addError(new FormError("正しい年度を入力してください"));
                    }
                } else {
                    $searchTypeName = '期間';
                    if (!is_null($searchData['target_date_start'])) {
                        $searchDateStart = $searchData['target_date_start']->format('Y/m/d');
                    }
                    if (!is_null($searchData['target_date_end'])) {
                        $searchDateEnd = $searchData['target_date_end']->format('Y/m/d');
                    }
                    $isValid = true;
                }
            }
            if ($isValid) {
                // paginator
                $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchDataForDanation($searchData);
                log_info("SQL:" . $qb->getQUery()->getSQL());

                $page_no = 1;
                $pagination = $app['paginator']()->paginate(
                    $qb,
                    $page_no,
                    $page_count
                );

                // sessionに検索条件を保持.
                $viewData = \Eccube\Util\FormUtil::getViewData($searchForm);
                $session->set('eccube.admin.donation_payment_customer.search', $viewData);
                $session->set('eccube.admin.donation_payment_customer.search.page_no', $page_no);
            } else {
                $app->addError('検索条件が不正です', 'admin');
            }
        } else {
            if (is_null($page_no) && $request->get('resume') != Constant::ENABLED) {
                // sessionを削除
                $session->remove('eccube.admin.donation_payment_customer.search');
                $session->remove('eccube.admin.donation_payment_customer.search.page_no');
                $session->remove('eccube.admin.donation_payment_customer.search.page_count');
            } else {
                // pagingなどの処理
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.donation_payment_customer.search.page_no'));
                } else {
                    $session->set('eccube.admin.donation_payment_customer.search.page_no', $page_no);
                }
                $viewData = $session->get('eccube.admin.donation_payment_customer.search');
                $searchData = array();
                if (!is_null($viewData)) {
                    // sessionに保持されている検索条件を復元.
                    $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);
                }
                // 表示件数
                $page_count = $request->get('page_count', $page_count);
                $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchDataForDanation($searchData);

                $event = new EventArgs(
                    array(
                        'form' => $searchForm,
                        'qb' => $qb,
                    ),
                    $request
                );

                $pagination = $app['paginator']()->paginate(
                    $qb,
                    $page_no,
                    $page_count
                );
            }
        }
        return $app->render('FormPrinting/donation_list.twig', array(
            'searchForm' => $searchForm->createView(),
            'searchType' => $searchType,
            'searchTypeName' => $searchTypeName,
            'searchDateStart' => $searchDateStart,
            'searchDateEnd' => $searchDateEnd,
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_count' => $page_count
        ));
    }

    public function donationListCsvExport(Application $app, Request $request = null)
    {
        $summarize = 0;
        $queryString = $request->getQueryString();
        if (!empty($queryString)) {
            // クエリーをparseする
            parse_str($queryString, $ary);
            foreach ($ary as $key => $val) {
                // キーが一致
                if (preg_match('/^summarize$/', $key)) {
                    $summarize = $val;
                }
            }
        }

        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        // sessionに保持されている検索条件を復元.
        $builder = $app['form.factory']
            ->createBuilder('admin_search_donation_payment_customer');
        $searchForm = $builder->getForm();
        $session = $request->getSession();
        $viewData = $session->get('eccube.admin.donation_payment_customer.search');
        $searchData = array();
        if (!is_null($viewData)) {
            // sessionに保持されている検索条件を復元.
            $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);
        }

        // サービスの取得
        /* @var DonationListCsvExportService $service */
        $service = $app['eccube.service.csv.donation_list.export'];

        // 顧客情報取得
        $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchDataForDanation($searchData)
                ->getQuery()
                ->getResult();
        log_info('SQL:' . $app['eccube.repository.customer']->getQueryBuilderBySearchDataForDanation($searchData)
                ->getQuery()->getSQL());

        $response = new StreamedResponse();
        $response->setCallback(function () use ($app, $request, $customers, $searchData, $summarize) {

            log_info('customers:' . count($customers));

            // サービスの取得
            /* @var DonationListCsvExportService $service */
            $service = $app['eccube.service.csv.donation_list.export'];

            // 顧客情報から寄付名簿CSVを作成する
            $service->makeCsv($customers, $searchData, $summarize);
        });

        $now = new \DateTime();
        $filename = 'DonationCustomerList_' . $now->format('YmdHis') . '.csv';
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);

        $response->send();

        log_info("CSVファイル名", array($filename));

        return $response;
    }

    public function donationListExport(Application $app, Request $request = null)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        // sessionに保持されている検索条件を復元.
        // sessionに保持されている検索条件を復元.
        $builder = $app['form.factory']
            ->createBuilder('admin_search_donation_payment_customer');
        $searchForm = $builder->getForm();
        $session = $request->getSession();
        $viewData = $session->get('eccube.admin.donation_payment_customer.search');
        $searchData = array();
        if (!is_null($viewData)) {
            // sessionに保持されている検索条件を復元.
            $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);
        }

        // 顧客情報取得
        $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchDataForDanation($searchData)
                ->OrderBy('c.kana01', 'asc')
                ->addOrderBy('c.kana02', 'asc')
                ->getQuery()
                ->getResult();

        // 年度情報
        $TermInfo = null;
        if (isset($searchData['target_term'])) {
            $TermInfo = $app['eccube.repository.master.term_info']->find($searchData['target_term']);
        }

        // サービスの取得
        /* @var DonationListPdfService $service */
        $service = $app['eccube.service.donation_list_pdf'];

        // 顧客情報から寄付名簿PDFを作成する
        $status = $service->makePdf($customers, $searchData, $TermInfo);

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('admin.donation_list_pdf.download.failure', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_form_printing_donation_list'));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('DonationListPdf download success!');
        return $response;
    }

    public function donationCertificateExport(Application $app, Request $request = null)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        // sessionに保持されている検索条件を復元.
        $builder = $app['form.factory']
            ->createBuilder('admin_search_donation_payment_customer');
        $searchForm = $builder->getForm();
        $session = $request->getSession();
        $viewData = $session->get('eccube.admin.donation_payment_customer.search');
        $searchData = array();
        if (!is_null($viewData)) {
            // sessionに保持されている検索条件を復元.
            $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);
        }
        // サービスの取得
        /* @var DonationListPdfService $service */
        $service = $app['eccube.service.donation_list_pdf'];

        // 顧客情報取得
        $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchDataForDanation($searchData)
                ->getQuery()
                ->getResult();

        // サービスの取得
        /* @var DonationCertificatePdfService $service */
        $service = $app['eccube.service.donation_certificate_pdf'];

        // 顧客情報から寄付名簿PDFを作成する
        $status = $service->makePdf($customers, $searchData);

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('admin.donation_certificate_pdf.download.failure', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_form_printing_donation_list'));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('DonationCertificatePdf download success!');
        return $response;
    }

    public function regularMemberList(Application $app, Request $request = null, $page_no = null)
    {
        $session = $request->getSession();
        $pagination = array();
        $builder = $app['form.factory']
            ->createBuilder('admin_search_regular_member');
        $searchForm = $builder->getForm();

        //アコーディオンの制御初期化( デフォルトでは閉じる )
        $active = false;

        $pageMaxis = $app['eccube.repository.master.page_max']->findAll();

        // 表示件数は順番で取得する、1.SESSION 2.設定ファイル
        $page_count = $session->get('eccube.admin.customer.search.page_count', $app['config']['default_page_count']);

        $page_count_param = $request->get('page_count');
        // 表示件数はURLパラメターから取得する
        if($page_count_param && is_numeric($page_count_param)){
            foreach($pageMaxis as $pageMax){
                if($page_count_param == $pageMax->getName()){
                    $page_count = $pageMax->getName();
                    // 表示件数入力値正し場合はSESSIONに保存する
                    $session->set('eccube.admin.customer.search.page_count', $page_count);
                    break;
                }
            }
        }

        if ('POST' === $request->getMethod()) {

            $searchForm->handleRequest($request);

            if ($searchForm->isValid()) {
                $searchData = $searchForm->getData();

                // paginator
                $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchRegularMemberData($searchData);
                $page_no = 1;

                $pagination = $app['paginator']()->paginate(
                    $qb,
                    $page_no,
                    $page_count
                );

                // sessionに検索条件を保持.
                $viewData = \Eccube\Util\FormUtil::getViewData($searchForm);
                $session->set('eccube.admin.customer.search', $viewData);
                $session->set('eccube.admin.customer.search.page_no', $page_no);
            }
        } else {
            if (is_null($page_no) && $request->get('resume') != Constant::ENABLED) {
                // sessionを削除
                $session->remove('eccube.admin.customer.search');
                $session->remove('eccube.admin.customer.search.page_no');
                $session->remove('eccube.admin.customer.search.page_count');
            } else {
                // pagingなどの処理
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.customer.search.page_no'));
                } else {
                    $session->set('eccube.admin.customer.search.page_no', $page_no);
                }
                $viewData = $session->get('eccube.admin.customer.search');
                if (!is_null($viewData)) {
                    // sessionに保持されている検索条件を復元.
                    $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);

                    // 表示件数
                    $page_count = $request->get('page_count', $page_count);

                    $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchRegularMemberData($searchData);

                    $event = new EventArgs(
                        array(
                            'form' => $searchForm,
                            'qb' => $qb,
                        ),
                        $request
                    );
                    $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_REGULAR_MEMBER_INDEX_SEARCH, $event);

                    $pagination = $app['paginator']()->paginate(
                        $qb,
                        $page_no,
                        $page_count
                    );
                }
            }
        }
        return $app->render('FormPrinting/regular_member_list.twig', array(
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_count' => $page_count,
            'active' => $active,
        ));
    }

    public function regularMemberListAllExportWithoutAnonymous(Application $app, Request $request = null)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        $session = $request->getSession();
        $viewData = $session->get('eccube.admin.customer.search');
        if (is_null($viewData)) {
            $app->addError('admin.regular_member_list_pdf.parameter.notfound', 'admin');
            log_info('The Customer cannot found!');
            return $app->redirect($app->url('admin_form_printing_regular_member_list'));
        }

        // sessionに保持されている検索条件を復元.
        $builder = $app['form.factory']
            ->createBuilder('admin_search_regular_member');
        $searchForm = $builder->getForm();
        $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);

        // サービスの取得
        /* @var RegularMemberListPdfService $service */
        $service = $app['eccube.service.regular_member_list_pdf'];

        // 顧客情報取得
        $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchRegularMemberData($searchData, true)
                ->getQuery()
                ->getResult();
        if (count($customers) == 0) {
            $app->addError('admin.regular_member_list_pdf.parameter.notfound', 'admin');
            log_info('The Customer cannot found!');
            return $app->redirect($app->url('admin_form_printing_regular_member_list'));
        }

        // 顧客情報からPDFを作成する
        $status = $service->makePdf($customers, true);

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('admin.regular_member_list_pdf.download.failure', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_form_printing_regular_member_list'));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('RegularMemberListPdf download success!', array('Customer:' => count($customers)));
        return $response;
    }

    public function regularMemberListSelectExportWithoutAnonymous(Application $app, Request $request = null)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        // requestから対象顧客IDの一覧を取得する.
        $ids = $this->getIds($request);
        if (count($ids) == 0) {
            $app->addError('admin.regular_member_list_pdf.parameter.notfound', 'admin');
            log_info('The Customer cannot found!');
            return $app->redirect($app->url('admin_form_printing_regular_member_list'));
        }

        // サービスの取得
        /* @var RegularMemberListPdfService $service */
        $service = $app['eccube.service.regular_member_list_pdf'];

        // 顧客情報取得
        $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchRegularMemberIds($ids, true)
                ->getQuery()
                ->getResult();
        if (count($customers) == 0) {
            $app->addError('admin.regular_member_list_pdf.parameter.notfound', 'admin');
            log_info('The Customer cannot found!');
            return $app->redirect($app->url('admin_form_printing_regular_member_list'));
        }

        // 顧客情報からPDFを作成する
        $status = $service->makePdf($customers, true);

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('admin.regular_member_list_pdf.download.failure', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_form_printing_regular_member_list'));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('RegularMemberListPdf download success!', array('Customer ID' => implode(',', $this->getIds($request))));
        return $response;
    }

    public function regularMemberListAllExport(Application $app, Request $request = null)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        $session = $request->getSession();
        $viewData = $session->get('eccube.admin.customer.search');
        if (is_null($viewData)) {
            $app->addError('admin.regular_member_list_pdf.parameter.notfound', 'admin');
            log_info('The Customer cannot found!');
            return $app->redirect($app->url('admin_form_printing_regular_member_list'));
        }

        // sessionに保持されている検索条件を復元.
        $builder = $app['form.factory']
            ->createBuilder('admin_search_regular_member');
        $searchForm = $builder->getForm();
        $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);

        // サービスの取得
        /* @var RegularMemberListPdfService $service */
        $service = $app['eccube.service.regular_member_list_pdf'];

        // 顧客情報取得
        $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchRegularMemberData($searchData)
                ->getQuery()
                ->getResult();

        // 顧客情報からPDFを作成する
        $status = $service->makePdf($customers);

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('admin.regular_member_list_pdf.download.failure', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_form_printing_regular_member_list'));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('RegularMemberListPdf download success!', array('Customer:' => count($customers)));
        return $response;
    }

    public function regularMemberListSelectExport(Application $app, Request $request = null)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        // requestから対象顧客IDの一覧を取得する.
        $ids = $this->getIds($request);
        if (count($ids) == 0) {
            $app->addError('admin.regular_member_list_pdf.parameter.notfound', 'admin');
            log_info('The Customer cannot found!');
            return $app->redirect($app->url('admin_form_printing_regular_member_list'));
        }

        // サービスの取得
        /* @var RegularMemberListPdfService $service */
        $service = $app['eccube.service.regular_member_list_pdf'];

        // 顧客情報取得
        $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchRegularMemberIds($ids)
                ->getQuery()
                ->getResult();

        // 顧客情報からPDFを作成する
        $status = $service->makePdf($customers);

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('admin.regular_member_list_pdf.download.failure', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_form_printing_regular_member_list'));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('RegularMemberListPdf download success!', array('Customer ID' => implode(',', $this->getIds($request))));
        return $response;
    }

    /**
     * requestからID一覧を取得する.
     *
     * @param Request $request
     *
     * @return array $isList
     */
    protected function getIds(Request $request)
    {
        $isList = array();

        // その他メニューのバージョン
        $queryString = $request->getQueryString();

        if (empty($queryString)) {
            return $isList;
        }

        // クエリーをparseする
        // idsX以外はない想定
        parse_str($queryString, $ary);

        foreach ($ary as $key => $val) {
            // キーが一致
            if (preg_match('/^ids\d+$/', $key)) {
                if (!empty($val) && $val == 'on') {
                    $isList[] = intval(str_replace('ids', '', $key));
                }
            }
        }

        // id順にソートする
        sort($isList);

        return $isList;
    }
}
