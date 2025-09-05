#!/bin/bash

# Deployment script for Symfony application
# This script should be placed on your server

set -e

echo "Starting deployment..."

# Navigate to project directory on server 199.247.1.220
cd /var/www/html/symphony-agent

# Pull latest changes from GitHub
echo "Pulling latest changes..."
git pull origin main

# Install/update dependencies
echo "Installing dependencies..."
composer install --no-dev --optimize-autoloader

# Clear cache
echo "Clearing cache..."
php bin/console cache:clear --env=prod

# Run database migrations
echo "Running migrations..."
php bin/console doctrine:migrations:migrate --no-interaction

# Set proper permissions
echo "Setting permissions..."
sudo chown -R www-data:www-data var/
sudo chmod -R 775 var/

# Reload PHP-FPM and Nginx
echo "Reloading services..."
sudo systemctl reload php8.1-fpm
sudo systemctl reload nginx

echo "Deployment completed successfully!"
echo "Application is now live at your domain."