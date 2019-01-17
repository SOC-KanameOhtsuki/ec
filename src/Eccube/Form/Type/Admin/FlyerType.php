<?php
/*
 * This file is Cusomized file
 */


namespace Eccube\Form\Type\Admin;

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class FlyerType.
 */
class FlyerType extends AbstractType
{
    /**
     * @var Application
     */
    public $app;

    /**
     * FlyerType constructor.
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
            // 講習会情報
            ->add('product_training_id', 'hidden', array(
                'required' => true,
            ))
            ->add('training_name', 'text', array(
                'required' => true,
                'read_only' =>'true',
            ))
            // チラシ掲載開始日付
            ->add('disp_from', 'text', array(
                'label' => '掲載開始日付',
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Regex(array('pattern' => '/^[0-9]{4}\/([0][1-9]|[1][0-2])\/([0][1-9]|[1-2][0-9]|[3][0-1]) ([0-1][0-9]|[2][0-3]):[0-5][0-9]$/')),
                ),
            ))
            // チラシ掲載終了日付
            ->add('disp_to', 'text', array(
                'label' => '掲載終了日付',
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Regex(array('pattern' => '/^[0-9]{4}\/([0][1-9]|[1][0-2])\/([0][1-9]|[1-2][0-9]|[3][0-1]) ([0-1][0-9]|[2][0-3]):[0-5][0-9]$/')),
                ),
            ))
            // チラシ詳細
            ->add('description', 'textarea', array(
                'label' => '詳細',
            ))
            // リンクラベル
            ->add('link_label', 'text', array(
                'label' => 'リンクラベル',
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array(
                        'max' => $this->app['config']['name_len'],
                    )),
                )
            ))
            // 公開対象
            ->add('Target', 'customer_type', array(
                'required' => true,
                'label' => '公開対象',
                'multiple' => false,
                'expanded' => false,
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            // 公開／非公開
            ->add('Status', 'disp', array(
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
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
        return 'admin_flyer';
    }
}
