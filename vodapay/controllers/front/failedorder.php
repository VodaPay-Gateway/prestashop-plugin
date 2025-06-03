<?php

class VodaPayFailedorderModuleFrontController extends ModuleFrontController
{
    /**
     * Processing of API response
     *
     * @return void
     */
    public function postProcess(): void
    {
        $status = Tools::getValue('status', 'Declined'); // Default to "Declined"
        $this->context->smarty->assign([
                                           'module' => \Configuration::get('VODAPAY_DISPLAY_NAME'),
                                           'status' => $status,
                                       ]);
        $this->setTemplate('module:vodapay/views/templates/front/payment_error.tpl');
    }
}
