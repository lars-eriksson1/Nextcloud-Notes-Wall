<?php

require_once 'config.php';

$id = $_POST['id'] ?? '';

$url =
    $config['notes_api_url']
    . '/'
    . urlencode($id);

$ch = curl_init($url);

curl_setopt(
    $ch,
    CURLOPT_CUSTOMREQUEST,
    'DELETE'
);

curl_setopt(
    $ch,
    CURLOPT_RETURNTRANSFER,
    true
);

curl_setopt(
    $ch,
    CURLOPT_USERPWD,
    $config['username']
    . ':'
    . $config['app_password']
);

$response = curl_exec($ch);

$httpCode =
    curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );

curl_close($ch);

header(
    'Content-Type: application/json'
);

echo json_encode([
    'success' =>
        ($httpCode === 200)
]);