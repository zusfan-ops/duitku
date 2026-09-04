<?php

/**
 * Production Database Configuration File
 * 
 * Salin file ini menjadi db_config.php di folder yang sama (app/Config/db_config.php)
 * File db_config.php sudah masuk ke .gitignore sehingga TIDAK akan tertimpa saat git pull/deploy.
 */

return [
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'duitku',
    'port'     => 3306,
    'DBDebug'  => false,
];
