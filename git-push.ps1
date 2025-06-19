Write-Host "📤 Git Auto Push Script" -ForegroundColor Green
Write-Host ""

# Check git status
$status = git status --porcelain
if (-not $status) {
    Write-Host "✅ No changes to commit" -ForegroundColor Yellow
    Read-Host "Press Enter to continue"
    exit
}

Write-Host "📝 Adding all changes..." -ForegroundColor Cyan
git add .

Write-Host "💾 Committing changes..." -ForegroundColor Cyan
$timestamp = Get-Date -Format "yyyy-MM-dd HH:mm:ss"
git commit -m "Auto commit - $timestamp"

Write-Host "🚀 Pushing to GitHub..." -ForegroundColor Cyan
git push origin main

Write-Host "✅ Done! Changes pushed to GitHub." -ForegroundColor Green
Read-Host "Press Enter to continue" 