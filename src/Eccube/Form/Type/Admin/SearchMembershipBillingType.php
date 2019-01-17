<?php
/*
 * This file is customized file
 */


namespace Eccube\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SearchMembershipBillingType extends AbstractType
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
            ->add('multi', 'text', array(
                'label' => '年会費支払処理ID、年会費対象年度',
                'required' => false,
            ))
              ->add('status', 'membership_billing_status', array(
                'label' => '年会費支払処理ステータス',
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('membership_year', 'text', array(
                'label' => '年会費対象年度',
                'required' => false,
                'constraints' => array(
                    new Assert\Regex(array('pattern' => '/^[0-9]{4}$/')),
                ),
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
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_search_membership_billing';
    }
}
