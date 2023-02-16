<?php

include 'vendor/autoload.php';

use GuzzleHttp\Client;
use Sunra\PhpSimple\HtmlDomParser;

$client = new Client([
    'base_uri' => 'https://www.youtube.com/',
    'timeout' => 2.0,
]);

$downloadURL = null;
$channelURL = $_REQUEST['channel_url'];
$downloadType = $_REQUEST['download_type'];

if (!in_array($downloadType, ['cover', 'profile'])) {
    print json_encode(['error' => 'Invalid download type']);
}

$response = $client->request('GET', $channelURL);
$responseBody = $response->getBody();
$responseDom = HtmlDomParser::str_get_html($responseBody);

$initialData = preg_match('/ytInitialData = (.*);<\/script>/', $responseBody, $matches);
$initialData = json_decode($matches[1], true);

$channelCover = end($initialData['header']['c4TabbedHeaderRenderer']['tvBanner']['tvBannerRenderer']['banner']['thumbnails'])['url'] ?? null;

$channelLogo = $responseDom->find('link[property="og:image"]', 0)->href ?? null;

if ($downloadType == 'cover') {
    $downloadURL = $channelCover;
} else {
    $downloadURL = $channelLogo;
}

print json_encode(['download_url' => $downloadURL]);