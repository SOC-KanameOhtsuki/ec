<?php
/*
 * This file is Customized File
 */


namespace Eccube\Form\Type\Admin;

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class QuestionnaireDetailChoiceType.
 */
class QuestionnaireDetailChoiceType extends AbstractType
{
    /**
     * @var Application
     */
    public $app;

    /**
     * QuestionnaireDetailChoiceType constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('choice_name', 'text', array(
                'label' => '選択肢',
                'constraints' => array(),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'questionnaire_detail_choice';
    }
}
