<?php
require 'vendor/autoload.php';

$fcm = new \App\Services\FcmService();
print_r($fcm->testConnection());
