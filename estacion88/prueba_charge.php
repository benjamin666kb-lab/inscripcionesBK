<?php

$token = $_GET['token'] ?? '';

if(empty($token)){
    die("Falta token");
}

$data = [
    "amount" => 5500,
    "currency_code" => "PEN",
    "email" => "accept@culqi.com",
    "source_id" => $token
];

$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.culqi.com/v2/charges",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "Authorization: Bearer sk_test_DQgiTt1Zu3hMARAk"
    ]
]);

$response = curl_exec($curl);

curl_close($curl);

echo "<pre>";
print_r(json_decode($response,true));
echo "</pre>";