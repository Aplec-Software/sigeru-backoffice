# SiGeRU - Backoffice

Aplicación web de administración para el personal municipal (solo usuarios con rol **administrador**).
Es un monolito PHP 8 con MVC que se conecta directamente a la base de datos, como pide la letra.

## Qué permite hacer

- Panel con indicadores, mapa de incidencias activas y monitoreo que se actualiza cada 20 segundos.
- Usuarios y roles: alta, edición, aprobación de solicitudes de registro, bloqueo y contraseña temporal.
- Incidencias: listado con filtros, asignación a cuadrillas y cambio de estado con historial.
- Reportes estadísticos (incidencias por zona, tiempos de resolución, volumen recolectado, ocupación de centros,
  uso de la flota, rendimiento de cuadrillas) con gráfico y exportación a CSV.
- Parámetros del sistema y catálogos (zonas y tipos de residuo) sin tocar el código.
- Auditoría de las acciones sensibles de todos los servicios.

## Seguridad

- Sesión de PHP en el servidor: cookie `SIGERU_BO` HttpOnly, SameSite=Strict y Secure; vence a los 30 minutos sin uso
  y el identificador se regenera al iniciar sesión.
- Token CSRF en todos los formularios.
- Consultas preparadas (PDO) y escape de todo lo que se imprime (`e()`), contra inyección SQL y XSS.
- Mismas reglas de validación y política de contraseñas que las APIs; bloqueo por intentos fallidos.
- Usuario de MySQL `backoffice` con permisos solo sobre lo que necesita.

## Estructura

```
app/Core          sesión, CSRF, vistas, router y el núcleo compartido con las APIs
app/Controllers   un controlador por pantalla
app/Models        consultas a la base
app/Views         plantillas PHP (layout + una por pantalla)
lang/             textos en español e inglés
public/           index.php, css, js (monitoreo y mapa) e imágenes
tests/            pruebas (PHPUnit, base SQLite en memoria)
```

## Variables de entorno

`DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER=backoffice`, `DB_PASS`, `BASE_PATH=/backoffice`, `COOKIE_SEGURA=1`, `APP_IDIOMA=es`.

## Pruebas

```bash
composer install
composer test
```

Se publica en `https://<servidor>/backoffice/` a través del gateway de `sigeru-infra`.

## Docker

Se construye con su `Dockerfile` y se levanta junto con el resto desde `sigeru-infra` (`docker compose up -d --build`).
La imagen fija la versión de PHP con `ARG PHP_VERSION=8.3.36` (`FROM php:${PHP_VERSION}-apache`), la primera que corrige
las vulnerabilidades «php2shell» (CVE-2026-109618 y CVE-2026-109619, octubre de 2026). El `php.ini` incluye
`session.upload_progress.enabled = 0`, la mitigación que recomendó PHP para CVE-2026-115560 hasta su parche. Para
actualizar a una versión nueva de PHP: cambiar `PHP_VERSION`, reconstruir con `docker compose build --pull` y subir
`PHP_MINIMO` en el servidor (ver `sigeru-infra`).
