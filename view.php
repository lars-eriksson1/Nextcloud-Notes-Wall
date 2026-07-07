<?php

require_once 'config.php';
require_once 'includes/api.php';

$id = $_GET['id'] ?? '';

if (!$id) {
    http_response_code(400);
    exit;
}

$note = getNoteById($id);

if (!$note) {
    http_response_code(404);
    exit;
}

header('Content-Type: application/json');

echo json_encode($note);