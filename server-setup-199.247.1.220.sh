#!/bin/bash

# Server Setup Script za 199.247.1.220
# Ovaj script priprema server za Symfony aplikaciju

set -e

echo "🚀 Setting up server 199.247.1.220 for Symphony Agent..."

# Update system
echo "📦 Updating system packages..."
sudo apt update && sudo apt upgrade -y

# Install required packages
echo "📦 Installing required packages..."
sudo apt install -y nginx php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip php8.1-intl php8.1-sqlite3 git unzip curl

# Install Composer
echo "📦 Installing Composer..."
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
sudo chmod +x /usr/local/bin/composer

# Create project directory
echo "📁 Creating project directory..."
sudo mkdir -p /var/www/html/symphony-agent
sudo chown $USER:$USER /var/www/html/symphony-agent

# Clone project from GitHub
echo "📥 Cloning project from GitHub..."
cd /var/www/html/symphony-agent
git clone https://github.com/Predrag88/symphony-agent.git .

# Install dependencies
echo "📦 Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

# Set proper permissions
echo "🔐 Setting proper permissions..."
sudo chown -R www-data:www-data /var/www/html/symphony-agent
sudo chmod -R 755 /var/www/html/symphony-agent
sudo chmod -R 775 /var/www/html/symphony-agent/var
sudo chmod -R 775 /var/www/html/symphony-agent/public/uploads

# Create Nginx configuration
echo "🌐 Creating Nginx configuration..."
sudo tee /etc/nginx/sites-available/symphony-agent > /dev/null <<EOF
server {
    listen 80;
    server_name 199.247.1.220;
    root /var/www/html/symphony-agent/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;

    # Main location
    location / {
        try_files \$uri \$uri/ /index.php\$is_args\$args;
    }

    # PHP handling
    location ~ \.php\$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Security - deny access to sensitive files
    location ~ /\.(ht|git) {
        deny all;
    }

    location ~ /\.(env|yml|yaml|json)\$ {
        deny all;
    }

    # Static files caching
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)\$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;
}
EOF

# Enable site
echo "🔗 Enabling Nginx site..."
sudo ln -sf /etc/nginx/sites-available/symphony-agent /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Test Nginx configuration
echo "🧪 Testing Nginx configuration..."
sudo nginx -t

# Configure PHP-FPM
echo "⚙️ Configuring PHP-FPM..."
sudo sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/' /etc/php/8.1/fpm/php.ini
sudo sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 50M/' /etc/php/8.1/fpm/php.ini
sudo sed -i 's/post_max_size = 8M/post_max_size = 50M/' /etc/php/8.1/fpm/php.ini
sudo sed -i 's/max_execution_time = 30/max_execution_time = 300/' /etc/php/8.1/fpm/php.ini
sudo sed -i 's/memory_limit = 128M/memory_limit = 512M/' /etc/php/8.1/fpm/php.ini

# Restart services
echo "🔄 Restarting services..."
sudo systemctl restart php8.1-fpm
sudo systemctl restart nginx

# Enable services to start on boot
echo "🚀 Enabling services on boot..."
sudo systemctl enable php8.1-fpm
sudo systemctl enable nginx

# Setup firewall (optional)
echo "🔥 Configuring firewall..."
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw --force enable

# Create environment file
echo "📝 Creating production environment file..."
cd /var/www/html/symphony-agent
cp .env .env.local
sed -i 's/APP_ENV=dev/APP_ENV=prod/' .env.local
sed -i 's/APP_DEBUG=1/APP_DEBUG=0/' .env.local

# Clear cache and run migrations
echo "🧹 Clearing cache and running migrations..."
php bin/console cache:clear --env=prod
php bin/console doctrine:migrations:migrate --no-interaction

# Final permissions
echo "🔐 Setting final permissions..."
sudo chown -R www-data:www-data /var/www/html/symphony-agent
sudo chmod -R 755 /var/www/html/symphony-agent
sudo chmod -R 775 /var/www/html/symphony-agent/var

echo "✅ Server setup completed successfully!"
echo "🌐 Your application should be accessible at: http://199.247.1.220"
echo ""
echo "📋 Next steps:"
echo "1. Add GitHub Secrets for automated deployment:"
echo "   - SSH_USERNAME: your server username"
echo "   - SSH_PRIVATE_KEY: your SSH private key"
echo "2. Test the application: http://199.247.1.220"
echo "3. Push changes to GitHub to trigger automatic deployment"
echo ""
echo "🔧 Useful commands:"
echo "   - Check Nginx status: sudo systemctl status nginx"
echo "   - Check PHP-FPM status: sudo systemctl status php8.1-fpm"
echo "   - View Nginx logs: sudo tail -f /var/log/nginx/error.log"
echo "   - View application logs: tail -f /var/www/html/symphony-agent/var/log/prod.log"