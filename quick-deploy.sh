#!/bin/bash

# Quick deployment script - za brže ažuriranje bez GitHub Actions
# Koristi ovaj script kada želiš da brže deploy-uješ promene direktno na server

set -e

echo "🚀 Starting quick deployment..."

# Proveri da li su sve promene commit-ovane
if [[ -n $(git status --porcelain) ]]; then
    echo "⚠️  You have uncommitted changes. Committing them now..."
    git add .
    read -p "Enter commit message: " commit_message
    git commit -m "$commit_message"
fi

# Push na GitHub
echo "📤 Pushing to GitHub..."
git push origin main

echo "✅ Code pushed to GitHub successfully!"
echo "🔄 GitHub Actions will automatically deploy to your server."
echo "📊 Check deployment status at: https://github.com/Predrag88/symphony-agent/actions"

# Opciono - direktan deployment na server (ako imaš SSH pristup)
read -p "Do you want to deploy directly to server now? (y/n): " deploy_now

if [[ $deploy_now == "y" || $deploy_now == "Y" ]]; then
    read -p "Enter server SSH address (user@server): " ssh_address
    read -p "Enter project path on server: " project_path
    
    echo "🔧 Deploying directly to server..."
    
    ssh $ssh_address << EOF
        cd $project_path
        git pull origin main
        composer install --no-dev --optimize-autoloader
        php bin/console cache:clear --env=prod
        php bin/console doctrine:migrations:migrate --no-interaction
        sudo chown -R www-data:www-data var/
        sudo chmod -R 775 var/
        sudo systemctl reload php8.1-fpm
        sudo systemctl reload nginx
EOF
    
    echo "🎉 Direct deployment completed!"
else
    echo "ℹ️  GitHub Actions will handle the deployment automatically."
fi

echo "🌐 Your application should be live shortly!"