#!/bin/bash

echo "Testing Docker build context..."

# Check if backend files exist
echo "Checking backend files:"
ls -la backend/composer.json
ls -la backend/package.json
ls -la backend/artisan

# Test Docker build (dry run)
echo "Testing Docker build..."
docker build --no-cache -t skillsxchange-test .

echo "Docker build test completed."
