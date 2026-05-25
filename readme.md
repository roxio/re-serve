# Re-Serve - Salon Booking System

Aplikacja pozwala obsługiwać usługi, agentów/pracowników, rezerwacje, klientów, galerię, strony, blog, płatności Stripe, ustawienia poczty, social login, reCAPTCHA, meta tagi, reklamy i analitykę.


Panel administracyjny:

```text
/auth/login
```

Domyślne konto:

```text
login: admin1
hasło: test
```

### Wymagania

- PHP 8.2 lub nowszy.
- MySQL albo MariaDB.
- Apache z włączonym `mod_rewrite`.
- Rozszerzenia PHP: `intl`, `mbstring`, `mysqli`, `curl`, `fileinfo`.
- Uprawnienia zapisu dla `writable`.
- Uprawnienia zapisu dla katalogów uploadów w `public/uploads`, jeśli używasz dodawania plików z panelu.

### Uruchomienie lokalne przez wbudowany serwer PHP

Przejdź do głównego katalogu projektu, czyli tego, w którym znajduje się plik `spark`, i uruchom:

```powershell
.\serve-local.ps1
```

Aplikacja będzie dostępna pod:

```text
http://127.0.0.1:8080/public/
```

W tym wariancie w `.env` ustaw:

```ini
CI_ENVIRONMENT = development
app.baseURL = 'http://127.0.0.1:8080/public/'
app.indexPage = ''
app.appTimezone = 'Europe/Warsaw'

database.default.hostname = localhost
database.default.database = salon
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
```

### Uruchomienie lokalne przez XAMPP/htdocs

Jeśli pliki projektu znajdują się bezpośrednio w `htdocs`, a Apache pokazuje katalog `htdocs` jako główny katalog serwera, aplikacja będzie otwierana przez:

```text
http://127.0.0.1/public/
```

Wtedy w pliku `.env` w głównym katalogu projektu ustaw:

```ini
CI_ENVIRONMENT = development
app.baseURL = 'http://127.0.0.1/public/'
app.indexPage = ''
app.appTimezone = 'Europe/Warsaw'

database.default.hostname = localhost
database.default.database = salon
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
```

Jeżeli linki nadal prowadzą na stary adres, wyczyść:

```text
writable/cache/
writable/debugbar/
writable/session/
```

### Świeża baza danych

Na pustej bazie danych uruchom z głównego katalogu projektu:

```powershell
php spark migrate --all
php spark db:seed AdminUserSeeder
```

### Wdrożenie na serwer

Najlepsza konfiguracja produkcyjna to ustawienie katalogu publicznego domeny bezpośrednio na:

```text
public
```

Wtedy w `.env` ustawiasz:

```ini
CI_ENVIRONMENT = production
app.baseURL = 'https://twojadomena.pl/'
app.indexPage = ''
```

Jeśli hosting nie pozwala ustawić domeny bezpośrednio na katalog `public`, aplikacja może działać przez adres z `/public/`, ale wtedy `app.baseURL` musi zawierać ten fragment, np.:

```ini
app.baseURL = 'https://twojadomena.pl/public/'
```

Przed wdrożeniem:

- wykonaj backup plików i bazy danych;
- ustaw produkcyjne dane bazy;
- wygeneruj unikalny `encryption.key`;
- ustaw poprawne uprawnienia do `writable`;
- skonfiguruj HTTPS;
- wpisz prawdziwe klucze Google, Facebook, Stripe, SMTP i reCAPTCHA, jeśli są używane.

### Główne ścieżki testowe

- `/`
- `/blog`
- `/page/privacy-policy`
- `/login`
- `/auth/login`
- `/general/dashboard`
- `/service/services`
- `/bookings`
- `/clients/clients`
- `/gallery/listGallery`
- `/allpayments/stripe`
- `/layout/social_keys`
- `/updates/main`
- `/account/me`

---

## English

Salon booking management system migrated from an older CodeIgniter version to CodeIgniter 4. The application supports services, agents/staff, bookings, clients, gallery, pages, blog, Stripe payments, mail settings, social login, reCAPTCHA, meta tags, ads, and analytics.

Admin panel:

```text
/auth/login
```

Default admin account:

```text
login: admin1
password: test
```

### Requirements

- PHP 8.2 or newer.
- MySQL or MariaDB.
- Apache with `mod_rewrite` enabled.
- PHP extensions: `intl`, `mbstring`, `mysqli`, `curl`, `fileinfo`.
- Write permissions for `writable`.
- Write permissions for upload directories in `public/uploads` if file uploads are used from the admin panel.

### Important Migration Note

This version no longer uses the browser installer at `/install`. The installer routes are intentionally safe to prevent accidental database overwrites. Configuration is handled through `.env`, and a fresh database can be created with CI4 migrations.

### Local Run with PHP Built-In Server

Go to the main project directory, the one that contains the `spark` file, and run:

```powershell
.\serve-local.ps1
```

The app will be available at:

```text
http://127.0.0.1:8080/public/
```

Use this `.env` setup:

```ini
CI_ENVIRONMENT = development
app.baseURL = 'http://127.0.0.1:8080/public/'
app.indexPage = ''
app.appTimezone = 'Europe/Warsaw'

database.default.hostname = localhost
database.default.database = salon
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
```

### Local Run with XAMPP/htdocs

If the project files are placed directly in `htdocs`, and Apache serves `htdocs` as the main server directory, the app opens at:

```text
http://127.0.0.1/public/
```

Then set this in `.env` in the main project directory:

```ini
CI_ENVIRONMENT = development
app.baseURL = 'http://127.0.0.1/public/'
app.indexPage = ''
app.appTimezone = 'Europe/Warsaw'

database.default.hostname = localhost
database.default.database = salon
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
```

If links still point to the old URL, clear:

```text
writable/cache/
writable/debugbar/
writable/session/
```

### Fresh Database

For an empty database, run from the main project directory:

```powershell
php spark migrate --all
php spark db:seed AdminUserSeeder
```

### Production Deployment

The recommended production setup is to point the domain document root directly to:

```text
public
```

Then set this in `.env`:

```ini
CI_ENVIRONMENT = production
app.baseURL = 'https://your-domain.com/'
app.indexPage = ''
```

If your hosting cannot point the domain directly to the `public` directory, the app can run with `/public/` in the URL, but then `app.baseURL` must include it, for example:

```ini
app.baseURL = 'https://your-domain.com/public/'
```

Before going live:

- create a backup of files and database;
- set production database credentials;
- generate a unique `encryption.key`;
- configure writable permissions;
- enable HTTPS;
- enter real Google, Facebook, Stripe, SMTP, and reCAPTCHA keys if used.

### Main Smoke Test Paths

- `/`
- `/blog`
- `/page/privacy-policy`
- `/login`
- `/auth/login`
- `/general/dashboard`
- `/service/services`
- `/bookings`
- `/clients/clients`
- `/gallery/listGallery`
- `/allpayments/stripe`
- `/layout/social_keys`
- `/updates/main`
- `/account/me`
