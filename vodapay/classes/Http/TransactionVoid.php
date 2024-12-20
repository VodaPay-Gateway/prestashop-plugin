<?php

namespace VodaPay\Http;

use Exception;
use VodaPay\Command;
use VodaPay\Config\Config;
use VodaPay\Http\AbstractTransaction;

class TransactionVoid extends Abstracttransaction
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
        $config   = new Config();
        $command  = new Command();
        $response = json_decode($responseString, true);
        if (isset($response['errors']) && is_array($response['errors'])) {
            return null;
        } else {
            $transactionId = '';
            if (isset($response['_links']['self']['href'])) {
                $transactionArr = explode('/', $response['_links']['self']['href']);
                $transactionId  = end($transactionArr);
            }
            $state          = isset($response['state']) ? $response['state'] : '';
            $orderReference = isset($response['orderReference']) ? $response['orderReference'] : '';
            $orderStatus    = $config->getOrderStatus() . '_AUTH_REVERSED';
            $vodaPayOrder   = [
                'status'    => $orderStatus,
                'state'     => $state,
                'reference' => $orderReference,

            ];
            $command->updateVodaPay($vodaPayOrder);
            $_SESSION['vodapay_auth_reversed'] = 'true';

            return [
                'result' => [
                    'state'        => $state,
                    'order_status' => $orderStatus,
                    'id_capture'   => $transactionId,
                ]
            ];
        }
    }
}
