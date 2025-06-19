<?php
session_start();
require_once __DIR__ . '/utils/limits.php';
require_once __DIR__ . '/utils/portfolio.php';
require_once __DIR__ . '/utils/portfolio_levels.php';
require_once __DIR__ . '/utils/date_utils.php';
require_once __DIR__ . '/utils/limit_profiles.php';
if (!isset($_SESSION['limits'])) {
    $_SESSION['limits'] = getCategoryLimits();
}
if (!isset($_SESSION['portfolio'])) {
    $_SESSION['portfolio'] = getDemoPortfolio();
}
if (!isset($_SESSION['auto_mode'])) {
    $_SESSION['auto_mode'] = true;
}
if (!isset($_SESSION['selected_profile'])) {
    $_SESSION['selected_profile'] = 'auto';
}
$portfolio = $_SESSION['portfolio'];
$totalValue = calculatePortfolioValue($portfolio);
$level = getPortfolioLevel($totalValue);
$levelBenefits = getLevelBenefits($level);
$marketData = getMarketData();
$btcDominance = $marketData['btcDominance'];
$profiles = getLimitProfiles();
// Определяем профиль по BTC-доминированию
function getProfileByBtcDominance($btcDominance, $profiles) {
    if ($btcDominance > 50) return $profiles[0]; // conservative
    if ($btcDominance > 40) return $profiles[1]; // balanced
    return $profiles[2]; // aggressive
}
// Обработка переключения режима и выбора профиля
if (isset($_POST['auto_mode'])) {
    $_SESSION['auto_mode'] = $_POST['auto_mode'] === '1';
}
if (isset($_POST['profile_id'])) {
    $_SESSION['selected_profile'] = $_POST['profile_id'];
}
// Применяем лимиты из профиля
if ($_SESSION['auto_mode']) {
    $profile = getProfileByBtcDominance($btcDominance, $profiles);
    $_SESSION['selected_profile'] = 'auto';
    $limits = $profile['limits'];
    $profileName = $profile['name'] . ' (Auto)';
    $profileDesc = $profile['description'];
} else {
    $profile = null;
    foreach ($profiles as $p) {
        if ($p['id'] === $_SESSION['selected_profile']) $profile = $p;
    }
    if ($profile) {
        $limits = $profile['limits'];
        $profileName = $profile['name'];
        $profileDesc = $profile['description'];
    } else {
        $limits = $_SESSION['limits'];
        $profileName = 'Пользовательский';
        $profileDesc = '';
    }
}
$allocation = calculateAllocation($portfolio);
$validation = validateLimits($limits);
$violations = checkPortfolioViolations($portfolio, $limits, $allocation);
$recommendations = getRebalanceRecommendations($portfolio, $limits, $allocation, $totalValue);
$status = 'success';
if (!$validation['valid']) $status = 'danger';
elseif (count($violations) > 0) $status = 'warning';
?>
<?php require_once 'menu.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Лимиты по категориям</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/style.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container py-4">
    <h1 class="mb-4">Лимиты по категориям</h1>
    <div class="alert alert-<?= $status ?> mb-4">
      <?php if ($status === 'success'): ?>
        ✅ Лимиты корректны, нарушений нет.
      <?php elseif ($status === 'warning'): ?>
        ⚠️ Проверьте нарушения лимитов:<br>
        <ul class="mb-0">
          <?php foreach ($violations as $v): ?><li><?= htmlspecialchars($v) ?></li><?php endforeach; ?>
        </ul>
      <?php else: ?>
        ❌ Ошибка в лимитах:<br>
        <ul class="mb-0">
          <?php foreach ($validation['errors'] as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
    <?php if (count($recommendations) > 0): ?>
    <div class="alert alert-info mb-4">
      <strong>Рекомендации по ребалансировке:</strong>
      <ul class="mb-0">
        <?php foreach ($recommendations as $r): ?><li><?= htmlspecialchars($r) ?></li><?php endforeach; ?>
      </ul>
    </div>
    <?php endif; ?>
    <form method="post" class="mb-4 d-flex align-items-center gap-3">
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" id="autoModeSwitch" name="auto_mode" value="1" onchange="this.form.submit()" <?= $_SESSION['auto_mode'] ? 'checked' : '' ?>>
        <label class="form-check-label" for="autoModeSwitch">Auto Mode</label>
      </div>
      <?php if (!$_SESSION['auto_mode']): ?>
      <select name="profile_id" class="form-select w-auto" onchange="this.form.submit()">
        <?php foreach ($profiles as $p): ?>
          <option value="<?= $p['id'] ?>" <?= $_SESSION['selected_profile'] === $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['name']) ?></option>
        <?php endforeach; ?>
        <option value="custom" <?= $_SESSION['selected_profile'] === 'custom' ? 'selected' : '' ?>>Пользовательский</option>
      </select>
      <?php endif; ?>
    </form>
    <div class="mb-4">
      <div class="card">
        <div class="card-header">Профиль лимитов: <strong><?= htmlspecialchars($profileName) ?></strong></div>
        <div class="card-body">
          <div><?= htmlspecialchars($profileDesc) ?></div>
        </div>
      </div>
    </div>
    <form method="post">
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>Категория</th>
            <th>Мин. %</th>
            <th>Макс. %</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($limits as $i => $limit): ?>
          <tr>
            <td><?= htmlspecialchars($limit['name']) ?></td>
            <td><input type="number" name="min[<?= $i ?>]" value="<?= htmlspecialchars($limit['min']) ?>" class="form-control" min="0" max="100" <?= $_SESSION['auto_mode'] ? 'readonly' : '' ?>></td>
            <td><input type="number" name="max[<?= $i ?>]" value="<?= htmlspecialchars($limit['max']) ?>" class="form-control" min="0" max="100" <?= $_SESSION['auto_mode'] ? 'readonly' : '' ?>></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php if (!$_SESSION['auto_mode']): ?>
      <button type="submit" class="btn btn-primary">Сохранить изменения</button>
      <?php endif; ?>
    </form>
  </div>
</body>
</html> 