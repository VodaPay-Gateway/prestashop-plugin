<?php

namespace VodaPay\Config;

use Exception;
use VodaPay\Command;
use SimpleXMLElement;

class Config
{
    /**
     * Config tags
     */
    const TOKEN_ENDPOINT     = "/identity/auth/access-token";
    const ORDER_ENDPOINT     = "/transactions/outlets/%s/orders";
    const FETCH_ENDPOINT     = "/transactions/outlets/%s/orders/%s";
    const CAPTURE_ENDPOINT   = "/transactions/outlets/%s/orders/%s/payments/%s/captures";
    const VOID_AUTH_ENDPOINT = "/transactions/outlets/%s/orders/%s/payments/%s/cancel";
    const REFUND_ENDPOINT    = "/transactions/outlets/%s/orders/%s/payments/%s/captures/%s/refund";
    const SANDBOX            = 'sandbox';
    const LIVE               = 'live';


    /**
     * Gets Display Name.
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getDisplayName(int $storeId = null): string
    {
        return \Configuration::get('VODAPAY_DISPLAY_NAME', $storeId = null);
    }

    /**
     * Get XML data
     *
     * @return false|SimpleXMLElement Token
     * @throws Exception
     */
    public function getConfig()
    {
        /** @noinspection PhpUndefinedConstantInspection */
        $file = _PS_MODULE_DIR_ . 'vodapay/bankconfig.xml';
        if (file_exists($file)) {
            return new SimpleXMLElement(\Tools::file_get_contents($file));
        } else {
            return false;
        }
    }

    /**
     * Gets Module Name.
     *
     * @return string
     * @throws Exception
     */
    public function getModuleName(): false|string
    {
        $config = $this->getConfig();
        if (!empty($config->name)) {
            return $config->name;
        }

        return false;
    }

    /**
     * Gets Module Display Name.
     *
     * @return false|string
     * @throws Exception
     */
    public function getModuleDisplayName(): false|string
    {
        $config = $this->getConfig();
        if (!empty($config->displayName)) {
            return $config->displayName;
        }

        return false;
    }

    /**
     * Gets Module Description.
     *
     * @return false|string
     * @throws Exception
     */
    public function getModuleDescription(): false|string
    {
        $config = $this->getConfig();
        if (!empty($config->description)) {
            return $config->description;
        }

        return false;
    }

    /**
     * Gets Order Status.
     *
     * @return false|string
     * @throws Exception
     */
    public function getOrderStatus(): false|string
    {
        $config = $this->getConfig();
        if (!empty($config->orderStatus)) {
            return $config->orderStatus;
        }

        return false;
    }

    /**
     * Gets Order Status Label.
     *
     * @return false|string
     * @throws Exception
     */
    public function getOrderStatusLabel(): false|string
    {
        $config = $this->getConfig();
        if (!empty($config->orderStatusLabel)) {
            return $config->orderStatusLabel;
        }

        return false;
    }

    /**
     * Get Sandbox API URL
     *
     * @return string URL
     */
    public function getSandboxApiUrl(): string
    {
        return \Configuration::get('VODAPAY_UAT_API_URL');
    }

    /**
     * Get Live API URL
     *
     * @return string URL
     */
    public function getLiveApiUrl(): string
    {
        return \Configuration::get('VODAPAY_LIVE_API_URL');
    }


    /**
     * Gets Api Key.
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getApiKey(int $storeId = null): string
    {
        return \Configuration::get('VODAPAY_API_KEY', $storeId);
    }

    /**
     * Gets Debug On.
     *
     * @param int|null $storeId
     *
     * @return int
     */
    public function isDebugMode(int $storeId = null): bool|int
    {
        return (bool)\Configuration::get('VODAPAY_DEBUG', $storeId);
    }

    /**
     * Gets Debug Cron On.
     *
     * @param int|null $storeId
     *
     * @return bool|int
     */
    public function isDebugCron(int $storeId = null): bool|int
    {
        return (bool)\Configuration::get('VODAPAY_DEBUG_CRON', $storeId);
    }

    /**
     * Gets Outlet Reference ID.
     *
     * @param $orderRef
     *
     * @return string
     */
    public function getOutletReferenceId($orderRef): false|string
    {
        $command = new Command();
        $ngOrder = $command->getVodaPayOrderReference($orderRef);
        if ($ngOrder['outlet_id']) {
            return $ngOrder['outlet_id'];
        }

        return false;
    }

    /**
     * Gets Outlet Reference ID.
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getMultiOutletReferenceId($currency, int $storeId = null): false|string
    {
        $decCurOut = json_decode(\Configuration::get('VODAPAY_CURRENCY_OUTLETID', $storeId), true);
        foreach ($decCurOut as $value) {
            if ($value['CURRENCY'] == $currency) {
                return $value['OUTLET_ID'];
            }
        }

        return false;
    }

    /**
     * Gets Initial Status.
     *
     * @param int|null $storeId
     *
     * @return string
     * @throws Exception
     */
    public function getInitialStatus(int $storeId = null): string
    {
        $config = new Config();

        return \Configuration::get($config->getOrderStatus() . '_PENDING', $storeId);
    }

    /**
     * Gets value of configured environment.
     * Possible values: yes or no.
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isActive(int $storeId = null): bool
    {
        return (bool)\Configuration::get('ENABLED', $storeId);
    }

    /**
     * Retrieve apikey and outletReferenceId empty or not
     *
     * @param null $storeId
     *
     * @return bool
     */
    public function isComplete($storeId = null): bool
    {
        return (!empty(Config::getApiKey($storeId)));
    }

    /**
     * Gets Environment.
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getEnvironment(int $storeId = null): string
    {
        return \Configuration::get('VODAPAY_ENVIRONMENT', $storeId);
    }

    /**
     * Gets CURL HTTP Version
     *
     * @param $storeId
     *
     * @return mixed
     */
    public function getHTTPVersion($storeId = null): mixed
    {
        return \Configuration::get('VODAPAY_HTTP_VERSION', $storeId);
    }

    /**
     * Gets Api Url.
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getApiUrl(int $storeId = null): string
    {
        $value = $this->getLiveApiUrl();
        if ($this->getEnvironment($storeId) == Config::SANDBOX) {
            $value = $this->getSandboxApiUrl();
        }

        return $value;
    }

    /**
     * Gets Token Request URL.
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getTokenRequestURL(int $storeId = null): string
    {
        $token_endpoint = Config::TOKEN_ENDPOINT;

        return $this->getApiUrl($storeId) . $token_endpoint;
    }

    /**
     * Gets Order Request URL.
     *
     * @param $currency
     * @param int|null $storeId
     *
     * @return string
     */
    public function getOrderRequestURL($currency, int $storeId = null): string
    {
        $order_endpoint = Config::ORDER_ENDPOINT;
        $endpoint       = sprintf($order_endpoint, $this->getMultiOutletReferenceId($currency, $storeId));

        return $this->getApiUrl($storeId) . $endpoint;
    }

    /**
     * Gets Fetch Request URL.
     *
     * @param int|null $storeId
     * @param string $orderRef
     *
     * @return string
     */
    public function getFetchRequestURL(string $orderRef, int $storeId = null): string
    {
        $fetch_endpoint = Config::FETCH_ENDPOINT;
        $endpoint       = sprintf($fetch_endpoint, $this->getOutletReferenceId($orderRef, $storeId), $orderRef);

        return $this->getApiUrl($storeId) . $endpoint;
    }

    /**
     * Gets Debug On.
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isDebugOn(int $storeId = null): bool
    {
        /** @noinspection PhpUndefinedClassConstantInspection */
        return (bool)$this->getValue(Config::VODAPAY_DEBUG, $storeId);
    }

    /**
     * Gets Order Capture URL.
     *
     * @param int|null $storeId
     * @param string $orderRef
     * @param string $paymentRef
     *
     * @return string
     */
    public function getOrderCaptureURL(string $orderRef, string $paymentRef, int $storeId = null): string
    {
        $capture_endpoint = Config::CAPTURE_ENDPOINT;
        $endpoint         = sprintf(
            $capture_endpoint,
            $this->getOutletReferenceId($orderRef, $storeId),
            $orderRef,
            $paymentRef
        );

        return $this->getApiUrl($storeId) . $endpoint;
    }

    /**
     * Gets Order Void URL.
     *
     * @param int|null $storeId
     * @param string $orderRef
     * @param string $paymentRef
     *
     * @return string
     */
    public function getOrderVoidURL(string $orderRef, string $paymentRef, int $storeId = null): string
    {
        $void_endpoint = Config::VOID_AUTH_ENDPOINT;
        $endpoint      = sprintf(
            $void_endpoint,
            $this->getOutletReferenceId($orderRef, $storeId),
            $orderRef,
            $paymentRef
        );

        return $this->getApiUrl() . $endpoint;
    }

    /**
     * Gets Refund Void URL.
     *
     * @param int|null $storeId
     * @param string $orderRef
     * @param string $paymentRef
     * @param string $transactionId
     *
     * @return string
     */
    public function getOrderRefundURL(
        string $orderRef,
        string $paymentRef,
        string $transactionId,
        int $storeId = null
    ): string {
        $refund_endpoint = Config::REFUND_ENDPOINT;
        $endpoint        = sprintf(
            $refund_endpoint,
            $this->getOutletReferenceId($orderRef, $storeId),
            $orderRef,
            $paymentRef,
            $transactionId
        );

        return $this->getApiUrl() . $endpoint;
    }

    /**
     * Get Plugin Name
     *
     * @return string
     */
    public function getPluginVersion(): string
    {
        $moduleFolder = 'vodapay';
        $xmlFilePath  = _PS_MODULE_DIR_ . $moduleFolder . '/config.xml';

        // Check if the XML file exists
        if (file_exists($xmlFilePath)) {
            // Load and parse the XML file
            $xml = simplexml_load_file($xmlFilePath);

            // Check if the 'version' element exists in the XML
            if (isset($xml->version)) {
                // Access the version information
                $moduleVersion = (string)$xml->version;

                return $moduleVersion;
            } else {
                return "Version information not found file.";
            }
        } else {
            return "Config file not found.";
        }
    }
}
