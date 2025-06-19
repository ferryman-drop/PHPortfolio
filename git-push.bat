@echo off
echo Git Auto Push Script
echo.
echo Adding all changes...
git add .
echo.
echo Committing changes...
git commit -m "Auto commit - %date% %time%"
echo.
echo Pushing to GitHub...
git push origin main
echo.
echo Done! Changes pushed to GitHub.
pause 