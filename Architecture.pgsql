       ┌─────────────────────────────────────────────────┐
                                   │                    Internet                     │
                                   │  (Visiteurs, Tatoueurs, Admin)                  │
                                   └─────────────────────────────────────────────────┘
                                                     │     HTTPS
                                                     ▼
                               ┌───────────────────────────────────────────────┐
                               │                   FRONT                      │
                               │                                               │
                               │  Dev:  React + Vite Dev Server (Node 20)      │
                               │       - Live reload                            │
                               │       - VITE_API_URL=http://api:80            │
                               │                                               │
                               │  Prod:  Nginx (statique)                      │
                               │       - Fichiers /dist de React               │
                               │       - Reverse proxy/API via domaine         │
                               └───────────────────────────────────────────────┘
                                                     │    HTTP/JSON (fetch)
                                                     ▼
┌───────────────────────────────────────────────────────────────────────────────────────────────┐
│                                           API                                                 │
│                                                                                               │
│  Conteneur PHP 8.2 + Apache (Symfony)                                                         │
│  ──────────────────────────────────────────────────────────────────────────────────────────   │
│  • Bundles applicatifs :                                                                     │
│      - EasyAdmin (back-office CRUD)    - VichUploader (uploads)                              │
│      - Doctrine ORM + Migrations       - Security (auth, rôles)                              │
│      - Mailer / Reset-Password         - Serializer (groups)                                 │
│  • Code (src/) + config/ + public/ + vendor/                                                 │
│  • entrypoint.sh (ordre de boot) :                                                           │
│      1) Attente MySQL (connexion réelle)                                                     │
│      2) Composer install si besoin                                                           │
│      3) Création BDD si absente                                                              │
│      4) Migrations ou Import SQL (si backup présent)                                         │
│      5) Clear cache Symfony                                                                  │
│      6) apache2-foreground                                                                   │
│  • Variables env : APP_ENV, APP_DEBUG, DATABASE_URL, MAILER_DSN, VITE_API_URL (front)        │
│                                                                                               │
│                              │                                           │                    │
│                              │ Doctrine                                 │ Mail               │
│                              ▼                                           ▼                    │
│       ┌──────────────────────────────┐                      ┌───────────────────────────┐     │
│       │            MySQL             │◄───────────────►     │     SMTP (ex: provider)  │     │
│       │  - Base le_21                │   Import/Export      │  - Envoi d’invitations   │     │
│       │  - Volumes persistants       │   mysqldump          │    et réinitialisation   │     │
│       └──────────────────────────────┘                      └───────────────────────────┘     │
│                                                                                               │
│  Stockage médias                                                                               │
│  ──────────────────────────────────────────────────────────────────────────────────────────   │
│  • Dossier uploads/ monté en volume Docker (persistance)                                       │
│  • VichUploader gère fileName / chemins                                                        │
│  • Media → utilisé par Carrousel, Flash, Article (réutilisation)                               │
└───────────────────────────────────────────────────────────────────────────────────────────────┘


                                 ┌───────────────────────────────────────────┐
                                 │                 Docker                    │
                                 │     (réseau interne + volumes)            │
                                 │                                           │
                                 │  Services (docker-compose.yml) :          │
                                 │   - front   (Node20 dev  | Nginx prod)    │
                                 │   - api     (php:8.2-apache + entrypoint) │
                                 │   - db      (mysql:8.0 + healthcheck)     │
                                 │                                           │
                                 │  Volumes :                                │
                                 │   - db_data (données MySQL)               │
                                 │   - uploads (médias)                      │
                                 │   - backups (read-only pour import SQL)   │
                                 │                                           │
                                 │  Réseau : le21_net (API⇄DB⇄Front interne) │
                                 └───────────────────────────────────────────┘