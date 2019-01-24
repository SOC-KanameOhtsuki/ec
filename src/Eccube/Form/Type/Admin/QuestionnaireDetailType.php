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
 * Class QuestionnaireDetailType.
 */
class QuestionnaireDetailType extends AbstractType
{
    /**
     * @var Application
     */
    public $app;

    /**
     * QuestionnaireDetailType constructor.
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
            ->add('detail_name', 'text', array(
                'label' => '項目名',
                'constraints' => array(),
            ))
            ->add('detail_description', 'textarea', array(
                'label' => '項目説明説明',
                'constraints' => array(),
            ))
            // アンケート選択肢
            ->add('QuestionnaireDetailChoices', 'collection', array(
                'type' => 'questionnaire_detail_choice',
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
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
        return 'questionnaire_detail';
    }
}
