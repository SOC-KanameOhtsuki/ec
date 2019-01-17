<?php
/*
 * This file is Customized file
 */


namespace Eccube\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ProductMembershipType extends AbstractType
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
        $builder
            ->add('membership_year', 'text', array(
                'label' => '年会費対象年度',
                'constraints' => array(
                    new Assert\Regex(array(
                        'pattern' => "/^\d{4}$/u",
                        'message' => 'form.type.numeric.invalid')),
                ),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Eccube\Entity\ProductMembership',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_product_membership';
    }
}
