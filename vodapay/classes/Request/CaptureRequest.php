<?php

namespace VodaPay\Request;

use VodaPay\Logger;
use VodaPay\Config\Config;
use Ngenius\NgeniusCommon\Formatter\ValueFormatter;
use VodaPay\Request\TokenRequest;

class CaptureRequest
{
    /**
     * Builds ENV Capture request
     *
     * @param array $ngenusOrder
     *
     * @return array|bool
     */
    public function build(array $ngenusOrder): bool|array
    {
        $config               = new Config();
        $logger               = new Logger();
        $tokenRequest         = new TokenRequest();
        $log                  = [];
        $log['path']          = __METHOD__;
        $log['is_configured'] = false;
        $storeId              = isset(\Context::getContext()->shop->id) ? (int)\Context::getContext()->shop->id : null;
        $currencyCode         = $ngenusOrder['currency'];
        $amount               = ValueFormatter::floatToIntRepresentation($currencyCode, $ngenusOrder['amount']);

        if ($config->isComplete()) {
            $log['is_configured']   = true;
            $data                   = [
                'token'   => $tokenRequest->getAccessToken(),
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
                    'uri'    => $config->getOrderCaptureURL(
                        $ngenusOrder['reference'],
                        $ngenusOrder['id_payment'],
                        $storeId
                    )
                ]
            ];
            $log['capture_request'] = json_encode($data);
            $logger->addLog($log);

            return $data;
        } else {
            return false;
        }
    }
}
