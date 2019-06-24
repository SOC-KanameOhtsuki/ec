<?php
/*
 * This file is customized file
 */


namespace Eccube\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SearchMembershipPaymentCustomerType extends AbstractType
{
    public $app;

    public function __construct(\Silex\Application $app)
    {
        $this->app = $app;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $app = $this->app;
        $arrMembership = $this->app['eccube.repository.product_membership']->getList(null, true);
        $Memberships = array();
        foreach($arrMembership as $Membership) {
            $Memberships[$Membership->getId()] = $Membership->getMembershipYear();
        }

        $builder
            ->add('membership_year', 'choice', array(
                'label' => '対象年度',
                'required' => true,
                'choices' => $Memberships,
                'expanded' => false,
                'multiple' => false,
                'mapped' => false,
                'empty_value' => false,
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_search_membership_payment_customer';
    }
}
