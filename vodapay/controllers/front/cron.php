<?php

use VodaPay\Command;
use VodaPay\CronLogger;
use VodaPay\Config\Config;
use Ngenius\NgeniusCommon\Processor\ApiProcessor;

/** @noinspection PhpUndefinedConstantInspection */
include_once _PS_MODULE_DIR_ . 'vodapay/controllers/front/redirect.php';

class VodaPayCronModuleFrontController extends VodaPayRedirectModuleFrontController
{
    /**
     * Cron Task.
     *
     * @return void
     */
    public function postProcess(): void
    {
        $command    = new Command();
        $cronLogger = new CronLogger();
        $token      = $_REQUEST['token'];
        if (isset($token) && $token == \Configuration::get('NING_CRON_TOKEN')) {
            if ($crondatas = $command->validateVodaPayCronSchedule()) {
                foreach ($crondatas as $crondata) {
                    $data = [
                        'id'          => $crondata['id'],
                        'executed_at' => date("Y-m-d h:i:s"),
                        'status'      => 'running',
                    ];
                    $command->updateVodaPayCronSchedule($data);
                    if ($this->cronTask()) {
                        $data = [
                            'id'          => $crondata['id'],
                            'finished_at' => date("Y-m-d h:i:s"),
                            'status'      => 'complete',
                        ];
                        $command->updateVodaPayCronSchedule($data);
                        $command->addVodaPayCronSchedule();
                        $cronLogger->addLog('Successfully run the cron job!.');
                        die;
                    }
                }
            } elseif (!$command->getVodaPayCronSchedule()) {
                $command->addVodaPayCronSchedule();
                $cronLogger->addLog('Successfully add the cron job!.');
                die;
            }
        } else {
            $cronLogger->addLog('Invalid Token!.');
            die;
        }
    }


    /**
     * Cron Task.
     *
     * @return bool|void
     * @throws Exception
     */
    public function cronTask()
    {
        $sql        = new DbQuery();
        $config     = new Config();
        $command    = new Command();
        $cronLogger = new CronLogger();

        $cronLogger->addLog('VODAPAY: Cron started');

        $sql->select('*')
            ->from("vodapay_online_payment")
            ->where(
                'DATE_ADD(created_at, interval 60 MINUTE) < NOW()
                AND status ="' . pSQL($config->getOrderStatus() . '_PENDING') . '"
                AND (id_payment ="" OR id_payment ="null")'
            );
        $vodaPayOrders = \Db::getInstance()->executeS($sql);
        $counter       = 0;

        $cronLogger->addLog('VODAPAY: Found ' . sizeof($vodaPayOrders) . ' unprocessed order(s)');

        foreach ($vodaPayOrders as $vodaPayOrder) {
            if ($counter >= 5) {
                $cronLogger->addLog("VODAPAY: Breaking loop at 5 orders to avoid timeout");
                break;
            }

            $vodaPayOrder['status'] = 'VODAPAY_CRON';
            $command->updateVodaPay($vodaPayOrder);

            try {
                $response = $command->getOrderStatusRequest($vodaPayOrder['reference']);
                $response = json_decode(json_encode($response), true);

                // Check if the response contains an error message and code
                if (isset($response['code']) == 404 || isset($response['errors'])) {
                    throw new Exception("Error " . $response['code'] . ": " . $response['message']);
                }

                if ($response && isset($response['_embedded']['payment']) && is_array(
                        $response['_embedded']['payment']
                    )) {
                    $apiProcessor = new ApiProcessor($response);

                    if ($this->processOrder($apiProcessor, $vodaPayOrder, true)) {
                        $cronLogger->addLog("VODAPAY: State is " . $vodaPayOrder['state']);
                        $cronLogger->addLog(json_encode($this->getVodaPayOrder($vodaPayOrder['reference'])));
                    } else {
                        $cronLogger->addLog('VODAPAY: Failed to process order');
                    }
                } else {
                    $cronLogger->addLog("VODAPAY: Payment result not found");
                }
            } catch (Exception $exception) {
                $cronLogger->addLog("VODAPAY: Exception " . $exception->getMessage());
            }

            $counter++;
        }

        $cronLogger->addLog("VODAPAY: Cron ended");

        return true;
    }
}
