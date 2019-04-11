<?php
/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */


namespace Eccube\Form\Type\Admin;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class SearchCustomerType extends AbstractType
{
    /**
     * @var Application
     */
    public $app;

    private $config;

    public function __construct($app)
    {
        $this->app = $app;
        $this->config = $app['config'];
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $config = $this->config;
        $BaseInfoStatusList = $this->app['eccube.repository.customer_basic_info_status']->getStatusList();
        $months = range(1, 12);
        $builder
            // 会員番号・会員ID・旧会員ID・メールアドレス・名前・名前(フリガナ)
            ->add('multi', 'text', array(
                'label' => '会員番号・会員ID・旧会員ID・メールアドレス・名前・名前(フリガナ)',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
            ))
            ->add('customer_id', 'text', array(
                'label' => '会員番号',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
            ))
            ->add('customer_number', 'text', array(
                'label' => '会員ID',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
            ))
            ->add('customer_number_old', 'text', array(
                'label' => '旧会員ID',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
            ))
            ->add('company_name', 'text', array(
                'label' => '会社名',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
            ))
             ->add('pref_area', 'choice', array(
                'label' => '地方グループ',
                'required' => false,
                'choices' => array(1 => '北海道', 2 => '東北', 3 => '関東', 4 => '北陸', 5 => '関西', 6 => '東海', 7 => '中国', 8 => '四国', 9 => '九州', 10 => '沖縄'),
                'expanded' => true,
                'multiple' => true,
                'empty_value' => false,
            ))
            ->add('pref', 'pref', array(
                'label' => '都道府県',
                'required' => false,
            ))
            ->add('address', 'text', array(
                'label' => '市町村名',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
            ))
            ->add('sex', 'sex', array(
                'label' => '性別',
                'required' => false,
                'expanded' => true,
                'multiple' => true,
            ))
            ->add('birth_month', 'choice', array(
                'label' => '誕生月',
                'required' => false,
                'choices' => array_combine($months, $months),
            ))
            ->add('birth_start', 'birthday', array(
                'label' => '誕生日',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('birth_end', 'birthday', array(
                'label' => '誕生日',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add(
                $builder->create('tel', 'text', array(
                        'required' => false,
                        'constraints' => array(
                            new Assert\Regex(array(
                                'pattern' => "/^[\d-]+$/u",
                                'message' => 'form.type.admin.nottelstyle',
                            )),
                        ),
                    ))
                    ->addEventSubscriber(new \Eccube\EventListener\ConvertTelListener())
            )
            ->add('buy_total_start', 'integer', array(
                'label' => '購入金額',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['price_len'])),
                ),
            ))
            ->add('buy_total_end', 'integer', array(
                'label' => '購入金額',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['price_len'])),
                ),
            ))
            ->add('buy_times_start', 'integer', array(
                'label' => '購入回数',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['int_len'])),
                ),
            ))
            ->add('buy_times_end', 'integer', array(
                'label' => '購入回数',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['int_len'])),
                ),
            ))
            ->add('create_date_start', 'date', array(
                'label' => '登録日',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('create_date_end', 'date', array(
                'label' => '登録日',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('update_date_start', 'date', array(
                'label' => '更新日',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('update_date_end', 'date', array(
                'label' => '更新日',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('last_buy_start', 'date', array(
                'label' => '最終購入日',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('last_buy_end', 'date', array(
                'label' => '最終購入日',
                'required' => false,
                'input' => 'datetime',
                'widget' => 'single_text',
                'format' => 'yyyy-MM-dd',
                'empty_value' => array('year' => '----', 'month' => '--', 'day' => '--'),
            ))
            ->add('buy_product_name', 'text', array(
                'label' => '購入商品名',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
            ))
            ->add('buy_product_code', 'text', array(
                'label' => '購入商品コード',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
            ))
            ->add('buy_category', 'category', array(
                'label' => '商品カテゴリ',
                'required' => false,
            ))
            ->add('customer_status', 'choice', array(
                'label' => 'EC会員ステータス',
                'required' => false,
                'choices' => array(
                    '1' => '仮会員',
                    '2' => '本会員',
                ),
                'expanded' => true,
                'multiple' => true,
                'empty_value' => false,
            ))
            ->add('customer_group', 'text', array(
                'label' => '会員グループ',
                'required' => false,
                'constraints' => array(
                    new Assert\Length(array('max' => $config['stext_len'])),
                ),
            ))
            ->add('customer_basicinfo_status', 'choice', array(
                'label' => '会員ステータス',
                'required' => false,
                'choices' => $BaseInfoStatusList,
                'expanded' => true,
                'multiple' => true,
                'empty_value' => false,
            ))
            ->add('customer_basicinfo_bureau', 'bureau', array(
                'label' => '振興局',
                'required' => false,
                'multiple' => false,
                'expanded' => false,
                'constraints' => array(),
            ))
            ->add('customer_basicinfo_supporter_type', 'supporter_type', array(
                'label' => 'サポータ資格',
                'required' => false,
                'multiple' => false,
                'expanded' => false,
                'constraints' => array(),
            ))
            ->add('customer_basicinfo_instructor_type', 'instructor_type', array(
                'label' => 'インストラクタ資格',
                'required' => false,
                'multiple' => false,
                'expanded' => false,
                'constraints' => array(),
            ))
        ;
        $arrMembership = $this->app['eccube.repository.product_membership']->getList(null, true);
        foreach($arrMembership as $Membership) {
            $builder->add('membership_pay_' . $Membership->getId(), 'choice', array(
                'label' => $Membership->getMembershipYear() . '年度',
                'required' => false,
                'choices' => array(1 => '納入済', 2 => '未納', 3 => '免除', 4 => '特免'),
                'expanded' => true,
                'multiple' => true,
                'empty_value' => false,
            ));

        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'admin_search_customer';
    }
}
