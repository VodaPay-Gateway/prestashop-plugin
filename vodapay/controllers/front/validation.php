<?php

use VodaPay\Command;
use VodaPay\Logger;
use VodaPay\Config\Config;
use Ngenius\NgeniusCommon\Formatter\ValueFormatter;
use Ngenius\NgeniusCommon\NgeniusUtilities;

class VodaPayValidationModuleFrontController extends ModuleFrontController
{
    public const QUERY_LITERAL = 'index.php?controller=order&step=1';

    /**
     * @see FrontController::postProcess()
     */
    public function postProcess(): void
    {
        $config = new Config();
        $cart   = $this->context->cart;
        if ($cart->id_customer == 0
            || $cart->id_address_delivery == 0
            || $cart->id_address_invoice == 0
            || !$this->module->active
        ) {
            Tools::redirect(self::QUERY_LITERAL);
        }
        // Check that this payment option is still available in case the
        // customer changed his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'vodapay') {
                $authorized = true;
                break;
            }
        }
        if (!$authorized) {
            $this->errors[] = $this->l('This payment method is not available.');
            $this->redirectWithNotifications(self::QUERY_LITERAL);
        }

        if (!$config->isComplete()) {
            $this->errors[] = $this->l('This payment method is not configured.');
            $this->redirectWithNotifications(self::QUERY_LITERAL);
        }

        $this->context->smarty->assign([
                                           'params' => $_REQUEST,
                                       ]);
        // validate Customer
        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect(self::QUERY_LITERAL);
        }
        // create order
        $cart  = $this->context->cart;
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);

        if (!isset($cart->id)) {
            $this->errors[] = $this->l('Your Cart is empty!.');
            $this->redirectWithNotifications(self::QUERY_LITERAL);
        }

        if (!$config->getMultiOutletReferenceId($this->context->currency->iso_code)) {
            $this->errors[] = $this->l('Invalid Combination of Currency & Outlet Id!.');
            $this->redirectWithNotifications(self::QUERY_LITERAL);
        }

        $paymentType = \Configuration::get('VODAPAY_PAYMENT_ACTION');
        $this->paymentActionProcess($paymentType, $total);
    }

    /**
     * Gets order.
     *
     * @return void
     * @throws Exception
     */
    public function paymentActionProcess($paymentType, $total)
    {
        $command = new Command();
        $order   = $this->getOrder();

        $paymentUrl = false;
        switch ($paymentType) {
            case "authorize_capture": // sale
                $paymentUrl = $command->order($order, $total);
                break;
            case "authorize": // authorize
                $paymentUrl = $command->authorize($order, $total);
                break;
            case "authorize_purchase": // purchase
                $paymentUrl = $command->purchase($order, $total);
                break;
            default:
                $this->errors[] = $this->l('Invalid PAYMENT ACTION.');
                $this->redirectWithNotifications(self::QUERY_LITERAL);
                break;
        }
        if (!$paymentUrl) {
            $this->failedPaymentRedirect();
        }
        Tools::redirect($paymentUrl);
    }

    /**
     * Sets order to failed and redirects with error
     *
     * @return void
     */
    public function failedPaymentRedirect(): void
    {
        $errorMessage   = $_SESSION['vodapay_errors'] ?? "Could not start VodaPay...";
        $this->errors[] = $this->l($errorMessage);
        $this->redirectWithNotifications(self::QUERY_LITERAL);
    }

    /**
     * Gets order.
     *
     * @return array
     */
    public function getOrder(): array
    {
        $cart         = $this->context->cart;
        $address      = new Address($cart->id_address_delivery);
        $utilities    = new NgeniusUtilities();
        $countryCode  = $this->context->country->iso_code;
        $currencyCode = $this->context->currency->iso_code;
        $amount       = (float)$cart->getOrderTotal(true, Cart::BOTH);

        /** @noinspection PhpUndefinedConstantInspection */
        return [
            'action'                 => null,
            'amount'                 => [
                'currencyCode' => $currencyCode,
                'value'        => ValueFormatter::floatToIntRepresentation($currencyCode, $amount),
            ],
            'merchantAttributes'     => [
                "redirectUrl" => filter_var(
                    $this->context->link->getModuleLink(
                        $this->module->name,
                        'redirect',
                        [],
                        true
                    ),
                    FILTER_SANITIZE_URL
                )
            ],
            'billingAddress'         => [
                'firstName'   => $address->firstname,
                'lastName'    => $address->lastname,
                'address1'    => $address->address1,
                'address2'    => $address->address2,
                'city'        => $address->city,
                'stateCode'   => $this->getState($address),
                'postalCode'  => $address->postcode,
                'countryCode' => $countryCode,
            ],
            'emailAddress'           => $this->context->customer->email,
            'phoneNumber'            => [
                'countryCode' => $utilities->getCountryTelephonePrefix($countryCode),
                'subscriber'  => $address->phone,
            ],
            'merchantOrderReference' => $cart->id,
            'method'                 => null,
            'uri'                    => null
        ];
    }

    /**
     * @param Address $address
     *
     * @return string
     */
    public function getState($address): string
    {
        $state = new State($address->id_state) ?? '';

        return $state ? (string)$state->name : '';
    }
}
