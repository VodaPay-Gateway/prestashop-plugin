<?php

namespace VodaPay;

use VodaPay\Config\Config;
use Ngenius\NgeniusCommon\Formatter\ValueFormatter;

class Model
{
    const ID_ORDER_LITERAL = 'id_order ="';

    /**
     * Place VodaPay Order
     *
     * @param array $data
     *
     * @return bool
     */
    public function placeVodaPayOrder($data)
    {
        $currencyCode = $data['currency'];

        $amount = $data['amount'];

        $insertData = array(
            'id_cart'      => (int)$data['id_cart'],
            'id_order'     => (int)$data['id_order'],
            'amount'       => ValueFormatter::intToFloatRepresentation($currencyCode, $amount),
            'currency'     => pSQL($currencyCode),
            'reference'    => pSQL($data['reference']),
            'action'       => pSQL($data['action']),
            'status'       => pSQL($data['status']),
            'state'        => pSQL($data['state']),
            'outlet_id'    => pSQL($data['outlet_id']),
            'id_payment'   => null,
            'capture_amt'  => null,
            'refunded_amt' => null,
        );

        if (self::getVodaPayOrderByCartId($insertData['id_cart'])) {
            return self::updateVodaPayOrderByCartId($insertData);
        }

        return (\Db::getInstance()->insert("vodapay_online_payment", $insertData))
            ? (bool)true : (bool)false;
    }

    /**
     * Gets VodaPay Order
     *
     * @param int $orderId
     *
     * @return bool|array
     */
    public static function getVodaPayOrder($orderId)
    {
        $sql = new \DbQuery();
        $sql->select('*')
            ->from("vodapay_online_payment")
            ->where(self::ID_ORDER_LITERAL . pSQL($orderId) . '"');

        return \Db::getInstance()->getRow($sql);
    }

    /**
     * Gets VodaPay Order
     *
     * @param $cartId
     *
     * @return array|bool
     */
    public static function getVodaPayOrderByCartId($cartId): array|bool
    {
        $sql = new \DbQuery();
        $sql->select('*')
            ->from("vodapay_online_payment")
            ->where('id_cart ="' . pSQL($cartId) . '"');

        return \Db::getInstance()->getRow($sql);
    }

    /**
     * Updates VodaPay Order
     *
     * @param $cartId
     *
     * @return array|bool
     */
    public static function updateVodaPayOrderByCartId($data): bool
    {
        return \Db::getInstance()->update(
            'vodapay_online_payment',
            $data,
            'id_cart = "' . pSQL($data['id_cart']) . '"'
        );
    }

    /**
     * Deletes vodapay order by reference
     *
     * @param string $reference
     *
     * @return void
     */
    public static function deleteVodaPayOrder(int $cartId): void
    {
        $tableName = 'vodapay_online_payment';

        $db = \Db::getInstance();

        $sql = "DELETE FROM `" . _DB_PREFIX_ . "$tableName` WHERE `id_cart` = '" . pSQL($cartId) . "'";

        $db->execute($sql);
    }

    /**
     * Gets VodaPay Order
     *
     * @param int $orderId
     *
     * @return bool
     */
    public static function getVodaPayOrderReference($orderRef)
    {
        $sql = new \DbQuery();
        $sql->select('*')
            ->from("vodapay_online_payment")
            ->where('reference ="' . pSQL($orderRef) . '"');

        return \Db::getInstance()->getRow($sql);
    }

    /**
     * Update VodaPay order table
     *
     * @param array $data
     *
     * @return bool
     */
    public static function updateVodaPay($data)
    {
        \Db::getInstance()->update(
            'vodapay_online_payment',
            $data,
            'reference = "' . pSQL($data['reference']) . '"'
        );
    }

    /**
     * Gets Customer Thread
     *
     * @param array $order
     *
     * @return array|bool
     */
    public static function getCustomerThread($order)
    {
        $sql = new \DbQuery();
        $sql->select('*')->from("customer_thread")->where(self::ID_ORDER_LITERAL . (int)$order->id . '"');
        if ($thread = \Db::getInstance()->getRow($sql)) {
            return $thread;
        } else {
            return false;
        }
    }

    /**
     * set VodaPay Order Email Content
     *
     * @param array $data
     *
     * @return bool
     */
    public function addVodaPayOrderEmailContent($data)
    {
        return (\Db::getInstance()->insert("vodapay_order_email_content", $data)) ? (bool)true : (bool)false;
    }

    /**
     * Gets VodaPay Order Email Content
     *
     * @param int $customerId
     * @param int $savedCardId
     *
     * @return bool
     */
    public function getVodaPayOrderEmailContent($idOrder)
    {
        $sql = new \DbQuery();
        $sql->select('*')
            ->from("vodapay_order_email_content")
            ->where(self::ID_ORDER_LITERAL . pSQL($idOrder) . '"');

        return \Db::getInstance()->getRow($sql);
    }

    /**
     * Update VodaPay Order Email Content
     *
     * @param array $data
     *
     * @return bool
     */
    public static function updateVodaPayOrderEmailContent($data)
    {
        return \Db::getInstance()->update(
            'vodapay_order_email_content',
            $data,
            'id_order = "' . pSQL($data['id_order']) . '"'
        );
    }

    /**
     * Set VodaPay cron schedule
     *
     * @return bool
     */
    public function addVodaPayCronSchedule()
    {
        $seconds      = \Configuration::get('VODAPAY_CRON_SCHEDULE');
        $created_at   = date("Y-m-d h:i:s");
        $scheduled_at = date("Y-m-d H:i:00", (strtotime(date($created_at)) + $seconds));
        $data         = [
            'created_at'   => $created_at,
            'scheduled_at' => $scheduled_at,
        ];

        return (\Db::getInstance()->insert("vodapay_cron_schedule", $data)) ? (bool)true : (bool)false;
    }

    /**
     * Gets VodaPay cron schedule
     *
     * @return bool
     */
    public function getVodaPayCronSchedule()
    {
        $sql = new \DbQuery();
        $sql->select('*')
            ->from("vodapay_cron_schedule")
            ->where('status ="' . pSQL('pending') . '"');
        if ($result = \Db::getInstance()->getRow($sql)) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Update VodaPay cron schedule
     *
     * @param array $data
     *
     * @return bool
     */
    public static function updateVodaPayCronSchedule($data)
    {
        return \Db::getInstance()->update(
            'vodapay_cron_schedule',
            $data,
            'id = "' . pSQL($data['id']) . '"'
        );
    }

    /**
     * Gets VodaPay cron schedule
     *
     * @return bool
     */
    public function validateVodaPayCronSchedule()
    {
        $sql = new \DbQuery();
        $sql->select('*')
            ->from("vodapay_cron_schedule")
            ->where('status ="' . pSQL('pending') . '" AND scheduled_at <= "' . date("Y-m-d h:i:s") . '"');
        if ($result = \Db::getInstance()->executeS($sql)) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * Gets Authorization Transaction
     *
     * @param array $vodaPayOrder
     *
     * @return array|bool
     */
    public static function getAuthorizationTransaction($vodaPayOrder)
    {
        if (!empty($vodaPayOrder['id_payment'])
            && !empty($vodaPayOrder['reference'])
            && $vodaPayOrder['state'] == 'AUTHORISED') {
            return $vodaPayOrder;
        } else {
            return false;
        }
    }

    /**
     * Gets Refunded Transaction
     *
     * @param array $vodaPayOrder
     *
     * @return array|bool
     */
    public static function getRefundedTransaction($vodaPayOrder)
    {
        if (isset($vodaPayOrder['id_capture'])
            && !empty($vodaPayOrder['id_capture'])
            && $vodaPayOrder['capture_amt'] > 0
            && $vodaPayOrder['state'] == 'CAPTURED') {
            return $vodaPayOrder;
        } else {
            return false;
        }
    }

    /**
     * Gets Delivery Transaction
     *
     * @param array $vodaPayOrder
     *
     * @return array|bool
     */
    public static function getDeliveryTransaction(array $vodaPayOrder): bool|array
    {
        if (isset($vodaPayOrder['id_payment'])
            && !empty($vodaPayOrder['id_capture'])
            && $vodaPayOrder['capture_amt'] > 0) {
            return $vodaPayOrder;
        } else {
            return false;
        }
    }

    /**
     * Gets Order Details Core
     *
     * @param $idOrderDetail
     *
     * @return bool|array
     */
    public function getOrderDetailsCore($idOrderDetail): bool|array
    {
        $sql = new \DbQuery();
        $sql->select('*')
            ->from("order_detail")
            ->where('id_order_detail ="' . pSQL($idOrderDetail) . '"');

        return \Db::getInstance()->getRow($sql);
    }
}
