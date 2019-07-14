<?php
/*
 * This file is customized file
 */


namespace Eccube\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SearchDonationPaymentCustomerType extends AbstractType
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
        $arrTermInfos = $this->app['eccube.repository.master.term_info']->createQueryBuilder('t')
                ->addOrderBy('t.term_year', 'desc')
                ->addOrderBy('t.term_end', 'desc')
                ->getQuery()
                ->getResult();
        $Terms = array();
        $minYear = date('Y');
        $maxYear = date('Y');
        foreach($arrTermInfos as $Term) {
            $Terms[$Term->getId()] = $Term->getTermName();
            if ($Term->getTermStart()->format('Y') < $minYear) {
                $minYear = $Term->getTermStart()->format('Y');
            }
        }
        $Years = array();
        for ($year = $maxYear; $year >= $minYear; --$year) {
            $Years[$year] = $year;
        }

        $builder
            ->add('search_donation_type', 'choice', array(
                'label' => '',
                'required' => false,
                'choices' => array(0 => '期間', 1 => '西暦', 2 => '年度'),
                'expanded' => true,
                'multiple' => false,
                'empty_value' => false,
            ))
            ->add('target_year', 'choice', array(
                'label' => '',
                'required' => false,
                'choices' => $Years,
                'expanded' => false,
                'multiple' => false,
                'empty_value' => false,
            ))
            ->add('target_term', 'choice', array(
                'label' => '',
                'required' => false,
                'choices' => $Terms,
                'expanded' => false,
                'multiple' => false,
                'empty_value' => false,
            ))
            ->add('target_date_start', 'date', array(
                'label' => '対象期間',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('target_date_end', 'date', array(
                'label' => '対象期間',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
      ;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_search_donation_payment_customer';
    }
}
