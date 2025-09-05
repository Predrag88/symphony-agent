# AI Alati Browser Extension

## Opis

AI Alati Browser Extension je moćna ekstenzija za Chrome/Edge koja omogućava direktnu integraciju AI alata za analizu, optimizaciju i generisanje sadržaja direktno u vašem browseru.

## Funkcionalnosti

### 🔍 Analiza Sadržaja
- Automatska analiza sadržaja stranice
- SEO preporuke i optimizacija
- Analiza čitljivosti teksta
- Preporuke za poboljšanje

### ✨ Generisanje Sadržaja
- Kreiranje sadržaja na osnovu konteksta stranice
- Optimizacija postojećeg teksta
- Generisanje meta opisa i naslova
- Kreiranje poziva na akciju

### 🎯 SEO Optimizacija
- Analiza ključnih reči
- Optimizacija meta tagova
- Preporuke za poboljšanje rangiranja
- Analiza konkurencije

### 📊 Izvoz Sadržaja
- Ekstraktovanje glavnog sadržaja
- Kreiranje rezimea
- Identifikacija ključnih tačaka
- Analiza strukture sadržaja

## Instalacija

### Korak 1: Preuzimanje
1. Preuzmite `ai-alati-extension.zip` fajl
2. Ekstraktujte sadržaj u željeni direktorijum

### Korak 2: Instalacija u Chrome
1. Otvorite Chrome browser
2. Idite na `chrome://extensions/`
3. Uključite "Developer mode" u gornjem desnom uglu
4. Kliknite "Load unpacked"
5. Izaberite direktorijum gde ste ekstraktovali ekstenziju
6. Ekstenzija će biti instalirana i pojaviće se u toolbar-u

### Korak 3: Instalacija u Microsoft Edge
1. Otvorite Microsoft Edge browser
2. Idite na `edge://extensions/`
3. Uključite "Developer mode" u levom panelu
4. Kliknite "Load unpacked"
5. Izaberite direktorijum gde ste ekstraktovali ekstenziju
6. Ekstenzija će biti instalirana i pojaviće se u toolbar-u

## Korišćenje

### Osnovne Funkcije

#### Pokretanje Panela
- Kliknite na ikonu ekstenzije u toolbar-u
- Ili koristite prečicu `Ctrl+Shift+A` (Windows/Linux) ili `Cmd+Shift+A` (Mac)

#### Analiza Stranice
1. Otvorite bilo koju web stranicu
2. Kliknite na ikonu ekstenzije
3. Izaberite "🔍 Analiziraj Stranicu"
4. Sačekajte rezultate analize

#### SEO Optimizacija
1. Na stranici koju želite da optimizujete
2. Otvorite AI Alati panel
3. Kliknite "🎯 Optimizuj SEO"
4. Pregledajte preporuke i primenite ih

#### Generisanje Sadržaja
1. Pozicionirajte se na stranici sa relevantnim kontekstom
2. Otvorite panel
3. Kliknite "✨ Generiši Sadržaj"
4. Kopirajte generisani sadržaj

#### Ekstraktovanje Sadržaja
1. Na stranici sa sadržajem koji želite da ekstraktujete
2. Otvorite panel
3. Kliknite "📊 Izvezi Sadržaj"
4. Sačuvajte ili kopirajte rezultate

### Napredne Funkcije

#### Kontekstni Meni
- Označite tekst na stranici
- Desni klik → "Analiziraj označeni tekst"
- Ili "Generiši sadržaj na osnovu označenog"

#### Prečice na Tastaturi
- `Ctrl+Shift+A` - Uključi/isključi panel
- `Ctrl+Shift+Q` - Brza analiza
- `Ctrl+Shift+E` - Izvezi sadržaj

## Podešavanja

### Pristup Podešavanjima
1. Kliknite na ikonu ekstenzije
2. Kliknite na "⚙️" dugme u panelu
3. Ili idite na `chrome://extensions/` → AI Alati → "Options"

### Dostupna Podešavanja

#### API Konfiguracija
- **API Endpoint**: URL vašeg AI servisa (default: `http://localhost:8001/api`)
- **API Key**: Vaš API ključ (ako je potreban)

#### Ponašanje
- **Auto Analiza**: Automatski analiziraj stranice pri učitavanju
- **Notifikacije**: Prikaži notifikacije o završenim akcijama
- **Pozicija Panela**: Gde da se prikaže panel (gore-desno, gore-levo, itd.)

#### Prečice
- Prilagodite prečice na tastaturi prema vašim potrebama

## Sistemski Zahtevi

### Podržani Browseri
- Google Chrome 88+
- Microsoft Edge 88+
- Chromium-based browseri

### Sistemski Zahtevi
- Windows 10+, macOS 10.14+, ili Linux
- Minimum 4GB RAM
- Aktivna internet konekcija

### Backend Zahtevi
- AI Alati backend servis (Symfony aplikacija)
- PHP 8.1+
- Aktivna konekcija sa AI servisom

## Troubleshooting

### Česti Problemi

#### Ekstenzija se ne učitava
1. Proverite da li je Developer mode uključen
2. Osvežite stranicu sa ekstenzijama
3. Restartujte browser

#### Panel se ne prikazuje
1. Proverite da li je ekstenzija aktivna
2. Osvežite stranicu
3. Proverite konzolu za greške (`F12` → Console)

#### API greške
1. Proverite da li je backend servis pokrenut
2. Verifikujte API endpoint u podešavanjima
3. Proverite mrežnu konekciju

#### Performanse
1. Zatvorite nepotrebne tabove
2. Restartujte browser
3. Proverite dostupnu memoriju

### Debug Mode
1. Otvorite Developer Tools (`F12`)
2. Idite na Console tab
3. Potražite poruke koje počinju sa "AI Alati:"

## Bezbednost i Privatnost

### Podaci
- Ekstenzija šalje sadržaj stranica na vaš AI backend
- Nema čuvanja podataka u ekstenziji
- Svi podaci se obrađuju preko vašeg servera

### Dozvole
- **activeTab**: Pristup trenutno aktivnom tabu
- **storage**: Čuvanje podešavanja
- **notifications**: Prikazivanje notifikacija
- **contextMenus**: Kontekstni meni opcije

## Podrška

### Kontakt
- Email: support@ai-alati.com
- Website: http://localhost:8001
- GitHub: [AI Alati Repository]

### Dokumentacija
- Kompletna dokumentacija: http://localhost:8001/docs
- API dokumentacija: http://localhost:8001/api/docs
- Video tutorijali: http://localhost:8001/tutorials

## Verzija

**Trenutna verzija**: 1.0.0

### Changelog

#### v1.0.0 (Početna verzija)
- Osnovna funkcionalnost analize sadržaja
- SEO optimizacija
- Generisanje sadržaja
- Ekstraktovanje sadržaja
- Kontekstni meni
- Prečice na tastaturi
- Podešavanja i konfiguracija

## Licenca

AI Alati Browser Extension je vlasništvo AI Alati tima. Sva prava zadržana.

---

*Za dodatne informacije i podršku, posetite našu web stranicu ili nas kontaktirajte putem email-a.*