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
        $this->context->smarty->assign([
                                           'module' => \Configuration::get('VODAPAY_DISPLAY_NAME'),
                                       ]);
        $this->setTemplate('module:vodapay/views/templates/front/payment_error.tpl');
    }
}
