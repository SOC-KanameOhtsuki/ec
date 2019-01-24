<?php
/*
 * This file is custmized file
 */

namespace Eccube\Form\Type;

use Eccube\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class SearchProductTrainingType to search product.
 */
class SearchProductTrainingType extends AbstractType
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * SearchProductTrainingType constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $Years = $this->app['eccube.repository.product_training']
            ->getYearList(isset($options['history'])?$options['history']:null);
        $Areas = $this->app['eccube.repository.product_training']
            ->getAreaList(isset($options['history'])?$options['history']:null);
        $builder->add('year', 'choice', array(
            'label' => '年',
            'choices' => $Years,
            'required' => false,
            'multiple' => false,
            'expanded' => false,
        ));
        $builder->add('month', 'choice', array(
            'label' => '月',
            'choices' => array(
                1 => '1',
                2 => '2',
                3 => '3',
                4 => '4',
                5 => '5',
                6 => '6',
                7 => '7',
                8 => '8',
                9 => '9',
                10 => '10',
                11 => '11',
                12 => '12'
            ),
            'required' => false,
            'multiple' => false,
            'expanded' => false,
        ));
        $builder->add('area', 'choice', array(
            'label' => '地域',
            'choices' => $Areas,
            'required' => false,
            'multiple' => false,
            'expanded' => false,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired('history');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'search_product_training';
    }
}
