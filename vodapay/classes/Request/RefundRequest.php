<?php

namespace VodaPay\Request;

use VodaPay\Logger;
use VodaPay\Config\Config;
use Ngenius\NgeniusCommon\Formatter\ValueFormatter;
use VodaPay\Request\TokenRequest;
use Ngenius\NgeniusCommon\Processor\RefundProcessor;

class RefundRequest
{

    const CNP_CAPTURE = "cnp:capture";
    const CNP_REFUND  = "cnp:refund";

    /**
     * Builds ENV refund request
     *
     * @param array $order
     * @param array $ngenusOrder
     *
     * @return array|bool
     */
    public function build(array $ngenusOrder): bool|array
    {
        $tokenRequest              = new TokenRequest();
        $config                    = new Config();
        $logger                    = new Logger();
        $data                      = array();
        $log                       = [];
        $log['path']               = __METHOD__;
        $log['is_configured']      = false;
        $storeId                   = isset(\Context::getContext()->shop->id) ? (int)\Context::getContext(
        )->shop->id : null;
        $token                     = $tokenRequest->getAccessToken();
        $log['order_data']         = json_encode($ngenusOrder);
        $data['fetch_request_url'] = $config->getFetchRequestURL($ngenusOrder['reference']);
        $data['token']             = $token;

        $response = $this->query_order($data);

        if (isset($response->errors)) {
            return $response->errors[0]->message;
        }

        $payment = $response->_embedded->payment[0];

        $refund_url = RefundProcessor::extractUrl($payment);

        $currencyCode = $ngenusOrder['currency'];

        $amount = ValueFormatter::floatToIntRepresentation($currencyCode, $ngenusOrder['amount']);

        if (empty($refund_url)) {
            return false;
        }

        if ($config->isComplete()) {
            $log['is_configured'] = true;
            $data                 = [
                'token'   => $token,
                'request' => [
                    'data'   => [
                        'amount'              => [
                            'currencyCode' => $ngenusOrder['currency'],
                            'value'        => $amount,
                        ],
                        'merchantDefinedData' => [
                            'pluginName'    => 'prestashop',
                            'pluginVersion' => $config->getPluginVersion()
                        ],
                    ],
                    'method' => "POST",
                    'uri'    => $refund_url
                ]
            ];
            $logger->addLog($log);

            return $data;
        }

        return false;
    }

    public function get_refund_url($payment)
    {
        $refund_url = "";
        $cnpcapture = self::CNP_CAPTURE;
        $cnprefund  = self::CNP_REFUND;
        if ($payment->state == "PURCHASED" && isset($payment->_links->$cnprefund->href)) {
            $refund_url = $payment->_links->$cnprefund->href;
        } elseif ($payment->state == "CAPTURED"
                  && isset($payment->_embedded->$cnpcapture[0]->_links->$cnprefund->href)) {
            $refund_url = $payment->_embedded->$cnpcapture[0]->_links->$cnprefund->href;
        } else {
            if (isset($payment->_links->$cnprefund->href)
                || isset($payment->_embedded->$cnpcapture[0]->_links->$cnprefund->href)) {
                $refund_url = $payment->_embedded->$cnpcapture[0]->_links->$cnprefund->href;
            }
        }

        return $refund_url;
    }

    public function query_order($data)
    {
        $authorization = "Authorization: Bearer " . $data['token'];
        $headers       = array(
            'Content-Type: application/vnd.ni-payment.v2+json',
            $authorization,
            'Accept: application/vnd.ni-payment.v2+json'
        );

        $ch         = curl_init();
        $curlConfig = array(
            CURLOPT_URL            => $data['fetch_request_url'],
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_RETURNTRANSFER => true,
        );

        curl_setopt_array($ch, $curlConfig);
        $response = curl_exec($ch);

        return json_decode($response);
    }
}
