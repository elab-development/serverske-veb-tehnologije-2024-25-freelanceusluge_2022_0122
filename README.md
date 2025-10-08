 
Opis
-----
Web aplikacija (Laravel) koja spaja klijente i freelancere.
Klijenti objavljuju projekte, freelanceri daju ponude (bidove),
a nakon prihvatanja ponude nastaje angažman (engagement).

Glavne funkcionalnosti
----------------------
- Registracija/Login (Sanctum token), profil sa uploadom avatara/banera
- Projekti: listanje + filteri, kreiranje, izmena, brisanje
- Ponude (bids): kreiranje, izmena, povlačenje, prihvatanje
- Angažmani: kreiranje, ažuriranje, završetak/otkazivanje
- Skillovi: katalog + dodela profilu
- HIBP provera lozinke prilikom registracije (range API)

Tehnologije
-----------
- PHP 8+, Laravel 10+, MySQL/MariaDB (ili SQLite za test)
- Laravel Sanctum (auth), Eloquent ORM, Laravel Storage (public disk)
- Postman za testiranje API-ja

Instalacija i pokretanje
------------------------
1) Kloniraj repo i instaliraj zavisnosti:
   composer install

2) Napravi .env i postavi DB kredencijale (primer):
   cp .env.example .env
   php artisan key:generate
   # .env -> DB_DATABASE, DB_USERNAME, DB_PASSWORD
   # Za lokalni storage link:
   php artisan storage:link

3) Migracije i seed:
   php artisan migrate --seed

4) Pokreni lokalni server:
   php artisan serve
   # -> http://127.0.0.1:8000

Napomena o fajlovima
--------------------
- Upload slika ide na disk "public" (storage/app/public).
- URL do slika (avatar_url, banner_url) radi uz: php artisan storage:link

Autentifikacija
---------------
- Nakon uspešnog logina/registracije dobija se Bearer token.
- U zaštićenim rutama koristi header:
  Authorization: Bearer <token>

Primeri ruta (osnovno)
----------------------
Auth:
- POST /api/auth/register        (multipart/form-data ako šalješ avatar/banner)
- POST /api/auth/login
- GET  /api/auth/me
- POST /api/auth/logout

Projekti:
- GET    /api/projects?status=open&client_id=1&q=api&min_budget=100&max_budget=2000&tag=react&mine=1
- POST   /api/projects
- GET    /api/projects/{id}
- PATCH  /api/projects/{id}
- DELETE /api/projects/{id}

Ponude (bids):
- GET    /api/bids?project_id=10&status=pending&mine=1
- POST   /api/projects/{projectId}/bids
- GET    /api/bids/{bidId}
- PATCH  /api/bids/{bidId}
- DELETE /api/bids/{bidId}
- POST   /api/bids/{bidId}/withdraw
- POST   /api/bids/{bidId}/accept

Angažmani:
- GET    /api/engagements?project_id=&provider_id=&client_id=&state=
- POST   /api/engagements
- GET    /api/engagements/{id}
- PATCH  /api/engagements/{id}
- DELETE /api/engagements/{id}
- POST   /api/engagements/{id}/complete
- POST   /api/engagements/{id}/cancel

Skillovi:
- GET    /api/skills?q=react
- POST   /api/skills
- GET    /api/skills/{id}
- PATCH  /api/skills/{id}
- DELETE /api/skills/{id}
- POST   /api/skills/{id}/attach   (body: {"profile_id": X})
- DELETE /api/skills/{id}/detach   (body: {"profile_id": X})



