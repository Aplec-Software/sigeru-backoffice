<?php

namespace App\Core;

/**
 * Auditoría de las acciones hechas desde el backoffice (misma tabla que las APIs).
 */
class Audit
{
    public static function registrar(string $accion, string $entidad, int|string|null $id = null, ?string $detalle = null): void
    {
        $ip = filter_var($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '', FILTER_VALIDATE_IP) ?: null;
        Database::conexion()->prepare('INSERT INTO auditoria (usuario_id, servicio, accion, entidad, entidad_id, detalle, ip, creado_en) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute([Sesion::usuario()['id'] ?? null, 'backoffice', $accion, $entidad, $id === null ? null : (string) $id,
                $detalle === null ? null : mb_substr($detalle, 0, 500), $ip, Clock::ahora()]);
    }
}
