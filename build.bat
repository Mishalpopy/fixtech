@echo off
setlocal

echo Starting build process...

REM Install PHP dependencies
echo Installing PHP dependencies...
composer install --no-dev --optimize-autoloader

REM Clear npm cache and remove problematic files
echo Clearing npm cache...
npm cache clean --force

REM Remove package-lock.json and node_modules if they exist
if exist package-lock.json (
    echo Removing package-lock.json...
    del package-lock.json
)

if exist node_modules (
    echo Removing node_modules...
    rmdir /s /q node_modules
)

REM Install npm dependencies with proper flags
echo Installing npm dependencies...
npm install --legacy-peer-deps --no-optional

REM Rebuild native modules
echo Rebuilding native modules...
npm rebuild

REM Build frontend assets
echo Building frontend assets...
npm run build

echo Build completed successfully!
