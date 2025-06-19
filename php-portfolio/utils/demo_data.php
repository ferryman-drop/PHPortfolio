<?php
function getDemoData() {
    return [
        [
            'token' => [
                'id' => 'bitcoin',
                'symbol' => 'btc',
                'name' => 'Bitcoin',
                'current_price' => 45000,
                'category' => 'BTC',
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
                'category' => 'ETH_BLUECHIPS',
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
                'category' => 'STABLECOINS',
            ],
            'amount' => 1000,
            'value' => 1000,
        ],
    ];
} 