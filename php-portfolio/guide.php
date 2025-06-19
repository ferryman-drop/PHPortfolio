<?php require_once 'menu.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Portfolio Guide</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/style.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<div class="container py-4">
  <h1 class="mb-4"><i class="bi bi-journal-text"></i> Portfolio Guide</h1>
  <h2 class="section-title">🎯 Understanding the 4 Categories</h2>
  <ul>
    <li><b>BTC (Bitcoin):</b> Store of value, digital gold. <span class="text-secondary">Target: 25% (adjusts based on BTC dominance)</span><br><span class="text-muted">Examples: Bitcoin</span></li>
    <li><b>ETH & Blue Chips:</b> Smart contract platforms, established projects. <span class="text-secondary">Target: 25%</span><br><span class="text-muted">Examples: Ethereum, BNB, Cardano, Solana</span></li>
    <li><b>Stablecoins:</b> Low volatility, USD-pegged assets. <span class="text-secondary">Target: 25%</span><br><span class="text-muted">Examples: USDC, USDT, DAI, BUSD</span></li>
    <li><b>DeFi & Altcoins:</b> Higher risk/reward, innovative projects. <span class="text-secondary">Target: 25%</span><br><span class="text-muted">Examples: Uniswap, Aave, Compound, Chainlink</span></li>
  </ul>
  <h2 class="section-title">🔄 Auto Mode Explained</h2>
  <p>When Auto Mode is <b>ON</b>:</p>
  <ul>
    <li><b>High BTC Dominance (&gt;50%):</b> BTC 35%, ETH 25%, Stablecoins 25%, DeFi 15%</li>
    <li><b>Moderate BTC Dominance (40-50%):</b> BTC 30%, ETH 25%, Stablecoins 25%, DeFi 20%</li>
    <li><b>Low BTC Dominance (&lt;40%):</b> Equal allocation (25% each)</li>
  </ul>
  <p>When Auto Mode is <b>OFF</b>: Fixed 25% allocation across all categories</p>
  <h2 class="section-title">📊 Understanding the Dashboard</h2>
  <ul>
    <li><b>Portfolio Overview:</b> Current/target allocation, total value</li>
    <li><b>Analytics & Recommendations:</b> Risk score, rebalancing, market analysis</li>
    <li><b>Market Data:</b> BTC dominance, total market cap, trend</li>
  </ul>
  <h2 class="section-title">⚠️ Important Notes</h2>
  <ol>
    <li>API Rate Limits: CoinGecko free tier has 50 calls/minute limit</li>
    <li>Real-time Data: Prices update automatically every minute</li>
    <li>No Data Persistence: Portfolio data is stored in browser memory only</li>
    <li>Educational Purpose: This is a demo application for learning</li>
  </ol>
  <h2 class="section-title">🆘 Troubleshooting</h2>
  <ul>
    <li><b>"Token not found" Error:</b> Check spelling, use CoinGecko ID, search on <a href="https://coingecko.com">CoinGecko</a></li>
    <li><b>Charts Not Loading:</b> Check internet, browser console, refresh</li>
    <li><b>Slow Performance:</b> Reduce tokens, check connection, consider API upgrade</li>
  </ul>
  <h2 class="section-title">🎉 Next Steps</h2>
  <ol>
    <li>Add Your Portfolio</li>
    <li>Monitor Recommendations</li>
    <li>Adjust Auto Mode</li>
    <li>Track Performance</li>
  </ol>
</div>
</body>
</html> 