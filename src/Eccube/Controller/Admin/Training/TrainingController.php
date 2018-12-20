<?php
/*
 * This file is Customized File
 */


namespace Eccube\Controller\Admin\Training;

use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Master\CsvType;
use Eccube\Entity\ProductTag;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Service\CsvExportService;
use Eccube\Util\FormUtil;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

class TrainingController extends AbstractController
{
    public function indexType(Application $app, Request $request, $page_no = null)
    {

        $session = $app['session'];

        $builder = $app['form.factory']
            ->createBuilder('admin_search_training_type');

        $searchForm = $builder->getForm();

        $pagination = array();

        $pageMaxis = $app['eccube.repository.master.page_max']->findAll();

        // 表示件数は順番で取得する、1.SESSION 2.設定ファイル
        $page_count = $session->get('eccube.admin.training_type.search.page_count', $app['config']['default_page_count']);
        // 表示件数
        $page_count_param = $request->get('page_count');
        // 表示件数はURLパラメターから取得する
        if ($page_count_param && is_numeric($page_count_param)) {
            foreach ($pageMaxis as $pageMax) {
                if ($page_count_param == $pageMax->getName()) {
                    $page_count = $pageMax->getName();
                    // 表示件数入力値正し場合はSESSIONに保存する
                    $session->set('eccube.admin.training_type.search.page_count', $page_count);
                    break;
                }
            }
        }

        $page_status = null;

        if ('POST' === $request->getMethod()) {

            $searchForm->handleRequest($request);

            if ($searchForm->isValid()) {
                $searchData = $searchForm->getData();

                // paginator
                $qb = $app['eccube.repository.master.training_type']->getQueryBuilderBySearchData($searchData);
                $page_no = 1;

                $pagination = $app['paginator']()->paginate(
                    $qb,
                    $page_no,
                    $page_count,
                    array('wrap-queries' => true)
                );

                // sessionに検索条件を保持
                $viewData = FormUtil::getViewData($searchForm);
                $session->set('eccube.admin.training_type.search', $viewData);
                $session->set('eccube.admin.training_type.search.page_no', $page_no);
            }
        } else {
            if (is_null($page_no) && $request->get('resume') != Constant::ENABLED) {
                // sessionを削除
                $session->remove('eccube.admin.training_type.search');
                $session->remove('eccube.admin.training_type.search.page_no');
                $session->remove('eccube.admin.training_type.search.page_count');
            } else {
                // pagingなどの処理
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.training_type.search.page_no'));
                } else {
                    $session->set('eccube.admin.training_type.search.page_no', $page_no);
                }
                $viewData = $session->get('eccube.admin.training_type.search');
                if (!is_null($viewData)) {
                    // 表示件数
                    $page_count = $request->get('page_count', $page_count);
                    $searchData = FormUtil::submitAndGetData($searchForm, $viewData);
                    $session->set('eccube.admin.training_type.search', $viewData);

                    $qb = $app['eccube.repository.master.training_type']->getQueryBuilderBySearchData($searchData);

                    $pagination = $app['paginator']()->paginate(
                        $qb,
                        $page_no,
                        $page_count,
                        array('wrap-queries' => true)
                    );
                }
            }
        }

        return $app->render('Training/index_type.twig', array(
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_status' => $page_status,
            'page_count' => $page_count,
        ));
    }

    public function editType(Application $app, Request $request, $id = null)
    {
        if (is_null($id)) {
            $TrainingType = new \Eccube\Entity\Master\TrainingType();
        } else {
            $TrainingType = $app['eccube.repository.master.training_type']->find($id);
            if (!$TrainingType) {
                throw new NotFoundHttpException();
            }
        }

        $builder = $app['form.factory']
            ->createBuilder('admin_training_type', $TrainingType);

        $form = $builder->getForm();

        if ('POST' === $request->getMethod()) {
            $request_data = $request->request->all();
            $request_data['admin_training']['class']['product_type'] = $app['config']['product_type_training'];
            $request->request->add($request_data);
            $form->handleRequest($request);
            if ($form->isValid()) {
                log_info('講習会種別登録開始', array($id));
                // 講習会種別の登録
                $TrainingType = $form->getData();
                $app['orm.em']->persist($TrainingType);
                $app['orm.em']->flush();

                log_info('講習会種別登録完了', array($id));

                $app->addSuccess('admin.register.complete', 'admin');

                return $app->redirect($app->url('admin_training_type_edit', array(
                    'id' => $TrainingType->getId(),
                )));
            } else {
                log_info('講習会種別登録チェックエラー', array($id));
                $app->addError('admin.register.failed', 'admin');
            }
        }

        // 検索結果の保持
        $builder = $app['form.factory']
            ->createBuilder('admin_search_training_type');

        $searchForm = $builder->getForm();

        if ('POST' === $request->getMethod()) {
            $searchForm->handleRequest($request);
        }

        return $app->render('Training/edit_type.twig', array(
            'TrainingType' => $TrainingType,
            'form' => $form->createView(),
            'searchForm' => $searchForm->createView(),
            'id' => $id,
        ));
    }

    public function indexProduct(Application $app, Request $request, $page_no = null)
    {

        $session = $app['session'];

        $builder = $app['form.factory']
            ->createBuilder('admin_search_training');

        $searchForm = $builder->getForm();

        $pagination = array();

        $disps = $app['eccube.repository.master.disp']->findAll();
        $pageMaxis = $app['eccube.repository.master.page_max']->findAll();

        // 表示件数は順番で取得する、1.SESSION 2.設定ファイル
        $page_count = $session->get('eccube.admin.product.search.page_count', $app['config']['default_page_count']);
        // 表示件数

        $page_count_param = $request->get('page_count');
        // 表示件数はURLパラメターから取得する
        if ($page_count_param && is_numeric($page_count_param)) {
            foreach ($pageMaxis as $pageMax) {
                if ($page_count_param == $pageMax->getName()) {
                    $page_count = $pageMax->getName();
                    // 表示件数入力値正し場合はSESSIONに保存する
                    $session->set('eccube.admin.product.search.page_count', $page_count);
                    break;
                }
            }
        }

        $page_status = null;
        $active = false;

        if ('POST' === $request->getMethod()) {

            $searchForm->handleRequest($request);

            if ($searchForm->isValid()) {
                $searchData = $searchForm->getData();

                // paginator
                $qb = $app['eccube.repository.product']->getQueryBuilderBySearchTrainingDataForAdmin($searchData);
                $page_no = 1;

                $event = new EventArgs(
                    array(
                        'qb' => $qb,
                        'searchData' => $searchData,
                    ),
                    $request
                );
                $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_PRODUCT_INDEX_SEARCH, $event);
                $searchData = $event->getArgument('searchData');

                $pagination = $app['paginator']()->paginate(
                    $qb,
                    $page_no,
                    $page_count,
                    array('wrap-queries' => true)
                );

                // sessionに検索条件を保持
                $viewData = FormUtil::getViewData($searchForm);
                $session->set('eccube.admin.product.search', $viewData);
                $session->set('eccube.admin.product.search.page_no', $page_no);
            }
        } else {
            if (is_null($page_no) && $request->get('resume') != Constant::ENABLED) {
                // sessionを削除
                $session->remove('eccube.admin.product.search');
                $session->remove('eccube.admin.product.search.page_no');
                $session->remove('eccube.admin.product.search.page_count');
            } else {
                // pagingなどの処理
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.product.search.page_no'));
                } else {
                    $session->set('eccube.admin.product.search.page_no', $page_no);
                }
                $viewData = $session->get('eccube.admin.product.search');
                if (!is_null($viewData)) {
                    // 公開ステータス
                    // 1:公開, 2:非公開, 3:在庫なし
                    $linkStatus = $request->get('status');
                    if (!empty($linkStatus)) {
                        // リンクステータスは在庫なし:3以外
                        if ($linkStatus != $app['config']['admin_product_stock_status']) {
                            $viewData['link_status'] = $linkStatus;
                            $viewData['stock_status'] = null;
                            $viewData['status'] = null;
                        } else {
                            // リンクステータスは在庫なし:3
                            $viewData['link_status'] = null;
                            $viewData['stock_status'] = Constant::DISABLED;
                            $viewData['status'] = null;
                        }
                        // ページステータスを設定します（リンクステータスAタグ表示のために）
                        $page_status = $linkStatus;
                    } else {
                        // すべてを選択
                        $viewData['link_status'] = null;
                        $viewData['stock_status'] = null;
                        if (!$viewData['status']) {
                            $viewData['status'] = array();
                        }
                    }

                    // 表示件数
                    $page_count = $request->get('page_count', $page_count);
                    $searchData = FormUtil::submitAndGetData($searchForm, $viewData);
                    if ($viewData['link_status']) {
                        $searchData['link_status'] = $app['eccube.repository.master.disp']->find($viewData['link_status']);
                    }
                    // リンクステータス[在庫なし]設定されている場合は検索パラメター設定する
                    if (isset($viewData['stock_status'])) {
                        $searchData['stock_status'] = $viewData['stock_status'];
                    }

                    $session->set('eccube.admin.product.search', $viewData);

                    $qb = $app['eccube.repository.product']->getQueryBuilderBySearchDataForAdmin($searchData);

                    $event = new EventArgs(
                        array(
                            'qb' => $qb,
                            'searchData' => $searchData,
                        ),
                        $request
                    );
                    $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_PRODUCT_INDEX_SEARCH, $event);
                    $searchData = $event->getArgument('searchData');


                    $pagination = $app['paginator']()->paginate(
                        $qb,
                        $page_no,
                        $page_count,
                        array('wrap-queries' => true)
                    );
                }
            }
        }

        return $app->render('Training/index_product.twig', array(
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'disps' => $disps,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_status' => $page_status,
            'page_count' => $page_count,
            'active' => $active,
        ));
    }

    public function indexProductByStudent(Application $app, Request $request, $page_no = null)
    {

        $session = $app['session'];

        $builder = $app['form.factory']
            ->createBuilder('admin_search_training');

        $searchForm = $builder->getForm();

        $pagination = array();

        $disps = $app['eccube.repository.master.disp']->findAll();
        $pageMaxis = $app['eccube.repository.master.page_max']->findAll();

        // 表示件数は順番で取得する、1.SESSION 2.設定ファイル
        $page_count = $session->get('eccube.admin.pruductbystudent.search.page_count', $app['config']['default_page_count']);
        // 表示件数

        $page_count_param = $request->get('page_count');
        // 表示件数はURLパラメターから取得する
        if ($page_count_param && is_numeric($page_count_param)) {
            foreach ($pageMaxis as $pageMax) {
                if ($page_count_param == $pageMax->getName()) {
                    $page_count = $pageMax->getName();
                    // 表示件数入力値正し場合はSESSIONに保存する
                    $session->set('eccube.admin.pruductbystudent.search.page_count', $page_count);
                    break;
                }
            }
        }

        $page_status = null;
        $active = false;

        if ('POST' === $request->getMethod()) {

            $searchForm->handleRequest($request);

            if ($searchForm->isValid()) {
                $searchData = $searchForm->getData();

                // paginator
                $qb = $app['eccube.repository.product']->getQueryBuilderBySearchOrderedTrainingDataForAdmin($searchData);
                $page_no = 1;

                $event = new EventArgs(
                    array(
                        'qb' => $qb,
                        'searchData' => $searchData,
                    ),
                    $request
                );
                $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_PRODUCT_INDEX_SEARCH, $event);
                $searchData = $event->getArgument('searchData');

                $pagination = $app['paginator']()->paginate(
                    $qb,
                    $page_no,
                    $page_count,
                    array('wrap-queries' => true)
                );

                // sessionに検索条件を保持
                $viewData = FormUtil::getViewData($searchForm);
                $session->set('eccube.admin.pruductbystudent.search', $viewData);
                $session->set('eccube.admin.pruductbystudent.search.page_no', $page_no);
            }
        } else {
            if (is_null($page_no) && $request->get('resume') != Constant::ENABLED) {
                // sessionを削除
                $session->remove('eccube.admin.pruductbystudent.search');
                $session->remove('eccube.admin.pruductbystudent.search.page_no');
                $session->remove('eccube.admin.pruductbystudent.search.page_count');
            } else {
                // pagingなどの処理
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.pruductbystudent.search.page_no'));
                } else {
                    $session->set('eccube.admin.pruductbystudent.search.page_no', $page_no);
                }
                $viewData = $session->get('eccube.admin.pruductbystudent.search');
                if (!is_null($viewData)) {
                    // 公開ステータス
                    // 1:公開, 2:非公開, 3:在庫なし
                    $linkStatus = $request->get('status');
                    if (!empty($linkStatus)) {
                        // リンクステータスは在庫なし:3以外
                        if ($linkStatus != $app['config']['admin_product_stock_status']) {
                            $viewData['link_status'] = $linkStatus;
                            $viewData['stock_status'] = null;
                            $viewData['status'] = null;
                        } else {
                            // リンクステータスは在庫なし:3
                            $viewData['link_status'] = null;
                            $viewData['stock_status'] = Constant::DISABLED;
                            $viewData['status'] = null;
                        }
                        // ページステータスを設定します（リンクステータスAタグ表示のために）
                        $page_status = $linkStatus;
                    } else {
                        // すべてを選択
                        $viewData['link_status'] = null;
                        $viewData['stock_status'] = null;
                        if (!$viewData['status']) {
                            $viewData['status'] = array();
                        }
                    }

                    // 表示件数
                    $page_count = $request->get('page_count', $page_count);
                    $searchData = FormUtil::submitAndGetData($searchForm, $viewData);
                    if ($viewData['link_status']) {
                        $searchData['link_status'] = $app['eccube.repository.master.disp']->find($viewData['link_status']);
                    }
                    // リンクステータス[在庫なし]設定されている場合は検索パラメター設定する
                    if (isset($viewData['stock_status'])) {
                        $searchData['stock_status'] = $viewData['stock_status'];
                    }

                    $session->set('eccube.admin.pruductbystudent.search', $viewData);

                    $qb = $app['eccube.repository.product']->getQueryBuilderBySearchOrderedTrainingDataForAdmin($searchData);

                    $event = new EventArgs(
                        array(
                            'qb' => $qb,
                            'searchData' => $searchData,
                        ),
                        $request
                    );
                    $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_PRODUCT_INDEX_SEARCH, $event);
                    $searchData = $event->getArgument('searchData');


                    $pagination = $app['paginator']()->paginate(
                        $qb,
                        $page_no,
                        $page_count,
                        array('wrap-queries' => true)
                    );
                }
            }
        }

        return $app->render('Training/index_product_by_student.twig', array(
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'disps' => $disps,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_status' => $page_status,
            'page_count' => $page_count,
            'active' => $active,
        ));
    }

    public function indexStudent(Application $app, Request $request, $id = null, $page_no = null)
    {
        if (is_null($id)) {
            return $app->redirect($app->url('admin_pruduct_by_student', array()));
        }
        $session = $app['session'];

        $pagination = array();
        $pageMaxis = $app['eccube.repository.master.page_max']->findAll();
        $attendanceDenialReasons = $app['eccube.repository.master.attendance_denial_reason']->findAll();



        // 表示件数は順番で取得する、1.SESSION 2.設定ファイル
        $page_count = $session->get('eccube.admin.student.search.page_count', $app['config']['default_page_count']);
        // 表示件数

        $page_count_param = $request->get('page_count');
        // 表示件数はURLパラメターから取得する
        if ($page_count_param && is_numeric($page_count_param)) {
            foreach ($pageMaxis as $pageMax) {
                if ($page_count_param == $pageMax->getName()) {
                    $page_count = $pageMax->getName();
                    // 表示件数入力値正し場合はSESSIONに保存する
                    $session->set('eccube.admin.student.search.page_count', $page_count);
                    break;
                }
            }
        }

        $page_status = null;

        if ('POST' === $request->getMethod()) {
            // paginator
            $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchTrainingProductIds($id);
            $page_no = 1;
            $pagination = $app['paginator']()->paginate(
                $qb,
                $page_no,
                $page_count,
                array('wrap-queries' => true)
            );

            // sessionに検索条件を保持
            $session->set('eccube.admin.student.search.page_no', $page_no);
        } else {
            // pagingなどの処理
            if (is_null($page_no)) {
                $page_no = intval($session->get('eccube.admin.student.search.page_no'));
                if ($page_no < 1) {
                    $page_no = 1;
                }
            } else {
                $session->set('eccube.admin.student.search.page_no', $page_no);
            }
            // 表示件数
            $page_count = $request->get('page_count', $page_count);
            $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchTrainingProductIds($id);

            $pagination = $app['paginator']()->paginate(
                $qb,
                $page_no,
                $page_count,
                array('wrap-queries' => true)
            );
        }

        foreach ($pagination as $Customer) {
            foreach ($Customer->getAttendanceHistories() as $history) {
                if ($history->getProductTraining()->getId() == $id) {
                    $Customer->setAttendanceHistory($history);
                    break;
                }
            }
        }

        return $app->render('Training/index_student.twig', array(
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_status' => $page_status,
            'page_count' => $page_count,
            'ProductId' => $id,
            'attendanceDenialReasons' => $attendanceDenialReasons
        ));
    }

    /**
     * チラシ情報一覧を表示する。
     *
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexFlyer(Application $app, Request $request, $page_no = null)
    {
        $session = $app['session'];

        $builder = $app['form.factory']
            ->createBuilder('admin_search_flyer');

        $searchForm = $builder->getForm();

        $pagination = array();

        $disps = $app['eccube.repository.master.disp']->findAll();
        $pageMaxis = $app['eccube.repository.master.page_max']->findAll();

        // 表示件数は順番で取得する、1.SESSION 2.設定ファイル
        $page_count = $session->get('eccube.admin.flyer.search.page_count', $app['config']['default_page_count']);
        // 表示件数
        $page_count_param = $request->get('page_count');

        // 表示件数はURLパラメターから取得する
        if ($page_count_param && is_numeric($page_count_param)) {
            foreach ($pageMaxis as $pageMax) {
                if ($page_count_param == $pageMax->getName()) {
                    $page_count = $pageMax->getName();
                    // 表示件数入力値正し場合はSESSIONに保存する
                    $session->set('eccube.admin.flyer.search.page_count', $page_count);
                    break;
                }
            }
        }

        $page_status = null;
        $active = false;

        if ('POST' === $request->getMethod()) {

            $searchForm->handleRequest($request);

            if ($searchForm->isValid()) {
                $searchData = $searchForm->getData();

                // paginator
                $qb = $app['eccube.repository.flyer']->getQueryBuilderBySearchDataForAdmin($searchData);
                $page_no = 1;

                $pagination = $app['paginator']()->paginate(
                    $qb,
                    $page_no,
                    $page_count,
                    array('wrap-queries' => true)
                );

                // sessionに検索条件を保持
                $viewData = FormUtil::getViewData($searchForm);
                $session->set('eccube.admin.flyer.search', $viewData);
                $session->set('eccube.admin.flyer.search.page_no', $page_no);
            }
        } else {
            if (is_null($page_no) && $request->get('resume') != Constant::ENABLED) {
                // sessionを削除
                $session->remove('eccube.admin.flyer.search');
                $session->remove('eccube.admin.flyer.search.page_no');
                $session->remove('eccube.admin.flyer.search.page_count');
            } else {
                // pagingなどの処理
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.flyer.search.page_no'));
                } else {
                    $session->set('eccube.admin.flyer.search.page_no', $page_no);
                }
                $viewData = $session->get('eccube.admin.flyer.search');
                if (!is_null($viewData)) {
                    // 表示件数
                    $page_count = $request->get('page_count', $page_count);
                    $searchData = FormUtil::submitAndGetData($searchForm, $viewData);
                    $session->set('eccube.admin.flyer.search', $viewData);
                    $qb = $app['eccube.repository.flyer']->getQueryBuilderBySearchDataForAdmin($searchData);

                    $pagination = $app['paginator']()->paginate(
                        $qb,
                        $page_no,
                        $page_count,
                        array('wrap-queries' => true)
                    );
                }
            }
        }

        $builder = $app->form();

        $form = $builder->getForm();
        return $app->render('Training/index_flyer.twig', array(
            'searchForm' => $searchForm->createView(),
            'pagination' => $pagination,
            'pageMaxis' => $pageMaxis,
            'page_no' => $page_no,
            'page_status' => $page_status,
            'page_count' => $page_count,
            'active' => $active,
        ));
    }

    public function displayFlyer(Application $app, Request $request, $id = null)
    {
        if (!is_null($id)) {
            return $app->redirect($app->url('flyer_download', array('id' => $id, 'admin' => '1')));
        }
        $session = $request->getSession();
        $page_no = intval($session->get('eccube.admin.flyer.search.page_no'));
        $page_no = $page_no ? $page_no : Constant::ENABLED;
        return $app->redirect($app->url('admin_training_flyer_page', array('page_no' => $page_no)).'?resume='.Constant::ENABLED);
    }

    public function addImage(Application $app, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException('リクエストが不正です');
        }

        $images = $request->files->get('admin_training');

        $files = array();
        if (count($images) > 0) {
            foreach ($images as $img) {
                foreach ($img as $image) {
                    //ファイルフォーマット検証
                    $mimeType = $image->getMimeType();
                    if (0 !== strpos($mimeType, 'image')) {
                        throw new UnsupportedMediaTypeHttpException('ファイル形式が不正です');
                    }

                    $extension = $image->getClientOriginalExtension();
                    $filename = date('mdHis').uniqid('_').'.'.$extension;
                    $image->move($app['config']['image_temp_realdir'], $filename);
                    $files[] = $filename;
                }
            }
        }

        return $app->json(array('files' => $files), 200);
    }

    public function editProduct(Application $app, Request $request, $id = null)
    {
        if (is_null($id)) {
            $Product = new \Eccube\Entity\Product();
            $ProductClass = new \Eccube\Entity\ProductClass();
            $ProductTraining = new \Eccube\Entity\ProductTraining();
            $Disp = $app['eccube.repository.master.disp']->find(\Eccube\Entity\Master\Disp::DISPLAY_HIDE);
            $ProductType = $app['eccube.repository.master.product_type']->find($app['config']['product_type_training']);
            $Product
                ->setDelFlg(Constant::DISABLED)
                ->addProductClass($ProductClass)
                ->setProductTraining($ProductTraining)
                ->setStatus($Disp);
            $ProductClass
                ->setDelFlg(Constant::DISABLED)
                ->setStockUnlimited(true)
                ->setProductType($ProductType)
                ->setProduct($Product);
            $ProductStock = new \Eccube\Entity\ProductStock();
            $ProductClass->setProductStock($ProductStock);
            $ProductStock->setProductClass($ProductClass);
            $ProductTraining->setProduct($Product);
        } else {
            $Product = $app['eccube.repository.product']->find($id);
            if (!$Product) {
                throw new NotFoundHttpException();
            }
            if (count($Product->getProductClasses()) < 1) {
                throw new MethodNotAllowedHttpException();
            }
            $ProductClasses = $Product->getProductClasses();
            $ProductClass = $ProductClasses[0];
            $ProductStock = $ProductClasses[0]->getProductStock();
            $ProductTraining = $Product->getProductTraining();
        }

        $builder = $app['form.factory']
            ->createBuilder('admin_training', $Product);

        $form = $builder->getForm();
        $form['product_training']->setData($ProductTraining);
        $ProductClass->setStockUnlimited((boolean)$ProductClass->getStockUnlimited());
        $form['class']->setData($ProductClass);

        $Tags = array();
        $ProductTags = $Product->getProductTag();
        foreach ($ProductTags as $ProductTag) {
            $Tags[] = $ProductTag->getTag();
        }
        $form['Tag']->setData($Tags);

        // ファイルの登録
        $images = array();
        $ProductImages = $Product->getProductImage();
        foreach ($ProductImages as $ProductImage) {
            $images[] = $ProductImage->getFileName();
        }
        $form['images']->setData($images);

        if ('POST' === $request->getMethod()) {
            $request_data = $request->request->all();
            $request_data['admin_training']['class']['product_type'] = $app['config']['product_type_training'];
            $request->request->add($request_data);
            $form->handleRequest($request);
            if ($form->isValid()) {
                log_info('講習会登録開始', array($id));
                // 講習会情報の登録
                $Product = $form->getData();
                $ProductClass = $form['class']->getData();

                // 個別消費税
                $BaseInfo = $app['eccube.repository.base_info']->get();
                if ($BaseInfo->getOptionProductTaxRule() == Constant::ENABLED) {
                    if ($ProductClass->getTaxRate() !== null) {
                        if ($ProductClass->getTaxRule()) {
                            if ($ProductClass->getTaxRule()->getDelFlg() == Constant::ENABLED) {
                                $ProductClass->getTaxRule()->setDelFlg(Constant::DISABLED);
                            }

                            $ProductClass->getTaxRule()->setTaxRate($ProductClass->getTaxRate());
                        } else {
                            $taxrule = $app['eccube.repository.tax_rule']->newTaxRule();
                            $taxrule->setTaxRate($ProductClass->getTaxRate());
                            $taxrule->setApplyDate(new \DateTime());
                            $taxrule->setProduct($Product);
                            $taxrule->setProductClass($ProductClass);
                            $ProductClass->setTaxRule($taxrule);
                        }
                    } else {
                        if ($ProductClass->getTaxRule()) {
                            $ProductClass->getTaxRule()->setDelFlg(Constant::ENABLED);
                        }
                    }
                }
                $app['orm.em']->persist($ProductClass);

                // 在庫情報を作成
                if (!$ProductClass->getStockUnlimited()) {
                    $ProductStock->setStock($ProductClass->getStock());
                } else {
                    // 在庫無制限時はnullを設定
                    $ProductStock->setStock(null);
                }
                $app['orm.em']->persist($ProductStock);

                $ProductTraining = $form['product_training']->getData();
                $ProductTraining->setTrainingDateStart(new \DateTime(date('Y-m-d H:i:s', strtotime($request_data['admin_training']['product_training']['training_date_start']))));
                $ProductTraining->setTrainingDateEnd(new \DateTime(date('Y-m-d H:i:s', strtotime($request_data['admin_training']['product_training']['training_date_end']))));
                $ProductTraining->setAcceptLimitDate(new \DateTime(date('Y-m-d H:i:s', strtotime($request_data['admin_training']['product_training']['accept_limit_date']))));
                $app['orm.em']->persist($ProductTraining);

                // カテゴリの登録
                // 一度クリア
                /* @var $Product \Eccube\Entity\Product */
                foreach ($Product->getProductCategories() as $ProductCategory) {
                    $Product->removeProductCategory($ProductCategory);
                    $app['orm.em']->remove($ProductCategory);
                }
                // 画像の登録
                $add_images = $form->get('add_images')->getData();
                foreach ($add_images as $add_image) {
                    $ProductImage = new \Eccube\Entity\ProductImage();
                    $ProductImage
                        ->setFileName($add_image)
                        ->setProduct($Product)
                        ->setRank(1);
                    $Product->addProductImage($ProductImage);
                    $app['orm.em']->persist($ProductImage);

                    // 移動
                    $file = new File($app['config']['image_temp_realdir'].'/'.$add_image);
                    $file->move($app['config']['image_save_realdir']);
                }

                // 画像の削除
                $delete_images = $form->get('delete_images')->getData();
                foreach ($delete_images as $delete_image) {
                    $ProductImage = $app['eccube.repository.product_image']
                        ->findOneBy(array('file_name' => $delete_image));

                    // 追加してすぐに削除した画像は、Entityに追加されない
                    if ($ProductImage instanceof \Eccube\Entity\ProductImage) {
                        $Product->removeProductImage($ProductImage);
                        $app['orm.em']->remove($ProductImage);

                    }
                    $app['orm.em']->persist($Product);

                    // 削除
                    if (!empty($delete_image)) {
                        $fs = new Filesystem();
                        $fs->remove($app['config']['image_save_realdir'].'/'.$delete_image);
                    }
                }
                $app['orm.em']->persist($Product);
                $app['orm.em']->flush();

                $count = 1;
                $Category = $app['eccube.repository.category']->find(\Eccube\Entity\Category::TRAINING_CATEGORY);
                foreach ($Category->getPath() as $ParentCategory) {
                    if (!isset($categoriesIdList[$ParentCategory->getId()])) {
                        $ProductCategory = $this->createProductCategory($Product, $ParentCategory, $count);
                        $app['orm.em']->persist($ProductCategory);
                        $count++;
                        /* @var $Product \Eccube\Entity\Product */
                        $Product->addProductCategory($ProductCategory);
                        $categoriesIdList[$ParentCategory->getId()] = true;
                    }
                }
                if (!isset($categoriesIdList[$Category->getId()])) {
                    $ProductCategory = $this->createProductCategory($Product, $Category, $count);
                    $app['orm.em']->persist($ProductCategory);
                    $count++;
                    /* @var $Product \Eccube\Entity\Product */
                    $Product->addProductCategory($ProductCategory);
                    $categoriesIdList[$Category->getId()] = true;
                }

                // 商品タグの登録
                $Tags = $form->get('Tag')->getData();
                foreach ($Tags as $Tag) {
                    $ProductTag = new ProductTag();
                    $ProductTag
                        ->setProduct($Product)
                        ->setTag($Tag);
                    $Product->addProductTag($ProductTag);
                    $app['orm.em']->persist($ProductTag);
                }

                $Product->setUpdateDate(new \DateTime());
                $app['orm.em']->flush();

                log_info('講習会登録完了', array($id));

                $app->addSuccess('admin.register.complete', 'admin');

                return $app->redirect($app->url('admin_training_edit', array(
                    'id' => $Product->getId(),
                )));
            } else {
                log_info('講習会登録チェックエラー', array($id));
                foreach ($form->getErrors(true) as $Error) { 
                    log_info('error:', array($Error->getOrigin()->getName(), $Error->getMessage()));
                }
                $app->addError('admin.register.failed', 'admin');
            }
        }

        // 検索結果の保持
        $builder = $app['form.factory']
            ->createBuilder('admin_search_training');

        $searchForm = $builder->getForm();

        if ('POST' === $request->getMethod()) {
            $searchForm->handleRequest($request);
        }

        return $app->render('Training/edit_product.twig', array(
            'Product' => $Product,
            'form' => $form->createView(),
            'searchForm' => $searchForm->createView(),
            'id' => $id,
        ));
    }

    /**
     * チラシ情報を登録・編集する。
     *
     * @param Application $app
     * @param Request $request
     * @param integer $id
     * @throws NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editFlyer(Application $app, Request $request, $id = null)
    {
        if ($id) {
            $Flyer = $app['eccube.repository.flyer']->find($id);
            if (!$Flyer) {
                throw new NotFoundHttpException();
            }
        } else {
            $Flyer = new \Eccube\Entity\Flyer();
        }

        $builder = $app['form.factory']
            ->createBuilder('admin_flyer', $Flyer);

        $form = $builder->getForm();

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                log_info('チラシ登録開始', array($id));
                $Flyer = $form->getData();
                $ProductTraining = $app['eccube.repository.product_training']->find($request->get('admin_flyer')['product_training_id']);
                $Flyer->setDispFrom(new \DateTime(date('Y-m-d H:i:s', strtotime($request->get('admin_flyer')['disp_from']))));
                $Flyer->setDispTo(new \DateTime(date('Y-m-d H:i:s', strtotime($request->get('admin_flyer')['disp_to']))));
                $Flyer->setProductTraining($ProductTraining);
                $app['orm.em']->persist($Flyer);
                $app['orm.em']->flush();
                log_info('チラシ登録完了', array($id));

                $app->addSuccess('チラシの登録が完了しました', 'admin');
                return $app->redirect($app->url('admin_training_flyer_edit', array(
                    'id' => $Flyer->getId(),
                )));
            } else {
                $app->addError('チラシの登録に失敗しました', 'admin');
            }
        }

        $builder = $app['form.factory']
            ->createBuilder('admin_search_training');

        $searchForm = $builder->getForm();

        return $app->render('Training/edit_flyer.twig', array(
            'form' => $form->createView(),
            'searchTrainingModalForm' => $searchForm->createView(),
            'Flyer' => $Flyer,
        ));
    }

    /**
     * 講習会情報を検索する.
     *
     * @param Application $app
     * @param Request $request
     * @param integer $page_no
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function searchTrainingHtml(Application $app, Request $request, $page_no = null)
    {
        if ($request->isXmlHttpRequest()) {
            $app['monolog']->addDebug('search training start.');
            $page_count = $app['config']['default_page_count'];
            $session = $app['session'];

            if ('POST' === $request->getMethod()) {
                $page_no = 1;

                $searchData = array(
                    'multi' => $request->get('search_word'),
                );
                $session->set('eccube.admin.flyer.search.training', $searchData);
                $session->set('eccube.admin.flyer.search.training.page_no', $page_no);
            } else {
                $searchData = (array)$session->get('eccube.admin.flyer.search.training');
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.flyer.search.training.page_no'));
                } else {
                    $session->set('eccube.admin.flyer.search.training.page_no', $page_no);
                }
            }

            $qb = $app['eccube.repository.product']->getQueryBuilderBySearchOrderedTrainingDataForAdmin($searchData);
            $pagination = $app['paginator']()->paginate(
                $qb,
                $page_no,
                $page_count,
                array('wrap-queries' => true)
            );

            /** @var $Customers \Eccube\Entity\CustomerGroup[] */
            $Trainings = $pagination->getItems();

            if (empty($Trainings)) {
                $app['monolog']->addDebug('search training not found.');
            }

            $data = array();

            foreach ($Trainings as $Training) {
                $data[] = array(
                    'id' => $Training->getProductTraining()->getId(),
                    'name' => $Training->getName(),
                    'type_name' => $Training->getProductTraining()->getTrainingType()->getName(),
                    'place' => $Training->getProductTraining()->getPlace(),
                    'address' => $Training->getProductTraining()->getAddr01() . $Training->getProductTraining()->getAddr02(),
                );
            }

            return $app->render('Training/search_training.twig', array(
                'data' => $data,
                'pagination' => $pagination,
            ));
        }
    }

    /**
     * IDから講習会情報を検索する.
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function searchTrainingById(Application $app, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $app['monolog']->addDebug('search training by id start.');

            /** @var $Trining \Eccube\Entity\Trining */
            $Training = $app['eccube.repository.product_training']
                ->find($request->get('id'));

            if (is_null($Training)) {
                $app['monolog']->addDebug('search training by id not found.');

                return $app->json(array(), 404);
            }

            $app['monolog']->addDebug('search training by id found.');
            log_info('Trining ID:' . $Training->getId());
            log_info('Trining name:' . $Training->getProduct()->getName());

            $data = array('id' => $Training->getId(),
                        'name' => $Training->getProduct()->getName());
            return $app->json($data);
        }
    }

    /**
     * 指定したチラシ情報を削除する。
     *
     * @param Application $app
     * @param Request $request
     * @param integer $id
     * @throws NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteFlyer(Application $app, Request $request, $id)
    {
        $this->isTokenValid($app);
        $session = $request->getSession();
        $page_no = intval($session->get('eccube.admin.flyer.search.page_no'));
        $page_no = $page_no ? $page_no : Constant::ENABLED;

        if (!is_null($id)) {
            $TargetFlyer = $app['eccube.repository.flyer']->find($id);
            if (!$TargetFlyer) {
                return $app->redirect($app->url('admin_training_flyer_page', array('page_no' => $page_no)).'?resume='.Constant::ENABLED);
            }
            if ($TargetFlyer instanceof \Eccube\Entity\Flyer) {
                log_info('チラシ情報削除開始', array($id));
                $TargetFlyer->setDelFlg(Constant::ENABLED);
                $app['orm.em']->persist($TargetFlyer);
                $app['orm.em']->flush();
                log_info('チラシ情報削除完了', array($id));
                $app->addSuccess('チラシ情報を削除しました', 'admin');
            } else {
                log_info('商品削除エラー', array($id));
                $app->addError('チラシ情報の削除に失敗しました', 'admin');
            }
        } else {
            log_info('商品削除エラー', array($id));
            $app->addError('チラシ情報の削除に失敗しました', 'admin');
        }
        return $app->redirect($app->url('admin_training_flyer_page', array('page_no' => $page_no)).'?resume='.Constant::ENABLED);
    }

    /**
     * CSVの出力.
     * @param Application $app
     * @param Request $request
     * @return StreamedResponse
     */
    public function outCsvMemberList(Application $app, Request $request, $id = null)
    {
        // タイムアウトを無効にする.
        set_time_limit(0);

        // sql loggerを無効にする.
        $em = $app['orm.em'];
        $em->getConfiguration()->setSQLLogger(null);

        $response = new StreamedResponse();
        $response->setCallback(function () use ($app, $request, $id) {

            // CSV種別を元に初期化.
            $app['eccube.service.csv.export']->initCsvType(6);

            // ヘッダ行の出力.
            $app['eccube.service.csv.export']->exportHeader();

            // 会員データ検索用のクエリビルダを取得.
            $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchTrainingProductIds($id);

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
                    $ExportCsvRow->setData($csvService->getData($Csv, $Customer));

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
        $filename = 'memberList_' . $now->format('YmdHis') . '.csv';
        $response->headers->set('Content-Type', 'application/octet-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename=' . $filename);

        $response->send();

        log_info("CSVファイル名", array($filename));

        return $response;
    }

    public function printFaxAccept(Application $app, Request $request, $id = null)
    {
        if (is_null($id)){
            return $app->redirect($app->url('admin_pruduct_by_student', array()));
        }
        $modeAll = 1;
        $queryString = $request->getQueryString();
        if (!empty($queryString)) {
            // クエリーをparseする
            // idsX以外はない想定
            parse_str($queryString, $ary);
            foreach ($ary as $key => $val) {
                // キーが一致
                if (preg_match('/^modeAll$/', $key)) {
                    $modeAll = $val;
                }
            }
        }
        if ($modeAll == 0) {
            // requestから対象顧客IDの一覧を取得する.
            $ids = $this->getIds($request);
            if (count($ids) == 0) {
                $app->addError('処理対象が選択されていません', 'admin');
                log_info('The Customer cannot found!');
                return $app->redirect($app->url('admin_student', array('id' => $id)));
            }
            // 顧客情報取得
            $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchCustomerIds($ids)
                    ->getQuery()
                    ->getResult();
        } else {
            // 顧客情報取得
            $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchTrainingProductIds($id)
                    ->getQuery()
                    ->getResult();
        }

        // サービスの取得
        /* @var FaxAcceptPdfService $service */
        $service = $app['eccube.service.fax_accept_pdf'];
        $session = $request->getSession();

        // 顧客情報からPDFを作成する
        $status = $service->makePdf($customers, $app['eccube.repository.product']->find($id));

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('FAX受付票出力に失敗しました', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_student'), array('id' => $id));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('FaxAcceptPdfDownload success!', array());
        return $response;
    }

    public function printPaymentConfirm(Application $app, Request $request, $id = null)
    {
        if (is_null($id)){
            return $app->redirect($app->url('admin_pruduct_by_student', array()));
        }
        $modeAll = 1;
        $queryString = $request->getQueryString();
        if (!empty($queryString)) {
            // クエリーをparseする
            // idsX以外はない想定
            parse_str($queryString, $ary);
            foreach ($ary as $key => $val) {
                // キーが一致
                if (preg_match('/^modeAll$/', $key)) {
                    $modeAll = $val;
                }
            }
        }
        if ($modeAll == 0) {
            // requestから対象顧客IDの一覧を取得する.
            $ids = $this->getIds($request);
            if (count($ids) == 0) {
                $app->addError('処理対象が選択されていません', 'admin');
                log_info('The Customer cannot found!');
                return $app->redirect($app->url('admin_student', array('id' => $id)));
            }
            // 顧客情報取得
            $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchCustomerIds($ids)
                    ->getQuery()
                    ->getResult();
        } else {
            // 顧客情報取得
            $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchTrainingProductIds($id)
                    ->getQuery()
                    ->getResult();
        }

        // サービスの取得
        /* @var PaymentConfirmPdfService $service */
        $service = $app['eccube.service.payment_confirm_pdf'];
        $session = $request->getSession();

        // 顧客情報からPDFを作成する
        $status = $service->makePdf($customers, $app['eccube.repository.product']->find($id));

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('入金確認票出力に失敗しました', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_student'), array('id' => $id));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('PaymentConfirmPdfDownload success!', array());
        return $response;
    }

    public function printRegistrationConfirm(Application $app, Request $request, $id = null)
    {
        if (is_null($id)){
            return $app->redirect($app->url('admin_pruduct_by_student', array()));
        }
        $modeAll = 1;
        $queryString = $request->getQueryString();
        if (!empty($queryString)) {
            // クエリーをparseする
            // idsX以外はない想定
            parse_str($queryString, $ary);
            foreach ($ary as $key => $val) {
                // キーが一致
                if (preg_match('/^modeAll$/', $key)) {
                    $modeAll = $val;
                }
            }
        }
        if ($modeAll == 0) {
            // requestから対象顧客IDの一覧を取得する.
            $ids = $this->getIds($request);
            if (count($ids) == 0) {
                $app->addError('処理対象が選択されていません', 'admin');
                log_info('The Customer cannot found!');
                return $app->redirect($app->url('admin_student', array('id' => $id)));
            }
            // 顧客情報取得
            $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchCustomerIds($ids)
                    ->getQuery()
                    ->getResult();
        } else {
            // 顧客情報取得
            $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchTrainingProductIds($id)
                    ->getQuery()
                    ->getResult();
        }

        // サービスの取得
        /* @var RegistrationConfirmPdfService $service */
        $service = $app['eccube.service.registration_confirm_pdf'];
        $session = $request->getSession();

        // 顧客情報からPDFを作成する
        $status = $service->makePdf($customers, $app['eccube.repository.product']->find($id));

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('登録内容確認票出力に失敗しました', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_student'), array('id' => $id));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('RegistrationConfirmPdfService success!', array());
        return $response;
    }

    public function printNameTag(Application $app, Request $request, $id = null)
    {
        if (is_null($id)){
            return $app->redirect($app->url('admin_pruduct_by_student', array()));
        }
        $modeAll = 1;
        $queryString = $request->getQueryString();
        if (!empty($queryString)) {
            // クエリーをparseする
            // idsX以外はない想定
            parse_str($queryString, $ary);
            foreach ($ary as $key => $val) {
                // キーが一致
                if (preg_match('/^modeAll$/', $key)) {
                    $modeAll = $val;
                }
            }
        }
        if ($modeAll == 0) {
            // requestから対象顧客IDの一覧を取得する.
            $ids = $this->getIds($request);
            if (count($ids) == 0) {
                $app->addError('処理対象が選択されていません', 'admin');
                log_info('The Customer cannot found!');
                return $app->redirect($app->url('admin_student', array('id' => $id)));
            }
            // 顧客情報取得
            $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchCustomerIds($ids)
                    ->getQuery()
                    ->getResult();
        } else {
            // 顧客情報取得
            $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchTrainingProductIds($id)
                    ->getQuery()
                    ->getResult();
        }

        // サービスの取得
        /* @var NameTagPdfService $service */
        $service = $app['eccube.service.name_tag_pdf'];
        $session = $request->getSession();

        // 顧客情報からPDFを作成する
        $status = $service->makePdf($customers, $app['eccube.repository.product']->find($id));

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('名札出力に失敗しました', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_student'), array('id' => $id));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('NameTagPdfDownload success!', array());
        return $response;
    }

    public function printCertification(Application $app, Request $request, $id = null)
    {
        if (is_null($id)){
            return $app->redirect($app->url('admin_pruduct_by_student', array()));
        }
        $modeAll = 1;
        $queryString = $request->getQueryString();
        if (!empty($queryString)) {
            // クエリーをparseする
            // idsX以外はない想定
            parse_str($queryString, $ary);
            foreach ($ary as $key => $val) {
                // キーが一致
                if (preg_match('/^modeAll$/', $key)) {
                    $modeAll = $val;
                }
            }
        }
        if ($modeAll == 0) {
            // requestから対象顧客IDの一覧を取得する.
            $ids = $this->getIds($request);
            if (count($ids) == 0) {
                $app->addError('処理対象が選択されていません', 'admin');
                log_info('The Customer cannot found!');
                return $app->redirect($app->url('admin_student', array('id' => $id)));
            }
            // 顧客情報取得
            $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchCustomerIds($ids)
                    ->getQuery()
                    ->getResult();
        } else {
            // 顧客情報取得
            $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchTrainingProductIds($id)
                    ->getQuery()
                    ->getResult();
        }

        // サービスの取得
        /* @var RegularMemberListPdfService $service */
        $service = $app['eccube.service.certification_pdf'];
        $session = $request->getSession();

        // 顧客情報からPDFを作成する
        $status = $service->makePdf($customers, $app['eccube.repository.product']->find($id));

        // 異常終了した場合の処理
        if (!$status) {
            log_info('CertificationPdfDownload success!', array());
            $app->addError('認定証出力に失敗しました', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_student'), array('id' => $id));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('CertificationPdfDownload success!', array());
        return $response;
    }

    public function printMailLabel(Application $app, Request $request, $id = null)
    {
        if (is_null($id)){
            return $app->redirect($app->url('admin_pruduct_by_student', array()));
        }
        $modeAll = 1;
        $queryString = $request->getQueryString();
        if (!empty($queryString)) {
            // クエリーをparseする
            // idsX以外はない想定
            parse_str($queryString, $ary);
            foreach ($ary as $key => $val) {
                // キーが一致
                if (preg_match('/^modeAll$/', $key)) {
                    $modeAll = $val;
                }
            }
        }
        if ($modeAll == 0) {
            // requestから対象顧客IDの一覧を取得する.
            $ids = $this->getIds($request);
            if (count($ids) == 0) {
                $app->addError('処理対象が選択されていません', 'admin');
                log_info('The Customer cannot found!');
                return $app->redirect($app->url('admin_student', array('id' => $id)));
            }
            // 顧客情報取得
            $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchCustomerIds($ids)
                    ->getQuery()
                    ->getResult();
        } else {
            // 顧客情報取得
            $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchTrainingProductIds($id)
                    ->getQuery()
                    ->getResult();
        }

        // サービスの取得
        /* @var MailLabelPdfService $service */
        $service = $app['eccube.service.mail_label_pdf'];
        $session = $request->getSession();

        // 顧客情報からPDFを作成する
        $status = $service->makePdf($customers, $app['eccube.repository.product']->find($id));

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('郵送用ラベル出力に失敗しました', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_student', array('id' => $id)));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('MailLabelPdfDownload success!', array());
        return $response;
    }

    public function printCertificationSenddingNote(Application $app, Request $request, $id = null)
    {
        if (is_null($id)){
            return $app->redirect($app->url('admin_pruduct_by_student', array()));
        }
        $modeAll = 1;
        $queryString = $request->getQueryString();
        if (!empty($queryString)) {
            // クエリーをparseする
            // idsX以外はない想定
            parse_str($queryString, $ary);
            foreach ($ary as $key => $val) {
                // キーが一致
                if (preg_match('/^modeAll$/', $key)) {
                    $modeAll = $val;
                }
            }
        }
        if ($modeAll == 0) {
            // requestから対象顧客IDの一覧を取得する.
            $ids = $this->getIds($request);
            if (count($ids) == 0) {
                $app->addError('処理対象が選択されていません', 'admin');
                log_info('The Customer cannot found!');
                return $app->redirect($app->url('admin_student', array('id' => $id)));
            }
            // 顧客情報取得
            $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchCustomerIds($ids)
                    ->getQuery()
                    ->getResult();
        } else {
            // 顧客情報取得
            $customers = $app['eccube.repository.customer']->getQueryBuilderBySearchTrainingProductIds($id)
                    ->getQuery()
                    ->getResult();
        }

        // サービスの取得
        /* @var CertificationSenddingNotePdfService $service */
        $service = $app['eccube.service.certification_sendding_note_pdf'];
        $session = $request->getSession();

        // 顧客情報からPDFを作成する
        $status = $service->makePdf($customers, $app['eccube.repository.product']->find($id));

        // 異常終了した場合の処理
        if (!$status) {
            $app->addError('資格認定証送付状出力に失敗しました', 'admin');
            log_info('Unable to create pdf files! Process have problems!');
            return $app->redirect($app->url('admin_student', array('id' => $id)));
        }

        // ダウンロードする
        $response = new Response(
            $service->outputPdf(),
            200,
            array('content-type' => 'application/pdf')
        );

        // レスポンスヘッダーにContent-Dispositionをセットし、ファイル名を指定
        $response->headers->set('Content-Disposition', 'attachment; filename="'.$service->getPdfFileName().'"');
        log_info('CertificationSenddingNotePdfDownload success!', array());
        return $response;
    }

    public function delete(Application $app, Request $request, $id = null)
    {
        $this->isTokenValid($app);
        $session = $request->getSession();
        $page_no = intval($session->get('eccube.admin.product.search.page_no'));
        $page_no = $page_no ? $page_no : Constant::ENABLED;

        if (!is_null($id)) {
            /* @var $Product \Eccube\Entity\Product */
            $Product = $app['eccube.repository.product']->find($id);
            if (!$Product) {
                $app->deleteMessage();

                return $app->redirect($app->url('admin_training_page', array('page_no' => $page_no)).'?resume='.Constant::ENABLED);
            }

            if ($Product instanceof \Eccube\Entity\Product) {
                log_info('商品削除開始', array($id));

                $Product->setDelFlg(Constant::ENABLED);

                $ProductClasses = $Product->getProductClasses();
                $deleteImages = array();
                foreach ($ProductClasses as $ProductClass) {
                    $ProductClass->setDelFlg(Constant::ENABLED);
                    $Product->removeProductClass($ProductClass);

                    $ProductClasses = $Product->getProductClasses();
                    foreach ($ProductClasses as $ProductClass) {
                        $ProductClass->setDelFlg(Constant::ENABLED);
                        $Product->removeProductClass($ProductClass);

                        $ProductStock = $ProductClass->getProductStock();
                        $app['orm.em']->remove($ProductStock);
                    }

                    $ProductImages = $Product->getProductImage();
                    foreach ($ProductImages as $ProductImage) {
                        $Product->removeProductImage($ProductImage);
                        $deleteImages[] = $ProductImage->getFileName();
                        $app['orm.em']->remove($ProductImage);
                    }

                    $ProductCategories = $Product->getProductCategories();
                    foreach ($ProductCategories as $ProductCategory) {
                        $Product->removeProductCategory($ProductCategory);
                        $app['orm.em']->remove($ProductCategory);
                    }

                }

                $ProductTraining = $Product->getProductTraining();
                if (!is_null($ProductTraining)) {
                    $app['orm.em']->remove($ProductTraining);
                }

                $app['orm.em']->persist($Product);

                $app['orm.em']->flush();

                $event = new EventArgs(
                    array(
                        'Product' => $Product,
                        'ProductClass' => $ProductClasses,
                        'deleteImages' => $deleteImages,
                    ),
                    $request
                );
                $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_PRODUCT_DELETE_COMPLETE, $event);
                $deleteImages = $event->getArgument('deleteImages');

                // 画像ファイルの削除(commit後に削除させる)
                foreach ($deleteImages as $deleteImage) {
                    try {
                        if (!empty($deleteImage)) {
                            $fs = new Filesystem();
                            $fs->remove($app['config']['image_save_realdir'].'/'.$deleteImage);
                        }
                    } catch (\Exception $e) {
                        // エラーが発生しても無視する
                    }
                }

                log_info('講習会削除完了', array($id));

                $app->addSuccess('admin.delete.complete', 'admin');
            } else {
                log_info('講習会削除エラー', array($id));
                $app->addError('admin.delete.failed', 'admin');
            }
        } else {
            log_info('講習会削除エラー', array($id));
            $app->addError('admin.delete.failed', 'admin');
        }

        return $app->redirect($app->url('admin_training_page', array('page_no' => $page_no)).'?resume='.Constant::ENABLED);
    }

    public function copy(Application $app, Request $request, $id = null)
    {
        $this->isTokenValid($app);

        if (!is_null($id)) {
            $Product = $app['eccube.repository.product']->find($id);
            if ($Product instanceof \Eccube\Entity\Product) {
                $CopyProduct = clone $Product;
                $CopyProduct->copy();
                $Disp = $app['eccube.repository.master.disp']->find(\Eccube\Entity\Master\Disp::DISPLAY_HIDE);
                $CopyProduct->setStatus($Disp);

                $CopyProductCategories = $CopyProduct->getProductCategories();
                foreach ($CopyProductCategories as $Category) {
                    $app['orm.em']->persist($Category);
                }

                // 規格あり商品の場合は, デフォルトの商品規格を取得し登録する.
                if ($CopyProduct->hasProductClass()) {
                    $softDeleteFilter = $app['orm.em']->getFilters()->getFilter('soft_delete');
                    $softDeleteFilter->setExcludes(array(
                        'Eccube\Entity\ProductClass'
                    ));
                    $dummyClass = $app['eccube.repository.product_class']->findOneBy(array(
                        'del_flg' => \Eccube\Common\Constant::ENABLED,
                        'ClassCategory1' => null,
                        'ClassCategory2' => null,
                        'Product' => $Product,
                    ));
                    $dummyClass = clone $dummyClass;
                    $dummyClass->setProduct($CopyProduct);
                    $CopyProduct->addProductClass($dummyClass);
                    $softDeleteFilter->setExcludes(array());
                }

                $CopyProductClasses = $CopyProduct->getProductClasses();
                foreach ($CopyProductClasses as $Class) {
                    $Stock = $Class->getProductStock();
                    $CopyStock = clone $Stock;
                    $CopyStock->setProductClass($Class);
                    $app['orm.em']->persist($CopyStock);

                    $app['orm.em']->persist($Class);
                }
                $Images = $CopyProduct->getProductImage();
                foreach ($Images as $Image) {

                    // 画像ファイルを新規作成
                    $extension = pathinfo($Image->getFileName(), PATHINFO_EXTENSION);
                    $filename = date('mdHis').uniqid('_').'.'.$extension;
                    try {
                        $fs = new Filesystem();
                        $fs->copy($app['config']['image_save_realdir'].'/'.$Image->getFileName(), $app['config']['image_save_realdir'].'/'.$filename);
                    } catch (\Exception $e) {
                        // エラーが発生しても無視する
                    }
                    $Image->setFileName($filename);

                    $app['orm.em']->persist($Image);
                }
                $Tags = $CopyProduct->getProductTag();
                foreach ($Tags as $Tag) {
                    $app['orm.em']->persist($Tag);
                }

                $app['orm.em']->persist($CopyProduct);

                $ProductTraining = $Product->getProductTraining();
                if (!is_null($ProductTraining)) {
                    $CopyProductTraining = new \Eccube\Entity\ProductTraining();
                    $CopyProductTraining->setTimeStart($ProductTraining->getTimeStart());
                    $CopyProductTraining->setTimeEnd($ProductTraining->getTimeEnd());
                    $CopyProductTraining->setPlace($ProductTraining->getPlace());
                    $CopyProductTraining->setZip01($ProductTraining->getZip01());
                    $CopyProductTraining->setZip02($ProductTraining->getZip02());
                    $CopyProductTraining->setZipcode($ProductTraining->getZipcode());
                    $CopyProductTraining->setAddr01($ProductTraining->getAddr01());
                    $CopyProductTraining->setAddr02($ProductTraining->getAddr02());
                    $CopyProductTraining->setTarget($ProductTraining->getTarget());
                    $CopyProductTraining->setPurpose($ProductTraining->getPurpose());
                    $CopyProductTraining->setItem($ProductTraining->getItem());
                    $CopyProductTraining->setPref($ProductTraining->getPref());
                    $CopyProductTraining->setTrainingDateStart(new \DateTime($ProductTraining->getTrainingDateStart()));
                    $CopyProductTraining->setTrainingDateEnd(new \DateTime($ProductTraining->getTrainingDateEnd()));
                    $CopyProductTraining->setTrainingType($ProductTraining->getTrainingType());
                    $CopyProductTraining->setProduct($CopyProduct);
                    $CopyProduct->setProductTraining($CopyProductTraining);
                    $app['orm.em']->persist($CopyProductTraining);
                }

                $app['orm.em']->flush();

                $event = new EventArgs(
                    array(
                        'Product' => $Product,
                        'CopyProduct' => $CopyProduct,
                        'CopyProductCategories' => $CopyProductCategories,
                        'CopyProductClasses' => $CopyProductClasses,
                        'images' => $Images,
                        'Tags' => $Tags,
                    ),
                    $request
                );
                $app['eccube.event.dispatcher']->dispatch(EccubeEvents::ADMIN_PRODUCT_COPY_COMPLETE, $event);

                $app->addSuccess('admin.product.copy.complete', 'admin');

                return $app->redirect($app->url('admin_training_edit', array('id' => $CopyProduct->getId())));
            } else {
                $app->addError('admin.product.copy.failed', 'admin');
            }
        } else {
            $app->addError('admin.product.copy.failed', 'admin');
        }

        return $app->redirect($app->url('admin_training'));
    }

    public function display(Application $app, Request $request, $id = null)
    {
        if (!is_null($id)) {
            return $app->redirect($app->url('product_detail', array('id' => $id, 'admin' => '1')));
        }

        return $app->redirect($app->url('admin_training'));
    }

    /**
     * ProductCategory作成
     * @param \Eccube\Entity\Product $Product
     * @param \Eccube\Entity\Category $Category
     * @return \Eccube\Entity\ProductCategory
     */
    private function createProductCategory($Product, $Category, $count)
    {
        $ProductCategory = new \Eccube\Entity\ProductCategory();
        $ProductCategory->setProduct($Product);
        $ProductCategory->setProductId($Product->getId());
        $ProductCategory->setCategory($Category);
        $ProductCategory->setCategoryId($Category->getId());
        $ProductCategory->setRank($count);

        return $ProductCategory;
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
