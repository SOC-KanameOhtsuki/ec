<?php
/*
 * This file is Cusomized file
 */


namespace Eccube\Form\Type\Master;

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class TrainingTypeType.
 */
class TrainingTypeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'class' => 'Eccube\Entity\Master\TrainingType',
            'expanded' => true,
        ));
    }

    public function getParent()
    {
        return 'master';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'training_type';
    }
}
