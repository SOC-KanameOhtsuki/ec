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
        $Memberships = array();
        foreach($arrMembership as $Membership) {
            $Memberships[$Membership->getId()] = $Membership->getMembershipYear();
        }

        $builder
            ->add('year', 'choice', array(
                'label' => '対象年度',
                'required' => false,
                'choices' => $Memberships,
                'expanded' => true,
                'multiple' => true,
                'mapped' => false,
                'empty_value' => false,
            ))
            ->add('status', 'choice', array(
                'label' => '対象会員',
                'required' => false,
                'choices' => array(1 => '正会員', 5 => '休眠者', 6 => '滞納者', 7 => '元会員'),
                'expanded' => true,
                'multiple' => true,
                'mapped' => false,
                'empty_value' => false,
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
