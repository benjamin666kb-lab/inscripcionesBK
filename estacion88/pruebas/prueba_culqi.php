<?php

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.culqi.com/v2/charges",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer sk_test_DQgiTt1Zu3hMARAk"
    ]
]);

$response = curl_exec($curl);

echo "<pre>";
print_r($response);
echo "</pre>";

curl_close($curl);