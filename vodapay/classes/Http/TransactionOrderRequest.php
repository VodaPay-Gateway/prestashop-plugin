<?php

namespace VodaPay\Http;

use VodaPay\Command;
use VodaPay\Config\Config;
use VodaPay\Http\AbstractTransaction;

class TransactionOrderRequest extends AbstractTransaction
{
    /**
     * Processing of API response
     *
     * @param $responseString
     *
     * @return array|null
     */
    public function postProcess($responseString): ?array
    {
        if ($responseString) {
            return json_decode($responseString, true);
        }

        return null;
    }
}
