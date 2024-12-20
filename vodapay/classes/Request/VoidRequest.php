<?php

namespace VodaPay\Request;

use VodaPay\Logger;
use VodaPay\Config\Config;
use VodaPay\Request\TokenRequest;

class VoidRequest
{
    /**
     * Builds ENV void request
     *
     * @param array $order
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
        if ($config->isComplete()) {
            $log['is_configured'] = true;
            $data                 = [
                'token'   => $tokenRequest->getAccessToken(),
                'request' => [
                    'data'   => [],
                    'method' => "PUT",
                    'uri'    => $config->getOrderVoidURL(
                        $ngenusOrder['reference'],
                        $ngenusOrder['id_payment'],
                        $storeId
                    )
                ]
            ];
            $log['void_request']  = json_encode($data);
            $logger->addLog($log);

            return $data;
        } else {
            return false;
        }
    }
}
