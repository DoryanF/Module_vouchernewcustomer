<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}

class VoucherNewCustomer extends Module implements WidgetInterface
{
    public function __construct()
    {
        $this->name = 'vouchernewcustomer';
        $this->tab = 'pricing_promotion';
        $this->version = '1.0.0';
        $this->author = 'Doryan Fourrichon';
        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_
        ];
        
        //récupération du fonctionnement du constructeur de la méthode __construct de Module
        parent::__construct();
        $this->bootstrap = true;

        $this->displayName = $this->l('Vouche New Customer');
        $this->description = $this->l('New Module');

        $this->confirmUninstall = $this->l('Do you want to delete this module');

    }

    public function install()
    {
        if(!parent::install() ||
        !Configuration::updateValue('ACTIVATE_VOUCHER',0) ||
        !Configuration::updateValue('SEND_VOUCHER_BY_EMAIL',0) ||
        !Configuration::updateValue('MY_GROUPS','') ||
        !Configuration::updateValue('MYCODEPROMO','') ||
        !Configuration::updateValue('STARTCODEPROMO','') ||
        !Configuration::updateValue('ENDCODEPROMO','') ||
        !Configuration::updateValue('MONTANTCODEPROMO','') ||
        !Configuration::updateValue('TYPEDECODEPROMO', 0) ||
        !Configuration::updateValue('MINIMUMCODEPROMO',1) ||
        !Configuration::updateValue('CODEPROMOREDUC', 0) ||
        !Configuration::updateValue('CODEPROMOCATEGORIES', '') ||
        !$this->registerHook('header') ||
        !$this->registerHook('leftColumn') ||
        !$this->registerHook('actionCustomerAccountAdd')
        
        )
        {
            return false;
        }
            return true;
    }

    public function uninstall()
    {
        if(!parent::uninstall() ||
        !Configuration::deleteByName('ACTIVATE_VOUCHER') ||
        !Configuration::deleteByName('SEND_VOUCHER_BY_EMAIL') ||
        !Configuration::deleteByName('MY_GROUPS') ||
        !Configuration::deleteByName('MYCODEPROMO') ||
        !Configuration::deleteByName('STARTCODEPROMO') ||
        !Configuration::deleteByName('ENDCODEPROMO') ||
        !Configuration::deleteByName('MONTANTCODEPROMO') ||
        !Configuration::deleteByName('TYPEDECODEPROMO') ||
        !Configuration::deleteByName('MINIMUMCODEPROMO') ||
        !Configuration::deleteByName('CODEPROMOREDUC') ||
        !Configuration::deleteByName('CODEPROMOCATEGORIES') ||
        !$this->unregisterHook('header') ||
        !$this->unregisterHook('leftColumn') ||
        !$this->unregisterHook('actionCustomerAccountAdd')
        
        )
        {
            return false;
        }
            return true;
    }

    public function getContent()
    {
        return $this->postProcess().$this->renderForm();
    }

    public function renderForm()
    {
        $groups = Group::getGroups($this->context->language->id);

        $groups_list = array();

        foreach ($groups as $group) 
        {
            $groups_list[] = array(
                'id' => $group['id_group'],
                'name' => $group['name'],
            );
        }

        $selected_cat = json_decode(
            Configuration::get(
                'CODEPROMOCATEGORIES'
            )
        );

        if (!is_array($selected_cat)) {
            $selected_cat = array($selected_cat);
        }

        $tree = array(
            'selected_categories' => $selected_cat,
            'use_search' => true,
            'use_checkbox' => true,
            'id' => 'id_category_tree',
        );

        $field_form[0]['form'] = [
            'legend' => [
                'title' => $this->l('Setings'),
            ],
            'input' => [
                [
                    'type' => 'switch',
                        'label' => $this->l('Active voucher code'),
                        'name' => 'ACTIVATE_VOUCHER',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'label2_on',
                                'value' => 1,
                                'label' => $this->l('Oui')
                            ),
                            array(
                                'id' => 'label2_off',
                                'value' => 0,
                                'label' => $this->l('Non')
                            )
                        )
                ],
                [
                    'type' => 'switch',
                        'label' => $this->l('Send code by email'),
                        'name' => 'SEND_VOUCHER_BY_EMAIL',
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'label2_on',
                                'value' => 1,
                                'label' => $this->l('Oui')
                            ),
                            array(
                                'id' => 'label2_off',
                                'value' => 0,
                                'label' => $this->l('Non')
                            )
                        )
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('choose your group'),
                    'name' => 'MY_GROUPS',
                    'required' => true,
                    'options' => [
                        'query' => $groups_list,
                        'id' => 'id',
                        'name' => 'name'
                    ]
                ],
                [
                    "type" => "text",
                    'label' => $this->l('Promo code'),
                    'name' => 'MYCODEPROMO',
                    'required' => true,
                ],
                [
                    'type' => 'date',
                    'name' => 'STARTCODEPROMO',
                    'label' => $this->l('Début code promo'),
                    'required' => true,
                ],
                [
                    'type' => 'date',
                    'name' => 'ENDCODEPROMO',
                    'label' => $this->l('Fin code promo'),
                    'required' => true,
                ],
                [
                    "type" => "text",
                    'label' => $this->l('Montant code promo'),
                    'name' => 'MONTANTCODEPROMO',
                    'required' => true,
                ],
                [
                    'type' => 'switch',
                        'label' => $this->l('Discount amount or percent'),
                        'name' => 'TYPEDECODEPROMO',
                        'descr' => $this->l('Si actif alors réduction en %'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'label2_on',
                                'value' => 1,
                                'label' => $this->l('Percent')
                            ),
                            array(
                                'id' => 'label2_off',
                                'value' => 0,
                                'label' => $this->l('Amount')
                            )
                        )
                ],
                [
                    "type" => "text",
                    'label' => $this->l('Minimum amount'),
                    'desc' => $this->l('Minimum amount that the coupon can be used'),
                    'name' => 'MINIMUMCODEPROMO',
                    'required' => true,
                ],
                [
                    'type' => 'switch',
                        'label' => $this->l('Disallow voucher use on promotions ?'),
                        'desc' => $this->l('Setting to no will allow voucher use on products with reduction'),
                        'name' => 'CODEPROMOREDUC',
                        'descr' => $this->l('Si actif alors la réduction fonction pour les produits ayant déjà une remise'),
                        'is_bool' => true,
                        'values' => array(
                            array(
                                'id' => 'label2_on',
                                'value' => 1,
                                'label' => $this->l('Oui')
                            ),
                            array(
                                'id' => 'label2_off',
                                'value' => 0,
                                'label' => $this->l('Non')
                            )
                        )
                ],
                [
                    'type' => 'categories',
                    'name' => 'CODEPROMOCATEGORIES',
                    'required' => false,
                    'tree' => $tree
                ]
            ],
            'submit' => [
                'title' => $this->l('save'),
                'class' => 'btn btn-primary',
                'name' => 'saving'
            ]
        ];

        $helper = new HelperForm();
        $helper->module  = $this;
        $helper->name_controller = $this->name;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->fields_value['ACTIVATE_VOUCHER'] = Configuration::get('ACTIVATE_VOUCHER');
        $helper->fields_value['SEND_VOUCHER_BY_EMAIL'] = Configuration::get('SEND_VOUCHER_BY_EMAIL');
        $helper->fields_value['MY_GROUPS'] = Configuration::get('MY_GROUPS');
        $helper->fields_value['MYCODEPROMO'] = Configuration::get('MYCODEPROMO');
        $helper->fields_value['STARTCODEPROMO'] = Configuration::get('STARTCODEPROMO');
        $helper->fields_value['ENDCODEPROMO'] = Configuration::get('ENDCODEPROMO');
        $helper->fields_value['MONTANTCODEPROMO'] = Configuration::get('MONTANTCODEPROMO');
        $helper->fields_value['TYPEDECODEPROMO'] = Configuration::get('TYPEDECODEPROMO');
        $helper->fields_value['MINIMUMCODEPROMO'] = Configuration::get('MINIMUMCODEPROMO');
        $helper->fields_value['CODEPROMOREDUC'] = Configuration::get('CODEPROMOREDUC');

        $helper->fields_value['CODEPROMOCATEGORIES'] = Tools::getValue(
            'CODEPROMOCATEGORIES',
            json_decode(
                Configuration::get(
                    'CODEPROMOCATEGORIES'
                )
            )
        );

        return $helper->generateForm($field_form);
    }

    public function postProcess()
    {
        if(Tools::isSubmit('saving'))
        {
            Configuration::updateValue('ACTIVATE_VOUCHER',Tools::getValue('ACTIVATE_VOUCHER'));
            Configuration::updateValue('SEND_VOUCHER_BY_EMAIL',Tools::getValue('SEND_VOUCHER_BY_EMAIL'));
            Configuration::updateValue('MY_GROUPS',Tools::getValue('MY_GROUPS'));
            Configuration::updateValue('MYCODEPROMO',Tools::getValue('MYCODEPROMO'));
            Configuration::updateValue('STARTCODEPROMO',Tools::getValue('STARTCODEPROMO'));
            Configuration::updateValue('ENDCODEPROMO',Tools::getValue('ENDCODEPROMO'));
            Configuration::updateValue('MONTANTCODEPROMO',Tools::getValue('MONTANTCODEPROMO'));
            Configuration::updateValue('TYPEDECODEPROMO',Tools::getValue('TYPEDECODEPROMO'));
            Configuration::updateValue('MINIMUMCODEPROMO',Tools::getValue('MINIMUMCODEPROMO'));
            Configuration::updateValue('CODEPROMOREDUC',Tools::getValue('CODEPROMOREDUC'));
            Configuration::updateValue('CODEPROMOCATEGORIES',Tools::getValue('CODEPROMOCATEGORIES'));
            
            return $this->displayConfirmation('Bien enregistrer !');
        }

        
    }

    public function hookActionCustomerAccountAdd($params)
    {
        $customer = $params['newCustomer'];

        $languages = Language::getLanguages(true);

        if(Configuration::get('SEND_VOUCHER_BY_EMAIL') == 1)
        {
            // ----------------Création de mon coupon promo---------------------------- //
            $cartRule = new CartRule();
            $cartRule->group_restriction = 0;

            foreach ($languages as $language) {
                $cartRule->name[(int)$language['id_lang']] = Configuration::get('MYCODEPROMO')."_".$customer->firstname;
            }

            $cartRule->code = Configuration::get('MYCODEPROMO')."_".$customer->firstname;
            $cartRule->date_from = Configuration::get('STARTCODEPROMO');
            $cartRule->date_to = Configuration::get('ENDCODEPROMO');
            $cartRule->minimum_amount = Configuration::get('MINIMUMCODEPROMO');

            if(Configuration::get('TYPEDECODEPROMO') == 1)
            {
                $cartRule->reduction_percent = Configuration::get('MONTANTCODEPROMO');
            }
            else {
                $cartRule->reduction_amount = Configuration::get('MONTANTCODEPROMO');
            }

            $cartRule->id_customer = $customer->id;
            $cartRule->description = "Code de bienvenue";
            $cartRule->minimum_amount_tax = 1;
            $cartRule->minimum_amount_currency = 1;
            $cartRule->minimum_amount_shipping = 0;
            $cartRule->country_restriction = 0;
            $cartRule->carrier_restriction = 0;
            $cartRule->cart_rule_restriction = 0;
            $cartRule->free_shipping = 0;
            $cartRule->reduction_tax = 1;
            $cartRule->reduction_currency = 1;
            $cartRule->reduction_product = 0;
            $cartRule->reduction_exclude_special = 0;
            $cartRule->gift_product = 0;
            $cartRule->gift_product_attribute = 0;
            $cartRule->highlight = 0;
            $cartRule->active = 1;

            $cartRule->add();

        // ----------------------------------------------------------------------- //

            $context = Context::getContext();
            $id_lang = (int) $context->language->id;
            $id_shop = (int) $context->shop->id;
            $configuration = Configuration::getMultiple(
                [
                'PS_SHOP_EMAIL',
                'PS_SHOP_NAME',
                'PS_SHOP_DOMAIN'
                ],$id_lang, null, $id_shop
            );

            $template_vars = [
                '{firstname}' => $customer->firstname,
                '{lastname}' => $customer->lastname,
                '{voucher_amount}' => Configuration::get('MONTANTCODEPROMO'),
                '{voucher_code}' => Configuration::get('MYCODEPROMO')."_".$customer->firstname,
                '{voucher_date}' => Configuration::get('ENDCODEPROMO'),
                '{shop_name}' => $configuration['PS_SHOP_EMAIL'],
                '{shop_url}' => 'https://'.$configuration['PS_SHOP_DOMAIN'].'/'.$configuration['PS_SHOP_NAME']
            ];

            Mail::send(
                $id_lang,
                'vouchersubscriber',
                $this->l('Welcome code'),
                $template_vars,
                $customer->email,
                null,
                null,
                null,
                null,
                null,
                _PS_MODULE_DIR_.'vouchernewcustomer/mails/fr/vouchersubscriber.html'
            );

        }
    }



    public function renderWidget($hookName, array $configuration)
    {
        if (Configuration::get('ACTIVATE_VOUCHER') == 1)
        {
            $variables = $this->getWidgetVariables($hookName, $configuration);

            if(empty($variables))
            {
                return false;
            }

            $this->smarty->assign($variables);

            return $this->fetch('module:vouchernewcustomer/views/templates/hook/blockLeftColumn.tpl');

        }
    }

    public function getWidgetVariables($hookName, array $configuration)
    {
        if (Configuration::get('ACTIVATE_VOUCHER') == 1) 
        {
            $type_reduce = '';

            if(Configuration::get('TYPEDECODEPROMO') == 1)
            {
                $type_reduce = "%";
            }
            else {
                $type_reduce = '€';
            }


            return [
                'amount' => Configuration::get('MONTANTCODEPROMO'),
                'type_reduce' => $type_reduce,
            ];
        }
        
    }


    public function hookDisplayHeader($params)
    {

        if (Configuration::get('ACTIVATE_VOUCHER') == 1) 
        {
            $type_reduce = '';

            if(Configuration::get('TYPEDECODEPROMO') == 1)
            {
                $type_reduce = "%";
            }
            else {
                $type_reduce = '€';
            }


            $this->context->smarty->assign([
                'amount' => Configuration::get('MONTANTCODEPROMO'),
                'type_reduce' => $type_reduce,
            ]);
            
            return $this->display(__FILE__,'views/templates/hook/blockHeader.tpl');
        }        
        
    }


}