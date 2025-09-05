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

### 4. Deployment na server 199.247.1.220

#### Opcija 1: Webhook Deployment (Preporučeno)

Ako nemate pristup GitHub Actions secrets, koristite webhook deployment:

```bash
# Pokrenite automatski setup
chmod +x simple-deploy-setup.sh
./simple-deploy-setup.sh
```

Script će:
- Instalirati sve potrebne pakete na serveru
- Podesiti Nginx i PHP-FPM
- Klonirati projekat
- Kreirati webhook endpoint
- Generisati secret token

#### Opcija 2: GitHub Actions (Napredni korisnici)

Ako imate pristup GitHub Secrets:

1. Idi na GitHub repozitorijum → Settings → Secrets and variables → Actions
2. Dodaj sledeće secrets:
   - `SSH_USERNAME`: SSH korisničko ime za server 199.247.1.220
   - `SSH_PRIVATE_KEY`: SSH privatni ključ za pristup serveru

**Napomena**: Server IP (199.247.1.220) i port (22) su već konfigurisani u workflow-u.

### Webhook Setup Instrukcije

Nakon pokretanja `simple-deploy-setup.sh` script-a:

1. **Kopirajte webhook URL i secret** koji će script prikazati
2. **Idite na GitHub repo → Settings → Webhooks**
3. **Kliknite "Add webhook"**
4. **Unesite podatke:**
   - Payload URL: `http://199.247.1.220/webhook-deploy.php`
   - Content type: `application/json`
   - Secret: (kopirajte generisani secret)
   - Events: Izaberite "Just the push event"
   - Active: ✅ Označeno

5. **Kliknite "Add webhook"**

### Manuelni Server Setup (Alternativa)

Ako ne možete da koristite automatski script:

```bash
# Instaliraj pakete
apt update && apt install -y nginx php8.1-fpm php8.1-mysql php8.1-xml php8.1-mbstring php8.1-curl php8.1-zip composer git

# Kloniraj projekat
cd /var/www/html
git clone https://github.com/Predrag88/symphony-agent.git
cd symphony-agent
composer install --no-dev

# Podesi dozvole
chown -R www-data:www-data /var/www/html/symphony-agent
chmod -R 755 /var/www/html/symphony-agent
```

### 5. Test deployment-a

#### Testiranje Webhook Deployment-a

Nakon setup-a, testirajte deployment:

```bash
# Napravite malu promenu i push-ujte
echo "# Test deployment" >> README.md
git add README.md
git commit -m "Test webhook deployment"
git push origin main
```

#### Kako funkcioniše

**Webhook Deployment:**
1. **Push na main branch** → GitHub šalje webhook na server
2. **Webhook prima zahtev** → Proverava signature i branch
3. **Automatski deployment** → Pokreće deploy.sh script
4. **Restart servisa** → Nginx i PHP-FPM se restartuju

**GitHub Actions (ako je podešeno):**
1. **Push na main branch** → GitHub Actions se automatski pokreće
2. **Testiranje** → Pokreću se PHPUnit testovi
3. **Deployment** → Kod se automatski deploy-uje na server
4. **Restart servisa** → Nginx i PHP-FPM se restartuju

#### Rezultat

- **Server IP:** 199.247.1.220
- **Aplikacija dostupna na:** http://199.247.1.220
- **Webhook endpoint:** http://199.247.1.220/webhook-deploy.php
- **Automatski deployment:** ✅ Svaki push na main branch

#### Monitoring

- **Webhook logovi:** `ssh root@199.247.1.220 "tail -f /var/log/webhook-deploy.log"`
- **GitHub Actions status:** Pratite u GitHub repo → Actions tab (ako koristite)
- **Server logovi:** `ssh root@199.247.1.220 "tail -f /var/log/nginx/error.log"`
- **App logovi:** `ssh root@199.247.1.220 "tail -f /var/www/html/symphony-agent/var/log/prod.log"`

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