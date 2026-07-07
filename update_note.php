<?php

require_once 'config.php';

$id =
    $_POST['id'] ?? '';

$title =
    $_POST['title'] ?? '';

$content =
    $_POST['content'] ?? '';

$category =
    $_POST['category'] ?? '';

$url =
    $config['notes_api_url']
    . '/'
    . urlencode($id);

$data = [
    'title'    => $title,
    'content'  => $content,
    'category' => $category
];

$ch = curl_init($url);

curl_setopt(
    $ch,
    CURLOPT_CUSTOMREQUEST,
    'PUT'
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

curl_setopt(
    $ch,
    CURLOPT_HTTPHEADER,
    [
        'Content-Type: application/json'
    ]
);

curl_setopt(
    $ch,
    CURLOPT_POSTFIELDS,
    json_encode($data)
);

$response =
    curl_exec($ch);

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