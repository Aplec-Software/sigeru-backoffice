<?php

namespace Tests\Support;

use App\Core\Clock;
use App\Core\Database;
use App\Core\Params;
use PDO;

/**
 * Antes de cada prueba se crea una base SQLite en memoria con la estructura
 * y los datos de prueba, y se limpia la sesión de PHP.
 */
abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected PDO $db;

    protected function setUp(): void
    {
        $dir = dirname(__DIR__) . '/fixtures';
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        foreach (['schema.sqlite.sql', '02_datos_base.sql', '03_datos_prueba.sql'] as $archivo) {
            $this->db->exec(file_get_contents("$dir/$archivo"));
        }
        Database::setConexion($this->db);
        Params::limpiar();
        Clock::fijar('2026-10-30 10:00:00');
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        Database::setConexion(null);
        Clock::fijar(null);
    }

    protected function contar(string $tabla, string $where = '1 = 1'): int
    {
        return (int) $this->db->query("SELECT COUNT(*) FROM $tabla WHERE $where")->fetchColumn();
    }
}
