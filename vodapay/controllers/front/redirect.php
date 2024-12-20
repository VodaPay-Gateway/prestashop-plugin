<?php

use VodaPay\Logger;
use VodaPay\CronLogger;
use VodaPay\Command;
use VodaPay\Config\Config;
use Ngenius\NgeniusCommon\Formatter\ValueFormatter;
use Ngenius\NgeniusCommon\Processor\ApiProcessor;

class VodaPayRedirectModuleFrontController extends ModuleFrontController
{
    public const VODAPAY_CAPTURE_LITERAL = 'cnp:capture';

    /**
     * Processing of API response
     *
     * @return void
     * @throws Exception
     */
    public function postProcess()
    {
        $logger      = new Logger();
        $config      = new Config();
        $command     = new Command();
        $log         = [];
        $log['path'] = __METHOD__;
        $ref         = $_REQUEST['ref'];

        $isValidRef = preg_match('/([a-z0-9]){8}-([a-z0-9]){4}-([a-z0-9]){4}-([a-z0-9]){4}-([a-z0-9]){12}$/', $ref);

        if (!$isValidRef || $config->isDebugCron()) {
            $log['redirected_to'] = 'module/vodapay/crondebug';
            $logger->addLog($log);
            Tools::redirect(\Tools::getHttpHost(true) . __PS_BASE_URI__ . 'module/vodapay/crondebug');
        }

        $vodaPayOrder = $this->getVodaPayOrder($ref);

        $cart_id = $vodaPayOrder["id_cart"];

        $this->context->cart = new Cart($cart_id);

        $this->context->cookie->id_cart = $cart_id;

        $cart = $this->context->cart;

        $response     = $command->getOrderStatusRequest($ref);
        $apiProcessor = new ApiProcessor($response);

        $orderState = $response['_embedded']['payment'][0]['state'] ?? null;

        if ($orderState != 'AUTHORISED'
            && $orderState != 'CAPTURED'
            && $orderState != 'PURCHASED'
        ) {
            $command::deleteVodaPayOrder($cart_id);
            $log['redirected_to'] = 'module/vodapay/failedorder';
            $logger->addLog($log);
            /** @noinspection PhpUndefinedConstantInspection */
            Tools::redirect(\Tools::getHttpHost(true) . __PS_BASE_URI__ . 'module/vodapay/failedorder');
        }

        if (!Order::getByCartId($cart_id)) {
            $this->module->validateOrder(
                (int)$cart_id,
                $config->getInitialStatus(),
                (float)$vodaPayOrder["amount"],
                $this->module->l($config->getModuleName(), 'validation'),
                null,
                [],
                $cart->id_currency,
                false,
                $cart->secure_key
            );
        }

        $order = Order::getByCartId($cart_id);

        $this->updateVodaPayOrderStatusToProcessing($vodaPayOrder, $order);

        $order->setCurrentState((int)Configuration::get($config->getOrderStatus() . '_PROCESSING'));

        $this->processOrder($apiProcessor, $vodaPayOrder);

        $redirectLink         = $this->module->getOrderConfUrl($order);
        $log['redirected_to'] = $redirectLink;
        $logger->addLog($log);
        Tools::redirectLink($redirectLink);
    }

    /**
     * Process Order.
     *
     * @param ApiProcessor $apiProcessor
     * @param array $vodaPayOrder
     * @param int|null $cronJob
     *
     * @return bool
     */
    public function processOrder(ApiProcessor $apiProcessor, $vodaPayOrder, $cronJob = false)
    {
        $command    = new Command();
        $config     = new Config();
        $cronLogger = new CronLogger();

        $cart_id = $vodaPayOrder['id_cart'];

        if (!Order::getByCartId($cart_id)) {
            $cart = new Cart($cart_id);

            if (empty($cart->id)) {
                $cronLogger->addLog("VODAPAY: Processing order #" . 'null');
                $cronLogger->addLog("VODAPAY: Platform order not found");
                $cronLogger->addLog("VODAPAY: Cron ended");
            }

            $this->module->validateOrder(
                (int)$cart_id,
                $config->getInitialStatus(),
                (float)$vodaPayOrder["amount"],
                $this->module->l($config->getModuleName(), 'validation'),
                null,
                [],
                $cart->id_currency,
                false,
                $cart->secure_key
            );
        }

        $order = Order::getByCartId($cart_id);

        $cronLogger->addLog("VODAPAY: Processing order #" . $order->id);

        $captureAmount = 0;
        $transactionId = null;
        $response      = $apiProcessor->getResponse();

        if (Validate::isLoadedObject($order)) {
            $paymentId = $apiProcessor->getPaymentId();
            $state     = $apiProcessor->getState() ?? null;
            $action    = $response['action'];
            $apiProcessor->processPaymentAction($action, $state);

            if ($apiProcessor->isPaymentAbandoned()) {
                $state = 'FAILED';
            }

            switch ($state) {
                case 'CAPTURED':
                case 'PURCHASED':
                    $captureAmount = ValueFormatter::intToFloatRepresentation(
                        $vodaPayOrder['currency'],
                        $apiProcessor->getCapturedAmount()
                    );
                    $transactionId = $apiProcessor->getTransactionId();
                    $status        = $config->getOrderStatus() . '_COMPLETE';
                    $command->sendOrderConfirmationMail($order);
                    if ($captureAmount === 0) {
                        $captureAmount = $vodaPayOrder['amount'];
                    }
                    break;

                case 'AUTHORISED':
                    $command->sendOrderConfirmationMail($order);
                    $status = $config->getOrderStatus() . '_AUTHORISED';
                    break;

                case 'FAILED':
                    $status = $config->getOrderStatus() . '_DECLINED';
                    break;

                default:
                    $status = $config->getOrderStatus() . '_PENDING';
                    break;
            }
            if (isset($state)) {
                $authResponse = $response['_embedded']['payment'][0]['authResponse'] ?? null;
                $data         = [
                    'id_order'      => $order->id,
                    'id_payment'    => $paymentId,
                    'capture_amt'   => $captureAmount,
                    'status'        => $status,
                    'state'         => $state,
                    'reference'     => $vodaPayOrder['reference'],
                    'id_capture'    => $transactionId,
                    'auth_response' => json_encode($authResponse, true),
                ];
                $command->updateVodaPay($data);
                $command->updatePsOrderPayment($this->getOrderPaymentRequest($apiProcessor));
                $command->addCustomerMessage($response, $order);
                $order->setCurrentState((int)Configuration::get($status));

                return true;
            }
        } else {
            $command::deleteVodaPayOrder($cart_id);
        }

        return false;
    }

    /**
     * Gets Order Payment Request
     *
     * @param ApiProcessor $apiProcessor
     *
     * @return array
     */
    public static function getOrderPaymentRequest(ApiProcessor $apiProcessor): array
    {
        $response      = $apiProcessor->getResponse();
        $paymentMethod = $response['_embedded']['payment'][0]['paymentMethod'] ?? null;
        if (isset($response['_embedded']['payment'][0]['state'])) {
            $transactionId = $apiProcessor->getTransactionId();
        }

        return [
            'id_order'        => $response['merchantOrderReference'],
            'amount'          => $response['amount']['value'],
            'transaction_id'  => $transactionId ?? null,
            'card_number'     => $paymentMethod['pan'] ?? null,
            'card_brand'      => $paymentMethod['name'] ?? null,
            'card_expiration' => $paymentMethod['expiry'] ?? null,
            'card_holder'     => $paymentMethod['cardholderName'] ?? null,
            'currencyCode'    => $response['amount']['currencyCode'],
        ];
    }

    /**
     * Gets vodapay order by reference
     *
     * @param string $reference
     *
     * @return array|bool
     */
    public static function getVodaPayOrder(string $reference): array|bool
    {
        $sql = new \DbQuery();
        $sql->select('*')->from("vodapay_online_payment")->where('reference ="' . pSQL($reference) . '"');

        return \Db::getInstance()->getRow($sql);
    }

    /**
     * Update VodaPay Order Status To Processing
     *
     * @param string $reference
     *
     * @return bool
     */
    public static function updateVodaPayOrderStatusToProcessing($ngenusOrder, $order)
    {
        $command      = new Command();
        $config       = new Config();
        $vodaPayOrder = [
            'status'    => $config->getOrderStatus() . '_PROCESSING',
            'reference' => $ngenusOrder['reference'],
            'id_order'  => $order->id
        ];
        $command->updateVodaPay($vodaPayOrder);
        $command->addCustomerMessage(null, $order);

        return true;
    }
}
