<?php

$sql = array();

foreach ($sql as $query) {
    if (!Db::getInstance()->execute($query)) {
        return false;
    }
}
