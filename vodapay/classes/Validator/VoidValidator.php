<?php

namespace VodaPay\Validator;

use VodaPay\Logger;

class VoidValidator
{
    /**
     * Performs reversed the authorization
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
        if (isset($response['result'])) {
            $log['response_validate'] = true;
            $logger->addLog($log);

            return true;
        } else {
            $log['response_validate'] = false;
            $logger->addLog($log);

            return false;
        }
    }
}
