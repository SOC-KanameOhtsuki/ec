<?php

/*
 * This file is part of the ProductVideo
 *
 * Copyright (C) 2018 ふまねっと
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductVideo\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ProductVideoConfigType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'number', array(
                'label' => '対象商品種別コード',
                'required' => true,
                'constraints' => array(
                    new Assert\NotBlank(),
                ),
            ));
    }

    public function getName()
    {
        return 'productvideo_config';
    }

}
