<?php

class pwsmsprosto extends Module
{
    public function __construct()
    {
        $this->name = get_class($this);
        $this->version = '0.1.0';
        $this->author = 'PrestaWeb';

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('SMS Prosto');
        $this->description = $this->l('SMS Prosto');

        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5.0.0', 'max' => _PS_VERSION_);
    }

    protected function renderForm()
    {
        if (Tools::isSubmit('submitF')) {
            $this->postProcess();
        }

        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => 'Настройка',
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => 'E-mail:',
                        'name' => 'email',
                    ),
                    array(
                        'type' => 'password',
                        'label' => 'Пароль',
                        'name' => 'password',
                    ),
                    array(
                        'type' => 'textarea',
                        'label' => 'Текст сообщения:',
                        'name' => 'textsms',
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Сгенерировать'),
                    'value' => 1
                )
            ),
        );
        
        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->submit_action = 'submitF';
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => array(
                'email' => Configuration::get('PWSMSPROSTO_EMAIL'),
                'password' => Configuration::get('PWSMSPROSTO_PASSWORD'),
                'textsms' => Configuration::get('PWSMSPROSTO_TEXTSMS'),
            )
        );
        
        return $helper->generateForm(array($fields_form));
    }

    protected function postProcess()
    {
        Configuration::updateValue('PWSMSPROSTO_EMAIL', Tools::getValue('email'));
        Configuration::updateValue('PWSMSPROSTO_PASSWORD', Tools::getValue('password'));
        Configuration::updateValue('PWSMSPROSTO_TEXTSMS', Tools::getValue('textsms'));
    }

    public function install($delete_params = true)
    {
        return parent::install();
    }

    public function uninstall($delete_params = true)
    {
        return parent::uninstall();
    }

    public function getContent()
    {
        return $this->renderForm();
    }
    
    public function hookDisplayOrderConfirmation($params)
    {
        $order = $params['order'];
        $idOrder = $order->id;
        
        $address = new Address($order->id_address_delivery);

        if (isset($address->phone)) {
            $email = Configuration::get('PWSMSPROSTO_EMAIL');
            $password = Configuration::get('PWSMSPROSTO_PASSWORD');
            $text = Configuration::get('PWSMSPROSTO_TEXTSMS');
            $phone = $address->phone;

            $text = str_replace('{order_number}', $order->id, $text);

            $url = 'http://api.sms-prosto.ru?&method=push_msg&format=json&email=' .
                $email . '&password=' .
                $password . '&text=' .
                $text . '&phone=' .
                $phone;
            file_get_contents($url);
        }

    }
}
