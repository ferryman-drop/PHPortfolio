<?php
function getCategoryLimits() {
    return [
        [
            'name' => 'BTC',
            'minPercentage' => 10,
            'maxPercentage' => 60,
            'enabled' => true,
        ],
        [
            'name' => 'ETH_BLUECHIPS',
            'minPercentage' => 10,
            'maxPercentage' => 40,
            'enabled' => true,
        ],
        [
            'name' => 'STABLECOINS',
            'minPercentage' => 5,
            'maxPercentage' => 50,
            'enabled' => true,
        ],
        [
            'name' => 'ALTCOINS',
            'minPercentage' => 0,
            'maxPercentage' => 30,
            'enabled' => true,
        ],
        [
            'name' => 'DEFI',
            'minPercentage' => 0,
            'maxPercentage' => 20,
            'enabled' => false,
        ],
    ];
}

function applyLevelReductions($baseLimits, $level) {
    if ($level['limitReduction'] == 0) return $baseLimits;
    $mult = (100 - $level['limitReduction']) / 100;
    $result = [];
    foreach ($baseLimits as $limit) {
        if (!$limit['enabled']) {
            $result[] = $limit;
            continue;
        }
        $limit['minPercentage'] = max(0, $limit['minPercentage'] * $mult);
        $limit['maxPercentage'] = min(100, $limit['maxPercentage'] * $mult);
        $result[] = $limit;
    }
    return $result;
}

function validateLimits($limits) {
    $sumMin = 0;
    $sumMax = 0;
    $valid = true;
    $errors = [];
    foreach ($limits as $limit) {
        $min = $limit['min'];
        $max = $limit['max'];
        if ($min < 0 || $max > 100 || $min > $max) {
            $valid = false;
            $errors[] = "Ошибка лимита для {$limit['name']}: 0% ≤ мин ≤ макс ≤ 100%";
        }
        $sumMin += $min;
        $sumMax += $max;
    }
    if ($sumMin > 100) {
        $valid = false;
        $errors[] = "Сумма минимумов превышает 100%";
    }
    if ($sumMax < 100) {
        $valid = false;
        $errors[] = "Сумма максимумов меньше 100%";
    }
    return [
        'valid' => $valid,
        'errors' => $errors,
        'sumMin' => $sumMin,
        'sumMax' => $sumMax
    ];
}

function checkPortfolioViolations($portfolio, $limits, $allocation) {
    $violations = [];
    foreach ($limits as $limit) {
        $cat = $limit['name'];
        $min = $limit['min'];
        $max = $limit['max'];
        $actual = $allocation[$cat] ?? 0;
        if ($actual < $min) {
            $violations[] = "$cat ниже минимума ({$actual}% < {$min}%)";
        }
        if ($actual > $max) {
            $violations[] = "$cat выше максимума ({$actual}% > {$max}%)";
        }
    }
    return $violations;
}

function getRebalanceRecommendations($portfolio, $limits, $allocation, $totalValue) {
    $actions = [];
    foreach ($limits as $limit) {
        $cat = $limit['name'];
        $min = $limit['min'];
        $max = $limit['max'];
        $actual = $allocation[$cat] ?? 0;
        $catValue = $totalValue * ($actual / 100);
        $minValue = $totalValue * ($min / 100);
        $maxValue = $totalValue * ($max / 100);
        if ($actual < $min) {
            $diff = $minValue - $catValue;
            $actions[] = "Купить на $" . number_format($diff, 2, '.', ' ') . " в категории $cat (до минимума)";
        } elseif ($actual > $max) {
            $diff = $catValue - $maxValue;
            $actions[] = "Продать на $" . number_format($diff, 2, '.', ' ') . " в категории $cat (до максимума)";
        }
    }
    return $actions;
} 