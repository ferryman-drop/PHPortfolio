<?php
session_start();
require_once __DIR__ . '/utils/portfolio.php';
$portfolio = isset($_SESSION['portfolio']) ? $_SESSION['portfolio'] : getDemoPortfolio();
$totalValue = array_sum(array_column($portfolio, 'value'));
$tokenCount = count($portfolio);
$avgPrice = $totalValue / max($tokenCount, 1);
// Распределение по токенам
$distribution = [];
foreach ($portfolio as $item) {
    $name = $item['token']['name'];
    $distribution[$name] = ($distribution[$name] ?? 0) + $item['value'];
}
$history = isset($_SESSION['portfolio_history']) ? $_SESSION['portfolio_history'] : [];
?>
<?php require_once 'menu.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Аналитика портфолио</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/style.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <h1 class="mb-4">Аналитика портфолио</h1>
    <ul class="list-group mb-4">
      <li class="list-group-item">Общая стоимость портфолио: <strong>$<?= number_format($totalValue, 2, '.', ' ') ?></strong></li>
      <li class="list-group-item">Количество токенов: <strong><?= $tokenCount ?></strong></li>
      <li class="list-group-item">Средняя стоимость позиции: <strong>$<?= number_format($avgPrice, 2, '.', ' ') ?></strong></li>
    </ul>
    <div class="card mb-4">
      <div class="card-header">Распределение по токенам</div>
      <div class="card-body">
        <table class="table table-bordered">
          <thead><tr><th>Токен</th><th>Доля</th><th>Стоимость</th></tr></thead>
          <tbody>
            <?php foreach ($distribution as $name => $value): ?>
              <tr>
                <td><?= htmlspecialchars($name) ?></td>
                <td><?= $totalValue > 0 ? round($value / $totalValue * 100, 2) : 0 ?>%</td>
                <td>$<?= number_format($value, 2, '.', ' ') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="card mb-4">
      <div class="card-header">История изменений портфеля</div>
      <div class="card-body">
        <?php if (count($history) === 0): ?>
          <div class="text-muted">История пуста</div>
        <?php else: ?>
        <table class="table table-sm table-bordered">
          <thead><tr><th>Время</th><th>Действие</th><th>Распределение</th></tr></thead>
          <tbody>
            <?php foreach (array_reverse($history) as $event): ?>
              <tr>
                <td><?= htmlspecialchars($event['time']) ?></td>
                <td><?= htmlspecialchars($event['action']) ?></td>
                <td>
                  <?php
                  $dist = [];
                  $sum = 0;
                  foreach ($event['portfolio'] as $item) {
                    $dist[$item['token']['name']] = ($dist[$item['token']['name']] ?? 0) + $item['value'];
                    $sum += $item['value'];
                  }
                  foreach ($dist as $name => $val) {
                    echo htmlspecialchars($name) . ': ' . ($sum > 0 ? round($val / $sum * 100, 2) : 0) . '%, ';
                  }
                  ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <?php endif; ?>
      </div>
    </div>
    <a href="index.php" class="btn btn-secondary">Назад к портфолио</a>
  </div>
</body>
</html> 