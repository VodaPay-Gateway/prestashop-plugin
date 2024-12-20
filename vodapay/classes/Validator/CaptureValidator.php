<?php

namespace VodaPay\Validator;

use VodaPay\Logger;

class CaptureValidator
{
    /**
     * Performs validation for capture transaction
     *
     * @param array $response
     *
     * @return bool
     */
    public function validate(array $response): bool
    {
        $logger      = new Logger();
        $log         = [];
        $log['path'] = __METHOD__;
        if (!isset($response['result']) && !is_array($response['result'])) {
            $log['response_validate'] = false;
            $logger->addLog($log);

            return false;
        } else {
            $log['response_validate'] = true;
            $logger->addLog($log);

            return true;
        }
    }
}
