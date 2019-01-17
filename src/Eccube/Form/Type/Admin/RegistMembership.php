<?php
/*
 * This file is Cusomized file
 */


namespace Eccube\Form\Type\Admin;

use Doctrine\Common\Collections\ArrayCollection;
use Eccube\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class RegistMembership.
 */
class RegistMembership extends AbstractType
{
    /**
     * @var Application
     */
    public $app;

    /**
     * RegistMembership constructor.
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
        /**
         * @var ArrayCollection $arrCategory array of category
         */
        $arrMembership = $this->app['eccube.repository.product_membership']->getList(null, true);

        $builder
            // 対象年度
            ->add('MembershipYear', 'entity', array(
                'class' => 'Eccube\Entity\ProductMembership',
                'property' => 'MembershipYear',
                'required' => true,
                'label' => '対象年度',
                'multiple' => false,
                'expanded' => false,
                'mapped' => false,
                // Choices list (overdrive mapped)
                'choices' => $arrMembership,
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_regist_membership';
    }
}
