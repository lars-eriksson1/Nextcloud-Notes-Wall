<?php

require_once 'config.php';
require_once 'includes/api.php';

$id = intval($_GET['id'] ?? 0);
$imageIndex = intval($_GET['image'] ?? 0);

if (!$id) {
    http_response_code(404);
    exit;
}

$note = getNoteById($id);

if (!$note) {
    http_response_code(404);
    exit;
}

$images = extractImages(
    $note['content']
);

if (!isset($images[$imageIndex])) {
    http_response_code(404);
    exit;
}

$imageFile = $images[$imageIndex];

$etag = $note['etag'] ?? 'unknown';

$cacheDir =
    __DIR__
    . '/cache/images';

if (!is_dir($cacheDir)) {
    mkdir(
        $cacheDir,
        0755,
        true
    );
}

$cacheFile =
    $cacheDir
    . '/'
    . $id
    . '_'
    . $imageIndex
    . '_'
    . md5($etag);

/*
 * Cache hit
 */

if (file_exists($cacheFile)) {

    $mimeType =
        mime_content_type(
            $cacheFile
        );

    header(
        'Content-Type: '
        . $mimeType
    );

    header(
        'Cache-Control: public, max-age=31536000'
    );

    readfile($cacheFile);
    exit;
}

/*
 * Remove old cached versions
 */

foreach (
    glob(
        $cacheDir
        . '/'
        . $id
        . '_'
        . $imageIndex
        . '_*'
    ) as $oldFile
) {
    @unlink($oldFile);
}

/*
 * Build WebDAV URL
 */

$noteFolder =
    dirname(
        $note['internalPath']
    );

$webdavPath =
    $noteFolder
    . '/'
    . $imageFile;

$url =
    $config['server']
    . '/remote.php/dav/files/'
    . $config['username']
    . $webdavPath;

/*
 * Download image
 */

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

$imageData =
    curl_exec($ch);

$contentType =
    curl_getinfo(
        $ch,
        CURLINFO_CONTENT_TYPE
    );

$httpCode =
    curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );

curl_close($ch);

if (
    $httpCode !== 200
    || empty($imageData)
) {
    http_response_code(404);
    exit;
}

/*
 * Save cache
 */

file_put_contents(
    $cacheFile,
    $imageData
);

/*
 * Return image
 */

header(
    'Content-Type: '
    . $contentType
);

header(
    'Cache-Control: public, max-age=31536000'
);

echo $imageData;