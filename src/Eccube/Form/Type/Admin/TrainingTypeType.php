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
 * Class TrainingTypeType.
 */
class TrainingTypeType extends AbstractType
{
    /**
     * @var Application
     */
    public $app;

    /**
     * TrainingType constructor.
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
            // 講習会種別名
            ->add('name', 'text', array(
                'label' => '講習会種別名',
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            // 正会員昇格
            ->add('rank_up', 'choice', array(
                'label' => '正会員昇格',
                'required' => true,
                'choices' => array(
                    '1' => 'する',
                    '0' => 'しない',
                ),
                'expanded' => true,
                'multiple' => false,
                'empty_value' => false,
                'mapped' => true,
            ))
            // 認定資格種別
            ->add('qualification_type', 'qualification_type', array(
                'label' => '認定資格種別',
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('name', 'text', array(
                'label' => '講習会種別名',
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('rank', 'integer', array(
                'label' => '表示順',
                'required' => false,
                'constraints' => array(
                    new Assert\NotBlank(),
                    //長さ制限。 Assert\も色々ある。
                    new Assert\Length(array(
                    //最大4文字
                    'max' => 4,
                    //それぞれに引っかかったときのエラーメッセージ
                    'maxMessage' => '4桁以下の数字を入力してください。',
                )),
                ),
        'attr' => array('max' => '9999','min' => '0'),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Eccube\Entity\Master\TrainingType',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_training_type';
    }
}
