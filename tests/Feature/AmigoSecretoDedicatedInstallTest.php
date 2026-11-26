<?php

namespace Tests\Feature;

use App\Models\Rifas\Rifa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AmigoSecretoDedicatedInstallTest extends TestCase
{
    use RefreshDatabase;

    public function test_raffle_and_pix_routes_are_unavailable_by_default_and_keep_existing_data(): void
    {
        $rifa = Rifa::create(['titulo' => 'Rifa de teste', 'valor' => 10]);
        $nome = $rifa->nomes()->create(['nome' => 'Número de teste']);

        $this->get('/')->assertOk();
        $this->get('/meu-amigo')->assertOk();
        foreach ([
            ['GET', '/rifas'],
            ['GET', '/rifas/pix/'.$nome->id],
            ['GET', '/rifas/pagar/'.$nome->id],
            ['POST', '/rifas/confirmar/'.$nome->id],
            ['POST', '/comprar/'.$nome->id],
            ['GET', '/admin/rifas/pendentes'],
            ['POST', '/admin/rifas/pagar/'.$nome->id],
            ['DELETE', '/crud/rifas/'.$rifa->id],
        ] as [$method, $url]) {
            $this->call($method, $url)->assertNotFound();
        }

        $this->assertModelExists($rifa);
        $this->assertModelExists($nome);
    }

    public function test_pwa_assets_use_https_only_when_forwarded_by_a_trusted_proxy(): void
    {
        config(['trustedproxy.proxies' => '192.0.2.10']);

        $this->withHeader('X-Forwarded-Proto', 'https')->withServerVariables(['REMOTE_ADDR' => '192.0.2.10'])
            ->get('http://amigo.example.test/meu-amigo')
            ->assertOk()->assertSee('https://amigo.example.test/manifest.webmanifest');
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.99'])
            ->get('http://amigo.example.test/meu-amigo')
            ->assertOk()->assertSee('http://amigo.example.test/manifest.webmanifest');
    }
}
