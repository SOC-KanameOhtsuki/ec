<?php
/*
 * This file is Customized File
 */

namespace Eccube\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CustomerGroupCustomerType extends AbstractType
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
            ->add('id', 'hidden', array(
                'required' => false,
            ))
            ->add('name', 'name', array(
                'required' => false,
            ))
            ->add('kana', 'kana', array(
                'required' => false,
            ))
            ->add('company_name', 'text', array(
                'required' => false,
            ))
            ->add('zip', 'zip', array(
                'required' => false,
            ))
            ->add('address', 'address', array(
                'required' => false,
            ))
            ->add('tel', 'tel', array(
                'required' => false,
            ))
            ->add('fax', 'tel', array(
                'required' => false,
            ))
            ->add('mobilephone', 'tel', array(
                'required' => false,
            ))
            ->add('email', 'email', array(
                'required' => false,
            ))
            ->add('status', 'customer_status', array(
                'required' => false,
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Eccube\Entity\Customer',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_customer_group_customer';
    }
}
