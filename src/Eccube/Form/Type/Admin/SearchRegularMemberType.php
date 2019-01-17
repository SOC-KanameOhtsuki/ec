<?php
/*
 * This file is Customize File
 */


namespace Eccube\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SearchRegularMemberType extends AbstractType
{
    private $config;

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
        $months = range(1, 12);
        $builder
            // 会員ID・メールアドレス・名前・名前(フリガナ)
            ->add('multi', 'text', array(
                'label' => '会員ID・メールアドレス・名前・名前(フリガナ)',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
            ))
            ->add('company_name', 'text', array(
                'label' => '会社名',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
            ))
            ->add('pref', 'pref', array(
                'label' => '都道府県',
                'required' => false,
            ))
            ->add('sex', 'sex', array(
                'label' => '性別',
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ))
            ->add('birth_month', 'choice', array(
                'label' => '誕生月',
                'required' => false,
                'choices' => array_combine($months, $months),
            ))
            ->add('birth_start', 'birthday', array(
                'label' => '誕生日',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('birth_end', 'birthday', array(
                'label' => '誕生日',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add(
                $builder->create('tel', 'text', array(
                        'required' => false,
                        'constraints' => array(
                            new Assert\Regex(array(
                                'pattern' => "/^[\d-]+$/u",
                                'message' => 'form.type.admin.nottelstyle',
                            )),
                        ),
                    ))
                    ->addEventSubscriber(new \Eccube\EventListener\ConvertTelListener())
            )
            ->add('buy_total_start', 'integer', array(
                'label' => '購入金額',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['price_len'])),
                ),
            ))
            ->add('buy_total_end', 'integer', array(
                'label' => '購入金額',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['price_len'])),
                ),
            ))
            ->add('buy_times_start', 'integer', array(
                'label' => '購入回数',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['int_len'])),
                ),
            ))
            ->add('buy_times_end', 'integer', array(
                'label' => '購入回数',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['int_len'])),
                ),
            ))
            ->add('create_date_start', 'date', array(
                'label' => '登録日',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('create_date_end', 'date', array(
                'label' => '登録日',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('update_date_start', 'date', array(
                'label' => '更新日',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('update_date_end', 'date', array(
                'label' => '更新日',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('last_buy_start', 'date', array(
                'label' => '最終購入日',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('last_buy_end', 'date', array(
                'label' => '最終購入日',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('buy_product_name', 'text', array(
                'label' => '購入商品名',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
            ))
            ->add('buy_product_code', 'text', array(
                'label' => '購入商品コード',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
            ))
            ->add('buy_category', 'category', array(
                'label' => '商品カテゴリ',
                'required' => false,
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_search_regular_member';
    }
}
