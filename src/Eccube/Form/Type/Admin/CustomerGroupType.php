<?php
/*
 * This file is Customize File
 */

namespace Eccube\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CustomerGroupType extends AbstractType
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
            ->add('name', 'text', array(
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array(
                        'max' => $this->config['name_len'],
                    )),
                )
            ))
            ->add('kana', 'text', array(
                'required' => true,
                'constraints' => array(
                    new Assert\Regex(array(
                        'pattern' => "/^[ァ-ヶｦ-ﾟー]+$/u",
                    )),
                    new Assert\Length(array(
                        'max' => $this->config['kana_len'],
                    )),
                )
            ))
            ->add('send_to_zip', 'zip', array(
                'required' => false,
                'zip01_name' => 'send_to_zip01',
                'zip02_name' => 'send_to_zip02',
            ))
            ->add('send_to_address', 'address', array(
                'required' => true,
                'pref_name' => 'send_to_pref',
                'addr01_name' => 'send_to_addr01',
                'addr02_name' => 'send_to_addr02',
            ))
            ->add('send_to_tel', 'tel', array(
                'required' => true,
                'tel01_name' => 'send_to_tel01',
                'tel02_name' => 'send_to_tel02',
                'tel03_name' => 'send_to_tel03',
            ))
            ->add('send_to_fax', 'tel', array(
                'required' => false,
                'tel01_name' => 'send_to_fax01',
                'tel02_name' => 'send_to_fax02',
                'tel03_name' => 'send_to_fax03',
            ))
            ->add('send_to_email', 'email', array(
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                    // configでこの辺りは変えられる方が良さそう
                    new Assert\Email(array('strict' => true)),
                    new Assert\Regex(array(
                        'pattern' => '/^[[:graph:][:space:]]+$/i',
                        'message' => 'form.type.graph.invalid',
                    )),
                ),
            ))
            ->add('bill_to', 'text', array(
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array(
                        'max' => $this->config['name_len'],
                    )),
                )
            ))
            ->add('bill_to_zip', 'zip', array(
                'required' => true,
                'zip01_name' => 'bill_to_zip01',
                'zip02_name' => 'bill_to_zip02',
            ))
            ->add('bill_to_address', 'address', array(
                'required' => true,
                'pref_name' => 'bill_to_pref',
                'addr01_name' => 'bill_to_addr01',
                'addr02_name' => 'bill_to_addr02',
            ))
            ->add('bill_to_tel', 'tel', array(
                'required' => true,
                'tel01_name' => 'bill_to_tel01',
                'tel02_name' => 'bill_to_tel02',
                'tel03_name' => 'bill_to_tel03',
            ))
            ->add('bill_to_fax', 'tel', array(
                'required' => false,
                'tel01_name' => 'bill_to_fax01',
                'tel02_name' => 'bill_to_fax02',
                'tel03_name' => 'bill_to_fax03',
            ))
            ->add('bill_to_email', 'email', array(
                'required' => false,
                'constraints' => array(
                    new Assert\NotBlank(),
                    // configでこの辺りは変えられる方が良さそう
                    new Assert\Email(array('strict' => true)),
                    new Assert\Regex(array(
                        'pattern' => '/^[[:graph:][:space:]]+$/i',
                        'message' => 'form.type.graph.invalid',
                    )),
                ),
            ))
            ->add('Customers', 'collection', array(
                'mapped' => false,
                'type' => 'admin_customer_group_customer',
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Eccube\Entity\CustomerGroup',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_customer_group';
    }
}
