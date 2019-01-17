<?php
/*
 * This file is Customize File
 */


namespace Eccube\Form\Type\Master;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExemptionTypeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options['sex_options']['required'] = $options['required'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'class' => 'Eccube\Entity\Master\ExemptionType',
            'expanded' => true,
        ));
    }

    public function getParent()
    {
        return 'master';
    }

    public function getName()
    {
        return 'exemption_type';
    }
}
