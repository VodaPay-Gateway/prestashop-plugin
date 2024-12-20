<?php

namespace VodaPay;

use VodaPay\Config\Config;

class Logger
{
    /**
     * add vodapay log
     *
     * @param string $$message
     *
     * @return void
     */
    public function addLog($message): void
    {
        $config = new Config();
        if ($config->isDebugMode()) {
            $logger = new \FileLogger(0); //0 == debug level, logDebug() won’t work without this.
            /** @noinspection PhpUndefinedConstantInspection */
            $logger->setFilename(_PS_ROOT_DIR_ . "/var/logs/payment.log");
            $logger->logDebug($message);
        }
    }
}
