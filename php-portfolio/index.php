<?php
session_start();
require_once __DIR__ . '/utils/security.php';
require_once __DIR__ . '/utils/market_data.php';
require_once __DIR__ . '/utils/portfolio.php';
require_once __DIR__ . '/utils/portfolio_levels.php';
require_once __DIR__ . '/utils/date_utils.php';
require_once __DIR__ . '/utils/limits.php';
require_once __DIR__ . '/utils/limit_profiles.php';

// Apply security measures
secureSession();

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
    if (!checkRateLimit('delete_token', 10, 60)) {
        $addError = 'Занадто багато спроб видалення. Спробуйте пізніше.';
    } else {
        $idx = (int)$_POST['delete_token'];
        if (isset($_SESSION['portfolio'][$idx])) {
            $tokenName = $_SESSION['portfolio'][$idx]['token']['name'];
            array_splice($_SESSION['portfolio'], $idx, 1);
            $_SESSION['portfolio_history'][] = [
                'time' => date('Y-m-d H:i:s'),
                'action' => 'Удаление токена: ' . $tokenName,
                'portfolio' => $_SESSION['portfolio']
            ];
        }
    }
}
// Редактирование токена
if (isset($_POST['edit_token'], $_POST['edit_amount'])) {
    if (!checkRateLimit('edit_token', 10, 60)) {
        $addError = 'Занадто багато спроб редагування. Спробуйте пізніше.';
    } else {
        $idx = (int)$_POST['edit_token'];
        $amount = (float)$_POST['edit_amount'];
        if (isset($_SESSION['portfolio'][$idx]) && $amount > 0 && $amount <= 999999999) {
            $oldAmount = $_SESSION['portfolio'][$idx]['amount'];
            $_SESSION['portfolio'][$idx]['amount'] = $amount;
            $_SESSION['portfolio'][$idx]['value'] = $amount * $_SESSION['portfolio'][$idx]['token']['current_price'];
            $_SESSION['portfolio_history'][] = [
                'time' => date('Y-m-d H:i:s'),
                'action' => "Редактирование токена: зміна кількості з {$oldAmount} на {$amount}",
                'portfolio' => $_SESSION['portfolio']
            ];
        }
    }
}
// Добавление токена
$addError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_token'])) {
    // Rate limiting
    if (!checkRateLimit('add_token', 5, 60)) {
        $addError = 'Занадто багато спроб. Спробуйте пізніше.';
    } else {
        // CSRF validation
        if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
            $addError = 'Помилка безпеки. Оновіть сторінку та спробуйте знову.';
        } else {
            $tokenId = trim($_POST['token_id']);
            $amount = (float)$_POST['amount'];
            $buyPrice = isset($_POST['buy_price']) ? (float)$_POST['buy_price'] : 0;
            $category = isset($_POST['category']) ? $_POST['category'] : '';
            $txLink = trim($_POST['tx_link'] ?? '');
            
            // Validate input
            $validationErrors = validateTokenInput($tokenId, $amount, $buyPrice, $category, $txLink);
            
            if (!empty($validationErrors)) {
                $addError = implode(', ', $validationErrors);
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
  <link href="assets/ux-improvements.css" rel="stylesheet">
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
    <form method="post" class="row g-3 mb-4 align-items-end" id="add-token-form">
      <input type="hidden" name="add_token" value="1">
      <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
      <div class="col-md-2">
        <label class="form-label">CoinGecko ID</label>
        <input type="text" name="token_id" class="form-control" placeholder="bitcoin" required 
               pattern="[a-zA-Z0-9-]+" title="Тільки літери, цифри та дефіси">
        <div class="form-text">Наприклад: bitcoin, ethereum, usd-coin</div>
      </div>
      <div class="col-md-2">
        <label class="form-label">Кількість</label>
        <input type="number" name="amount" class="form-control" min="0.0001" step="any" required>
        <div class="form-text">Мінімум: 0.0001</div>
      </div>
      <div class="col-md-2">
        <label class="form-label">Ціна покупки ($)</label>
        <input type="number" name="buy_price" class="form-control" min="0.0001" step="any" required>
        <div class="form-text">Ціна за одиницю</div>
      </div>
      <div class="col-md-2">
        <label class="form-label">Категорія</label>
        <select name="category" class="form-select">
          <option value="">Авто</option>
          <option value="BTC">BTC</option>
          <option value="ETH & Blue Chips">ETH & Blue Chips</option>
          <option value="Stablecoins">Stablecoins</option>
          <option value="DeFi & Altcoins">DeFi & Altcoins</option>
        </select>
        <div class="form-text">Залиште "Авто" для автоматичного визначення</div>
      </div>
      <div class="col-md-2">
        <label class="form-label">Посилання на транзакцію</label>
        <input type="url" name="tx_link" class="form-control" placeholder="https://...">
        <div class="form-text">Необов'язково</div>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-success w-100" id="add-token-btn">
          <span class="btn-text">Додати токен</span>
          <span class="btn-loading" style="display: none;">
            <span class="spinner-border spinner-border-sm me-2"></span>Завантаження...
          </span>
        </button>
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
          <div class="card-header d-flex justify-content-between align-items-center">
            <span>Ваше портфоліо</span>
            <span class="badge bg-primary"><?= count($portfolio) ?> токенів</span>
          </div>
          <div class="card-body">
            <?php if (empty($portfolio)): ?>
              <div class="text-center text-muted py-4">
                <i class="bi bi-wallet2" style="font-size: 3rem; opacity: 0.5;"></i>
                <p class="mt-3">Ваше портфоліо порожнє</p>
                <p class="small">Додайте свій перший токен вище</p>
              </div>
            <?php else: ?>
            <ul class="list-group" id="portfolio-list">
              <?php foreach ($portfolio as $i => $item): ?>
                <li class="list-group-item">
                  <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="d-flex align-items-center">
                      <span class="fw-bold"><?= escapeHTML($item['token']['name']) ?></span>
                      <?= categoryBadge($item['token']['category']) ?>
                    </div>
                    <div class="btn-group btn-group-sm">
                      <button class="btn btn-outline-warning" onclick="showEditForm(<?= $i ?>, <?= htmlspecialchars($item['amount']) ?>)">
                        <i class="bi bi-pencil"></i>
                      </button>
                      <form method="post" style="display:inline">
                        <input type="hidden" name="delete_token" value="<?= $i ?>">
                        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete('<?= escapeHTML($item['token']['name']) ?>', this.form)">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    </div>
                  </div>
                  <div class="row text-muted small">
                    <div class="col-6">
                      <strong>Кількість:</strong> <?= number_format($item['amount'], 8) ?>
                    </div>
                    <div class="col-6">
                      <strong>Ціна покупки:</strong> $<?= number_format($item['buy_price'], 2) ?>
                    </div>
                  </div>
                  <div class="row text-muted small">
                    <div class="col-6">
                      <strong>Поточна ціна:</strong> $<?= number_format($item['token']['current_price'], 2) ?>
                    </div>
                    <div class="col-6">
                      <strong>Вартість:</strong> $<?= number_format($item['value'], 2) ?>
                    </div>
                  </div>
                  <div class="mt-2">
                    <strong>ROI:</strong> 
                    <span class="<?= $item['roi'] >= 0 ? 'roi-pos' : 'roi-neg' ?>">
                      <?= $item['roi'] >= 0 ? '+' : '' ?><?= number_format($item['roi'], 2) ?>%
                    </span>
                  </div>
                  <?php if (!empty($item['tx_link'])): ?>
                  <div class="mt-1">
                    <a href="<?= escapeHTML($item['tx_link']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                      <i class="bi bi-link-45deg"></i> Транзакція
                    </a>
                  </div>
                  <?php endif; ?>
                </li>
                <li class="list-group-item edit-form" id="edit-form-<?= $i ?>" style="display:none;">
                  <form method="post" class="d-flex align-items-center gap-2">
                    <input type="hidden" name="edit_token" value="<?= $i ?>">
                    <input type="number" name="edit_amount" value="<?= htmlspecialchars($item['amount']) ?>" min="0.0001" step="any" class="form-control" style="width:120px;">
                    <button type="submit" class="btn btn-primary btn-sm">Зберегти</button>
                    <button type="button" class="btn btn-secondary btn-sm" onclick="hideEditForm(<?= $i ?>)">Скасувати</button>
                  </form>
                </li>
              <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            <div class="mt-3 p-3 bg-light rounded">
              <div class="d-flex justify-content-between align-items-center">
                <strong>Загальна вартість:</strong>
                <span class="h5 mb-0" id="portfolio-value">$<?= formatNumberWithCommas($totalValue) ?></span>
              </div>
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
  <script src="components/notifications.js"></script>
  <script src="components/portfolioChart.js"></script>
  <script src="components/portfolioUpdater.js"></script>
  <script>
    // Show notifications for errors
    <?php if ($addError): ?>
    window.addEventListener('DOMContentLoaded', function() {
        notifications.error('<?= addslashes($addError) ?>');
    });
    <?php endif; ?>

    function showEditForm(idx, amount) {
      document.getElementById('edit-form-' + idx).style.display = 'flex';
    }
    function hideEditForm(idx) {
      document.getElementById('edit-form-' + idx).style.display = 'none';
    }

    // Enhanced delete confirmation
    function confirmDelete(tokenName, form) {
        confirmDialog.show(
            `Ви впевнені, що хочете видалити токен "${tokenName}"?`,
            () => form.submit(),
            () => {}
        );
    }

    // Enhanced form handling
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('add-token-form');
        const submitBtn = document.getElementById('add-token-btn');
        const btnText = submitBtn.querySelector('.btn-text');
        const btnLoading = submitBtn.querySelector('.btn-loading');

        if (form) {
            form.addEventListener('submit', function(e) {
                // Show loading state
                btnText.style.display = 'none';
                btnLoading.style.display = 'inline-flex';
                submitBtn.disabled = true;

                // Validate form
                const tokenId = form.querySelector('input[name="token_id"]').value.trim();
                const amount = parseFloat(form.querySelector('input[name="amount"]').value);
                const price = parseFloat(form.querySelector('input[name="buy_price"]').value);

                if (!tokenId || !amount || !price) {
                    e.preventDefault();
                    notifications.error('Будь ласка, заповніть всі обов\'язкові поля');
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                    submitBtn.disabled = false;
                    return;
                }

                if (amount <= 0 || price <= 0) {
                    e.preventDefault();
                    notifications.error('Кількість та ціна повинні бути більше нуля');
                    btnText.style.display = 'inline';
                    btnLoading.style.display = 'none';
                    submitBtn.disabled = false;
                    return;
                }

                // Form is valid, let it submit
                notifications.info('Додавання токена...');
            });
        }

        // Auto-format numbers
        const numberInputs = document.querySelectorAll('input[type="number"]');
        numberInputs.forEach(input => {
            input.addEventListener('blur', function() {
                if (this.value && !isNaN(this.value)) {
                    this.value = parseFloat(this.value).toFixed(8);
                }
            });
        });

        // Token ID suggestions
        const tokenIdInput = document.querySelector('input[name="token_id"]');
        if (tokenIdInput) {
            const suggestions = ['bitcoin', 'ethereum', 'usd-coin', 'tether', 'bnb', 'solana'];
            const datalist = document.createElement('datalist');
            datalist.id = 'token-suggestions';
            suggestions.forEach(suggestion => {
                const option = document.createElement('option');
                option.value = suggestion;
                datalist.appendChild(option);
            });
            tokenIdInput.setAttribute('list', 'token-suggestions');
            document.body.appendChild(datalist);
        }
    });
  </script>
</body>
</html> 