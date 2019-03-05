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

class CustomerAddressType extends AbstractType
{
    public $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'name', array(
                'required' => $options['name_required'],
            ))
            ->add('kana', 'kana', array(
                'required' => $options['kana_required'],
            ))
            ->add('company_name', 'text', array(
                'required' => $options['company_name_required'],
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => $this->config['stext_len'],
                    )),
                ),
            ))
            ->add('zip', 'zip', array(
                'required' => $options['zip_required'],
            ))
            ->add('address', 'address', array(
                'required' => $options['address_required'],
            ))
            ->add('tel', 'tel', array(
                'required' => $options['tel_required'],
            ))
            ->add('fax', 'tel', array(
                'required' => $options['fax_required'],
            ))
            ->add('mobilephone', 'tel', array(
                'required' => $options['mobilephone_required'],
            ))
            ->add('email', 'repeated_email', array(
                'required' => $options['email_required'],
            ))
        ;
        $builder->setAttribute('kana_required', $options['kana_required']);
        $builder->setAttribute('company_name_required', $options['company_name_required']);
        $builder->setAttribute('address_required', $options['address_required']);
        $builder->setAttribute('zip_required', $options['zip_required']);
        $builder->setAttribute('tel_required', $options['tel_required']);
        $builder->setAttribute('fax_required', $options['fax_required']);
        $builder->setAttribute('mobilephone_required', $options['mobilephone_required']);
        $builder->setAttribute('email_required', $options['email_required']);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Eccube\Entity\CustomerAddress',
            'name_required' => false,
            'kana_required' => false,
            'company_name_required' => false,
            'address_required' => false,
            'zip_required' => false,
            'tel_required' => false,
            'fax_required' => false,
            'mobilephone_required' => false,
            'email_required' => false,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'customer_address';
    }
}
