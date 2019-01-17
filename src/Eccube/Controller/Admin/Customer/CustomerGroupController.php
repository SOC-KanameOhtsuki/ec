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
