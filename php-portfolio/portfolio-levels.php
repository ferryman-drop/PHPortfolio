<?php
session_start();
require_once __DIR__ . '/utils/portfolio.php';
require_once __DIR__ . '/utils/portfolio_levels.php';
require_once __DIR__ . '/utils/date_utils.php';
$portfolio = isset($_SESSION['portfolio']) ? $_SESSION['portfolio'] : getDemoPortfolio();
$totalValue = calculatePortfolioValue($portfolio);
$level = getPortfolioLevel($totalValue);
$levelProgress = getLevelProgress($totalValue);
$levelBenefits = getLevelBenefits($level);
$levels = getPortfolioLevels();
?>
<?php require_once 'menu.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Уровни портфолио</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/style.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <h1 class="mb-4">Уровни портфолио</h1>
    <div class="mb-4">
      <div class="card">
        <div class="card-header">Ваш текущий уровень: <strong><?= htmlspecialchars($level['name']) ?></strong></div>
        <div class="card-body">
          <div>Текущий уровень: <strong><?= htmlspecialchars($level['level']) ?></strong></div>
          <div>Прогресс до следующего уровня: <strong><?= round($levelProgress['progress'], 2) ?>%</strong></div>
          <ul>
            <?php foreach ($levelBenefits as $benefit): ?>
              <li><?= htmlspecialchars($benefit) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
    <div class="row">
      <?php foreach ($levels as $l): ?>
      <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100">
          <div class="card-header bg-primary text-white">Уровень <?= $l['level'] ?>: <?= htmlspecialchars($l['name']) ?></div>
          <div class="card-body">
            <p><strong>Диапазон:</strong> $<?= formatNumberWithCommas($l['minValue']) ?> — $<?= formatNumberWithCommas($l['maxValue']) ?></p>
            <p><strong>Описание:</strong> <?= htmlspecialchars($l['description']) ?></p>
            <?php if ($l['limitReduction'] > 0): ?>
              <p><strong>Снижение лимитов:</strong> <?= $l['limitReduction'] ?>%</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <a href="index.php" class="btn btn-secondary mt-3">Назад к портфолио</a>
  </div>
</body>
</html> 