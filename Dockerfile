# =============================================================================
# Stage 1: Composer dependencies
# =============================================================================
FROM composer:2.9 AS composer-deps

WORKDIR /inspektor

# Copy only composer files first to leverage layer caching
COPY composer.json composer.lock ./

# Install production dependencies only, no scripts, no autoloader yet
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --no-interaction \
    --prefer-dist \
    --ignore-platform-reqs

# Copy the rest of the source and dump optimised autoloader.
# --no-scripts prevents Laravel's post-autoload-dump hooks (package:discover,
# ComposerScripts, etc.) from firing at build time — the framework cannot boot
# without a real .env / database, so those hooks must run at container startup.
COPY . .
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev --no-scripts


# =============================================================================
# Stage 2: Node / front-end assets
# =============================================================================
FROM node:24-alpine AS node-assets

WORKDIR /inspektor

# Copy package files and install deps (cached unless lockfile changes)
COPY package.json package-lock.json* yarn.lock* ./
RUN npm ci --ignore-scripts

# Copy source and build
COPY . .

# Some packages (e.g. livewire/flux) ship CSS/assets inside vendor/.
# Vite resolves those paths at build time, so vendor must be present in this
# stage even though it is a PHP dependency. We copy it from the composer stage
# rather than re-running composer install here.
COPY --from=composer-deps /inspektor/vendor ./vendor

RUN npm run build


# =============================================================================
# Stage 3: Final production image
# =============================================================================
# serversideup/php is a Laravel-optimised image maintained by the Laravel
# community. It ships with:
#   - Nginx + PHP-FPM pre-configured and managed by s6-overlay (no Supervisor)
#   - All common Laravel extensions pre-installed: pdo_pgsql, pgsql, redis,
#     opcache, bcmath, mbstring, intl, zip, gd, pcntl, and more
#   - Sensible production php.ini and FPM pool defaults
#   - A non-root "webuser" already set up
FROM serversideup/php:8.4-fpm-nginx AS production

LABEL maintainer="angsss@net.hr" \
    org.opencontainers.image.title="Inspektor" \
    org.opencontainers.image.description="Inspektor app for inspekting" \
    org.opencontainers.image.source="https://github.com/markoangelovski/content_inspector"

# serversideup/php is configured entirely via environment variables.
# OPcache is off by default; enable it here so it is baked into the image.
# All other tuning (memory, upload limits, etc.) can be overridden at runtime
# via env vars in docker-compose.yml or your orchestrator.
ENV PHP_OPCACHE_ENABLE=1 \
    PHP_OPCACHE_MEMORY_CONSUMPTION=256 \
    PHP_OPCACHE_MAX_ACCELERATED_FILES=20000 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0 \
    PHP_OPCACHE_REVALIDATE_FREQ=0 \
    PHP_OPCACHE_SAVE_COMMENTS=1

# Root is required to place the hook script and fix permissions
USER root

# Bootstrap hook:
# serversideup/php uses s6-overlay as its init system. Overriding ENTRYPOINT
# would bypass s6, causing the container to exit immediately (bootloop).
# Scripts in /etc/entrypoint.d/ are sourced by the image's own entrypoint
# before s6 brings up Nginx and PHP-FPM, so the container stays alive.
COPY docker/entrypoint.sh /etc/entrypoint.d/00-bootstrap.sh
RUN chmod +x /etc/entrypoint.d/00-bootstrap.sh

WORKDIR /var/www/html

# Vendor from composer stage
COPY --from=composer-deps --chown=www-data:www-data /inspektor/vendor ./vendor

# Front-end build artefacts from node stage
COPY --from=node-assets --chown=www-data:www-data /inspektor/public/build ./public/build

# Application code
COPY --chown=www-data:www-data . .

# Fix storage permissions AFTER all COPYs.
# COPY . . may bring in a storage/ directory from the repo with root ownership
# or restrictive permissions, so we always re-apply the correct ownership and
# mode in a single layer after all source has been placed.
RUN mkdir -p storage/framework/{cache,sessions,views} \
    storage/logs \
    bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwX            storage bootstrap/cache

# Drop back to non-root for runtime. serversideup/php also enforces this
# internally via s6-overlay, but setting it here ensures the correct user
# is applied even if the container is run without the s6 init system.
USER www-data

# serversideup/php listens on 8080 (non-root safe port)
EXPOSE 8080

# Health-check against Laravel's built-in /up endpoint
HEALTHCHECK --interval=30s --timeout=10s --start-period=30s --retries=3 \
    CMD curl -fsSL http://localhost:8080/up || exit 1