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

### Actualizaciones manuales

```bash
cd ~/domains/laurarivera.com.co/laura-rivera
php artisan down
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --class=RolesAndPermissionsSeeder --force   # solo si hay permisos nuevos (es idempotente)
php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

## Despliegue automático (CI/CD)

Hay dos repositorios:

- **`laura-rivera`** (este): código fuente. Aquí se desarrolla y se crean los *tags* de versión.
- **`laura-rivera-produccion`**: artefacto listo para producción. Incluye `vendor/` ya instalado y optimizado, para que Hostinger despliegue con un simple `git pull` (sin Composer pesado ni Node).

> **Sobre los assets:** el sitio usa CSS y JS planos en `public/` por diseño (así Hostinger no necesita Node). Por eso el pipeline **no** corre `vite build`: no hay nada que compilar. La "compilación" aquí es instalar `vendor/` optimizado sin dependencias de desarrollo. Si algún día las plantillas pasan a usar `@vite`, hay un paso de Vite listo para descomentar en `deploy.yml`.

Flujo completo:

```
tag v1.2.3 en laura-rivera
        │  (.github/workflows/deploy.yml)
        ▼
GitHub Actions: composer install --no-dev --optimize-autoloader
        │  publica el resultado (push a main)
        ▼
laura-rivera-produccion (main)
        │  (.github/workflows/deploy-hostinger.yml)
        ▼
GitHub Actions entra por SSH a Hostinger:
git reset --hard + migrate --force + seed roles + cache + up
        ▼
Sitio actualizado en https://laurarivera.com.co
```

### Publicar una versión

```bash
git tag v1.2.3      # usar versionado semántico
git push origin v1.2.3
```

Eso dispara todo el proceso. También se puede lanzar a mano desde la pestaña
**Actions → Build & Deploy (producción) → Run workflow** (compila el estado
actual de `main`, sin crear tag).

### Secretos a configurar (una sola vez)

**En `laura-rivera` → Settings → Secrets and variables → Actions:**

| Secreto | Qué es |
|---|---|
| `PRODUCCION_DEPLOY_TOKEN` | Token de GitHub con permiso de escritura (*Contents: Read and write*) sobre `laura-rivera-produccion`. Lo más limpio es un **Fine-grained personal access token** limitado a ese único repositorio. Permite que el workflow publique el artefacto. |

**En `laura-rivera-produccion` → Settings → Secrets and variables → Actions:**

| Secreto | Qué es / ejemplo |
|---|---|
| `HOSTINGER_SSH_HOST` | IP o host SSH de Hostinger (hPanel → *SSH access*). |
| `HOSTINGER_SSH_PORT` | Puerto SSH de Hostinger (normalmente `65002`). |
| `HOSTINGER_SSH_USER` | Usuario SSH (p. ej. `u123456789`). |
| `HOSTINGER_SSH_KEY` | **Clave privada** SSH (contenido completo del archivo, no la pública). |
| `HOSTINGER_DEPLOY_PATH` | Ruta del proyecto en Hostinger, p. ej. `~/domains/laurarivera.com.co/laura-rivera`. |
| `HOSTINGER_PHP_BIN` | *(Opcional)* Ruta a PHP si `php` no es 8.3 en SSH, p. ej. `/usr/bin/php8.3`. |
| `HOSTINGER_SSH_KNOWN_HOSTS` | *(Opcional, recomendado)* Línea de `known_hosts` del servidor para fijar su huella. Si se deja vacío, el workflow la descubre automáticamente (menos seguro). |

> Ningún secreto se guarda en el código. Si falta `PRODUCCION_DEPLOY_TOKEN` o los `HOSTINGER_SSH_*`, el workflow correspondiente falla con un mensaje claro.

> **Nota sobre repos privados:** como `laura-rivera-produccion` es privado, Hostinger necesita además un token de **solo lectura** para bajar el código (ver *Pasos manuales en Hostinger → paso 3*). Ese token **no** es un secreto de GitHub Actions: se usa una sola vez al clonar y queda guardado en el `.git/config` del servidor. Son dos tokens distintos: uno de **escritura** (`PRODUCCION_DEPLOY_TOKEN`, para que el repo fuente publique) y otro de **lectura** (en el servidor, para bajar).

### Pasos manuales en GitHub

1. Crear el token fino: **GitHub → Settings (tu cuenta) → Developer settings → Personal access tokens → Fine-grained tokens → Generate**. *Resource owner*: tu cuenta; *Repository access*: solo `laura-rivera-produccion`; *Permissions*: `Contents` → *Read and write*. Copiar el token y pegarlo como `PRODUCCION_DEPLOY_TOKEN` en los secretos de `laura-rivera`.
2. Generar un par de llaves SSH para el despliegue (en tu máquina):
   ```bash
   ssh-keygen -t ed25519 -C "deploy-laura" -f deploy_key -N ""
   ```
   - La **pública** (`deploy_key.pub`) se agrega en Hostinger (paso siguiente).
   - La **privada** (`deploy_key`) se pega como `HOSTINGER_SSH_KEY` en los secretos de `laura-rivera-produccion`.
3. Llenar el resto de secretos SSH de `laura-rivera-produccion` con los datos que da hPanel en *SSH access*.

### Pasos manuales en Hostinger (primera vez)

1. **Activar SSH:** hPanel → *Avanzado → SSH access* → activar y anotar host, puerto y usuario.
2. **Autorizar la llave de despliegue:** pegar el contenido de `deploy_key.pub` en *SSH access → Manage SSH keys* (o por SSH en `~/.ssh/authorized_keys`).
3. **Clonar el repo de producción** (incluye `vendor/`, no hace falta Composer).
   Como `laura-rivera-produccion` es **privado**, el servidor necesita leerlo con
   un token de **solo lectura**. Crea un *Fine-grained personal access token*
   aparte (**GitHub → Settings → Developer settings → Fine-grained tokens**):
   *Repository access* solo `laura-rivera-produccion`, *Permissions* → `Contents`
   = **Read-only**. Luego, con ese token en la URL:
   ```bash
   cd ~/domains/laurarivera.com.co
   git clone https://x-access-token:<TOKEN_SOLO_LECTURA>@github.com/ronaldo-duran/laura-rivera-produccion.git laura-rivera
   cd laura-rivera
   cp .env.example .env
   php artisan key:generate
   # editar .env: APP_ENV=production, APP_DEBUG=false,
   # APP_URL=https://laurarivera.com.co, DB_* y WOMPI_*
   php artisan migrate --force --seed
   php artisan app:crear-admin
   php artisan storage:link
   ```
   El token queda guardado en el `.git/config` del servidor, así que los
   despliegues siguientes (`git reset --hard origin/main` que hace el workflow)
   funcionan solos. Si el token caduca, los despliegues fallan al bajar el
   código: basta volver a poner un token nuevo en la URL del remoto con
   `git remote set-url origin https://x-access-token:<TOKEN_NUEVO>@github.com/ronaldo-duran/laura-rivera-produccion.git`.
4. **Carpeta pública:** `public_html` debe apuntar a `laura-rivera/public`:
   ```bash
   cd ~/domains/laurarivera.com.co
   mv public_html public_html_respaldo
   ln -s laura-rivera/public public_html
   ```
5. A partir de aquí, cada `tag` nuevo actualiza el sitio solo. El `.env` de
   Hostinger nunca se toca (está en `.gitignore`), así que las llaves y la BD
   se conservan entre despliegues.

> **Si Hostinger no tiene SSH** (algunos planes compartidos): usar la opción de
> *Git* de hPanel apuntando a `laura-rivera-produccion` con *auto-deployment*
> activado. Eso trae los archivos, pero **no corre migraciones**; en ese caso
> habría que ejecutar `php artisan migrate --force` manualmente o por *cron*.
> Por eso se recomienda SSH, que es lo que permite las migraciones automáticas.

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
