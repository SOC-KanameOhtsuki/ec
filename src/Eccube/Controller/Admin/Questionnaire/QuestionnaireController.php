<?php
/*
 * This file is Customized File.
 */


namespace Eccube\Controller\Admin\Questionnaire;

use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Entity\Master\CsvType;
use Eccube\Entity\QuestionnaireTag;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Service\CsvExportService;
use Eccube\Util\FormUtil;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

class QuestionnaireController extends AbstractController
{
    public function index(Application $app, Request $request, $page_no = null)
    {
        $session = $app['session'];
        $builder = $app['form.factory']
            ->createBuilder('admin_search_questionnaire');
        $searchForm = $builder->getForm();

        $pagination = array();
        $disps = $app['eccube.repository.master.disp']->findAll();
        $pageMaxis = $app['eccube.repository.master.page_max']->findAll();
        // 表示件数は順番で取得する、1.SESSION 2.設定ファイル
        $page_count = $session->get('eccube.admin.questionnaire.search.page_count', $app['config']['default_page_count']);
        // 表示件数
        $page_count_param = $request->get('page_count');
        // 表示件数はURLパラメターから取得する
        if ($page_count_param && is_numeric($page_count_param)) {
            foreach ($pageMaxis as $pageMax) {
                if ($page_count_param == $pageMax->getName()) {
                    $page_count = $pageMax->getName();
                    // 表示件数入力値正し場合はSESSIONに保存する
                    $session->set('eccube.admin.questionnaire.search.page_count', $page_count);
                    break;
                }
            }
        }

        $page_status = null;
        $active = false;

/*
        if ('POST' === $request->getMethod()) {
            $searchForm->handleRequest($request);
            if ($searchForm->isValid()) {
                $searchData = $searchForm->getData();

                // paginator
                $qb = $app['eccube.repository.product']->getQueryBuilderBySearchDataForAdmin($searchData);
                if (empty($searchData['category_id']) || !($searchData['category_id'])) {
                    $qb
                        ->leftJoin('p.QuestionnaireCategories', 'pct')
                        ->leftJoin('pct.Category', 'c')
                        ->andWhere('c.id <> 1 or pct IS NULL');
                }
                $page_no = 1;
                $pagination = $app['paginator']()->paginate(
                    $qb,
                    $page_no,
                    $page_count,
                    array('wrap-queries' => true)
                );
                // sessionに検索条件を保持
                $viewData = FormUtil::getViewData($searchForm);
                $session->set('eccube.admin.questionnaire.search', $viewData);
                $session->set('eccube.admin.questionnaire.search.page_no', $page_no);
            }
        } else {
            if (is_null($page_no) && $request->get('resume') != Constant::ENABLED) {
                // sessionを削除
                $session->remove('eccube.admin.questionnaire.search');
                $session->remove('eccube.admin.questionnaire.search.page_no');
                $session->remove('eccube.admin.questionnaire.search.page_count');
            } else {
                // pagingなどの処理
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.questionnaire.search.page_no'));
                } else {
                    $session->set('eccube.admin.questionnaire.search.page_no', $page_no);
                }
                $viewData = $session->get('eccube.admin.questionnaire.search');
                if (!is_null($viewData)) {
                    // 表示件数
                    $page_count = $request->get('page_count', $page_count);
                    $searchData = FormUtil::submitAndGetData($searchForm, $viewData);
                    if ($viewData['link_status']) {
                        $searchData['link_status'] = $app['eccube.repository.master.disp']->find($viewData['link_status']);
                    }
                    $session->set('eccube.admin.questionnaire.search', $viewData);
                    $qb = $app['eccube.repository.product']->getQueryBuilderBySearchDataForAdmin($searchData);
                    if (empty($searchData['category_id']) || !($searchData['category_id'])) {
                        $qb
                            ->innerJoin('p.QuestionnaireCategories', 'pct')
                            ->innerJoin('pct.Category', 'c')
                            ->andWhere('pct.Category <> 1');
                    }
                    $pagination = $app['paginator']()->paginate(
                        $qb,
                        $page_no,
                        $page_count,
                        array('wrap-queries' => true)
                    );
                }
            }
        }
*/

        return $app->render('Questionnaire/index.twig', array(
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

    public function addAttachment(Application $app, Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            throw new BadRequestHttpException('リクエストが不正です');
        }

        $attachments = $request->files->get('admin_questionnaire');

        $files = array();
        if (count($attachments) > 0) {
            foreach ($attachments as $attachmentFile) {
                foreach ($attachmentFile as $attachment) {
                    log_info('attachment:' . print_r($attachment, true));
                    //ファイルフォーマット検証
                    $mimeType = $attachment->getMimeType();
                    $extension = $attachment->getClientOriginalExtension();
                    $filename = date('mdHis').uniqid('_').'.'.$extension;
                    $attachment->move($app['config']['file_temp_realdir'], $filename);
                    $files[] = ['save_file' => $filename, 'org_file' => $attachment->getClientOriginalName()];
                }
            }
        }

        return $app->json(array('files' => $files), 200);
    }

    public function edit(Application $app, Request $request, $id = null)
    {
        if (is_null($id)) {
            $Questionnaire = new \Eccube\Entity\Questionnaire();
        } else {
            $Questionnaire = $app['eccube.repository.questionnaire']->find($id);
            if (!$Product) {
                throw new NotFoundHttpException();
            }
        }
        $builder = $app['form.factory']
            ->createBuilder('admin_questionnaire');
        $form = $builder->getForm();

        // ファイルの登録
        $images = array();
        $QuestionnaireAttachments = $Questionnaire->getQuestionnaireAttachments();
        foreach ($QuestionnaireAttachments as $QuestionnaireAttachment) {
            $attachments[] = ['save_file' => $QuestionnaireAttachment->getFileName(), 'org_file' => $QuestionnaireAttachment->getLabel()];
        }
        $form['attachments']->setData($attachments);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                log_info('アンケート登録開始', array($Customer->getId()));
            }
        }

        // 検索結果の保持
        $builder = $app['form.factory']
            ->createBuilder('admin_search_questionnaire');
        $searchForm = $builder->getForm();
        if ('POST' === $request->getMethod()) {
            $searchForm->handleRequest($request);
        }

        return $app->render('Questionnaire/edit.twig', array(
            'Questionnaire' => $Questionnaire,
            'form' => $form->createView(),
            'searchForm' => $searchForm->createView(),
            'id' => $id,
        ));
    }

    public function delete(Application $app, Request $request, $id = null)
    {
        $this->isTokenValid($app);
        return $app->redirect($app->url('admin_questionnaire_page', array('page_no' => $page_no)).'?resume='.Constant::ENABLED);
    }

    public function copy(Application $app, Request $request, $id = null)
    {
        $this->isTokenValid($app);
        return $app->redirect($app->url('admin_questionnaire'));
    }

    public function display(Application $app, Request $request, $id = null)
    {
        if (!is_null($id)) {
            return $app->redirect($app->url('product_detail', array('id' => $id, 'admin' => '1')));
        }

        return $app->redirect($app->url('admin_questionnaire'));
    }
}
