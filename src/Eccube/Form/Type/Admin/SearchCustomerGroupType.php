<?php
/*
 * This file is Customized File
 */


namespace Eccube\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SearchCustomerGroupType extends AbstractType
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
            // 会員グループID・会員グループ名・会員グループ(フリガナ)
            ->add('multi', 'text', array(
                'label' => '会員グループID・会員グループ名・会員グループ(フリガナ)・送付先住所・請求先住所・請求先名',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
            ))
            ->add('customer_group_id', 'text', array(
                'label' => '会員グループID',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['int_len'])),
                ),
            ))
            ->add('customer_group_name', 'text', array(
                'label' => '会員グループ名',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
            ))
            ->add('send_to_pref', 'pref', array(
                'label' => '送付先都道府県',
                'required' => false,
            ))
            ->add(
                $builder->create('send_to_tel', 'text', array(
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
            ->add('bill_to', 'text', array(
                'label' => '請求先名',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
            ))
            ->add('bill_to_pref', 'pref', array(
                'label' => '請求先都道府県',
                'required' => false,
            ))
            ->add(
                $builder->create('bill_to_tel', 'text', array(
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
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_search_customer_group';
    }
}
