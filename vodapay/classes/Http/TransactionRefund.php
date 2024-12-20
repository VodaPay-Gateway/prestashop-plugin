<?php

namespace VodaPay\Http;

use Exception;
use VodaPay\Command;
use VodaPay\Config\Config;
use VodaPay\Http\AbstractTransaction;
use VodaPay\Logger;
use Ngenius\NgeniusCommon\Formatter\ValueFormatter;

class TransactionRefund extends Abstracttransaction
{
    /**
     * Processing of API response
     *
     * @param $responseString
     *
     * @return array|null
     * @throws Exception
     */
    public function postProcess($responseString): ?array
    {
        $response = json_decode($responseString, true);
        if (isset($response['errors']) && is_array($response['errors'])) {
            return null;
        } else {
            $lastTransaction = '';
            if (isset($response['_embedded'][self::VODAPAY_REFUND_LITERAL])
                && is_array($response['_embedded'][self::VODAPAY_REFUND_LITERAL])) {
                $lastTransaction = end($response['_embedded'][self::VODAPAY_REFUND_LITERAL]);
            }

            $logger = new Logger();
            $logger->addLog("******************");
            $logger->addLog($response);
            $logger->addLog("********************");


            if (isset($lastTransaction['state']) && ($lastTransaction['state'] == 'SUCCESS') ||
                (isset($lastTransaction['_links']['cnp:china_union_pay_results'])
                 && $lastTransaction['state'] == 'REQUESTED')) {
                return $this->refundProcess($response, $lastTransaction);
            } else {
                return null;
            }
        }
    }

    /**
     * Processing refund response
     *
     * @param array $response
     * @param $lastTransaction
     *
     * @return array|bool
     * @throws Exception
     */
    protected function refundProcess(array $response, $lastTransaction): array|bool
    {
        $config         = new Config();
        $command        = new Command();
        $currencyCode   = $response['amount']['currencyCode'];
        $captured_amt   = ValueFormatter::intToFloatRepresentation($currencyCode, $response['amount']['value']);
        $getRefundedAmt = $this->refundedAmount($response);
        $refunded_amt   = ValueFormatter::intToFloatRepresentation($currencyCode, $getRefundedAmt);
        $logger         = new Logger();
        $logger->addLog("refund amount");
        $logger->addLog($refunded_amt);
        $getLastRefundedAmt = $this->lastRefundAmount($lastTransaction);
        $last_refunded_amt  = ValueFormatter::intToFloatRepresentation($currencyCode, $getLastRefundedAmt);
        $transactionId      = $this->transactionId($lastTransaction);
        $orderReference     = $response['orderReference'] ?? '';
        $state              = $response['state'] ?? '';
        $captureAmt         = $captured_amt > 0 ? $captured_amt : 0;
        $refundedAmt        = $refunded_amt > 0 ? $refunded_amt : 0;
        if (($captureAmt - $refundedAmt) == 0) {
            $orderStatus                      = $config->getOrderStatus() . '_FULLY_REFUNDED';
            $_SESSION['vodapay_fully_refund'] = 'true';
        } else {
            $orderStatus                        = $config->getOrderStatus() . '_PARTIALLY_REFUNDED';
            $_SESSION['vodapay_partial_refund'] = 'true';
        }
        $vodaPayOrder = [
            'capture_amt'  => $captured_amt,
            'refunded_amt' => $refunded_amt,
            'status'       => $orderStatus,
            'state'        => $state,
            'reference'    => $orderReference
        ];

        $logger = new Logger();
        $logger->addLog("***********************");
        $logger->addLog($vodaPayOrder);
        $logger->addLog("***********************");

        $command->updateVodaPay($vodaPayOrder);

        return [
            'result' => [
                'total_refunded' => $refunded_amt,
                'refunded_amt'   => $last_refunded_amt,
                'state'          => $state,
                'order_status'   => $orderStatus,
                'payment_id'     => $transactionId
            ]
        ];
    }

    /**
     * get captured Amount
     *
     * @param array $response
     *
     * @return int|string
     */
    protected function capturedAmount(array $response): int|string
    {
        $captured_amt = 0;
        if (isset($response['_embedded'][self::VODAPAY_CAPTURE_LITERAL])
            && is_array($response['_embedded'][self::VODAPAY_CAPTURE_LITERAL])
        ) {
            foreach ($response['_embedded'][self::VODAPAY_CAPTURE_LITERAL] as $capture) {
                if (isset($capture['state']) && ($capture['state'] == 'SUCCESS')
                    && isset($capture['amount']['value'])) {
                    $captured_amt += $capture['amount']['value'];
                }
            }
            // for temp MCP enabled customers
            $captured_amt = $response['amount']['value'];
        }

        return $captured_amt;
    }

    /**
     * get refunded Amount
     *
     * @param array $response
     *
     * @return int|string
     */
    protected function refundedAmount(array $response): int|string
    {
        $refunded_amt = 0;
        if (isset($response['_embedded'][self::VODAPAY_REFUND_LITERAL])
            && is_array($response['_embedded'][self::VODAPAY_REFUND_LITERAL])
        ) {
            foreach ($response['_embedded'][self::VODAPAY_REFUND_LITERAL] as $refund) {
                if (isset($refund['state'])
                    && ($refund['state'] == 'SUCCESS'
                        || (isset($refund["_links"][self::CUP_RESULTS_LITERAL])
                            && $refund['state'] == 'REQUESTED'))
                    && isset($refund['amount']['value'])
                ) {
                    $refunded_amt += $refund['amount']['value'];
                }
            }
        }

        return $refunded_amt;
    }

    /**
     * get last refund amount
     *
     * @param array $lastTransaction
     *
     * @return string
     */
    protected function lastRefundAmount(array $lastTransaction): float|int|string
    {
        $last_refunded_amt = 0;
        if (isset($lastTransaction['state']) && ($lastTransaction['state'] == 'SUCCESS')
            && isset($lastTransaction['amount']['value'])) {
            $last_refunded_amt = $lastTransaction['amount']['value'];
        }

        return $last_refunded_amt;
    }

    /**
     * get transaction Id
     *
     * @param array $lastTransaction
     *
     * @return string
     */
    protected function transactionId(array $lastTransaction): string
    {
        $transactionId = '';
        if (isset($lastTransaction['_links']['self']['href'])) {
            $transactionArr = explode('/', $lastTransaction['_links']['self']['href']);
            $transactionId  = end($transactionArr);
        }

        return $transactionId;
    }
}
