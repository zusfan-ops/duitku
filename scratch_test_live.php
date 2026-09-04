<?php
require 'vendor/autoload.php';

$fcm = new \App\Services\FcmService();

$deviceToken = 'cNjLxX_YRJWUWOUKUFoRKU:APA91bGpfC50R8TvccVtiFevTvoKKdL1sNibOg-A6-543WbE4WKpgJXofxNydC_wmgek5dkBWyD8QwRlajH-84jn5c6HrfBDZF1pXsG-UDAw-c5-I6_IpJc';

echo "1. Sending direct push to BlueStacks device token...\n";
$resToken = $fcm->sendToToken($deviceToken, '🔔 DuitKu Push Test', 'Halo! Ini adalah notifikasi langsung dari Firebase ke BlueStacks.', [
    'action_url' => 'https://duitku.ordr.my.id',
]);
print_r($resToken);

echo "\n2. Sending broadcast push to topic 'duitku_broadcasts'...\n";
$resTopic = $fcm->sendToTopic('duitku_broadcasts', '📢 DuitKu Broadcast Test', 'Halo! Ini adalah notifikasi broadcast ke seluruh pengguna aplikasi DuitKu.', [
    'action_url' => 'https://duitku.ordr.my.id',
]);
print_r($resTopic);
