<?php
function getLimitProfiles() {
    return [
        [
            'id' => 'conservative',
            'name' => 'Консервативный',
            'condition' => 'BTC доминирование > 50%',
            'description' => 'Фокус на безопасности и стабильности',
            'limits' => [
                ['name' => 'BTC', 'min' => 30, 'max' => 40],
                ['name' => 'ETH & Blue Chips', 'min' => 20, 'max' => 30],
                ['name' => 'Stablecoins', 'min' => 25, 'max' => 35],
                ['name' => 'DeFi & Altcoins', 'min' => 5, 'max' => 15],
            ]
        ],
        [
            'id' => 'balanced',
            'name' => 'Сбалансированный',
            'condition' => 'BTC доминирование 40-50%',
            'description' => 'Умеренный риск и сбалансированное распределение',
            'limits' => [
                ['name' => 'BTC', 'min' => 25, 'max' => 35],
                ['name' => 'ETH & Blue Chips', 'min' => 20, 'max' => 30],
                ['name' => 'Stablecoins', 'min' => 20, 'max' => 30],
                ['name' => 'DeFi & Altcoins', 'min' => 15, 'max' => 25],
            ]
        ],
        [
            'id' => 'aggressive',
            'name' => 'Агрессивный',
            'condition' => 'BTC доминирование < 40%',
            'description' => 'Высокий риск/доходность',
            'limits' => [
                ['name' => 'BTC', 'min' => 20, 'max' => 30],
                ['name' => 'ETH & Blue Chips', 'min' => 20, 'max' => 30],
                ['name' => 'Stablecoins', 'min' => 15, 'max' => 25],
                ['name' => 'DeFi & Altcoins', 'min' => 25, 'max' => 35],
            ]
        ],
        [
            'id' => 'defi',
            'name' => 'DeFi фокус',
            'condition' => 'Любое BTC доминирование',
            'description' => 'Фокус на децентрализованных финансах',
            'limits' => [
                ['name' => 'BTC', 'min' => 15, 'max' => 25],
                ['name' => 'ETH & Blue Chips', 'min' => 25, 'max' => 35],
                ['name' => 'Stablecoins', 'min' => 10, 'max' => 20],
                ['name' => 'DeFi & Altcoins', 'min' => 30, 'max' => 40],
            ]
        ],
        [
            'id' => 'stable',
            'name' => 'Стабильность',
            'condition' => 'Любое BTC доминирование',
            'description' => 'Фокус на стабильных активах',
            'limits' => [
                ['name' => 'BTC', 'min' => 25, 'max' => 35],
                ['name' => 'ETH & Blue Chips', 'min' => 15, 'max' => 25],
                ['name' => 'Stablecoins', 'min' => 35, 'max' => 45],
                ['name' => 'DeFi & Altcoins', 'min' => 5, 'max' => 15],
            ]
        ],
    ];
} 