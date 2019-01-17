<?php
/*
 * This file is customized file
 */


namespace Eccube\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class GeneralCategoryType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'class' => 'Eccube\Entity\Category',
            'property' => 'NameWithLevel',
            'query_builder' => function(EntityRepository $er) {
                return $er->createQueryBuilder('c')
                    ->andWhere('c.id <> 1')
                    ->orderBy('c.rank', 'ASC');
            },
        ));
    }

    public function getParent()
    {
        return 'master';
    }

    public function getName()
    {
        return 'general_category';
    }
}
