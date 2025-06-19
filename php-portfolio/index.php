<?php
session_start();
require_once __DIR__ . '/utils/market_data.php';
require_once __DIR__ . '/utils/portfolio.php';
require_once __DIR__ . '/utils/portfolio_levels.php';
require_once __DIR__ . '/utils/date_utils.php';
require_once __DIR__ . '/utils/limits.php';
require_once __DIR__ . '/utils/limit_profiles.php';

function detectCategory($tokenId, $symbol) {
    $id = strtolower($tokenId);
    $symbol = strtoupper($symbol);
    if ($id === 'bitcoin' || $symbol === 'BTC') return 'BTC';
    if ($id === 'ethereum' || $symbol === 'ETH' || in_array($id, ['bnb','cardano','solana','polkadot'])) return 'ETH & Blue Chips';
    if (in_array($id, ['usd-coin','tether','dai','busd']) || in_array($symbol, ['USDC','USDT','DAI','BUSD'])) return 'Stablecoins';
    return 'DeFi & Altcoins';
}

function categoryBadge($cat) {
    $map = [
        'BTC' => ['bg' => 'bg-warning', 'color' => '#f7931a', 'icon' => 'bi-currency-bitcoin'],
        'ETH & Blue Chips' => ['bg' => 'bg-primary', 'color' => '#627eea', 'icon' => 'bi-gem'],
        'Stablecoins' => ['bg' => 'bg-success', 'color' => '#00d4aa', 'icon' => 'bi-cash-coin'],
        'DeFi & Altcoins' => ['bg' => 'bg-purple', 'color' => '#a259ff', 'icon' => 'bi-diagram-3']
    ];
    $m = $map[$cat] ?? ['bg'=>'bg-secondary','color'=>'#888','icon'=>'bi-question-circle'];
    return '<span class="badge px-2 py-1" style="background:'.$m['color'].';color:#fff;font-size:0.95em;"><i class="bi '.$m['icon'].' me-1"></i>'.$cat.'</span>';
}

if (!isset($_SESSION['portfolio'])) {
    $_SESSION['portfolio'] = getDemoPortfolio();
}
if (!isset($_SESSION['portfolio_history'])) {
    $_SESSION['portfolio_history'] = [];
}
// Удаление токена
if (isset($_POST['delete_token'])) {
    $idx = (int)$_POST['delete_token'];
    if (isset($_SESSION['portfolio'][$idx])) {
        array_splice($_SESSION['portfolio'], $idx, 1);
        $_SESSION['portfolio_history'][] = [
            'time' => date('Y-m-d H:i:s'),
            'action' => 'Удаление токена',
            'portfolio' => $_SESSION['portfolio']
        ];
    }
}
// Редактирование токена
if (isset($_POST['edit_token'], $_POST['edit_amount'])) {
    $idx = (int)$_POST['edit_token'];
    $amount = (float)$_POST['edit_amount'];
    if (isset($_SESSION['portfolio'][$idx]) && $amount > 0) {
        $_SESSION['portfolio'][$idx]['amount'] = $amount;
        $_SESSION['portfolio'][$idx]['value'] = $amount * $_SESSION['portfolio'][$idx]['token']['current_price'];
        $_SESSION['portfolio_history'][] = [
            'time' => date('Y-m-d H:i:s'),
            'action' => 'Редактирование токена',
            'portfolio' => $_SESSION['portfolio']
        ];
    }
}
// Добавление токена
$addError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_token'])) {
    $tokenId = trim($_POST['token_id']);
    $amount = (float)$_POST['amount'];
    $buyPrice = isset($_POST['buy_price']) ? (float)$_POST['buy_price'] : 0;
    $category = isset($_POST['category']) ? $_POST['category'] : '';
    $txLink = trim($_POST['tx_link'] ?? '');
    if (!$tokenId || $amount <= 0 || $buyPrice <= 0) {
        $addError = 'Проверьте корректность всех полей.';
    } else {
        $currentPrice = fetchTokenPrice($tokenId);
        if ($currentPrice <= 0) {
            $addError = 'Не удалось получить цену токена.';
        } else {
            $roi = $buyPrice > 0 ? round(($currentPrice - $buyPrice) / $buyPrice * 100, 2) : 0;
            $cat = $category ?: detectCategory($tokenId, $tokenId);
            $_SESSION['portfolio'][] = [
                'token' => [
                    'id' => $tokenId,
                    'symbol' => $tokenId,
                    'name' => ucfirst($tokenId),
                    'current_price' => $currentPrice,
                    'category' => $cat,
                ],
                'amount' => $amount,
                'buy_price' => $buyPrice,
                'value' => $amount * $currentPrice,
                'roi' => $roi,
                'tx_link' => $txLink,
            ];
            $_SESSION['portfolio_history'][] = [
                'time' => date('Y-m-d H:i:s'),
                'action' => 'Добавление токена',
                'portfolio' => $_SESSION['portfolio']
            ];
        }
    }
}
$marketData = getMarketData();
$portfolio = $_SESSION['portfolio'];
$totalValue = calculatePortfolioValue($portfolio);
$allocation = calculateAllocation($portfolio);
$level = getPortfolioLevel($totalValue);
$levelProgress = getLevelProgress($totalValue);
$levelBenefits = getLevelBenefits($level);
// KPI: средний ROI, прибыльные/убыточные, топ-3
$roiList = array_column($portfolio, 'roi');
$avgRoi = count($roiList) ? round(array_sum($roiList)/count($roiList),2) : 0;
$profitable = count(array_filter($roiList, fn($r) => $r > 0));
$loss = count(array_filter($roiList, fn($r) => $r < 0));
$top = $portfolio;
usort($top, fn($a,$b) => $b['roi'] <=> $a['roi']);
$top3 = array_slice($top,0,3);
$bottom3 = array_slice(array_reverse($top),0,3);
?>
<?php require_once 'menu.php'; ?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Crypto Portfolio Manager</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/style.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <style>
    .edit-form { display: flex; gap: 8px; align-items: center; }
    .roi-pos { color: #1e7e34; font-weight: bold; }
    .roi-neg { color: #d90429; font-weight: bold; }
  </style>
</head>
<body class="bg-light">
  <div class="container py-4">
    <h1 class="mb-4">Crypto Portfolio Manager</h1>
    <div class="row mb-4">
      <div class="col-md-4">
        <div class="kpi-block">
          <i class="bi bi-currency-dollar kpi-icon"></i>
          <div>
            <div class="kpi-value">$<?= formatNumberWithCommas($totalValue) ?></div>
            <div class="kpi-label">Общая стоимость</div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="kpi-block">
          <i class="bi bi-bar-chart-line kpi-icon"></i>
          <div>
            <div class="kpi-value"><?= $avgRoi ?>%</div>
            <div class="kpi-label">Средний ROI</div>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="kpi-block">
          <i class="bi bi-graph-up-arrow kpi-icon"></i>
          <div>
            <div class="kpi-value text-success">+<?= $profitable ?></div>
            <div class="kpi-label">Прибыльных</div>
            <div class="kpi-value text-danger">-<?= $loss ?></div>
            <div class="kpi-label">Убыточных</div>
          </div>
        </div>
      </div>
    </div>
    <div class="row mb-4">
      <div class="col-12">
        <div class="card mb-3">
          <div class="card-header">График ROI по токенам</div>
          <div class="card-body">
            <canvas id="roiChart" height="80"></canvas>
          </div>
        </div>
      </div>
    </div>
    <div class="row mb-4">
      <div class="col-md-6">
        <div class="card mb-3">
          <div class="card-header">Топ-3 ROI</div>
          <div class="card-body">
            <ul class="list-group">
              <?php foreach ($top3 as $item): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <span><?= htmlspecialchars($item['token']['name']) ?></span>
                  <span class="roi-pos"><?= $item['roi'] ?>%</span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card mb-3">
          <div class="card-header">Антилидеры ROI</div>
          <div class="card-body">
            <ul class="list-group">
              <?php foreach ($bottom3 as $item): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <span><?= htmlspecialchars($item['token']['name']) ?></span>
                  <span class="roi-neg"><?= $item['roi'] ?>%</span>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
    </div>
    <?php if ($addError): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($addError) ?></div>
    <?php endif; ?>
    <form method="post" class="row g-3 mb-4 align-items-end">
      <input type="hidden" name="add_token" value="1">
      <div class="col-md-2">
        <label class="form-label">CoinGecko ID</label>
        <input type="text" name="token_id" class="form-control" placeholder="bitcoin" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">Количество</label>
        <input type="number" name="amount" class="form-control" min="0.0001" step="any" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">Цена покупки</label>
        <input type="number" name="buy_price" class="form-control" min="0.0001" step="any" required>
      </div>
      <div class="col-md-2">
        <label class="form-label">Категория</label>
        <select name="category" class="form-select">
          <option value="">Авто</option>
          <option value="BTC">BTC</option>
          <option value="ETH & Blue Chips">ETH & Blue Chips</option>
          <option value="Stablecoins">Stablecoins</option>
          <option value="DeFi & Altcoins">DeFi & Altcoins</option>
        </select>
      </div>
      <div class="col-md-2">
        <label class="form-label">Ссылка на транзакцию</label>
        <input type="url" name="tx_link" class="form-control" placeholder="https://...">
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-success w-100">Добавить токен</button>
      </div>
    </form>
    <div class="info-tip mb-4">
      <i class="bi bi-info-circle me-2"></i>
      <?= $marketData['marketTrend'] === 'BULL' ? 'Рынок в бычьем тренде — рассмотрите увеличение доли BTC и ETH.' : ($marketData['marketTrend'] === 'BEAR' ? 'Медвежий рынок — увеличьте долю стейблкоинов.' : 'Боковой рынок — сбалансируйте портфель.') ?>
    </div>
    <div class="mb-4">
      <div class="card">
        <div class="card-header">Ваш уровень портфеля: <strong><?= htmlspecialchars($level['name']) ?></strong></div>
        <div class="card-body">
          <div>Текущий уровень: <strong><?= htmlspecialchars($level['level']) ?></strong></div>
          <div>Прогресс до следующего уровня:</div>
          <div class="progress mb-2" style="max-width:300px;">
            <div class="progress-bar" role="progressbar" style="width: <?= round($levelProgress['progress'], 2) ?>%;" aria-valuenow="<?= round($levelProgress['progress'], 2) ?>" aria-valuemin="0" aria-valuemax="100"><?= round($levelProgress['progress'], 2) ?>%</div>
          </div>
          <ul>
            <?php foreach ($levelBenefits as $benefit): ?>
              <li><?= htmlspecialchars($benefit) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
    <div class="row mb-4">
      <div class="col-md-6">
        <div class="card mb-3">
          <div class="card-header">Распределение по категориям</div>
          <div class="card-body">
            <?php foreach ($allocation as $cat => $perc): ?>
              <div class="mb-2">
                <div class="d-flex justify-content-between">
                  <span><i class="bi bi-circle-fill me-1" style="color:#1a4e8a;"></i> <?= htmlspecialchars($cat) ?></span>
                  <span><?= $perc ?>%</span>
                </div>
                <div class="progress" style="height:0.8rem;">
                  <div class="progress-bar" role="progressbar" style="width: <?= $perc ?>%;" aria-valuenow="<?= $perc ?>" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="card mb-3">
          <div class="card-header">Рыночные данные</div>
          <div class="card-body">
            <p><strong>Доминирование BTC:</strong> <span id="btc-dominance"><?= htmlspecialchars($marketData['btcDominance']) ?>%</span></p>
            <p><strong>Общая капитализация:</strong> <span id="total-marketcap">$<?= formatNumberWithCommas($marketData['totalMarketCap']) ?></span></p>
            <p><strong>Тренд рынка:</strong> <span id="market-trend"><?= htmlspecialchars($marketData['marketTrend']) ?></span></p>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card mb-3">
          <div class="card-header">Ваше портфолио</div>
          <div class="card-body">
            <ul class="list-group" id="portfolio-list">
              <?php foreach ($portfolio as $i => $item): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                  <span><?= htmlspecialchars($item['token']['name']) ?> <?= categoryBadge($item['token']['category']) ?></span>
                  <span><?= htmlspecialchars($item['amount']) ?> × $<?= htmlspecialchars($item['token']['current_price']) ?> = <strong>$<?= htmlspecialchars($item['value']) ?></strong></span>
                  <form method="post" style="display:inline">
                    <input type="hidden" name="delete_token" value="<?= $i ?>">
                    <button type="submit" class="btn btn-danger btn-sm ms-2">Удалить</button>
                  </form>
                  <button class="btn btn-warning btn-sm ms-2" onclick="showEditForm(<?= $i ?>, <?= htmlspecialchars($item['amount']) ?>)">Редактировать</button>
                </li>
                <li class="list-group-item edit-form" id="edit-form-<?= $i ?>" style="display:none;">
                  <form method="post" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="edit_token" value="<?= $i ?>">
                    <input type="number" name="edit_amount" value="<?= htmlspecialchars($item['amount']) ?>" min="0.0001" step="any" class="form-control" style="width:120px;">
                    <button type="submit" class="btn btn-primary btn-sm">Сохранить</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="hideEditForm(<?= $i ?>)">Отмена</button>
                  </form>
                </li>
              <?php endforeach; ?>
            </ul>
            <div class="mt-3">
              <strong>Общая стоимость:</strong> <span id="portfolio-value">$<?= formatNumberWithCommas($totalValue) ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header">График портфолио</div>
          <div class="card-body">
            <canvas id="portfolioChart" height="100"></canvas>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <script src="components/portfolioChart.js"></script>
  <script src="components/portfolioUpdater.js"></script>
  <script>
    function showEditForm(idx, amount) {
      document.getElementById('edit-form-' + idx).style.display = 'flex';
    }
    function hideEditForm(idx) {
      document.getElementById('edit-form-' + idx).style.display = 'none';
    }
  </script>
</body>
</html> 