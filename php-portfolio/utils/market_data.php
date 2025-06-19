<?php
function getMarketData() {
    $url = 'https://api.coingecko.com/api/v3/global';
    $json = @file_get_contents($url);
    if ($json === false) {
        // Fallback данные
        return [
            'btcDominance' => 52.5,
            'totalMarketCap' => 2500000000000,
            'marketTrend' => 'SIDEWAYS',
        ];
    }
    $data = json_decode($json, true);
    $btcDominance = $data['data']['market_cap_percentage']['btc'] ?? 52.5;
    $totalMarketCap = $data['data']['total_market_cap']['usd'] ?? 2500000000000;
    $change24h = $data['data']['market_cap_change_percentage_24h_usd'] ?? 0;
    $marketTrend = 'SIDEWAYS';
    if ($change24h > 1) $marketTrend = 'BULL';
    if ($change24h < -1) $marketTrend = 'BEAR';
    return [
        'btcDominance' => round($btcDominance, 1),
        'totalMarketCap' => $totalMarketCap,
        'marketTrend' => $marketTrend,
    ];
}

function fetchTokenPrice($tokenId) {
    $url = 'https://api.coingecko.com/api/v3/simple/price?ids=' . urlencode($tokenId) . '&vs_currencies=usd';
    $json = @file_get_contents($url);
    if ($json === false) return 0;
    $data = json_decode($json, true);
    return $data[$tokenId]['usd'] ?? 0;
} 