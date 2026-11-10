# Backoffice SiGeRU (PHP 8.3 + Apache)
# Versión fijada en una que incluye los parches de seguridad de octubre de 2026
# (php2shell: CVE-2026-109618 y CVE-2026-109619, corregidas en PHP 8.3.36).
# Para actualizar: cambiar PHP_VERSION y reconstruir con "docker compose build --pull".
ARG PHP_VERSION=8.3.36
FROM php:${PHP_VERSION}-apache

RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite headers \
    && sed -ri 's#DocumentRoot /var/www/html#DocumentRoot /var/www/html/public#' /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/html/public>\n    AllowOverride All\n</Directory>\nServerTokens Prod\nServerSignature Off\n' > /etc/apache2/conf-available/sigeru.conf \
    && a2enconf sigeru \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && printf 'expose_php = Off\nsession.use_strict_mode = 1\nsession.cookie_httponly = 1\nsession.gc_maxlifetime = 1800\n; mitigación recomendada por PHP para CVE-2026-115560 (aviso del 23/10/2026)\nsession.upload_progress.enabled = 0\n' > "$PHP_INI_DIR/conf.d/sigeru.ini"

WORKDIR /var/www/html
COPY app/ app/
COPY lang/ lang/
COPY public/ public/
RUN chown -R www-data:www-data /var/www/html

HEALTHCHECK --interval=30s --timeout=5s --retries=3 CMD php -r 'exit(@file_get_contents("http://127.0.0.1/login") === false ? 1 : 0);'
EXPOSE 80
