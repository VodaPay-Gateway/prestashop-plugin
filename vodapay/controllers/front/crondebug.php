<?php

class VodaPayCronDebugModuleFrontController extends ModuleFrontController
{
    /**
     * Processing of API response
     *
     * @return void
     * @throws PrestaShopException
     */
    public function postProcess(): void
    {
        $this->context->smarty->assign([
                                           'module' => \Configuration::get('VODAPAY_DISPLAY_NAME'),
                                       ]);
        $this->setTemplate('module:vodapay/views/templates/front/cron_debug.tpl');
    }
}
