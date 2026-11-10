<?php

namespace Tests\Unit;

use App\Core\Csrf;
use App\Core\Sesion;
use PHPUnit\Framework\TestCase;

class SeguridadTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testElTokenCsrfSeGeneraUnaVezPorSesion(): void
    {
        $token = Csrf::token();
        $this->assertSame(64, strlen($token));
        $this->assertSame($token, Csrf::token());
        $this->assertStringContainsString($token, Csrf::campo());
    }

    public function testSoloAceptaElTokenDeLaSesion(): void
    {
        $token = Csrf::token();
        $this->assertTrue(Csrf::valido($token));
        $this->assertFalse(Csrf::valido('otro-token'));
        $this->assertFalse(Csrf::valido(null));
        $_SESSION = [];
        $this->assertFalse(Csrf::valido($token));
    }

    public function testElAvisoSeMuestraUnaSolaVez(): void
    {
        Sesion::aviso('Cambios guardados.');
        $this->assertSame(['texto' => 'Cambios guardados.', 'tipo' => 'ok'], Sesion::aviso());
        $this->assertNull(Sesion::aviso());
    }

    public function testLasVistasEscapanHtml(): void
    {
        $this->assertSame('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', e('<script>alert("x")</script>'));
        $this->assertSame('', e(null));
    }
}
