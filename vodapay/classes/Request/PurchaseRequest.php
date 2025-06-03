<?php

namespace VodaPay\Request;

use VodaPay\Logger;
use VodaPay\Config\Config;
use Ngenius\NgeniusCommon\Formatter\ValueFormatter;
use VodaPay\Request\AbstractRequest;

class PurchaseRequest extends AbstractRequest
{
    /**
     * Builds ENV sale request array
     *
     * @param $order
     * @param $amount
     *
     * @return array
     */
    public function getBuildArray($order, $amount): array
    {
        $config              = new Config();
        $logger              = new Logger();
        $log                 = [];
        $log['path']         = __METHOD__;
        $storeId             = isset(\Context::getContext()->shop->id) ? (int)\Context::getContext()->shop->id : null;
        $currencyCode        = $order['amount']['currencyCode'];
        $data                = [
            'data'   => [
                'action'                 => 'PURCHASE',
                'amount'                 => [
                    'currencyCode' => $currencyCode,
                    'value'        => ValueFormatter::floatToIntRepresentation($currencyCode, $amount),
                ],
                'merchantAttributes'     => [
                    'redirectUrl'          => $order['merchantAttributes']['redirectUrl'],
                    'skipConfirmationPage' => true,
                    'cancelUrl'            => $order['merchantAttributes']['redirectUrl'],
                    'cancelText'           => 'Continue Shopping'
                ],
                'billingAddress'         => [
                    'firstName'   => $order['billingAddress']['firstName'],
                    'lastName'    => $order['billingAddress']['lastName'],
                    'address1'    => $order['billingAddress']['address1'],
                    'address2'    => $order['billingAddress']['address2'],
                    'city'        => $order['billingAddress']['city'],
                    'stateCode'   => $order['billingAddress']['stateCode'],
                    'postalCode'  => $order['billingAddress']['postalCode'],
                    'countryCode' => $order['billingAddress']['countryCode'],
                ],
                'phoneNumber'            => [
                    'countryCode' => $order['phoneNumber']['countryCode'],
                    'subscriber'  => $order['phoneNumber']['subscriber']
                ],
                'merchantOrderReference' => $order['merchantOrderReference'],
                'emailAddress'           => $order['emailAddress'],
                'merchantDefinedData'    => [
                    'pluginName'    => 'prestashop',
                    'pluginVersion' => $config->getPluginVersion()
                ],
            ],
            'method' => "POST",
            'uri'    => $config->getOrderRequestURL($order['amount']['currencyCode'], $storeId)
        ];
        $log['sale_request'] = json_encode($data);
        $logger->addLog($log);

        return $data;
    }
}
