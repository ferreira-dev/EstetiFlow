<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\CriaCenarioAgendamento;
use Tests\TestCase;

/**
 * Task 2.4 — comodidades configuráveis e upload da foto de capa.
 */
class PaginaPublicaConteudoTest extends TestCase
{
    use RefreshDatabase, CriaCenarioAgendamento;

    private array $cenario;

    protected function setUp(): void
    {
        parent::setUp();
        $this->criarRoles();
        $this->cenario = $this->criarProfissionalComServico();
        Storage::fake('public');
    }

    private function dadosEstabelecimento(array $extra = []): array
    {
        return array_merge([
            'nome_fantasia'      => 'Studio Teste',
            'url_personalizada'  => $this->cenario['estabelecimento']->url_personalizada,
            'agenda_online'      => true,
            'cor_primaria'       => 'pink',
            'telefone_principal' => '11999990000',
            'cep'                => '01001000',
            'logradouro'         => 'Praça da Sé',
            'numero'             => '1',
            'bairro'             => 'Sé',
            'cidade'             => 'São Paulo',
            'estado'             => 'SP',
        ], $extra);
    }

    public function test_comodidades_sao_salvas_e_exibidas_na_pagina_publica(): void
    {
        $this->actingAs($this->cenario['usuario'])
            ->post('/profissional/estabelecimento', $this->dadosEstabelecimento(['caracteristicas' => ['wifi', 'pix']]))
            ->assertSessionHasNoErrors();

        $this->assertSame(['wifi', 'pix'], $this->cenario['estabelecimento']->fresh()->caracteristicas);

        $this->get($this->cenario['estabelecimento']->url_publica)
            ->assertInertia(fn (Assert $page) => $page->where('estabelecimento.caracteristicas', ['wifi', 'pix']));
    }

    public function test_comodidade_desconhecida_e_rejeitada(): void
    {
        $this->actingAs($this->cenario['usuario'])
            ->post('/profissional/estabelecimento', $this->dadosEstabelecimento(['caracteristicas' => ['wifi', 'heliponto']]))
            ->assertSessionHasErrors('caracteristicas.1');
    }

    public function test_sem_comodidades_salva_lista_vazia(): void
    {
        $this->actingAs($this->cenario['usuario'])
            ->post('/profissional/estabelecimento', $this->dadosEstabelecimento())
            ->assertSessionHasNoErrors();

        $this->assertSame([], $this->cenario['estabelecimento']->fresh()->caracteristicas);
    }

    public function test_upload_de_capa_valido_substitui_a_anterior(): void
    {
        $usuario = $this->cenario['usuario'];

        $this->actingAs($usuario)->post('/profissional/estabelecimento/capa', [
            'capa' => UploadedFile::fake()->image('capa1.jpg', 1200, 600),
        ])->assertSessionHasNoErrors();

        $primeira = $this->cenario['estabelecimento']->fresh()->foto_capa_url;
        $this->assertStringStartsWith('/storage/capas/', $primeira);
        Storage::disk('public')->assertExists(substr($primeira, strlen('/storage/')));

        $this->actingAs($usuario)->post('/profissional/estabelecimento/capa', [
            'capa' => UploadedFile::fake()->image('capa2.png', 1200, 600),
        ])->assertSessionHasNoErrors();

        $segunda = $this->cenario['estabelecimento']->fresh()->foto_capa_url;
        $this->assertNotSame($primeira, $segunda);
        Storage::disk('public')->assertMissing(substr($primeira, strlen('/storage/')));
        Storage::disk('public')->assertExists(substr($segunda, strlen('/storage/')));
    }

    public function test_upload_invalido_e_rejeitado(): void
    {
        $usuario = $this->cenario['usuario'];

        $invalidos = [
            UploadedFile::fake()->create('documento.pdf', 100, 'application/pdf'),       // tipo
            UploadedFile::fake()->image('grande.jpg', 1200, 600)->size(3000),            // > 2 MB
            UploadedFile::fake()->image('pequena.jpg', 200, 100),                        // dimensões
        ];

        foreach ($invalidos as $arquivo) {
            $this->actingAs($usuario)
                ->post('/profissional/estabelecimento/capa', ['capa' => $arquivo])
                ->assertSessionHasErrors('capa');
        }

        $this->assertNull($this->cenario['estabelecimento']->fresh()->foto_capa_url);
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    public function test_remover_capa_apaga_o_arquivo(): void
    {
        $usuario = $this->cenario['usuario'];
        $this->actingAs($usuario)->post('/profissional/estabelecimento/capa', [
            'capa' => UploadedFile::fake()->image('capa.webp', 1200, 600),
        ]);

        $this->actingAs($usuario)->delete('/profissional/estabelecimento/capa')->assertSessionHasNoErrors();

        $this->assertNull($this->cenario['estabelecimento']->fresh()->foto_capa_url);
        $this->assertEmpty(Storage::disk('public')->allFiles());
    }

    public function test_cliente_nao_envia_capa(): void
    {
        $this->actingAs($this->criarCliente())->post('/profissional/estabelecimento/capa', [
            'capa' => UploadedFile::fake()->image('capa.jpg', 1200, 600),
        ])->assertForbidden();
    }
}
