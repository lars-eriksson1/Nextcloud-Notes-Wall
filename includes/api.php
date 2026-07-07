<?php

require_once __DIR__ . '/../config.php';

function getNotes()
{
    global $config;

    $ch = curl_init(
        $config['notes_api_url']
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

    $httpCode = curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );

    curl_close($ch);

    if ($httpCode !== 200) {
        return [];
    }

    $notes = json_decode(
        $response,
        true
    );

    return is_array($notes)
        ? $notes
        : [];
}

function getNoteById($id)
{
    global $config;

    $url =
        $config['notes_api_url']
        . '/'
        . urlencode($id);

    $ch = curl_init($url);

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

    $httpCode = curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );

    curl_close($ch);

    if ($httpCode !== 200) {
        return null;
    }

    return json_decode(
        $response,
        true
    );
}



function extractFirstImage($content)
{
    if (
        preg_match(
            '/!\[.*?\]\((.*?)\)/',
            $content,
            $matches
        )
    ) {
        return $matches[1];
    }

    return null;
}

function extractImages($content)
{
    preg_match_all(
        '/!\[.*?\]\((.*?)\)/',
        $content,
        $matches
    );

    return $matches[1] ?? [];
}
