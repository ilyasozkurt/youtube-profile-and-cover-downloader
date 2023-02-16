<?php

ini_set('display_errors', 1);

include 'vendor/autoload.php';
include 'library/simple_html_dom.php';

use GuzzleHttp\Client;

$client = new Client([
    'base_uri' => 'https://www.youtube.com/',
    'timeout' => 2.0,
]);

$downloadURL = null;
$requestData = json_decode(file_get_contents('php://input'), true);
$channelURL = $requestData['channel_url'];
$downloadType = $requestData['download_type'];

if (!in_array($downloadType, ['cover', 'profile'])) {
    print json_encode(['error' => 'Invalid download type']);
}

$response = $client->request('GET', $channelURL);
$responseBody = $response->getBody();
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