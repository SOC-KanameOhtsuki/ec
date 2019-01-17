<?php
/*
 * This file is Customize File
 */

namespace Eccube\Controller\Admin\Customer;

use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Controller\AbstractController;
use Eccube\Event\EventArgs;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerGroupEditController extends AbstractController
{
    public function index(Application $app, Request $request, $id = null)
    {
        $app['orm.em']->getFilters()->enable('incomplete_order_status_hidden');
        // 編集
        if ($id) {
            $CustomerGroup = $app['orm.em']
                ->getRepository('Eccube\Entity\CustomerGroup')
                ->find($id);
            if (is_null($CustomerGroup)) {
                throw new NotFoundHttpException();
            }
            $Customers = $app['eccube.repository.customer']->getQueryBuilderBySearchGroupId($id)
                                                            ->getQuery()
                                                            ->getResult();
            if (is_null($Customers)) {
                $Customers = [];
            }
            // 新規登録
        } else {
            $CustomerGroup = new \Eccube\Entity\CustomerGroup();
            $Customers = [];
        }

        // 会員グループ登録フォーム
        $builder = $app['form.factory']
            ->createBuilder('admin_customer_group', $CustomerGroup);

        $form = $builder->getForm();
        // ファイルの登録
        $customers = array();
        foreach ($Customers as $Customer) {
            $customers[] = $Customer;
        }
        $form['Customers']->setData($customers);

        if ('POST' === $request->getMethod()) {
            $request_data = $request->request->all();
            $inputCustomers = [];
            if (isset($request_data['admin_customer_group']['Customers'])) {
                $inputCustomers = $request_data['admin_customer_group']['Customers'];
                unset($request_data['admin_customer_group']['Customers']);
                $request->request->replace($request_data);
            }
            $form->handleRequest($request);
            if ($form->isValid()) {
                log_info('会員グループ登録開始', array($CustomerGroup->getId()));
                $app['orm.em']->persist($CustomerGroup);
                $app['orm.em']->flush();
                $inputCustomerKeys = [];
                foreach ($inputCustomers as $inputCustomer) {
                    $inputCustomerKeys[] = $inputCustomer['id'];
                }
                $notChangeCustomer = [];
                $deleteCustomer = [];
                foreach ($Customers as $Customer) {
                    if (in_array($Customer->getId(), $inputCustomerKeys)) {
                        $notChangeCustomer[] = $Customer->getId();
                    } else {
                        $deleteCustomer[] = $Customer->getId();
                    }
                }
                $updateCustomer = [];
                foreach ($inputCustomerKeys as $inputCustomerKey) {
                    if (!in_array($inputCustomerKey, $notChangeCustomer)) {
                        $updateCustomer[] = $inputCustomerKey;
                    }
                }

                // 所属会員の更新(グループ追加)
                if (0 < count($updateCustomer)) {
                    log_info('所属会員登録開始', array(print_r($deleteCustomer, true)));
                    if (!$app['eccube.repository.customer']->setCustomerGroupBySearchIds($updateCustomer, $CustomerGroup->getId())) {
                        $app->addError('admin.customer_group.save.failed', 'admin');
                        // 会員検索フォーム
                        $builder = $app['form.factory']
                            ->createBuilder('admin_search_customer');

                        $searchCustomerModalForm = $builder->getForm();

                        return $app->render('Customer/group_edit.twig', array(
                            'form' => $form->createView(),
                            'searchCustomerModalForm' => $searchCustomerModalForm->createView(),
                            'CustomerGroup' => $CustomerGroup,
                        ));
                    }
                }
                // 所属会員の更新(グループ解除)
                if (0 < count($deleteCustomer)) {
                    log_info('所属会員解除開始', array(print_r($deleteCustomer, true)));
                    if (!$app['eccube.repository.customer']->setCustomerGroupBySearchIds($deleteCustomer, 'NULL')) {
                        $app->addError('admin.customer_group.save.failed', 'admin');
                        // 会員検索フォーム
                        $builder = $app['form.factory']
                            ->createBuilder('admin_search_customer');

                        $searchCustomerModalForm = $builder->getForm();

                        return $app->render('Customer/group_edit.twig', array(
                            'form' => $form->createView(),
                            'searchCustomerModalForm' => $searchCustomerModalForm->createView(),
                            'CustomerGroup' => $CustomerGroup,
                        ));
                    }
                }
                log_info('会員グループ登録完了', array($CustomerGroup->getId()));
                $app->addSuccess('admin.customer_group.save.complete', 'admin');

                return $app->redirect($app->url('admin_customer_group_edit', array(
                    'id' => $CustomerGroup->getId(),
                )));
            } else {
                $app->addError('admin.customer_group.save.failed', 'admin');
            }
        }

        // 会員検索フォーム
        $builder = $app['form.factory']
            ->createBuilder('admin_search_customer');

        $searchCustomerModalForm = $builder->getForm();

        return $app->render('Customer/group_edit.twig', array(
            'form' => $form->createView(),
            'searchCustomerModalForm' => $searchCustomerModalForm->createView(),
            'CustomerGroup' => $CustomerGroup,
        ));
    }

    /**
     * 顧客情報を検索する.
     *
     * @param Application $app
     * @param Request $request
     * @param integer $page_no
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function searchCustomerHtml(Application $app, Request $request, $page_no = null)
    {
        if ($request->isXmlHttpRequest()) {
            $app['monolog']->addDebug('search customer start.');
            $page_count = $app['config']['default_page_count'];
            $session = $app['session'];

            if ('POST' === $request->getMethod()) {

                $page_no = 1;

                $searchData = array(
                    'multi' => $request->get('search_word'),
                );

                $session->set('eccube.admin.customer.group.customer.search', $searchData);
                $session->set('eccube.admin.customer.group.customer.search.page_no', $page_no);
            } else {
                $searchData = (array)$session->get('eccube.admin.customer.group.customer.search');
                if (is_null($page_no)) {
                    $page_no = intval($session->get('eccube.admin.customer.group.customer.search.page_no'));
                } else {
                    $session->set('eccube.admin.customer.group.customer.search.page_no', $page_no);
                }
            }

            $qb = $app['eccube.repository.customer']->getQueryBuilderBySearchData($searchData);
            $qb->andWhere('c.CustomerGroup IS NULL');
            $pagination = $app['paginator']()->paginate(
                $qb,
                $page_no,
                $page_count,
                array('wrap-queries' => true)
            );

            /** @var $Customers \Eccube\Entity\Customer[] */
            $Customers = $pagination->getItems();

            if (empty($Customers)) {
                $app['monolog']->addDebug('search customer not found.');
            }

            $data = array();

            $formatTel = '%s-%s-%s';
            $formatName = '%s%s(%s%s)';
            foreach ($Customers as $Customer) {
                $data[] = array(
                    'id' => $Customer->getId(),
                    'name' => sprintf($formatName, $Customer->getName01(), $Customer->getName02(), $Customer->getKana01(),
                        $Customer->getKana02()),
                    'tel' => sprintf($formatTel, $Customer->getTel01(), $Customer->getTel02(), $Customer->getTel03()),
                    'email' => $Customer->getEmail(),
                );
            }

            return $app->render('Customer/search_customer.twig', array(
                'data' => $data,
                'pagination' => $pagination,
            ));
        }
    }

    /**
     * 顧客情報を検索する.
     *
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function searchCustomerById(Application $app, Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $app['monolog']->addDebug('search customer by id start.');

            /** @var $Customer \Eccube\Entity\Customer */
            $Customer = $app['eccube.repository.customer']
                ->find($request->get('id'));

            if (is_null($Customer)) {
                $app['monolog']->addDebug('search customer by id not found.');

                return $app->json(array(), 404);
            }

            $app['monolog']->addDebug('search customer by id found.');

            $data = array(
                'id' => $Customer->getId(),
                'name01' => $Customer->getName01(),
                'name02' => $Customer->getName02(),
                'kana01' => $Customer->getKana01(),
                'kana02' => $Customer->getKana02(),
                'zip01' => $Customer->getZip01(),
                'zip02' => $Customer->getZip02(),
                'pref' => is_null($Customer->getPref()) ? null : $Customer->getPref()->getId(),
                'addr01' => $Customer->getAddr01(),
                'addr02' => $Customer->getAddr02(),
                'email' => $Customer->getEmail(),
                'tel01' => $Customer->getTel01(),
                'tel02' => $Customer->getTel02(),
                'tel03' => $Customer->getTel03(),
                'fax01' => $Customer->getFax01(),
                'fax02' => $Customer->getFax02(),
                'fax03' => $Customer->getFax03(),
                'company_name' => $Customer->getCompanyName(),
            );

            return $app->json($data);
        }
    }
}
