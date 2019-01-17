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
 * Class QuestionnaireType.
 */
class QuestionnaireType extends AbstractType
{
    /**
     * @var Application
     */
    public $app;

    /**
     * QuestionnaireType constructor.
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
            // 基本情報
            ->add('name', 'text', array(
                'label' => 'アンケート名',
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('description', 'textarea', array(
                'label' => 'アンケート説明',
                'constraints' => array(),
            ))
            // 募集期間(From)
            ->add('application_period_from', 'text', array(
                'label' => '募集期間(From)',
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[0-9]{4}\/([0][1-9]|[1][0-2])\/([0][1-9]|[1-2][0-9]|[3][0-1]) ([0-1][0-9]|[2][0-3]):[0-5][0-9]$/')),
                ),
            ))
            // 募集期間(To)
            ->add('application_period_to', 'text', array(
                'label' => '募集期間(To)',
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[0-9]{4}\/([0][1-9]|[1][0-2])\/([0][1-9]|[1-2][0-9]|[3][0-1]) ([0-1][0-9]|[2][0-3]):[0-5][0-9]$/')),
                ),
            ))
            ->add('attachment_file', 'file', array(
                'label' => '添付ファイル',
                'multiple' => true,
                'required' => false,
                'mapped' => false,
            ))
            ->add('target', 'customer_type', array(
                'required' => true,
                'label' => '公開対象',
                'multiple' => false,
                'expanded' => false,
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            // 右ブロック
            ->add('Status', 'disp', array(
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            // 添付
            ->add('attachments', 'collection', array(
                'type' => 'hidden',
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            ->add('add_attachments', 'collection', array(
                'type' => 'hidden',
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            ->add('delete_attachments', 'collection', array(
                'type' => 'hidden',
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            // アンケート詳細
            ->add('QuestionnaireDetails', 'collection', array(
                'type' => 'questionnaire_detail',
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
        return 'admin_questionnaire';
    }
}
