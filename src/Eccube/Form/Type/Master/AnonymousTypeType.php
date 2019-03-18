<?php
/*
 * This file is Customize File
 */


namespace Eccube\Form\Type\Master;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnonymousTypeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options['anonymous_options']['required'] = $options['required'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'class' => 'Eccube\Entity\Master\AnonymousType',
            'expanded' => true,
            'empty_value' => false,
        ));
    }

    public function getParent()
    {
        return 'master';
    }

    public function getName()
    {
        return 'anonymous_type';
    }
}
