<?php
function getDemoPortfolio() {
    return [
        [
            'token' => [
                'id' => 'bitcoin',
                'symbol' => 'btc',
                'name' => 'Bitcoin',
                'current_price' => 45000,
            ],
            'amount' => 0.5,
            'value' => 22500,
        ],
        [
            'token' => [
                'id' => 'ethereum',
                'symbol' => 'eth',
                'name' => 'Ethereum',
                'current_price' => 2500,
            ],
            'amount' => 2,
            'value' => 5000,
        ],
        [
            'token' => [
                'id' => 'usd-coin',
                'symbol' => 'usdc',
                'name' => 'USD Coin',
                'current_price' => 1,
            ],
            'amount' => 1000,
            'value' => 1000,
        ],
    ];
}

function getTokenData($tokenId) {
    $tokens = [
        'bitcoin' => [
            'id' => 'bitcoin',
            'symbol' => 'btc',
            'name' => 'Bitcoin',
            'current_price' => 45000,
        ],
        'ethereum' => [
            'id' => 'ethereum',
            'symbol' => 'eth',
            'name' => 'Ethereum',
            'current_price' => 2500,
        ],
        'usd-coin' => [
            'id' => 'usd-coin',
            'symbol' => 'usdc',
            'name' => 'USD Coin',
            'current_price' => 1,
        ],
    ];
    return $tokens[$tokenId] ?? null;
}

function calculatePortfolioValue($items) {
    $sum = 0;
    foreach ($items as $item) {
        $sum += $item['value'];
    }
    return $sum;
}

function calculateAllocation($items) {
    $total = calculatePortfolioValue($items);
    $allocation = [];
    foreach ($items as $item) {
        $cat = $item['token']['category'] ?? 'OTHER';
        $allocation[$cat] = ($allocation[$cat] ?? 0) + $item['value'];
    }
    foreach ($allocation as $cat => $val) {
        $allocation[$cat] = $total > 0 ? round($val / $total * 100, 2) : 0;
    }
    return $allocation;
}

function getTargetAllocation($btcDominance, $autoMode = true) {
    if (!$autoMode) {
        return [
            'BTC' => 30,
            'ETH_BLUECHIPS' => 30,
            'STABLECOINS' => 20,
            'ALTCOINS' => 20
        ];
    }
    if ($btcDominance > 50) {
        return [
            'BTC' => 50,
            'ETH_BLUECHIPS' => 20,
            'STABLECOINS' => 25,
            'ALTCOINS' => 5
        ];
    } elseif ($btcDominance > 40) {
        return [
            'BTC' => 40,
            'ETH_BLUECHIPS' => 30,
            'STABLECOINS' => 20,
            'ALTCOINS' => 10
        ];
    } else {
        return [
            'BTC' => 30,
            'ETH_BLUECHIPS' => 30,
            'STABLECOINS' => 20,
            'ALTCOINS' => 20
        ];
    }
}

function updatePortfolioPrices($items) {
    // Заглушка: в реальном проекте обновлять цены через API
    return $items;
} 