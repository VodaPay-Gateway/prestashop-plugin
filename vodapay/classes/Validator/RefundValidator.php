<?php

namespace VodaPay\Validator;

use VodaPay\Logger;

class RefundValidator
{
    /**
     * Performs refund validation for transaction
     *
     * @param array|null $response
     *
     * @return bool
     */
    public function validate(?array $response): bool
    {
        $logger      = new Logger();
        $log         = [];
        $log['path'] = __METHOD__;

        if (!$response || !isset($response['result']) && !is_array($response['result'])) {
            $log['response_validate'] = false;
            $valid                    = false;
        } else {
            $log['response_validate'] = true;
            $valid                    = true;
        }

        $logger->addLog($log);

        return $valid;
    }
}
