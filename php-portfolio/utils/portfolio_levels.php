<?php
require_once __DIR__ . '/portfolio_levels.php';

function getPortfolioLevels() {
    return [
        [
            'level' => 1,
            'name' => 'Новичок',
            'minValue' => 0,
            'maxValue' => 1000,
            'description' => 'Начальный уровень для новых инвесторов.',
            'limitReduction' => 0,
        ],
        [
            'level' => 2,
            'name' => 'Продвинутый',
            'minValue' => 1000,
            'maxValue' => 10000,
            'description' => 'Больше возможностей и гибкости.',
            'limitReduction' => 10,
        ],
        [
            'level' => 3,
            'name' => 'Эксперт',
            'minValue' => 10000,
            'maxValue' => 50000,
            'description' => 'Для опытных инвесторов.',
            'limitReduction' => 20,
        ],
        [
            'level' => 4,
            'name' => 'Профессионал',
            'minValue' => 50000,
            'maxValue' => 250000,
            'description' => 'Максимальная гибкость и минимальные лимиты.',
            'limitReduction' => 30,
        ],
        [
            'level' => 5,
            'name' => 'Легенда',
            'minValue' => 250000,
            'maxValue' => 1000000000,
            'description' => 'Элита среди инвесторов.',
            'limitReduction' => 40,
        ],
    ];
}

function getPortfolioLevel($totalValue) {
    $levels = getPortfolioLevels();
    foreach ($levels as $level) {
        if ($totalValue >= $level['minValue'] && $totalValue < $level['maxValue']) {
            return $level;
        }
    }
    return $levels[0];
}

function getLevelProgress($totalValue) {
    $current = getPortfolioLevel($totalValue);
    $levels = getPortfolioLevels();
    $next = null;
    foreach ($levels as $level) {
        if ($level['level'] === $current['level'] + 1) {
            $next = $level;
            break;
        }
    }
    if (!$next) {
        return ['current' => $current, 'next' => null, 'progress' => 100];
    }
    $range = $current['maxValue'] - $current['minValue'];
    $progress = min(100, ($totalValue - $current['minValue']) / $range * 100);
    return ['current' => $current, 'next' => $next, 'progress' => $progress];
}

function getLevelBenefits($level) {
    $benefits = [
        "Уровень {$level['level']}: {$level['name']}",
        $level['description']
    ];
    if ($level['limitReduction'] > 0) {
        $benefits[] = "Снижение лимитов: {$level['limitReduction']}%";
    }
    return $benefits;
}

function getAdjustedAllocation($baseAllocation, $level) {
    if ($level['limitReduction'] == 0) return $baseAllocation;
    $mult = (100 - $level['limitReduction']) / 100;
    $adj = [];
    foreach ($baseAllocation as $cat => $perc) {
        $adj[$cat] = $perc * $mult;
    }
    return $adj;
} 