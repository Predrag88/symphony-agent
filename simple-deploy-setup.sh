#!/bin/bash

# Jednostavan deployment setup za server 199.247.1.220
# Ovaj script postavlja webhook deployment bez GitHub Actions

set -e

echo "🚀 Setting up simple webhook deployment for 199.247.1.220..."

# Server details
SERVER_IP="199.247.1.220"
PROJECT_PATH="/var/www/html/symphony-agent"
WEBHOOK_SECRET="$(openssl rand -hex 32)"

echo "📝 Generated webhook secret: $WEBHOOK_SECRET"
echo "⚠️  Save this secret - you'll need it for GitHub webhook!"

# Function to run commands on server
run_on_server() {
    ssh root@$SERVER_IP "$1"
}

echo "📦 Installing required packages on server..."
run_on_server "apt update && apt install -y nginx php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip php8.1-intl php8.1-sqlite3 git unzip curl"

echo "📦 Installing Composer..."
run_on_server "curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer && chmod +x /usr/local/bin/composer"

echo "📁 Creating project directory..."
run_on_server "mkdir -p $PROJECT_PATH && chown www-data:www-data $PROJECT_PATH"

echo "📥 Cloning project..."
run_on_server "cd $PROJECT_PATH && git clone https://github.com/Predrag88/symphony-agent.git . && composer install --no-dev --optimize-autoloader"

echo "🔐 Setting permissions..."
run_on_server "chown -R www-data:www-data $PROJECT_PATH && chmod -R 755 $PROJECT_PATH && chmod -R 775 $PROJECT_PATH/var && chmod -R 775 $PROJECT_PATH/public/uploads"

echo "🌐 Creating Nginx configuration..."
run_on_server "cat > /etc/nginx/sites-available/symphony-agent << 'EOF'
server {
    listen 80;
    server_name $SERVER_IP;
    root $PROJECT_PATH/public;
    index index.php index.html;

    # Security headers
    add_header X-Frame-Options \"SAMEORIGIN\" always;
    add_header X-XSS-Protection \"1; mode=block\" always;
    add_header X-Content-Type-Options \"nosniff\" always;

    # Main location
    location / {
        try_files \\$uri \\$uri/ /index.php\\$is_args\\$args;
    }

    # Webhook endpoint
    location /webhook-deploy.php {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \\$realpath_root\\$fastcgi_script_name;
        include fastcgi_params;
    }

    # PHP handling
    location ~ \\.php\\$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \\$realpath_root\\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Security - deny access to sensitive files
    location ~ /\\.(ht|git) {
        deny all;
    }

    location ~ /\\.(env|yml|yaml|json)\\$ {
        deny all;
    }

    # Static files caching
    location ~* \\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)\\$ {
        expires 1y;
        add_header Cache-Control \"public, immutable\";
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;
}
EOF"

echo "🔗 Enabling Nginx site..."
run_on_server "ln -sf /etc/nginx/sites-available/symphony-agent /etc/nginx/sites-enabled/ && rm -f /etc/nginx/sites-enabled/default && nginx -t"

echo "⚙️ Configuring PHP-FPM..."
run_on_server "sed -i 's/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/' /etc/php/8.1/fpm/php.ini"
run_on_server "sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 50M/' /etc/php/8.1/fpm/php.ini"
run_on_server "sed -i 's/post_max_size = 8M/post_max_size = 50M/' /etc/php/8.1/fpm/php.ini"
run_on_server "sed -i 's/max_execution_time = 30/max_execution_time = 300/' /etc/php/8.1/fpm/php.ini"
run_on_server "sed -i 's/memory_limit = 128M/memory_limit = 512M/' /etc/php/8.1/fpm/php.ini"

echo "📝 Creating webhook deployment script..."
scp webhook-deploy.php root@$SERVER_IP:$PROJECT_PATH/public/
run_on_server "sed -i 's/your-secret-webhook-token-here/$WEBHOOK_SECRET/' $PROJECT_PATH/public/webhook-deploy.php"

echo "📝 Creating environment file..."
run_on_server "cd $PROJECT_PATH && cp .env .env.local && sed -i 's/APP_ENV=dev/APP_ENV=prod/' .env.local && sed -i 's/APP_DEBUG=1/APP_DEBUG=0/' .env.local"

echo "🧹 Clearing cache and running migrations..."
run_on_server "cd $PROJECT_PATH && php bin/console cache:clear --env=prod && php bin/console doctrine:migrations:migrate --no-interaction"

echo "🔄 Restarting services..."
run_on_server "systemctl restart php8.1-fpm && systemctl restart nginx && systemctl enable php8.1-fpm && systemctl enable nginx"

echo "🔥 Configuring firewall..."
run_on_server "ufw allow 22/tcp && ufw allow 80/tcp && ufw allow 443/tcp && ufw --force enable"

echo "✅ Setup completed successfully!"
echo ""
echo "🌐 Your application is accessible at: http://$SERVER_IP"
echo "🔗 Webhook URL: http://$SERVER_IP/webhook-deploy.php"
echo "🔑 Webhook Secret: $WEBHOOK_SECRET"
echo ""
echo "📋 Next steps:"
echo "1. Go to GitHub repo → Settings → Webhooks"
echo "2. Add webhook with URL: http://$SERVER_IP/webhook-deploy.php"
echo "3. Set Content type: application/json"
echo "4. Set Secret: $WEBHOOK_SECRET"
echo "5. Select 'Just the push event'"
echo "6. Make sure webhook is Active"
echo ""
echo "🎉 Now every push to main branch will automatically deploy!"