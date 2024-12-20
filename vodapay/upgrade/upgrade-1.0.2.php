<?php

function upgrade_module_1_0_2($module): bool
{
    /** @noinspection PhpUndefinedConstantInspection */
    $table = _DB_PREFIX_ . "vodapay_online_payment";

    $queries = array(
        "SELECT COUNT(*) INTO @column_exists
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = '$table'
    AND COLUMN_NAME = 'refunded_amt';

    IF @column_exists = 0 THEN
        ALTER TABLE $table
        ADD COLUMN `refunded_amt` INT(10) DEFAULT NULL AFTER `capture_amt`;
    END IF;"
    );

    $db      = Db::getInstance();
    $success = true;
    foreach ($queries as $query) {
        $success &= $db->execute($query);
    }

    $queries = array(
        "ALTER TABLE ps_vodapay_online_payment
    MODIFY COLUMN `refunded_amt` DECIMAL(10,2);"
    );

    $db      = Db::getInstance();
    $success = true;
    foreach ($queries as $query) {
        $success &= $db->execute($query);
    }

    $module->registerHook('displayHeader');
    $module->registerHook('actionFrontControllerSetMedia');

    return true; // Return true if success.
}

