<?php
/*
 * This file is Customized File.
 */


namespace Eccube\Controller\Block;

use Eccube\Application;

class QuestionnaireController
{
    public function index(Application $app)
    {
        $QuestionnaireList = $app['orm.em']->getRepository('\Eccube\Entity\Questionnaire')->getQuestionnaireList($app);
        return $app->render('Block/questionnaire.twig', array(
            'QuestionnaireList' => $QuestionnaireList,
        ));
    }
}
