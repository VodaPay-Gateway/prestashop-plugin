<?php

/** @noinspection PhpUndefinedConstantInspection */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_0_1($module)
{
    /** @noinspection PhpUndefinedConstantInspection */
    $queries = array(
        "ALTER TABLE `" . _DB_PREFIX_ . "vodapay_online_payment`
            `refunded_amt` int(10) DEFAULT NULL AFTER `capture_amt`"
    );
    $db      = Db::getInstance();
    $success = true;
    foreach ($queries as $query) {
        $success &= $db->execute($query);
    }
    $module->registerHook('displayHeader');
    $module->registerHook('actionFrontControllerSetMedia');

    return true;
}
