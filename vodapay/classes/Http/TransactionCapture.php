<?php

namespace VodaPay\Http;

use Exception;
use VodaPay\Command;
use VodaPay\Config\Config;
use VodaPay\Http\AbstractTransaction;
use Ngenius\NgeniusCommon\Formatter\ValueFormatter;

class TransactionCapture extends AbstractTransaction
{
    /**
     * Processing of API response
     *
     * @param $responseString
     *
     * @return array
     * @throws Exception
     */
    public function postProcess($responseString): ?array
    {
        $response = json_decode($responseString, true);

        if (isset($response['errors']) && is_array($response['errors'])) {
            return null;
        } else {
            $lastTransaction = '';
            if (isset($response['_embedded'][self::VODAPAY_CAPTURE_LITERAL])
                && is_array($response['_embedded'][self::VODAPAY_CAPTURE_LITERAL])
            ) {
                $lastTransaction = end($response['_embedded'][self::VODAPAY_CAPTURE_LITERAL]);
            }
            if (isset($lastTransaction['state']) && $lastTransaction['state'] == 'SUCCESS') {
                return $this->captureProcess($response, $lastTransaction);
            } else {
                return null;
            }
        }
    }

    /**
     * Processing of capture response
     *
     * @param array $response
     * @param array $lastTransaction
     *
     * @return array|bool
     * @throws Exception
     */
    protected function captureProcess($response, $lastTransaction)
    {
        $config         = new Config();
        $command        = new Command();
        $capturedAmt    = $this->captureAmount($lastTransaction);
        $transactionId  = $this->transactionId($lastTransaction);
        $state          = $response['state'] ?? '';
        $orderReference = $response['orderReference'] ?? '';
        $orderStatus    = $config->getOrderStatus() . '_FULLY_CAPTURED';

        $vodaPayOrder = [
            'capture_amt' => max($capturedAmt, 0),
            'status'      => $orderStatus,
            'state'       => $state,
            'reference'   => $orderReference,
            'id_capture'  => $transactionId,
        ];

        $command->updateVodaPay($vodaPayOrder);

        $_SESSION['vodapay_fully_captured'] = 'true';

        return [
            'result' => [
                'captured_amt' => $capturedAmt,
                'state'        => $state,
                'order_status' => $orderStatus,
                'payment_id'   => $transactionId
            ]
        ];
    }

    /**
     * get capture Amount
     *
     * @param array $lastTransaction
     *
     * @return int|string|float
     */
    protected function captureAmount(array $lastTransaction): int|string|float
    {
        $capturedAmt  = 0;
        $currencyCode = $lastTransaction['amount']['currencyCode'];

        if (isset($lastTransaction['state'])
            && ($lastTransaction['state'] == 'SUCCESS')
            && isset($lastTransaction['amount']['value'])) {
            $capturedAmt = ValueFormatter::intToFloatRepresentation($currencyCode, $lastTransaction['amount']['value']);
        }

        return $capturedAmt;
    }

    /**
     * get transaction Id
     *
     * @param array $lastTransaction
     *
     * @return string|int
     */
    protected function transactionId(array $lastTransaction): string|int
    {
        $transactionId = '';
        if (isset($lastTransaction['_links']['self']['href'])) {
            $transactionArr = explode('/', $lastTransaction['_links']['self']['href']);
            $transactionId  = end($transactionArr);
        }

        return $transactionId;
    }
}
