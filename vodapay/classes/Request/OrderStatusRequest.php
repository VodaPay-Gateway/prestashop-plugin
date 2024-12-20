<?php

namespace VodaPay\Request;

use VodaPay\Logger;
use VodaPay\Config\Config;
use VodaPay\Request\AbstractRequest;

class OrderStatusRequest extends AbstractRequest
{
    /**
     * Builds ENV order status request
     *
     * @param $orderRef
     * @param $amount
     *
     * @return array
     */
    public function getBuildArray($orderRef, $amount): array
    {
        $config              = new Config();
        $logger              = new Logger();
        $shopId              = isset(\Context::getContext()->shop->id) ? (int)\Context::getContext()->shop->id : null;
        $data                = [
            'data'   => [],
            'method' => "GET",
            'uri'    => $config->getFetchRequestURL($orderRef, $shopId)
        ];
        $log                 = [];
        $log['path']         = __METHOD__;
        $log['sale_request'] = json_encode($data);
        $logger->addLog($log);

        return $data;
    }
}
