<?php

namespace VodaPay\Http;

use Exception;
use VodaPay\Command;
use VodaPay\Config\Config;
use VodaPay\Http\AbstractTransaction;
use stdClass;

class TransactionPurchase extends AbstractTransaction
{
    /**
     * Processing of API response
     *
     * @param $responseString
     *
     * @return array|bool
     * @throws Exception
     */
    public function postProcess($responseString): ?array
    {
        $command  = new Command();
        $response = json_decode($responseString);

        if (isset($response->_links->payment->href)
            && $command->placeVodaPayOrder($this->buildVodaPayData($response))
        ) {
            return ['payment_url' => $response->_links->payment->href];
        }
        $_SESSION['vodapay_errors'] = $response->errors[0]->message;

        return null;
    }

    /**
     * Build VodaPay Data Array
     *
     * @param stdClass $response
     * @param $order
     *
     * @return array
     * @throws Exception
     */
    protected function buildVodaPayData(stdClass $response): array
    {
        $config            = new Config();
        $data              = [];
        $data['reference'] = $response->reference ?? '';
        $data['action']    = $response->action ?? '';
        $data['state']     = $response->_embedded->payment[0]->state ?? '';
        $data['status']    = $config->getOrderStatus() . '_PENDING';
        $data['id_order']  = '';
        $data['id_cart']   = $response->merchantOrderReference ?? '';
        $data['amount']    = $response->amount->value ?? '';
        $data['currency']  = $response->amount->currencyCode ?? '';
        $data['outlet_id'] = $response->outletId ?? '';

        return $data;
    }
}
