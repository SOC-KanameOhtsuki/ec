<?php
/*
 * This file is customized file
 */


namespace Eccube\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SearchFlyerType extends AbstractType
{
    public $app;

    public function __construct(\Silex\Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $app = $this->app;

        $builder
            // 講習会名・会場名
            ->add('multi', 'text', array(
                'label' => 'チラシリンクラベル',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $app['config']['stext_len'])),
                ),
            ))
            ->add('id', 'text', array(
                'label' => 'チラシID',
                'required' => false,
            ))
            ->add('link_label', 'text', array(
                'label' => 'チラシリンクラベル',
                'required' => false,
            ))
            ->add('disp_from', 'date', array(
                'label' => 'チラシ公開期間(FROM)',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd hh:mm',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('disp_to', 'date', array(
                'label' => 'チラシ公開期間(TO)',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd hh:mm',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('training_type', 'training_type', array(
                'label' => '対象講習会種別',
                'empty_value' => '選択してください',
                'required' => false,
                'multiple' => false,
                'expanded' => false,
            ))
            ->add('training_name', 'text', array(
                'label' => '対象商品名(対象講習会名)',
                'required' => false,
            ))
            ->add('place', 'date', array(
                'label' => '対象会場名',
                'required' => false,
            ))
            ->add('training_date_from', 'date', array(
                'label' => '講習会日(FROM)',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('training_date_to', 'date', array(
                'label' => '講習会日(TO)',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('status', 'disp', array(
                'label' => '種別',
                'multiple'=> true,
                'required' => false,
            ))
            ->add('create_date_start', 'date', array(
                'label' => '登録日(FROM)',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('create_date_end', 'date', array(
                'label' => '登録日(TO)',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('update_date_start', 'date', array(
                'label' => '更新日(FROM)',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('update_date_end', 'date', array(
                'label' => '更新日(TO)',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('link_status', 'hidden', array(
                'mapped' => false,
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_search_flyer';
    }
}
