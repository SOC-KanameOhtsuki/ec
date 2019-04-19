<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Eccube\Controller\Admin\Customer;

use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Master\CsvType;
use Eccube\Entity\Master\DeviceType;
use Eccube\Entity\ShipmentItem;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerController extends AbstractController
{
    public function index(Application $app, Request $request, $page_no = null)
    {
        log_info('index Start');
        $session = $request->getSession();
        $pagination = array();
        $custom_searchs = array();
        $builder = $app['form.factory']
            ->createBuilder('admin_search_customer');

        $event = new EventArgs(
            array(
                'builder' => $builder,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_CUSTOMER_INDEX_INITIALIZE, $event);

        $searchForm = $builder->getForm();

        //アコーディオンの制御初期化( デフォルトでは閉じる )
        $active = false;
        //カスタム検索アコーディオンの制御初期化( デフォルトでは閉じる )
        $activeCustom = false;

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
            $custom_search_input = ($request->request->has('custom_select')?$request->request->get('custom_select'):array());
            $activeCustom = ($request->request->has('open_customer_search')?($request->request->get('open_customer_search')==1):false);
            $original_searchs = array();
            foreach ($custom_search_input as $searchId => $custom_search) {
                $OrignalSearch = $app['eccube.repository.orignal_search']->findOneBy(array('id' => $searchId, 'del_flg' => 0));
                if ($OrignalSearch) {
                    $custom_searchs[] = array('id' => $searchId, 'name' => $OrignalSearch->getSearchName(), 'type' => $custom_search['join']);
                    $original_searchs[] = array('entity' => $OrignalSearch, 'join' => $custom_search['join']);
                }
            }
            $is_custom_search = $request->request->has('custom_search');
            $searchForm->handleRequest($request);
            if ($searchForm->isValid()) {
                if ((!$is_custom_search) || (count($original_searchs) < 1)) {
                    $searchData = $searchForm->getData();
                    log_info('searchData:' . print_r($searchData, true));

                    // paginator
                    $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchData($searchData);
                } else {
                    $searchDatas = array();
                    foreach($original_searchs as $original_search) {
                        $builder = $app['form.factory']
                            ->createBuilder('admin_search_customer');
                        $searchFormTemp = $builder->getForm();
                        $searchDatas[] = array('searchData' => \Eccube\Util\FormUtil::submitAndGetData($searchFormTemp, json_decode($original_search['entity']->getSearchValue(), true)), 'join' => $original_search['join']);
                    }
                    // paginator
                    $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchDatas($searchDatas);
                }
                $page_no = 1;
                $event = new EventArgs(
                    array(
                        'form' => $searchForm,
                        'qb' => $qb,
                    ),
                    $request
                );
                $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_CUSTOMER_INDEX_SEARCH, $event);

                $pagination = $app['paginator']()->paginate(
                    $qb,
                    $page_no,
                    $page_count
                );

                // sessionに検索条件を保持.
                $viewData = \Eccube\Util\FormUtil::getViewData($searchForm);
                $session->set('eccube.admin.customer.search', $viewData);
                $session->set('eccube.admin.customer.search.page_no', $page_no);
                $session->set('eccube.admin.customer.search.custom_search_input', json_encode($custom_search_input));
                $session->set('eccube.admin.customer.search.is_custom_search', $is_custom_search);
                $session->set('eccube.admin.customer.search.active_custom', $activeCustom);
            } else {
                log_info('Invalid!');
            }
        } else {
            if (is_null($page_no) && $request->get('resume') != Constant::ENABLED) {
                // sessionを削除
                $session->remove('eccube.admin.customer.search');
                $session->remove('eccube.admin.customer.search.page_no');
                $session->remove('eccube.admin.customer.search.page_count');
                $session->remove('eccube.admin.customer.search.custom_search_input');
                $session->remove('eccube.admin.customer.search.is_custom_search');
                $session->remove('eccube.admin.customer.search.active_custom');
            } else {
                // pagingなどの処理
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.customer.search.page_no'));
                } else {
                    $session->set('eccube.admin.customer.search.page_no', $page_no);
                }
                $custom_search_input = json_decode($session->get('eccube.admin.customer.search.custom_search_input'), true);
                $activeCustom = boolval($session->get('eccube.admin.customer.search.active_custom'));
                $original_searchs = array();
                foreach ($custom_search_input as $searchId => $custom_search) {
                    $OrignalSearch = $app['eccube.repository.orignal_search']->findOneBy(array('id' => $searchId, 'del_flg' => 0));
                    if ($OrignalSearch) {
                        $custom_searchs[] = array('id' => $searchId, 'name' => $OrignalSearch->getSearchName(), 'type' => $custom_search['join']);
                        $original_searchs[] = array('entity' => $OrignalSearch, 'join' => $custom_search['join']);
                    }
                }
                $is_custom_search = boolval($session->get('eccube.admin.customer.search.is_custom_search'));
                if ((!$is_custom_search) || (count($original_searchs) < 1)) {
                    $viewData = $session->get('eccube.admin.customer.search');
                    $searchData = array();
                    if (!is_null($viewData)) {
                        // sessionに保持されている検索条件を復元.
                        $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);
                    }
                    // 表示件数
                    $page_count = $request->get('page_count', $page_count);

                    $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchData($searchData);
                } else {
                    $searchDatas = array();
                    foreach($original_searchs as $original_search) {
                        $builder = $app['form.factory']
                            ->createBuilder('admin_search_customer');
                        $searchFormTemp = $builder->getForm();
                        $searchDatas[] = array('searchData' => \Eccube\Util\FormUtil::submitAndGetData($searchFormTemp, json_decode($original_search['entity']->getSearchValue(), true)), 'join' => $original_search['join']);
                    }
                    // paginator
                    $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchDatas($searchDatas);
                }
                $event = new EventArgs(
                    array(
                        'form' => $searchForm,
                        'qb' => $qb,
                    ),
                    $request
                );
                $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_CUSTOMER_INDEX_SEARCH, $event);

                $pagination = $app['paginator']()->paginate(
                    $qb,
                    $page_no,
                    $page_count
                );
            }
        }

        //dtb_category.id 2 == 寄付
        $ProductCategory = $app['eccube.repository.category']->find(2); 
        $Products = $app['eccube.repository.product_category']->getProductsForCategory($ProductCategory);

        foreach ($pagination as $Customer) {
            if (sizeof($app['eccube.repository.order']->getProductTrainingOrders($app, $Customer)) > 0) {
                $Customer->hasTrainingOrders = true;
            } else {
                $Customer->hasTrainingOrders = false;
            }

            if (sizeof($app['eccube.repository.order']->getContributionOrders($app, $Customer, $Products)) > 0) {
                $Customer->hasContributionOrders = true;
            } else {
                $Customer->hasContributionOrders = false;
            }
        }

        return $app->render('Customer/index.twig', array(
            'searchForm' => $searchForm->createView(),
            'custom_searchs' => $custom_searchs,
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_count' => $page_count,
            'active' => $active,
            'activeCustom' => $activeCustom,
        ));
    }

    public function getSearch(Application $app, Request $request)
    {
        log_info('getSearch Start');
        $getlist = $app['orm.em']->createQueryBuilder('a')
            ->select('a')
            ->from('\Eccube\Entity\OrignalSearch', 'a')
            ->where('a.target_type = :target_type and a.del_flg = :del_flg')
            ->setParameter('del_flg',  0)
            ->setParameter('target_type',  'customer_search')
            ->getQuery()
            ->execute();
        return $app->render('Customer/search_list.twig', array(
            // add parameter...
            'formlist'=>$getlist
        ));
    }

    public function saveSearch(Application $app, Request $request)
    {
        log_info('saveSearch Start');
        $session = $request->getSession();
        $builder = $app['form.factory']
            ->createBuilder('admin_search_customer');
        $searchForm = $builder->getForm();

        $save_name = $request->request->all()['form_save_name'];
        $searchForm->handleRequest($request);
        if ((strlen($save_name) < 1) || (!$searchForm->isValid())) {
            $message="値が不正です。　登録できませんでした。";
        } else {
            $OrignalSearch = $app['eccube.repository.orignal_search']->findOneBy(array('search_name' => $save_name, 'target_type' => 'customer_search'));
            if (!$OrignalSearch) {
                $OrignalSearch = new \Eccube\Entity\OrignalSearch();
                $message=$save_name."は新規登録されました。";
            } else {
                $message=$save_name."は再登録されました。";
            }
            $OrignalSearch->setSearchValue(json_encode(\Eccube\Util\FormUtil::getViewData($searchForm)));
            $OrignalSearch->setSearchName($save_name);
            $OrignalSearch->setTargetType('customer_search');
            $app['orm.em']->persist($OrignalSearch);
            $app['orm.em']->flush($OrignalSearch);
        }
        return $app->render('Customer/search_save.twig', array(
            // add parameter...
            "message"=>$message,
        ));
    }

    public function selectSearch(Application $app, Request $request)
    {
        $id = $request->get('id');
        log_info('selectSearch Start', array($id));
        $pagination = array();
        $session = $request->getSession();
        $builder = $app['form.factory']
            ->createBuilder('admin_search_customer');
        $searchForm = $builder->getForm();
        $OrignalSearch = $app['eccube.repository.orignal_search']->findOneBy(array('id' => $id, 'del_flg' => 0));
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
        if ($OrignalSearch) {
            // sessionに保持されている検索条件を復元.
            $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, json_decode($OrignalSearch->getSearchValue(), true));
            // 表示件数
            $page_no = 1;
            $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchData($searchData);
            $pagination = $app['paginator']()->paginate(
                $qb,
                $page_no,
                $page_count
            );
        } else {
            $app->addError('該当する。登録はありません。', 'admin');
            // pagingなどの処理
            $page_no = intval($session->get('eccube.admin.customer.search.page_no'));
            $viewData = $session->get('eccube.admin.customer.search');
            if (!is_null($viewData)) {
                // sessionに保持されている検索条件を復元.
                $searchData = \Eccube\Util\FormUtil::submitAndGetData($searchForm, $viewData);
                // 表示件数
                $page_count = $request->get('page_count', $page_count);
                $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchData($searchData);
                $pagination = $app['paginator']()->paginate(
                    $qb,
                    $page_no,
                    $page_count
                );
            }
        }

        //dtb_category.id 2 == 寄付
        $ProductCategory = $app['eccube.repository.category']->find(2); 
        $Products = $app['eccube.repository.product_category']->getProductsForCategory($ProductCategory);

        foreach ($pagination as $Customer) {
            if (sizeof($app['eccube.repository.order']->getProductTrainingOrders($app, $Customer)) > 0) {
                $Customer->hasTrainingOrders = true;
            } else {
                $Customer->hasTrainingOrders = false;
            }

            if (sizeof($app['eccube.repository.order']->getContributionOrders($app, $Customer, $Products)) > 0) {
                $Customer->hasContributionOrders = true;
            } else {
                $Customer->hasContributionOrders = false;
            }
        }

        return $app->render('Customer/index.twig', array(
            'searchForm' => $searchForm->createView(),
            'custom_searchs' => null,
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_count' => $page_count,
            'active' => $active,
            'activeCustom' => true,
        ));
    }

    public function deleteSearch(Application $app, Request $request)
    {
        $message="";
        $id = $request->get('id');
        log_info('deleteSearch Start', array($id));
        $OrignalSearch = $app['eccube.repository.orignal_search']->findOneBy(array('id' => $id, 'del_flg' => 0));
        if ($OrignalSearch) {
            $searchName = $OrignalSearch->getSearchName();
            $message= $searchName . "を削除しました。";
            $OrignalSearch->setDelFlg(1);
            $app['orm.em']->persist($OrignalSearch);
            $app['orm.em']->flush($OrignalSearch);
        } else {
            $message="該当する。登録はありません。";
        }
        return $app->render('Customer/search_delete.twig', array(
            "message"=>$message,
        ));
    }

    public function resend(Application $app, Request $request, $id)
    {
        $this->isTokenValid($app);

        $Customer = $app['orm.em']
            ->getRepository('Eccube\Entity\Customer')
            ->find($id);

        if (is_null($Customer)) {
            throw new NotFoundHttpException();
        }

        $activateUrl = $app->url('entry_activate', array('secret_key' => $Customer->getSecretKey()));

        // メール送信
        $app['eccube.service.mail']->sendAdminCustomerConfirmMail($Customer, $activateUrl);

        $event = new EventArgs(
            array(
                'Customer' => $Customer,
                'activateUrl' => $activateUrl,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_CUSTOMER_RESEND_COMPLETE, $event);

        $app->addSuccess('admin.customer.resend.complete', 'admin');

        return $app->redirect($app->url('admin_customer'));
    }

    public function delete(Application $app, Request $request, $id)
    {
        $this->isTokenValid($app);

        log_info('会員削除開始', array($id));

        $session = $request->getSession();
        $page_no = intval($session->get('eccube.admin.customer.search.page_no'));
        $page_no = $page_no ? $page_no : Constant::ENABLED;

        $Customer = $app['orm.em']
            ->getRepository('Eccube\Entity\Customer')
            ->find($id);

        if (!$Customer) {
            $app->deleteMessage();
            return $app->redirect($app->url('admin_customer_page', array('page_no' => $page_no)).'?resume='.Constant::ENABLED);
        }

        $Customer->setDelFlg(Constant::ENABLED);
        $app['orm.em']->persist($Customer);
        $app['orm.em']->flush();

        log_info('会員削除完了', array($id));

        $event = new EventArgs(
            array(
                'Customer' => $Customer,
            ),
            $request
        );
        $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_CUSTOMER_DELETE_COMPLETE, $event);

        $app->addSuccess('admin.customer.delete.complete', 'admin');

        return $app->redirect($app->url('admin_customer_page', array('page_no' => $page_no)).'?resume='.Constant::ENABLED);
    }

    /**
     * 会員CSVの出力.
     * @param Application $app
     * @param Request $request
     * @return StreamedResponse
     */
    public function export(Application $app, Request $request)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        $response = new StreamedResponse();
        $response->setCallback(function () use ($app, $request) {

            // CSV種別を元に初期化.
            $app['eccube.service.csv.export']->initCsvType(CsvType::CSV_TYPE_CUSTOMER);

            // ヘッダ行の出力.
            $app['eccube.service.csv.export']->exportHeader();

            // 会員データ検索用のクエリビルダを取得.
            $qb = $app['eccube.service.csv.export']
                ->getCustomerQueryBuilder($request);

            // データ行の出力.
            $app['eccube.service.csv.export']->setExportQueryBuilder($qb);
            $app['eccube.service.csv.export']->exportData(function ($entity, $csvService) use ($app, $request) {

                $Csvs = $csvService->getCsvs();

                /** @var $Customer \Eccube\Entity\Customer */
                $Customer = $entity;

                $ExportCsvRow = new \Eccube\Entity\ExportCsvRow();

                // CSV出力項目と合致するデータを取得.
                foreach ($Csvs as $Csv) {
                    // 会員データを検索.
                    if ($Csv->getFieldName() == 'CustomerBasicInfo') {
                        if ($Csv->getReferenceFieldName() == 'InstructorType') {
                            $ExportCsvRow->setData($Customer->getCustomerBasicInfo()->getInstructorType()->getName());
                        } else if ($Csv->getReferenceFieldName() == 'SupporterType') {
                            $ExportCsvRow->setData($Customer->getCustomerBasicInfo()->getSupporterType()->getName());
                        } else if ($Csv->getReferenceFieldName() == 'Nobulletin') {
                            $ExportCsvRow->setData($Customer->getCustomerBasicInfo()->getNobulletin()->getName());
                        } else if ($Csv->getReferenceFieldName() == 'Anonymous') {
                            $ExportCsvRow->setData($Customer->getCustomerBasicInfo()->getAnonymous()->getName());
                        } else if ($Csv->getReferenceFieldName() == 'AnonymousCompany') {
                            $ExportCsvRow->setData($Customer->getCustomerBasicInfo()->getAnonymousCompany()->getName());
                        } else if ($Csv->getReferenceFieldName() == 'Bureau') {
                            $ExportCsvRow->setData($Customer->getCustomerBasicInfo()->getBureau()->getName());
                        } else if ($Csv->getReferenceFieldName() == 'MembershipExemption') {
                            $ExportCsvRow->setData($Customer->getCustomerBasicInfo()->getMembershipExemption()->getName());
                        } else if ($Csv->getReferenceFieldName() == 'Status') {
                            $ExportCsvRow->setData($Customer->getCustomerBasicInfo()->getStatus()->getName());
                        } else {
                            $ExportCsvRow->setData($csvService->getData($Csv, $Customer));
                        }
                    } else {
                        $ExportCsvRow->setData($csvService->getData($Csv, $Customer));
                    }

                    $event = new EventArgs(
                        array(
                            'csvService' => $csvService,
                            'Csv' => $Csv,
                            'Customer' => $Customer,
                            'ExportCsvRow' => $ExportCsvRow,
                        ),
                        $request
                    );
                    $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_CUSTOMER_CSV_EXPORT, $event);

                    $ExportCsvRow->pushData();
                }

                //$row[] = number_format(memory_get_usage(true));
                // 出力.
                $csvService->fputcsv($ExportCsvRow->getRow());
            });
        });

        $now = new \DateTime();
        $filename = 'customer_' . $now->format('YmdHis') . '.csv';
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);

        $response->send();

        log_info("会員CSVファイル名", array($filename));

        return $response;
    }

    /**
     * 年会費支払い状況.
     * @param Application $app
     * @param Request $request
     * @param $id Customer ID
     * @return StreamedResponse
     */
    public function annualFeeReport(Application $app, Request $request, $id)
    {
        $Customer = $app['eccube.repository.customer']->find($request->get('id'));
        $billingStatuses = $app['eccube.repository.membership_billing_status']->getBillingStatus($Customer);

        if ($Customer->getCustomerBasicInfo()->getRegularMemberPromoted() == "" || $Customer->getCustomerBasicInfo()->getRegularMemberPromoted() == null) {
            return $app->redirect($app->url('admin_customer'));
        }

        $oldestMembershipPayment = (int) date('Y', strtotime($Customer->getCustomerBasicInfo()->getRegularMemberPromoted()));
        $currentYear = (int) date('Y');
        $termInfos = $app['eccube.repository.master.term_info']->createQueryBuilder('t')
                ->andWhere("t.term_end >= '" . date('Y-m-d') . "'")
                ->andWhere('t.del_flg = 0')
                ->addOrderBy('t.term_year', 'desc')
                ->getQuery()
                ->getResult();
        if ((0 < count($termInfos)) && (!is_null($termInfos))) {
            $currentYear = (int) $termInfos[0]->getTermYear();
        }
        $annualFeeStatuses = array();
        $annualFees = array();
        foreach ($billingStatuses as $billingStatus) {
            if ($billingStatus->getProductMembership()->getMembershipYear() < $oldestMembershipPayment) {
                $oldestMembershipPayment = $billingStatus->getProductMembership()->getMembershipYear();
            }
            if ($billingStatus->getProductMembership()->getMembershipYear() > $currentYear) {
                $currentYear = $billingStatus->getProductMembership()->getMembershipYear();
            }
            $annualFeeStatuses[$billingStatus->getProductMembership()->getMembershipYear()] = [
                $billingStatus->getProductMembership()->getMembershipYear(),
                $billingStatus->getStatus()->getName(),
                $billingStatus->getProductMembership()->getProduct()->getName(),
                false
            ];
        }

        $not_pay_min = "";
        for ($i = $currentYear; $i >= $oldestMembershipPayment; $i--) {
            if (isset($annualFeeStatuses[$i])) {
                $annualFees[$i] = $annualFeeStatuses[$i];
            } else {
                $annualFees[$i] = [
                    $i,
                    '未納',
                    $app['eccube.repository.product_membership']->getMembershipProductName($i),
                    false
                ];
                $not_pay_min = $i;
            }
        }
        if ($not_pay_min != "") {
            $annualFees[$not_pay_min][3] = true;
        }
        return $app->render('Customer/annual_fee_report.twig', array(
            'Customer' => $Customer,
            'annualFeeStatuses' => $annualFees
        ));
    }

    /**
     * 年会費支払い請求作成.
     * @param Application $app
     * @param Request $request
     * @param $id Customer ID
     * @return StreamedResponse
     */
    public function requestMembership(Application $app, Request $request, $id, $target_year)
    {
        log_info('年会費支払い請求作成開始', array($id, $target_year));
        $Customer = $app['eccube.repository.customer']->find($request->get('id'));
        $ProductMembership = $app['eccube.repository.product_membership']->getProductMembershipByMembershipYear($target_year);
        $result = true;
        $connoection = $app['orm.em']->getConnection();
        $connoection->beginTransaction();
        if ($app['eccube.repository.product_membership']->existsMembershipProductOrderByMembershipYear($id, $target_year)) {
            $app->addError('すでに請求済みです', 'admin');
            $result = false;
        }
        if ($result) {
            $result = $this->insertMembershipOrdere($app, $Customer, $ProductMembership);
            if (!$result) {
                $app->addError('受注登録に失敗しました', 'admin');
            }
        }
        if ($result) {
            $app->addSuccess('年会費支払い請求を登録しました', 'admin');
            $connoection->commit();
        } else {
            $connoection->rollBack();
        }
        log_info('年会費支払い請求作成完了', array($id, $target_year));
        return $app->redirect($app->url('admin_customer_annual_fee_report', array('id' => $id)));
    }

    /**
     * 選択年会費支払い請求作成.
     * @param Application $app
     * @param Request $request
     * @param $id Customer ID
     * @return StreamedResponse
     */
    public function requestMembershipSelect(Application $app, Request $request, $id)
    {
        log_info('選択年会費支払い請求作成開始', array($id));
        $Customer = $app['eccube.repository.customer']->find($request->get('id'));
        $targetYears = array();
        if (!is_null($request->get('check_target_year'))) {
            $targetYears = $request->get('check_target_year');
        }
        if (count($targetYears) < 1) {
            $app->addError('対象が選択されていません', 'admin');
            $result = false;
        } else {
            $result = true;
            $connoection = $app['orm.em']->getConnection();
            $connoection->beginTransaction();
            $requested = "";
            foreach($targetYears as $targetYear) {
                if ($app['eccube.repository.product_membership']->existsMembershipProductOrderByMembershipYear($id, $targetYear)) {
                    $requested .= ((strlen($requested) < 1)?"":"、") . $targetYear . "年度";
                    $result = false;
                }
            }
            if ($result) {
                foreach($targetYears as $targetYear) {
                    $ProductMembership = $app['eccube.repository.product_membership']->getProductMembershipByMembershipYear($targetYear);
                    $result = $this->insertMembershipOrdere($app, $Customer, $ProductMembership);
                    if (!$result) {
                        $app->addError('受注登録に失敗しました', 'admin');
                        break;
                    }
                }
            } else {
                $app->addError($requested . 'は、すでに請求済みです', 'admin');
            }
        }
        if ($result) {
            $app->addSuccess('選択された年度の年会費支払い請求を登録しました', 'admin');
            $connoection->commit();
        } else {
            $connoection->rollBack();
        }
        log_info('選択年会費支払い請求作成完了', array($id));
        return $app->redirect($app->url('admin_customer_annual_fee_report', array('id' => $id)));
    }

    /**
     * 年会費入金実績作成.
     * @param Application $app
     * @param Request $request
     * @param $id Customer ID
     * @return StreamedResponse
     */
    public function paymentMembership(Application $app, Request $request, $id, $target_year)
    {
        log_info('年会費入金実績作成開始', array($id, $target_year));
        $Customer = $app['eccube.repository.customer']->find($request->get('id'));
        $ProductMembership = $app['eccube.repository.product_membership']->getProductMembershipByMembershipYear($target_year);
        $result = false;
        $connoection = $app['orm.em']->getConnection();
        $connoection->beginTransaction();
        if (!$app['eccube.repository.membership_billing_status']->existsMembershipStatus($id, $target_year)) {
            $result = $this->insertMembershipPaymentRecorde($app, $Customer, $ProductMembership);
            if (!$result) {
                $app->addError('入金実績登録に失敗しました', 'admin');
            }
        } else {
            $result = false;
            $app->addError('すでに入金実績が存在します', 'admin');
        }
        if ($result) {
            $app->addSuccess('年会費入金実績を登録しました', 'admin');
            $connoection->commit();
        } else {
            $connoection->rollBack();
        }
        log_info('年会費入金実績作成完了', array($id, $target_year));
        return $app->redirect($app->url('admin_customer_annual_fee_report', array('id' => $id)));
    }

    /**
     * 選択年会費入金実績作成.
     * @param Application $app
     * @param Request $request
     * @param $id Customer ID
     * @return StreamedResponse
     */
    public function paymentMembershipSelect(Application $app, Request $request, $id)
    {
        log_info('選択年会費入金実績作成開始', array($id));
        $Customer = $app['eccube.repository.customer']->find($request->get('id'));
        $targetYears = array();
        if (!is_null($request->get('check_target_year'))) {
            $targetYears = $request->get('check_target_year');
        }
        if (count($targetYears) < 1) {
            $result = false;
            $app->addError('対象が選択されていません', 'admin');
        } else {
            $result = true;
            $connoection = $app['orm.em']->getConnection();
            $connoection->beginTransaction();
            $requested = "";
            foreach($targetYears as $targetYear) {
                if ($app['eccube.repository.membership_billing_status']->existsMembershipStatus($id, $targetYear)) {
                    $requested .= ((strlen($requested) < 1)?"":"、") . $targetYear . "年度";
                    $result = false;
                }
            }
            if ($result) {
                foreach($targetYears as $targetYear) {
                    $ProductMembership = $app['eccube.repository.product_membership']->getProductMembershipByMembershipYear($targetYear);
                    $result = $this->insertMembershipPaymentRecorde($app, $Customer, $ProductMembership);
                    if (!$result) {
                        $app->addError('受注登録に失敗しました', 'admin');
                        break;
                    }
                }
            } else {
                $app->addError($requested . 'は、すでに入金実績が存在します', 'admin');
            }
            if ($result) {
                $app->addSuccess('選択された年度の年会費入金実績を登録しました', 'admin');
                $connoection->commit();
            } else {
                $connoection->rollBack();
            }
        }
        log_info('選択年会費入金実績作成完了', array($id));
        return $app->redirect($app->url('admin_customer_annual_fee_report', array('id' => $id)));
    }

    /**
     * 受講履歴.
     * @param Application $app
     * @param Request $request
     * @param $id Customer ID
     * @return StreamedResponse
     */
    public function trainingOrderHistory(Application $app, Request $request, $id)
    {
        $Customer = $app['eccube.repository.customer']->find($id);
        $orders = $app['eccube.repository.order']->getProductTrainingOrders($app, $Customer);

        $trainingOrders = [];

        foreach ($orders as $order) {
            foreach ($order->getOrderDetails() as $orderDetail) {
                //mtb_product_type.id 4 == 講習会
                if ($orderDetail->getProduct()->getProductClasses()[0]->getProductType()->getId() == 4) {
                    $trainingOrders[] = [
                            $orderDetail->getProduct()->getName(),
                            $orderDetail->getProduct()->getProductTraining()->getTrainingDateStartDay(),
                            $order->getCreateDate()->format('Y/m/d'),
                            $orderDetail->getProduct()->getId()
                        ];
                }
            }
        }

        return $app->render('Customer/training_order_history.twig', array(
            'Customer' => $Customer,
            'trainingOrders' => $trainingOrders
        ));
    }

    /**
     * 寄付金入金照会.
     * @param Application $app
     * @param Request $request
     * @param $id Customer ID
     * @return StreamedResponse
     */
    public function contributionOrderHistory(Application $app, Request $request, $id)
    {
        $Customer = $app['eccube.repository.customer']->find($id);
        //dtb_category.id 2 == 寄付
        $ProductCategory = $app['eccube.repository.category']->find(2); 
        $Products = $app['eccube.repository.product_category']->getProductsForCategory($ProductCategory);

        $orders = $app['eccube.repository.order']->getContributionOrders($app, $Customer, $Products);

        $contributionOrders = [];

        foreach ($orders as $order) {
            foreach ($order->getOrderDetails() as $orderDetail) {
                $contributionOrders[] = [
                        $orderDetail->getProduct()->getName(),
                        $order->getPayment()->getMethod(),
                        $order->getCreateDate()->format('Y/m/d'),
                        $order->getId(),
                        $orderDetail->getPrice() * $orderDetail->getQuantity()
                    ];
            }
        }

        return $app->render('Customer/contribution_order_history.twig', array(
            'Customer' => $Customer,
            'contributionOrders' => $contributionOrders
        ));
    }

    private function insertMembershipOrdere(Application $app, \Eccube\Entity\Customer $Customer, \Eccube\Entity\ProductMembership $ProductMembership) {
        $result = true;
        try {
            $Product = $ProductMembership->getProduct();
            $price = $Product->getPrice02IncTaxMax();
            $productClass = $Product->getProductClasses()[0];
            $deviceType = $app['eccube.repository.master.device_type']->find(DeviceType::DEVICE_TYPE_ADMIN);
            $CommonTaxRule = $app['eccube.repository.tax_rule']->getByRule($Product, $Product->getProductClasses()[0]);
            $taxRate = $CommonTaxRule->getTaxRate();
            $taxRuleId = $CommonTaxRule->getId();
            $OrderStatus = $app['eccube.repository.master.order_status']->find(1);
            $Payment = $app['eccube.repository.payment']->find(3);
            $keepAliveTime = date('Y-m-d H:i:s');

            // 空のエンティティを作成.
            $order = new \Eccube\Entity\Order();
            $order->setDeviceType($deviceType);
            // 受注情報を設定
            $order->setCustomer($Customer)
                        ->setDiscount(0)
                        ->setSubtotal($price)
                        ->setTotal($price)
                        ->setPaymentTotal($price)
                        ->setCharge(0)
                        ->setTax($price - $Product->getPrice02Min())
                        ->setDeliveryFeeTotal(0)
                        ->setOrderStatus($OrderStatus)
                        ->setDelFlg(Constant::DISABLED)
                        ->setName01($Customer->getName01())
                        ->setName02($Customer->getName02())
                        ->setKana01($Customer->getKana01())
                        ->setKana02($Customer->getKana02())
                        ->setPref($Customer->getPref())
                        ->setZip01($Customer->getZip01())
                        ->setZip02($Customer->getZip02())
                        ->setAddr01($Customer->getAddr01())
                        ->setAddr02($Customer->getAddr02())
                        ->setEmail($Customer->getEmail())
                        ->setTel01($Customer->getTel01())
                        ->setTel02($Customer->getTel02())
                        ->setTel03($Customer->getTel03())
                        ->setFax01($Customer->getFax01())
                        ->setFax02($Customer->getFax02())
                        ->setFax03($Customer->getFax03())
                        ->setSex($Customer->getSex())
                        ->setJob($Customer->getJob())
                        ->setBirth($Customer->getBirth())
                        ->setPayment($Payment)
                        ->setPaymentMethod($Payment->getMethod());
            // 受注明細を作成
            $OrderDetail = new \Eccube\Entity\OrderDetail();
            $OrderDetail->setPriceIncTax($price);
            $OrderDetail->setProductName($Product->getName());
            $OrderDetail->setProductCode($productClass->getCode());
            $OrderDetail->setPrice($Product->getPrice02Min());
            $OrderDetail->setQuantity(1);
            $OrderDetail->setTaxRate($taxRate);
            $OrderDetail->setTaxRule($taxRuleId);
            $OrderDetail->setProduct($Product);
            $OrderDetail->setProductClass($productClass);
            $OrderDetail->setClassName1($Product->getClassName1());
            $OrderDetail->setClassName2($Product->getClassName2());
            $OrderDetail->setOrder($order);
            $order->addOrderDetail($OrderDetail);

            // 会員の場合、購入回数、購入金額などを更新
            $app['eccube.repository.customer']->updateBuyData($app, $Customer, 1);

            // 配送業者・お届け時間の更新
            $NewShipmentItem = new ShipmentItem();
            $NewShipmentItem
                ->setProduct($Product)
                ->setProductClass($productClass)
                ->setProductName($Product->getName())
                ->setProductCode($productClass->getCode())
                ->setClassCategoryName1($OrderDetail->getClassCategoryName1())
                ->setClassCategoryName2($OrderDetail->getClassCategoryName2())
                ->setClassName1($Product->getClassName1())
                ->setClassName2($Product->getClassName2())
                ->setPrice($Product->getPrice02Min())
                ->setQuantity(1)
                ->setOrder($order);

            // 配送商品の設定.
            $Shipping = new \Eccube\Entity\Shipping();
            $Shipping->setDelFlg(0);
            $Shipping->setName01($Customer->getName01());
            $Shipping->setName02($Customer->getName02());
            $Shipping->setKana01($Customer->getKana01());
            $Shipping->setKana02($Customer->getKana02());
            $NewShipmentItem->setShipping($Shipping);
            $Shipping->getShipmentItems()->add($NewShipmentItem);
            $order->addShipping($Shipping);
            $Shipping->setOrder($order);

            // 受注日/発送日/入金日の更新.
            $order->setOrderDate(new \DateTime());

            $app['orm.em']->persist($order);
            $app['orm.em']->flush();
        } catch (\Exception $ex) {
            $result = false;
        }
        return $result;
    }

    private function insertMembershipPaymentRecorde(Application $app, \Eccube\Entity\Customer $Customer, \Eccube\Entity\ProductMembership $ProductMembership) {
        $result = true;
        try {
            $Product = $ProductMembership->getProduct();
            $orders = $app['eccube.repository.order']->getProductOrder($Customer, $Product);
            if ($orders) {
                if (count($orders) == 1) {
                    $order = $orders[0];
                    // 単一受注のみ
                    if (1 == count($order->getOrderDetails())) {
                        if (!in_array($order->getOrderStatus()->getId(), array(5, 6))) {
                            // 受注日/発送日/入金日の更新.
                            $order->setOrderDate(new \DateTime());
                            $order->setOrderStatus($app['eccube.repository.master.order_status']->find(5));
                            $app['orm.em']->persist($order);
                            $app['orm.em']->flush();
                        }
                    } else {
                        throw new Exception('複数商品受注のため更新不可');
                    }
                } else {
                    throw new Exception('複数受注のため更新不可');
                }
            } else {
                $price = $Product->getPrice02IncTaxMax();
                $productClass = $Product->getProductClasses()[0];
                $deviceType = $app['eccube.repository.master.device_type']->find(DeviceType::DEVICE_TYPE_ADMIN);
                $CommonTaxRule = $app['eccube.repository.tax_rule']->getByRule($Product, $Product->getProductClasses()[0]);
                $taxRate = $CommonTaxRule->getTaxRate();
                $taxRuleId = $CommonTaxRule->getId();
                $OrderStatus = $app['eccube.repository.master.order_status']->find(1);
                $Payment = $app['eccube.repository.payment']->find(3);
                $keepAliveTime = date('Y-m-d H:i:s');

                // 空のエンティティを作成.
                $order = new \Eccube\Entity\Order();
                $order->setDeviceType($deviceType);
                // 受注情報を設定
                $order->setCustomer($Customer)
                            ->setDiscount(0)
                            ->setSubtotal($price)
                            ->setTotal($price)
                            ->setPaymentTotal($price)
                            ->setCharge(0)
                            ->setTax($price - $Product->getPrice02Min())
                            ->setDeliveryFeeTotal(0)
                            ->setOrderStatus($OrderStatus)
                            ->setDelFlg(Constant::DISABLED)
                            ->setName01($Customer->getName01())
                            ->setName02($Customer->getName02())
                            ->setKana01($Customer->getKana01())
                            ->setKana02($Customer->getKana02())
                            ->setPref($Customer->getPref())
                            ->setZip01($Customer->getZip01())
                            ->setZip02($Customer->getZip02())
                            ->setAddr01($Customer->getAddr01())
                            ->setAddr02($Customer->getAddr02())
                            ->setEmail($Customer->getEmail())
                            ->setTel01($Customer->getTel01())
                            ->setTel02($Customer->getTel02())
                            ->setTel03($Customer->getTel03())
                            ->setFax01($Customer->getFax01())
                            ->setFax02($Customer->getFax02())
                            ->setFax03($Customer->getFax03())
                            ->setSex($Customer->getSex())
                            ->setJob($Customer->getJob())
                            ->setBirth($Customer->getBirth())
                            ->setPayment($Payment)
                            ->setPaymentMethod($Payment->getMethod());
                // 受注明細を作成
                $OrderDetail = new \Eccube\Entity\OrderDetail();
                $OrderDetail->setPriceIncTax($price);
                $OrderDetail->setProductName($Product->getName());
                $OrderDetail->setProductCode($productClass->getCode());
                $OrderDetail->setPrice($Product->getPrice02Min());
                $OrderDetail->setQuantity(1);
                $OrderDetail->setTaxRate($taxRate);
                $OrderDetail->setTaxRule($taxRuleId);
                $OrderDetail->setProduct($Product);
                $OrderDetail->setProductClass($productClass);
                $OrderDetail->setClassName1($Product->getClassName1());
                $OrderDetail->setClassName2($Product->getClassName2());
                $OrderDetail->setOrder($order);
                $order->addOrderDetail($OrderDetail);

                // 会員の場合、購入回数、購入金額などを更新
                $app['eccube.repository.customer']->updateBuyData($app, $Customer, 1);

                // 配送業者・お届け時間の更新
                $NewShipmentItem = new ShipmentItem();
                $NewShipmentItem
                    ->setProduct($Product)
                    ->setProductClass($productClass)
                    ->setProductName($Product->getName())
                    ->setProductCode($productClass->getCode())
                    ->setClassCategoryName1($OrderDetail->getClassCategoryName1())
                    ->setClassCategoryName2($OrderDetail->getClassCategoryName2())
                    ->setClassName1($Product->getClassName1())
                    ->setClassName2($Product->getClassName2())
                    ->setPrice($Product->getPrice02Min())
                    ->setQuantity(1)
                    ->setOrder($order);

                // 配送商品の設定.
                $Shipping = new \Eccube\Entity\Shipping();
                $Shipping->setDelFlg(0);
                $Shipping->setName01($Customer->getName01());
                $Shipping->setName02($Customer->getName02());
                $Shipping->setKana01($Customer->getKana01());
                $Shipping->setKana02($Customer->getKana02());
                $NewShipmentItem->setShipping($Shipping);
                $Shipping->getShipmentItems()->add($NewShipmentItem);
                $order->addShipping($Shipping);
                $Shipping->setOrder($order);

                // 受注日/発送日/入金日の更新.
                $order->setOrderDate(new \DateTime());

                $app['orm.em']->persist($order);
                $app['orm.em']->flush();
            }

            // 入金記録を作成
            $MembershipBillingStatus = new \Eccube\Entity\MembershipBillingStatus();
            $MembershipBillingStatus->setCustomer($Customer)
                                    ->setProductMembership($ProductMembership)
                                    ->setStatus($app['eccube.repository.master.billing_status']->find(1));

            $app['orm.em']->persist($MembershipBillingStatus);
            $app['orm.em']->flush();
        } catch (\Exception $ex) {
            log_info('Exception:' . $ex);
            $result = false;
        }
        return $result;
    }

    public function membershipExemption(Application $app, Request $request)
    {
        $result = $app['eccube.repository.customer_basic_info']->clearMembershipExemption();
        return $app->json(array('message' => 'success', 'result' => $result), 200);
    }
}
