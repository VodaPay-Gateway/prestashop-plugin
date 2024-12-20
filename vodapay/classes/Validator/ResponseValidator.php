<?php

namespace VodaPay\Validator;

class ResponseValidator
{
    /**
     * Performs response validation for transaction
     *
     * @param array $response
     *
     * @return array|bool
     */
    public function validate(array $response): bool|string
    {
        if (isset($response['payment_url']) && filter_var($response['payment_url'], FILTER_VALIDATE_URL)) {
            return $response['payment_url'];
        } else {
            return false;
        }
    }
}
