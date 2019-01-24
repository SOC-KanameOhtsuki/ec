<?php
/*
 * This file Customize File
 */

namespace Eccube\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CustomerBasicInfoType extends AbstractType
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $config = $this->config;

        $builder
            ->add('customer_number', 'text', array(
                'label' => '会員ID',
                'required' => false,
                'mapped' => true,
            ))
            ->add('customer_number_old', 'text', array(
                'label' => '旧会員ID',
                'required' => false,
                'mapped' => true,
            ))
            ->add('customer_pin_code', 'text', array(
                'label' => 'PINコード',
                'required' => false,
                'mapped' => true,
            ))
            ->add('last_pay_membership_year', 'text', array(
                'label' => '最終支払会費年度',
                'required' => false,
                'mapped' => true,
                'read_only' =>'true',
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[0-9]*$/')),
                ),
            ))
            ->add('membership_expired', 'text', array(
                'label' => '正会員資格満了日',
                'required' => false,
                'read_only' =>'true',
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[0-9]{4}\/([0][1-9]|[1][0-2])\/([0][1-9]|[1-2][0-9]|[3][0-1])$/')),
                ),
                'mapped' => true,
            ))
            ->add('regular_member_promoted', 'text', array(
                'label' => '正会員資格取得日',
                'required' => false,
                'read_only' =>'true',
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[0-9]{4}\/([0][1-9]|[1][0-2])\/([0][1-9]|[1-2][0-9]|[3][0-1])$/')),
                ),
                'mapped' => true,
            ))
            ->add('qualification', 'text', array(
                'label' => '資格',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => $config['stext_len'],
                    ))
                ),
                'mapped' => true,
            ))
            ->add('instructor_type', 'instructor_type', array(
                'label' => 'インストラクタ',
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
                'mapped' => true,
            ))
            ->add('supporter_type', 'supporter_type', array(
                'label' => 'サポーター',
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
                'mapped' => true,
            ))
            ->add('nobulletin', 'nobulletin_type', array(
                'label' => '機関紙お届け',
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('bureau', 'bureau', array(
                'label' => '振興局',
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'constraints' => array(),
                'mapped' => true,
            ))
            ->add('membership_exemption', 'exemption_type', array(
                'label' => '年会費免除',
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('status', 'customer_basic_info_status', array(
                'label' => '基本情報ステータス',
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
                'mapped' => true,
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Eccube\Entity\CustomerBasicInfo',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_customer_basic_info';
    }
}
