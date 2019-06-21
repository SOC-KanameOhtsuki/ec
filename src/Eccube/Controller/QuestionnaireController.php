<?php
/*
 * This file is Custmized File.
 */


namespace Eccube\Controller;

use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Event\EccubeEvents;
use Eccube\Event\EventArgs;
use Eccube\Exception\CartException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class QuestionnaireController
{

    private $title;

    public function __construct()
    {
        $this->title = '';
    }

    public function index(Application $app, Request $request, $id = null)
    {
        $BaseInfo = $app['eccube.repository.base_info']->get();

        if ($id == null) {
            return $app->redirect($app->url('top'));
        }
        $Questionnaire = $app['eccube.repository.questionnaire']->find($id);
        if (!$Questionnaire) {
            throw new NotFoundHttpException();
        }
        $this->title = $Questionnaire->getName();

/*
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
            }
        } else {
        }
*/

        return $app->render('Questionnaire/index.twig', array(
//            'forms' => $forms,
            'Questionnaire' => $Questionnaire,
        ));
    }
}
