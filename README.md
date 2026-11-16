# Sitio web de Laura Rivera

Sitio oficial de Laura Rivera (empresaria) con noticias, biografía, propuestas, registro de voluntarios, aportes vía Wompi, panel de administración con roles y métricas propias.

> **Regla de contenido:** mientras no sea oficial, Laura se presenta como **empresaria**. No usar "candidata" ni "campaña" en textos del sitio. Hay una prueba automática que falla si "candidata" o "candidato" aparecen en las páginas públicas.

## Stack

- PHP 8.3+ y Laravel 13, vistas Blade renderizadas en el servidor (bueno para Google y para asistentes de IA).
- MariaDB 11.8 en producción (las pruebas corren también en SQLite).
- CSS y JS planos en `public/`: **no se necesita Node ni compilar nada** para desplegar.
- `spatie/laravel-permission` para roles y permisos.

## Secciones

| Público | Panel (`/admin`) |
|---|---|
| `/` inicio, `/quien-es-laura`, `/propuestas`, `/noticias` | Inicio con tarjetas según el rol |
| `/voluntarios` registro | Noticias (marketing) |
| `/donar` aportes con Wompi | Voluntarios (administrador) |
| `/terminos-y-condiciones`, `/politica-de-privacidad` | Donaciones (contabilidad) |
| `/sitemap.xml`, `/robots.txt`, `/llms.txt` | Métricas (marketing) |
| | Usuarios y Roles (administrador) |

Roles iniciales: **administrador** (todo), **contabilidad** (donaciones y exportación) y **marketing** (noticias y métricas). Desde *Roles y permisos* se pueden crear roles nuevos combinando permisos.

## Desarrollo local

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed          # crea tablas, roles y noticias de ejemplo
php artisan app:crear-admin         # pide nombre, correo y contraseña
php artisan storage:link
php artisan serve
```

Pruebas:

```bash
php artisan test                    # SQLite en memoria
```

La integración continua (`.github/workflows/tests.yml`) corre la suite en SQLite y en MariaDB 11.8.

## Despliegue en Hostinger (hosting compartido)

Requisitos del plan: PHP 8.3+, Composer, acceso SSH y una base de datos MariaDB.

### Primera vez

1. **Base de datos:** en hPanel → *Bases de datos MySQL*, crear base, usuario y contraseña.
2. **Código:** por SSH, clonar el repositorio **fuera** de `public_html`, por ejemplo:
   ```bash
   cd ~/domains/<dominio>
   git clone <url-del-repo> laura-rivera
   cd laura-rivera
   composer install --no-dev --optimize-autoloader
   cp .env.example .env
   php artisan key:generate
   ```
3. **Configurar `.env`** (ver tabla abajo). Como mínimo: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://<dominio>`, `DB_*` y `WOMPI_*`.
4. **Migraciones y datos iniciales:**
   ```bash
   php artisan migrate --force --seed
   php artisan app:crear-admin
   php artisan storage:link
   ```
5. **Carpeta pública:** el sitio debe servirse desde `laura-rivera/public`. En Hostinger lo más simple es reemplazar `public_html` por un enlace:
   ```bash
   cd ~/domains/<dominio>
   mv public_html public_html_respaldo
   ln -s laura-rivera/public public_html
   ```
6. **Optimizar:**
   ```bash
   cd ~/domains/<dominio>/laura-rivera
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```
7. **Wompi:** en el panel de Wompi → *Desarrolladores*, configurar la URL de eventos `https://<dominio>/webhooks/wompi` y copiar las llaves al `.env`. Probar primero en sandbox.
8. **Cron (opcional):** en hPanel → *Cron jobs*, cada minuto:
   `cd ~/domains/<dominio>/laura-rivera && php artisan schedule:run >> /dev/null 2>&1`
   Sirve para borrar métricas antiguas a diario.
9. **Google Search Console:** agregar el dominio y enviar `https://<dominio>/sitemap.xml`.

### Actualizaciones

```bash
cd ~/domains/<dominio>/laura-rivera
php artisan down
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --class=RolesAndPermissionsSeeder --force   # solo si hay permisos nuevos (es idempotente)
php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

### Variables de entorno

| Variable | Para qué |
|---|---|
| `APP_URL` | Dominio con `https://`. Se usa en el sitemap, enlaces canónicos y datos estructurados. |
| `DB_CONNECTION=mariadb`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` | Base de datos. |
| `TRUSTED_PROXIES` | Vacío por defecto. Si el sitio queda detrás de Cloudflare o un CDN, poner sus IPs (o `*` si todo el tráfico pasa por él) para que las métricas registren la IP real del visitante. |
| `WOMPI_ENV` | `sandbox` o `production`. |
| `WOMPI_PUBLIC_KEY`, `WOMPI_PRIVATE_KEY`, `WOMPI_INTEGRITY_SECRET`, `WOMPI_EVENTS_SECRET` | Llaves de Wompi. Sin la llave pública, `/donar` muestra "disponibles muy pronto". |
| `DONATION_MIN_AMOUNT`, `DONATION_MAX_AMOUNT` | Límites del aporte en pesos. |
| `METRICS_ENABLED` | `true`/`false` para activar el registro de visitas. |
| `METRICS_RETENTION_DAYS` | Días que se conservan visitas y clics (400 por defecto). |

### Contenido editable sin panel

Biografía, trayectoria, propuestas, barrios, tipos de documento y montos sugeridos están en `config/laura.php`. Tras editarlo en producción: `php artisan config:cache`.

## Métricas: cómo funcionan

- Cada página vista del sitio público queda registrada con su origen (asistentes de IA, buscadores, redes, campañas `utm`, otros sitios o directo), IP, dispositivo y navegador. Los rastreadores (GPTBot, ClaudeBot, Googlebot…) se cuentan aparte.
- Clics, desplazamiento y tiempo en página solo se registran si el visitante pulsa **Aceptar** en el aviso de cookies.
- Para medir una campaña, comparta enlaces con `?utm_source=instagram&utm_medium=bio&utm_campaign=nombre`.
- El panel no cuenta las visitas de usuarios del equipo con sesión iniciada.
