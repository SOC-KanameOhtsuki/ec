<?php
/*
 * This file is Customize File
 */


namespace Eccube\Controller\Admin\Customer;

use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Master\CsvType;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerGroupController extends AbstractController
{
    public function index(Application $app, Request $request, $page_no = null)
    {
        $session = $request->getSession();
        $pagination = array();
        $builder = $app['form.factory']
            ->createBuilder('admin_search_customer_group');

        $searchForm = $builder->getForm();

        //アコーディオンの制御初期化( デフォルトでは閉じる )
        $active = false;

        $pageMaxis = $app['eccube.repository.master.page_max']->findAll();

        // 表示件数は順番で取得する、1.SESSION 2.設定ファイル
        $page_count = $session->get('eccube.admin.customer_group.search.page_count', $app['config']['default_page_count']);

        $page_count_param = $request->get('page_count');
        // 表示件数はURLパラメターから取得する
        if($page_count_param && is_numeric($page_count_param)){
            foreach($pageMaxis as $pageMax){
                if($page_count_param == $pageMax->getName()){
                    $page_count = $pageMax->getName();
                    // 表示件数入力値正し場合はSESSIONに保存する
                    $session->set('eccube.admin.customer_group.search.page_count', $page_count);
                    break;
                }
            }
        }

        if ('POST' === $request->getMethod()) {

            $searchForm->handleRequest($request);

            if ($searchForm->isValid()) {
                $searchData = $searchForm->getData();

                // paginator
                $qb = $app['eccube.repository.customer_group']->getQueryBuilderBySearchData($searchData);
                $page_no = 1;

                $pagination = $app['paginator']()->paginate(
                    $qb,
                    $page_no,
                    $page_count
                );

                // sessionに検索条件を保持.
                $viewData = \Eccube\Util\FormUtil::getViewData($searchForm);
                $session->set('eccube.admin.customer_group.search', $viewData);
                $session->set('eccube.admin.customer_group.search.page_no', $page_no);
            }
        } else {
            if (is_null($page_no) && $request->get('resume') != Constant::ENABLED) {
                // sessionを削除
                $session->remove('eccube.admin.customer_group.search');
                $session->remove('eccube.admin.customer_group.search.page_no');
                $session->remove('eccube.admin.customer_group.search.page_count');
            } else {
                // pagingなどの処理
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.customer_group.search.page_no'));
                } else {
                    $session->set('eccube.admin.customer_group.search.page_no', $page_no);
                }
                $viewData = $session->get('eccube.admin.customer_group.search');
                if (!is_null($viewData)) {
                    // sessionに保持されている検索条件を復元.
                    $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);

                    // 表示件数
                    $page_count = $request->get('page_count', $page_count);

                    $qb = $app['eccube.repository.customer_group']->getQueryBuilderBySearchData($searchData);

                    $pagination = $app['paginator']()->paginate(
                        $qb,
                        $page_no,
                        $page_count
                    );
                }
            }
        }
        return $app->render('Customer/group_index.twig', array(
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_count' => $page_count,
            'active' => $active,
        ));
    }

    /**
     * 会員グループCSVの出力.
     * @param Application $app
     * @param Request $request
     * @return StreamedResponse
     */
    public function export(Application $app, Request $request)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        log_info("会員グループCSVファイル名");

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        $response = new StreamedResponse();
        $response->setCallback(function () use ($app, $request) {

            // CSV種別を元に初期化.
            $app['eccube.service.csv.export']->initCsvType(CsvType::CSV_TYPE_CUSTOMER_GROUP);

            // ヘッダ行の出力.
            $app['eccube.service.csv.export']->exportHeader();
            log_info("会員グループCSV 2");
            // 会員データ検索用のクエリビルダを取得.
            $qb = $app['eccube.service.csv.export']
                ->getCustomerGroupQueryBuilder($request);
            log_info("会員グループCSV 3");
            // データ行の出力.
            $app['eccube.service.csv.export']->setExportQueryBuilder($qb);
            $app['eccube.service.csv.export']->exportData(function ($entity, $csvService) use ($app, $request) {

                log_info("会員グループCSV 4");


                $Csvs = $csvService->getCsvs();

                /** @var $CustomerGroup \Eccube\Entity\CustomerGroup */
                $CustomerGroup = $entity;
                log_info("会員グループCSV 5");
                $ExportCsvRow = new \Eccube\Entity\ExportCsvRow();

                // CSV出力項目と合致するデータを取得.
                foreach ($Csvs as $Csv) {
                    // 会員データを検索.
                    $ExportCsvRow->setData($csvService->getData($Csv, $CustomerGroup));

                    $event = new EventArgs(
                        array(
                            'csvService' => $csvService,
                            'Csv' => $Csv,
                            'CustomerGroup' => $CustomerGroup,
                            'ExportCsvRow' => $ExportCsvRow,
                        ),
                        $request
                    );
                    $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_CUSTOMER_GROUP_CSV_EXPORT, $event);

                    $ExportCsvRow->pushData();
                }

                //$row[] = number_format(memory_get_usage(true));
                // 出力.
                $csvService->fputcsv($ExportCsvRow->getRow());
            });
        });

        $now = new \DateTime();
        $filename = 'customer_group_' . $now->format('YmdHis') . '.csv';
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);

        $response->send();

        log_info("会員グループCSVファイル名", array($filename));

        return $response;
    }


    public function delete(Application $app, Request $request, $id)
    {
        $this->isTokenValid($app);

        log_info('会員グループ削除開始', array($id));

        $session = $request->getSession();
        $page_no = intval($session->get('eccube.admin.customer_group.search.page_no'));
        $page_no = $page_no ? $page_no : Constant::ENABLED;

        $CustomerGroup = $app['orm.em']
            ->getRepository('Eccube\Entity\CustomerGroup')
            ->find($id);

        if (!$CustomerGroup) {
            $app->deleteMessage();
            return $app->redirect($app->url('admin_customer_group_page', array('page_no' => $page_no)).'?resume='.Constant::ENABLED);
        }

        $CustomerGroup->setDelFlg(Constant::ENABLED);
        $app['orm.em']->persist($CustomerGroup);
        $app['orm.em']->flush();

        log_info('会員グループ削除完了', array($id));

        $app->addSuccess('admin.customer_group.delete.complete', 'admin');

        return $app->redirect($app->url('admin_customer_group_page', array('page_no' => $page_no)).'?resume='.Constant::ENABLED);
    }
}
