<?php

namespace VodaPay\Request;

use VodaPay\Logger;
use VodaPay\Config\Config;
use Ngenius\NgeniusCommon\NgeniusHTTPCommon;
use Ngenius\NgeniusCommon\NgeniusHTTPTransfer;
use PrestaShopException;

class TokenRequest
{
    /**
     * Builds access token request
     *
     * @return array|bool
     * @throws PrestaShopException
     */
    public function getAccessToken(): bool|string
    {
        $config          = new Config();
        $logger          = new Logger();
        $result          = array();
        $log             = [];
        $log['path']     = __METHOD__;
        $tokenRequestURL = $config->getTokenRequestURL();
        $httpTransfer    = new NgeniusHTTPTransfer("");
        $httpTransfer->setTokenHeaders($config->getApiKey());
        $httpTransfer->setUrl($tokenRequestURL);
        $httpTransfer->setMethod("POST");
        $httpTransfer->setHttpVersion($config->getHTTPVersion());

        $log['token_request'] = $httpTransfer->getHeaders();

        $response = NgeniusHTTPCommon::placeRequest($httpTransfer);

        try {
            $result          = json_decode($response);
            $log['response'] = $result;

            return $result->access_token ?? false;
        } catch (PrestaShopException $e) {
            return false;
        } finally {
            $logger->addLog($log);
        }
    }
}
