<?php
/*
 * This file is Custmized File.
 */

namespace Eccube\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class CustomerAddressType extends AbstractType
{
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $config = $this->config;

        $builder
            ->add($options['name_name'], 'name', array(
                'required' => $options['name_required'],
            ))
            ->add($options['kana_name'], 'kana', array(
                'required' => $options['kana_required'],
            ))
            ->add($options['company_name_name'], 'text', array(
                'required' => $options['company_name_required'],
                'constraints' => array(
                    new Assert\Length(array(
                        'max' => $config['stext_len'],
                    ))
                ),
            ))
            ->add($options['zip_name'], 'zip', array(
                'required' => $options['zip_required'],
            ))
            ->add($options['address_name'], 'address', array(
                'required' => $options['address_required'],
            ))
            ->add($options['tel_name'], 'tel', array(
                'required' => $options['tel_required'],
            ))
            ->add($options['fax_name'], 'tel', array(
                'required' => $options['fax_required'],
            ))
            ->add($options['mobilephone_name'], 'tel', array(
                'required' => $options['mobilephone_required'],
            ))
            ->add($options['email_name'], 'email', array(
                'required' => $options['email_required'],
                'constraints' => array(
                    // configでこの辺りは変えられる方が良さそう
                    new Assert\Email(array('strict' => true)),
                    new Assert\Regex(array(
                        'pattern' => '/^[[:graph:][:space:]]+$/i',
                        'message' => 'form.type.graph.invalid',
                    )),
                ),
            ))
        ;
        $builder->setAttribute('kana_required', $options['kana_required']);
        $builder->setAttribute('company_name_required', $options['company_name_required']);
        $builder->setAttribute('address_required', $options['address_required']);
        $builder->setAttribute('zip_required', $options['zip_required']);
        $builder->setAttribute('tel_required', $options['tel_required']);
        $builder->setAttribute('fax_required', $options['fax_required']);
        $builder->setAttribute('mobilephone_required', $options['mobilephone_required']);
        $builder->setAttribute('email_required', $options['email_required']);
        $builder->setAttribute('name_name', $options['name_name']);
        $builder->setAttribute('kana_name', $options['kana_name']);
        $builder->setAttribute('company_name_name', $options['company_name_name']);
        $builder->setAttribute('tel_name', $options['tel_name']);
        $builder->setAttribute('fax_name', $options['fax_name']);
        $builder->setAttribute('mobilephone_name', $options['mobilephone_name']);
        $builder->setAttribute('email_name', $options['email_name']);
        $builder->setAttribute('pref_name', $options['pref_name']);
        $builder->setAttribute('addr01_name', $options['addr01_name']);
        $builder->setAttribute('addr02_name', $options['addr02_name']);
        $builder->setAttribute('zip01_name', $options['zip01_name']);
        $builder->setAttribute('zip02_name', $options['zip02_name']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $builder = $form->getConfig();
        $view->vars['name_required'] = $builder->getAttribute('name_required');
        $view->vars['kana_required'] = $builder->getAttribute('kana_required');
        $view->vars['company_name_required'] = $builder->getAttribute('company_name_required');
        $view->vars['address_required'] = $builder->getAttribute('address_required');
        $view->vars['zip_required'] = $builder->getAttribute('zip_required');
        $view->vars['tel_required'] = $builder->getAttribute('tel_required');
        $view->vars['fax_required'] = $builder->getAttribute('fax_required');
        $view->vars['mobilephone_required'] = $builder->getAttribute('mobilephone_required');
        $view->vars['email_required'] = $builder->getAttribute('email_required');
        $view->vars['name_name'] = $builder->getAttribute('name_name');
        $view->vars['kana_name'] = $builder->getAttribute('kana_name');
        $view->vars['company_name_name'] = $builder->getAttribute('company_name_name');
        $view->vars['tel_name'] = $builder->getAttribute('tel_name');
        $view->vars['fax_name'] = $builder->getAttribute('fax_name');
        $view->vars['mobilephone_name'] = $builder->getAttribute('mobilephone_name');
        $view->vars['email_name'] = $builder->getAttribute('email_name');
        $view->vars['pref_name'] = $builder->getAttribute('pref_name');
        $view->vars['addr01_name'] = $builder->getAttribute('addr01_name');
        $view->vars['addr02_name'] = $builder->getAttribute('addr02_name');
        $view->vars['zip01_name'] = $builder->getAttribute('zip01_name');
        $view->vars['zip02_name'] = $builder->getAttribute('zip02_name');
        $view->vars['address_name'] = $builder->getAttribute('addr02_name');
        $view->vars['zip_name'] = $builder->getAttribute('zip01_name');
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Eccube\Entity\CustomerAddress',
            'options' => array(),
            'help' => 'form.contact.address.help',
            'pref_options' => array('constraints' => array()),
            'addr01_options' => array(
                'constraints' => array(
                    new Assert\Length(array('max' => $this->config['address1_len'])),
                ),
            ),
            'addr02_options' => array(
                'constraints' => array(
                    new Assert\Length(array('max' => $this->config['address2_len'])),
                ),
            ),
            'zip01_options' => array(
                'constraints' => array(
                    new Assert\Type(array('type' => 'numeric', 'message' => 'form.type.numeric.invalid')),
                    new Assert\Length(array('min' => $this->config['zip01_len'], 'max' => $this->config['zip01_len'])),
                ),
            ),
            'zip02_options' => array(
                'constraints' => array(
                    new Assert\Type(array('type' => 'numeric', 'message' => 'form.type.numeric.invalid')),
                    new Assert\Length(array('min' => $this->config['zip02_len'], 'max' => $this->config['zip02_len'])),
                ),
            ),
            'name_name' => 'name',
            'kana_name' => 'kana',
            'company_name_name' => 'company_name',
            'tel_name' => 'tel',
            'fax_name' => 'fax',
            'mobilephone_name' => 'mobilephone',
            'email_name' => 'email',
            'pref_name' => 'pref',
            'addr01_name' => 'addr01',
            'addr02_name' => 'addr02',
            'zip01_name' => 'zip01',
            'zip02_name' => 'zip01',
            'address_name' => 'address',
            'zip_name' => 'zip',
            'name_required' => false,
            'kana_required' => false,
            'company_name_required' => false,
            'address_required' => false,
            'zip_required' => false,
            'tel_required' => false,
            'fax_required' => false,
            'mobilephone_required' => false,
            'email_required' => false,
            'error_bubbling' => false,
            'trim' => true,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_customer_address';
    }
}
