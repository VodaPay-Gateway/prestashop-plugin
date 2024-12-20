<?php

/** @noinspection PhpUndefinedConstantInspection */
$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "vodapay_online_payment`(
        `nid` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id',
        `id_cart` int(10) unsigned NOT NULL COMMENT 'Cart Id',
        `id_order` varchar(55) NOT NULL COMMENT 'Order Id',
        `outlet_id` varchar(55) NOT NULL COMMENT 'Outlet Id',
        `amount` decimal(12,4) unsigned NOT NULL COMMENT 'Amount',
        `currency` varchar(3) NOT NULL COMMENT 'Currency',
        `reference` varchar(55) NOT NULL COMMENT 'Reference',
        `action` varchar(20) NOT NULL COMMENT 'Action',
        `status` varchar(50) NOT NULL COMMENT 'Status',
        `state` varchar(50) NOT NULL COMMENT 'State',
        `id_payment` varchar(55) NOT NULL COMMENT 'Transaction ID',
        `capture_amt` decimal(12,4) unsigned NOT NULL COMMENT 'Capture Amount',
        `refunded_amt` decimal(12,4) unsigned NOT NULL COMMENT 'Refunded Amount',
        `id_capture` varchar(255) NOT NULL COMMENT 'Capture ID',
        `auth_response` text NULL COMMENT 'Auth Response',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Created On',
        PRIMARY KEY (`nid`),
        UNIQUE KEY `CART_ID_ORDER_ID` (`id_cart`,`id_order`)
    )ENGINE=`" . _MYSQL_ENGINE_ . "` CHARSET=utf8";


/** @noinspection PhpUndefinedConstantInspection */
$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "vodapay_order_email_content`(
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `id_order` int(11) NOT NULL,
        `data` text NOT NULL,
        `email_send` int(11) DEFAULT NULL,
        `sent_at` timestamp NULL DEFAULT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=`" . _MYSQL_ENGINE_ . "` CHARSET=utf8";

/** @noinspection PhpUndefinedConstantInspection */
$sql[] = "CREATE TABLE IF NOT EXISTS `" . _DB_PREFIX_ . "vodapay_cron_schedule`(
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `status` varchar(50) NOT NULL DEFAULT 'pending' COMMENT 'Status',
        `created_at` timestamp NULL DEFAULT NULL COMMENT 'Created At',
        `scheduled_at` timestamp NULL DEFAULT NULL COMMENT 'Scheduled At',
        `executed_at` timestamp NULL DEFAULT NULL COMMENT 'Executed At',
        `finished_at` timestamp NULL DEFAULT NULL COMMENT 'Finished At',
        PRIMARY KEY (`id`)
    ) ENGINE=`" . _MYSQL_ENGINE_ . "` CHARSET=utf8";

foreach ($sql as $query) {
    if (!Db::getInstance()->execute($query)) {
        return false;
    }
}
