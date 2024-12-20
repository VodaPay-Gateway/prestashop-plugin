<?php

namespace VodaPay\Request;

use VodaPay\Logger;
use VodaPay\Config\Config;
use VodaPay\Request\TokenRequest;

abstract class AbstractRequest
{

    /**
     * Builds ENV request
     *
     * @param array $order
     * @param float $amount
     *
     * @return array|bool
     */
    public function build(array $order, float $amount): bool|array
    {
        $logger               = new Logger();
        $config               = new Config();
        $tokenRequest         = new TokenRequest();
        $log                  = [];
        $log['path']          = __METHOD__;
        $log['is_configured'] = false;
        if ($config->isComplete()) {
            $log['is_configured'] = true;
            $logger->addLog($log);

            return [
                'token'   => $tokenRequest->getAccessToken(),
                'request' => $this->getBuildArray($order, $amount)
            ];
        } else {
            $logger->addLog($log);

            return false;
        }
    }

    /**
     * Builds abstract ENV request array
     *
     * @param $order
     * @param $amount
     *
     * @return array
     */
    abstract public function getBuildArray($order, $amount): array;
}
