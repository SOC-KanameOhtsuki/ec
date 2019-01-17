<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace Eccube\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CustomerType extends AbstractType
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
            ->add('name', 'name', array(
                'required' => true,
            ))
            ->add('kana', 'kana', array(
                'required' => true,
            ))
            ->add('company_name', 'text', array(
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => $config['stext_len'],
                    ))
                ),
            ))
            ->add('zip', 'zip', array(
                'required' => true,
            ))
            ->add('address', 'address', array(
                'required' => true,
            ))
            ->add('tel', 'tel', array(
                'required' => true,
            ))
            ->add('fax', 'tel', array(
                'required' => false,
            ))
            ->add('mobilephone', 'tel', array(
                'required' => false,
            ))
            ->add('email', 'email', array(
                'required' => false,
                'constraints' => array(
                    // configでこの辺りは変えられる方が良さそう
                    new Assert\Email(array('strict' => true)),
                    new Assert\Regex(array(
                        'pattern' => '/^[[:graph:][:space:]]+$/i',
                        'message' => 'form.type.graph.invalid',
                    )),
                ),
            ))
            ->add('sex', 'sex', array(
                'required' => false,
            ))
            ->add('office_name', 'text', array(
                'required' => false,
                'label' => '勤務先名',
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => $config['stext_len'],
                    ))
                ),
                'mapped' => false,
            ))
            ->add('office_address', 'address', array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('office_zip', 'zip', array(
                'required' => false,
                'mapped' => false,
                'zip01_options' => [
                    'mapped' => false,
                ],
                'zip02_options' => [
                    'mapped' => false,
                ],
            ))
            ->add('office_tel', 'tel', array(
                'required' => false,
                'mapped' => false,
                'tel01_options' => [
                    'mapped' => false,
                ],
                'tel02_options' => [
                    'mapped' => false,
                ],
                'tel03_options' => [
                    'mapped' => false,
                ],
            ))
            ->add('office_fax', 'tel', array(
                'required' => false,
                'mapped' => false,
                'tel01_options' => [
                    'mapped' => false,
                ],
                'tel02_options' => [
                    'mapped' => false,
                ],
                'tel03_options' => [
                    'mapped' => false,
                ],
            ))
            ->add('job', 'job', array(
                'required' => false,
            ))
            ->add('birth', 'birthday', array(
                'required' => false,
                'input' => 'datetime',
                'years' => range(date('Y'), date('Y') - $this->config['birth_max']),
                'widget' => 'choice',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
                'constraints' => array(
                    new Assert\LessThanOrEqual(array(
                        'value' => date('Y-m-d'),
                        'message' => 'form.type.select.selectisfuturedate',
                    )),
                ),
            ))
            ->add('password', 'repeated_password', array(
                // 'type' => 'password',
                'first_options'  => array(
                    'label' => 'パスワード',
                ),
                'second_options' => array(
                    'label' => 'パスワード(確認)',
                ),
            ))
            ->add('customer_image', 'file', array(
                'label' => 'プロフィール画像',
                'multiple' => true,
                'required' => false,
                'mapped' => false,
            ))
            ->add('status', 'customer_status', array(
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ))
            ->add('belongs_group_id', 'hidden', array(
                'required' => false,
                'mapped' => false,
            ))
            ->add('qrs', 'collection', array(
                'type' => 'hidden',
                'prototype' => true,
                'mapped' => false,
                'allow_add' => false,
                'allow_delete' => false,
            ))
            ->add('images', 'collection', array(
                'type' => 'hidden',
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            ->add('add_images', 'collection', array(
                'type' => 'hidden',
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            ->add('delete_images', 'collection', array(
                'type' => 'hidden',
                'prototype' => true,
                'mapped' => false,
                'allow_add' => true,
                'allow_delete' => true,
            ))
            ->add('note', 'textarea', array(
                'label' => 'SHOP用メモ',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => $config['ltext_len'],
                    )),
                ),
            ))
            // 会員基本情報
            ->add('basic_info', 'admin_customer_basic_info', array(
                'mapped' => false,
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
        return 'admin_customer';
    }
}
