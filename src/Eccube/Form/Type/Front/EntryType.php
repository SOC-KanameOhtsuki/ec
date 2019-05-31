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


namespace Eccube\Form\Type\Front;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class EntryType extends AbstractType
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
        $builder
            // 郵送先
            ->add('mail_to', 'choice', array(
                'label' => '郵送先',
                'required' => false,
                'choices' => array(1 => '自宅', 2 => '勤務先'),
                'expanded' => true,
                'multiple' => false,
                'mapped' => false,
                'empty_value' => false,
            ))
            // 自宅住所
            ->add('home_address', 'customer_address', array(
                'mapped' => false,
            ))
            // 勤務先住所
            ->add('office_address', 'customer_address', array(
                'mapped' => false,
            ))
            ->add('customer_pin_code', 'repeated_password', array(
                'mapped' => false,
            ))
            ->add('birth', 'birthday', array(
                'required' => false,
                'input' => 'datetime',
                'years' => range(date('Y'), date('Y') - $this->config['birth_max']),
                'widget' => 'choice',
                'format' => 'yyyy/MM/dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
                'constraints' => array(
                    new Assert\LessThanOrEqual(array(
                        'value' => date('Y-m-d'),
                        'message' => 'form.type.select.selectisfuturedate',
                    )),
                ),
            ))
            ->add('sex', 'sex', array(
                'required' => false,
            ))
            ->add('job', 'job', array(
                'required' => false,
            ))
            ->add('nobulletin', 'nobulletin_type', array(
                'label' => '機関紙お届け',
                'required' => false,
                'mapped' => false,
            ))
            ->add('anonymous', 'anonymous_type', array(
                'label' => '正会員一覧への名前掲載',
                'required' => false,
                'mapped' => false,
            ))
            ->add('anonymous_company', 'anonymous_company_type', array(
                'label' => '正会員一覧への施設名掲載',
                'required' => false,
                'mapped' => false,
            ))
            ->add('save', 'submit', array('label' => 'この内容で登録する'));
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
        // todo entry,mypageで共有されているので名前を変更する
        return 'entry';
    }
}
