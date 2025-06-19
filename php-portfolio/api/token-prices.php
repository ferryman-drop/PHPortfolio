<?php
header('Content-Type: application/json');
$ids = isset($_GET['ids']) ? $_GET['ids'] : '';
if (!$ids) {
    echo json_encode(['error' => 'No ids provided']);
    exit;
}
$url = 'https://api.coingecko.com/api/v3/simple/price?ids=' . urlencode($ids) . '&vs_currencies=usd';
$json = @file_get_contents($url);
if ($json === false) {
    echo json_encode(['error' => 'Failed to fetch prices']);
    exit;
}
echo $json; 