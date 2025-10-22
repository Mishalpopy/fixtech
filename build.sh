#!/bin/bash

# Exit on any error
set -e

echo "Starting build process..."

# Install PHP dependencies
echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Clear npm cache and remove problematic files
echo "Clearing npm cache..."
npm cache clean --force

# Remove package-lock.json and node_modules if they exist
if [ -f "package-lock.json" ]; then
    echo "Removing package-lock.json..."
    rm package-lock.json
fi

if [ -d "node_modules" ]; then
    echo "Removing node_modules..."
    rm -rf node_modules
fi

# Install npm dependencies with proper flags
echo "Installing npm dependencies..."
npm install --legacy-peer-deps --no-optional

# Rebuild native modules
echo "Rebuilding native modules..."
npm rebuild

# Build frontend assets
echo "Building frontend assets..."
npm run build

echo "Build completed successfully!"
