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

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_IppGateway extends PaymentModule
{
    private $_html = '';
    private $_postErrors = [];

    public $checkName;
    public $address;
    public $extra_mail_vars;

    public function __construct()
    {
        $this->name = '{{PlaceHolder-PartnerFolder}}';
        $this->tab = 'payments_gateways';
        $this->version = '2.0.5';
        $this->author = '{{PlaceHolder-PartnerName}}';
        $this->controllers = ['payment', 'validation'];

        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $config = Configuration::getMultiple(['MERCHANT_ID', 'PAYMENT_KEY']);
        if (isset($config['MERCHANT_ID'])) {
            $this->checkName = $config['MERCHANT_ID'];
        }
        if (isset($config['PAYMENT_KEY'])) {
            $this->address = $config['PAYMENT_KEY'];
        }

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('{{PlaceHolder-PartnerName}} - Payments by VISA og MasterCard', [], 'Modules.PsIppGateway.Admin');
        $this->description = $this->trans('This module allows you to accept payments by VISA og MasterCard.', [], 'Modules.PsIppGateway.Admin');
        $this->confirmUninstall = $this->trans('Are you sure you want to delete these details?', [], 'Modules.PsIppGateway.Admin');
        $this->ps_versions_compliancy = ['min' => '1.7.1.0', 'max' => _PS_VERSION_];

        if ((!isset($this->checkName) || !isset($this->address) || empty($this->checkName) || empty($this->address))) {
            $this->warning = $this->trans('The Merchant ID and Payment Key fields must be configured before using this module.', [], 'Modules.PsIppGateway.Admin');
        }
        if (!count(Currency::checkPaymentCurrencies($this->id))) {
            $this->warning = $this->trans('No currency has been set for this module.', [], 'Modules.PsIppGateway.Admin');
        }

    }



    public function install()
    {
        return parent::install()
            && $this->registerHook('paymentOptions')
            && $this->registerHook('paymentReturn')
            && $this->registerHook('actionFrontControllerSetMedia')
            ;
    }
    public function hookActionFrontControllerSetMedia($params)
    {
        $this->context->controller->registerJavascript(
            'ps_ippeurope-javascript',
            $this->_path.'views/js/payments.js',
            [
                'position' => 'bottom',
                'priority' => 1000,
            ]
        );
        $this->context->controller->registerJavascript(
            'ps_ippeurope-payment-status-javascript',
            'https://pay.ippeurope.com/payment_status.js',
            [
                'server'=>'remote',
                'position' => 'bottom',
                'priority' => 999,
            ]
        );
    }

    public function uninstall()
    {
        return Configuration::deleteByName('MERCHANT_ID')
            && Configuration::deleteByName('PAYMENT_KEY')
            && parent::uninstall()
        ;
    }

    private function _postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            if (!Tools::getValue('MERCHANT_ID')) {
                $this->_postErrors[] = $this->trans('The Merchant ID field is required.', [], 'Modules.PsIppGateway.Admin');
            } elseif (!Tools::getValue('PAYMENT_KEY')) {
                $this->_postErrors[] = $this->trans('The Payment Key field is required.', [], 'Modules.PsIppGateway.Admin');
            }
        }
    }

    private function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('MERCHANT_ID', Tools::getValue('MERCHANT_ID'));
            Configuration::updateValue('PAYMENT_KEY', Tools::getValue('PAYMENT_KEY'));
        }
        $this->_html .= $this->displayConfirmation($this->trans('Settings updated', [], 'Admin.Notifications.Success'));
    }

    private function _displayCheck()
    {
        return $this->display(__FILE__, './views/templates/hook/infos.tpl');
    }

    public function getContent()
    {
        $this->_html = '';

        if (Tools::isSubmit('btnSubmit')) {
            $this->_postValidation();
            if (!count($this->_postErrors)) {
                $this->_postProcess();
            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= $this->displayError($err);
                }
            }
        }

        $this->_html .= $this->_displayCheck();
        $this->_html .= $this->renderForm();

        return $this->_html;
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $this->smarty->assign(
            $this->getTemplateVars()
        );


        $newOption = new PaymentOption();
        $newOption->setModuleName($this->name)
                ->setCallToActionText($this->trans('Pay by VISA or MasterCard', [], 'Modules.PsIppGateway.Admin'))
                ->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true))
                ->setAdditionalInformation($this->fetch('module:{{PlaceHolder-PartnerFolder}}/views/templates/front/payment_infos.tpl'));

        return [$newOption];
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        $state = $params['order']->getCurrentState();
        $rest_to_paid = $params['order']->getOrdersTotalPaid() - $params['order']->getTotalPaid();
        if (in_array($state, [Configuration::get('PS_OS_CHEQUE'), Configuration::get('PS_OS_OUTOFSTOCK'), Configuration::get('PS_OS_OUTOFSTOCK_UNPAID')])) {
            $this->smarty->assign([
                'total_to_pay' => Tools::displayPrice(
                    $rest_to_paid,
                    new Currency($params['order']->id_currency),
                    false
                ),
                'shop_name' => $this->context->shop->name,
                'checkName' => $this->checkName,
                'checkAddress' => Tools::nl2br($this->address),
                'status' => 'ok',
                'id_order' => $params['order']->id,
            ]);
            if (isset($params['order']->reference) && !empty($params['order']->reference)) {
                $this->smarty->assign('reference', $params['order']->reference);
            }
        } else {
            $this->smarty->assign('status', 'failed');
        }

        return $this->fetch('module:{{PlaceHolder-PartnerFolder}}/views/templates/hook/payment_return.tpl');
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency((int) ($cart->id_currency));
        $currencies_module = $this->getCurrency((int) $cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    public function renderForm()
    {
        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Contact details', [], 'Modules.PsIppGateway.Admin'),
                    'icon' => 'icon-envelope',
                ],
                'input' => [
                    [
                        'type' => 'text',
                        'label' => $this->trans('Merchant ID ', [], 'Modules.PsIppGateway.Admin'),
                        'name' => 'MERCHANT_ID',
                        'required' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Payment Key', [], 'Modules.PsIppGateway.Admin'),
                        'name' => 'PAYMENT_KEY',
                        'required' => true,
                    ],
                ],
                'submit' => [
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->id = (int) Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFieldsValues(),
        ];

        return $helper->generateForm([$fields_form]);
    }

    public function getConfigFieldsValues()
    {
        return [
            'MERCHANT_ID' => Tools::getValue('MERCHANT_ID', Configuration::get('MERCHANT_ID')),
            'PAYMENT_KEY' => Tools::getValue('PAYMENT_KEY', Configuration::get('PAYMENT_KEY')),
        ];
    }

    public function getTemplateVars()
    {
        require_once 'classes/IPPGateway.php';
        global $currency;
        $cart = $this->context->cart;
        $total = $this->trans(
            '%amount% (tax incl.)',
            [
                '%amount%' => Tools::displayPrice($cart->getOrderTotal(true, Cart::BOTH)),
            ],
            'Modules.PsIppGateway.Admin'
        );

        $checkOrder = Configuration::get('MERCHANT_ID');
        if (!$checkOrder) {
            $checkOrder = '___________';
        }

        $checkAddress = Tools::nl2br(Configuration::get('PAYMENT_KEY'));
        if (!$checkAddress) {
            $checkAddress = '___________';
        }

        $ipp = new IPPGateway(Configuration::get('MERCHANT_ID'),Configuration::get('PAYMENT_KEY'));
        $data   = [];
        $data["currency"] = $currency->iso_code;
        $data["amount"] = number_format($cart->getOrderTotal(true, Cart::BOTH),2,"","");
        $data["order_id"] = $cart->id;
        $data["transaction_type"] = "ECOM";
        $data["accepturl"] = $this->context->link->getModuleLink($this->name, 'validation', [], true);
        $data = $ipp->checkout_id($data);
        return [
            'checkoutId'    => $data->checkout_id,
            'cryptogram'    => $data->cryptogram,
            'checkTotal' => $total,
            'checkOrder' => $checkOrder,
            'checkAddress' => $checkAddress,
        ];
    }
}
