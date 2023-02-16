<?php

ini_set('display_errors', 1);

include 'vendor/autoload.php';
include 'library/simple_html_dom.php';

use GuzzleHttp\Client;

$client = new Client([
    'timeout' => 2.0,
    'header' => [
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/87.0.4280.88 Safari/537.36',
    ],
]);

$downloadURL = null;
$requestData = json_decode(file_get_contents('php://input'), true);
$channelURL = $requestData['channel_url'];
$downloadType = $requestData['download_type'];

if (!in_array($downloadType, ['cover', 'profile'])) {
    print json_encode(['error' => 'Invalid download type']);
}

$response = $client->request('GET', $channelURL);
$responseBody = $response->getBody()->getContents();
$responseDom = str_get_html($responseBody);

if (!$responseDom) {
    print json_encode(['error' => 'Invalid channel URL']);
}

$initialData = preg_match('/ytInitialData = (.*);<\/script>/', $responseBody, $matches);
$initialData = json_decode($matches[1], true);

$channelCover = end($initialData['header']['c4TabbedHeaderRenderer']['banner']['thumbnails'])['url'] ?? null;

$channelLogo = $responseDom->find('meta[property="og:image"]', 0)->getAttribute('content') ?? null;
if ($downloadType == 'cover') {
    preg_match('/(.*?)\=w/', $channelCover, $matches);
    $fullCoverURL = $matches[1] . '=w4000';
    $downloadURL = $fullCoverURL;
} else {
    $downloadURL = $channelLogo;
}

print json_encode(['download_url' => $downloadURL]);