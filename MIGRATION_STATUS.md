# Status migracji

## Etap 1

Zrobione:

- Przeniesiono kod aplikacyjny do `NEWCODEINTEGER/application`.
- Przeniesiono zasoby publiczne do `NEWCODEINTEGER/public`.
- Przeniesiono główny `.htaccess`.
- Zachowano nowy rdzeń frameworka w `NEWCODEINTEGER/system` bez zmian.
- Scalono najważniejsze ustawienia: routing, autoload, stałe aplikacji, bazę danych, `base_url`, klucz szyfrowania i sesje.
- Dodano katalog sesji `application/cache/sessions`.
- Usunięto domyślne pliki startowe `Welcome`, ponieważ aplikacja używa `Homepage`.
- Zamieniono niezgodne z CI3 `esc()` w widokach błędów na `html_escape()`.
- W trybie developerskim wyciszono ostrzeżenia `E_DEPRECATED` z PHP 8.2, żeby nie przykrywały właściwych błędów aplikacji.

Sprawdzone:

- `NEWCODEINTEGER/system` zgłasza CodeIgniter 3.1.13.
- Wszystkie pliki PHP w `NEWCODEINTEGER` przechodzą kontrolę składni na PHP 8.2.12.
- Uruchomienie CLI renderuje stronę główną z danymi z bazy `salon`.
- Katalog instalacyjny SQL jest przeniesiony: `application/views/install/includes/sql/latest.sql`.
- Lokalna baza została ustawiona na `localhost`, użytkownik `root`, puste hasło, baza `salon`.
- Lokalny `base_url` został ustawiony na `http://127.0.0.1/`.
- Strona główna, logowanie i blog odpowiadają statusem `200` na lokalnym serwerze testowym.
- Panel admina i sekcja usług zwracają `307`, czyli aplikacja poprawnie kieruje je przez mechanizm logowania/uprawnień.
- Poprawiono brakujące domyślne dane Stripe w widokach publicznych.
- Poprawiono obsługę niedostępnych danych aktualizacji, żeby brak odpowiedzi zewnętrznego endpointu nie generował ostrzeżeń PHP.
- Poprawiono zapytanie logowania administratora, usuwając ręcznie składany warunek SQL.
- Po poprawkach test tras `/`, `/login`, `/login/signUp`, `/blog`, `/page/privacy-policy`, `/admin` i `/service` nie dopisał nowych błędów do logów.
- Domyślne konto administratora w instalacyjnym SQL: login `admin1`, hasło `test`.
- Hasło `test` zostało ustawione również w lokalnej bazie `salon` dla konta `admin1`.
- Logowanie admina `admin1` / `test` działa i przekierowuje do panelu.
- Po zalogowaniu sprawdzono główne ekrany panelu: dashboard, ustawienia, usługi, dodawanie usługi, rezerwacje, klienci, galeria, kategorie galerii, płatności, Stripe, strony, e-mail, social keys, reCAPTCHA, meta tags, reklamy, analityka i komentarze.
- Wszystkie sprawdzone ekrany panelu zwróciły `200` bez błędów PHP i bez nowych wpisów w logach.
- Sprawdzono podstawowe zasoby panelu i motywu: CSS, JS, obrazki i pliki publiczne są dostępne pod `/NEWCODEINTEGER/`.

## Następny etap

- Przejść formularze mutujące dane: dodanie/edycja usługi, rezerwacja testowa, zmiana ustawień, upload grafiki.
- Sprawdzić przepływ użytkownika końcowego: rejestracja/logowanie klienta, wybór usługi, data/godzina, zapis rezerwacji.
- Naprawić błędy runtime wynikające z PHP 8.2, nowszego CI i realnych danych.

## Kontrola po aktualizacji folderu

- Folder `NEWCODEINTEGER` nie istnieje już jako osobny katalog; aktywny projekt znajduje się w katalogu głównym repozytorium.
- Root projektu ma CodeIgniter 3.1.13.
- `base_url` został dostosowany do root: `http://127.0.0.1/`.
- Konfiguracja bazy nadal wskazuje na `localhost`, `root`, puste hasło i bazę `salon`.
- Pełny lint PHP dla projektu przechodzi bez błędów.
- Runtime jest obecnie zablokowany, ponieważ MySQL/MariaDB nie działa na `localhost`; aplikacja zwraca błąd połączenia z bazą.
