# Symphony Agent - Symfony Dashboard

Moderni dashboard za n8n agenta kreiran u Symfony framework-u sa Tailwind CSS i jQuery.

## Funkcionalnosti

- 🎯 Product AI Generator sa naprednim opcijama
- 📱 Potpuno responsive dizajn
- 🎨 Moderni UI sa Tailwind CSS
- ⚡ Brze AJAX interakcije sa jQuery
- 🔄 Real-time ažuriranje sadržaja
- 📊 Dashboard sa analitikama

## Tehnologije

- **Backend**: Symfony 6.3
- **Frontend**: Tailwind CSS + jQuery
- **Database**: SQLite/MySQL
- **Deployment**: GitHub Actions + SSH

## Lokalno pokretanje

```bash
# Kloniraj repozitorijum
git clone https://github.com/Predrag88/symphony-agent.git
cd symphony-agent

# Instaliraj dependencies
composer install

# Pokreni development server
php -S localhost:8000 -t public
```

## Deployment na server

### 1. Priprema servera

```bash
# Instaliraj potrebne pakete
sudo apt update
sudo apt install nginx php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring git composer

# Kreiraj direktorijum za projekat
sudo mkdir -p /var/www/html/symphony-agent
sudo chown $USER:$USER /var/www/html/symphony-agent
```

### 2. Kloniraj projekat na server

```bash
cd /var/www/html/symphony-agent
git clone https://github.com/Predrag88/symphony-agent.git .
composer install --no-dev --optimize-autoloader
```

### 3. Konfiguriši Nginx

Kreiraj `/etc/nginx/sites-available/symphony-agent`:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/symphony-agent/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php$is_args$args;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}
```

```bash
# Aktiviraj sajt
sudo ln -s /etc/nginx/sites-available/symphony-agent /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 4. Automatski deployment na server 199.247.1.220

#### Dodaj GitHub Secrets:

1. Idi na GitHub repozitorijum → Settings → Secrets and variables → Actions
2. Dodaj sledeće secrets:
   - `SSH_USERNAME`: SSH korisničko ime za server 199.247.1.220
   - `SSH_PRIVATE_KEY`: SSH privatni ključ za pristup serveru

**Napomena**: Server IP (199.247.1.220) i port (22) su već konfigurisani u workflow-u.

#### Pripremi server 199.247.1.220:

**Opcija 1: Automatski setup (preporučeno)**
```bash
# Kopiraj setup script na server
scp server-setup-199.247.1.220.sh user@199.247.1.220:~/

# Pokreni setup script
ssh user@199.247.1.220 "chmod +x ~/server-setup-199.247.1.220.sh && ~/server-setup-199.247.1.220.sh"
```

**Opcija 2: Manuelni setup**
```bash
# Kopiraj deploy script na server
scp deploy.sh user@199.247.1.220:/var/www/html/symphony-agent/

# Učini ga izvršnim
ssh user@199.247.1.220 "chmod +x /var/www/html/symphony-agent/deploy.sh"

# Dodaj sudo privilegije za web server restart
ssh user@199.247.1.220 "sudo visudo"
# Dodaj liniju: your-user ALL=(ALL) NOPASSWD: /bin/systemctl reload nginx, /bin/systemctl reload php8.1-fpm
```

### 5. Test deployment-a

```bash
# Push promene na GitHub
git add .
git commit -m "Update application"
git push origin main

# GitHub Actions će automatski:
# 1. Pokrenuti testove
# 2. Deploy-ovati na server
# 3. Restartovati servise
```

## Struktura projekta

```
src/
├── Controller/          # Symfony kontroleri
│   ├── DashboardController.php
│   └── ProductAIController.php
├── Entity/             # Doctrine entiteti
├── Form/               # Symfony forme
├── Service/            # Biznis logika
└── Repository/         # Database repozitorijumi

templates/
├── base.html.twig      # Osnovni template
├── dashboard/          # Dashboard stranice
└── product_ai/         # Product AI stranice

public/
├── index.php           # Entry point
└── uploads/            # Upload direktorijum
```

## Konfiguracija

### Environment varijable (.env)

```env
APP_ENV=prod
APP_SECRET=your-secret-key
DATABASE_URL="sqlite:///%kernel.project_dir%/var/data.db"
```

### Za produkciju (.env.local)

```env
APP_ENV=prod
APP_DEBUG=0
DATABASE_URL="mysql://user:password@localhost:3306/symphony_agent"
```

## Održavanje

### Backup baze podataka

```bash
# SQLite
cp var/data.db backups/data_$(date +%Y%m%d).db

# MySQL
mysqldump -u user -p symphony_agent > backups/backup_$(date +%Y%m%d).sql
```

### Monitoring logova

```bash
# Symfony logovi
tail -f var/log/prod.log

# Nginx logovi
sudo tail -f /var/log/nginx/access.log
sudo tail -f /var/log/nginx/error.log
```

## Troubleshooting

### Česti problemi:

1. **Permissions greške**:
   ```bash
   sudo chown -R www-data:www-data var/
   sudo chmod -R 775 var/
   ```

2. **Cache problemi**:
   ```bash
   php bin/console cache:clear --env=prod
   ```

3. **Database greške**:
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

## Kontakt

Za pitanja i podršku kontaktiraj: [your-email@domain.com]

## Licenca

MIT License - pogledaj LICENSE fajl za detalje.