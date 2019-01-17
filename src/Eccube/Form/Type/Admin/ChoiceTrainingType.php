<?php
/*
 * This file is customized file
 */


namespace Eccube\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class ChoiceTrainingType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'class' => 'Eccube\Entity\TrainingType',
            'property' => 'NameWithLevel',
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('tt')
                    ->orderBy('tt.rank', 'ASC');
            },
        ));
    }

    public function getName()
    {
        return 'choice_training_type';
    }
}
