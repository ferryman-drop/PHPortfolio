Write-Host "🚀 Starting Crypto Portfolio Manager..." -ForegroundColor Green
Write-Host ""

# Check if node_modules exists
if (-not (Test-Path "node_modules")) {
    Write-Host "📦 Installing dependencies..." -ForegroundColor Yellow
    npm install
    Write-Host ""
}

Write-Host "🌐 Starting development server..." -ForegroundColor Cyan
Write-Host "📱 Open http://localhost:3000 in your browser" -ForegroundColor Green
Write-Host ""

# Start the development server
npm run dev 