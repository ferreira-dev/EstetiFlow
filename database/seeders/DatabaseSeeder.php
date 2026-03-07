<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Perfil;
use App\Models\Estabelecimento;
use App\Models\Profissional;
use App\Models\Servico;
use App\Models\ServicoProfissional;
use App\Models\HorarioFuncionamento;
use App\Models\Agendamento;
use App\Models\ItemAgendamento;
use App\Models\HistoricoAgendamento;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ══════════════════════════════════════════════════════════════════
        //  1. ROLES & PERMISSIONS (Spatie)
        // ══════════════════════════════════════════════════════════════════
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Estabelecimentos
            'ver-estabelecimentos',
            'gerenciar-estabelecimento',
            // Profissionais
            'ver-profissionais',
            'gerenciar-profissionais',
            // Serviços
            'ver-servicos',
            'gerenciar-servicos',
            // Agenda
            'ver-agenda',
            'gerenciar-horarios',
            'gerenciar-bloqueios',
            // Agendamentos
            'criar-agendamento',
            'cancelar-agendamento-cliente',
            'cancelar-agendamento-prof',
            'ver-agendamentos',
            'atualizar-status-agendamento',
            // Financeiro
            'ver-relatorios',
            // Admin
            'gerenciar-usuarios',
            'gerenciar-plataforma',
        ];

        foreach ($permissions as $perm) {
            Permission::create(['name' => $perm]);
        }

        // ── Roles ───────────────────────────────────────────────────────
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $profRole = Role::create(['name' => 'profissional']);
        $profRole->givePermissionTo([
            'ver-estabelecimentos',
            'gerenciar-estabelecimento',
            'ver-profissionais',
            'gerenciar-profissionais',
            'ver-servicos',
            'gerenciar-servicos',
            'ver-agenda',
            'gerenciar-horarios',
            'gerenciar-bloqueios',
            'cancelar-agendamento-prof',
            'ver-agendamentos',
            'atualizar-status-agendamento',
            'ver-relatorios',
        ]);

        $clienteRole = Role::create(['name' => 'cliente']);
        $clienteRole->givePermissionTo([
            'ver-estabelecimentos',
            'ver-profissionais',
            'ver-servicos',
            'criar-agendamento',
            'cancelar-agendamento-cliente',
            'ver-agendamentos',
        ]);

        // ══════════════════════════════════════════════════════════════════
        //  2. USUÁRIOS + PERFIS
        // ══════════════════════════════════════════════════════════════════
        $joao = User::create([
            'nome_completo' => 'João Silva',
            'email'         => 'joao@email.com',
            'password'      => Hash::make('123456'),
            'tipo'          => 'cliente',
        ]);
        $joao->assignRole('cliente');
        Perfil::create([
            'usuario_id' => $joao->id,
            'telefone'   => '11999001122',
            'cidade'     => 'São Paulo',
            'estado'     => 'SP',
        ]);

        $profUser = User::create([
            'nome_completo' => 'Carlos Barbosa',
            'email'         => 'profissional@email.com',
            'password'      => Hash::make('123456'),
            'tipo'          => 'profissional',
        ]);
        $profUser->assignRole('profissional');
        Perfil::create([
            'usuario_id' => $profUser->id,
            'telefone'   => '11988776655',
            'cidade'     => 'São Paulo',
            'estado'     => 'SP',
        ]);

        $adminUser = User::create([
            'nome_completo' => 'Admin Sistema',
            'email'         => 'admin@email.com',
            'password'      => Hash::make('123456'),
            'tipo'          => 'admin',
        ]);
        $adminUser->assignRole('admin');
        Perfil::create(['usuario_id' => $adminUser->id]);

        $maria = User::create([
            'nome_completo' => 'Maria Santos',
            'email'         => 'maria@email.com',
            'password'      => Hash::make('123456'),
            'tipo'          => 'cliente',
        ]);
        $maria->assignRole('cliente');
        Perfil::create([
            'usuario_id' => $maria->id,
            'telefone'   => '11977665544',
            'cidade'     => 'São Paulo',
            'estado'     => 'SP',
        ]);

        $profUser2 = User::create([
            'nome_completo' => 'Ana Oliveira',
            'email'         => 'ana@email.com',
            'password'      => Hash::make('123456'),
            'tipo'          => 'profissional',
        ]);
        $profUser2->assignRole('profissional');
        Perfil::create([
            'usuario_id' => $profUser2->id,
            'telefone'   => '11966554433',
            'cidade'     => 'São Paulo',
            'estado'     => 'SP',
        ]);

        // ══════════════════════════════════════════════════════════════════
        //  3. ESTABELECIMENTOS
        // ══════════════════════════════════════════════════════════════════
        $studioElegance = Estabelecimento::create([
            'nome_fantasia'     => 'Studio Elegance',
            'descricao'         => 'Espaço moderno e sofisticado para cuidados estéticos completos. Ambiente climatizado, Wi-Fi gratuito e atendimento personalizado.',
            'foto_capa_url'     => 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=800',
            'telefone_principal' => '11999887766',
            'telefone_secundario' => '1133445566',
            'logradouro'        => 'Rua das Flores',
            'numero'            => '123',
            'bairro'            => 'Centro',
            'cidade'            => 'São Paulo',
            'estado'            => 'SP',
            'latitude'          => -23.5505199,
            'longitude'         => -46.6333094,
        ]);

        $barbeariaVintage = Estabelecimento::create([
            'nome_fantasia'     => 'Barbearia Vintage',
            'descricao'         => 'Barbearia clássica com toque moderno. Experiência única de corte e barba com cerveja artesanal.',
            'foto_capa_url'     => 'https://images.unsplash.com/photo-1503951914875-452c29dbb399?w=800',
            'telefone_principal' => '11988776655',
            'logradouro'        => 'Av. Paulista',
            'numero'            => '456',
            'bairro'            => 'Bela Vista',
            'cidade'            => 'São Paulo',
            'estado'            => 'SP',
            'latitude'          => -23.5614327,
            'longitude'         => -46.6564195,
        ]);

        $nailArt = Estabelecimento::create([
            'nome_fantasia'     => 'Nail Art Studio',
            'descricao'         => 'Especialistas em nail art, esmaltação em gel e cuidados para mãos e pés. Ambiente relaxante.',
            'foto_capa_url'     => 'https://images.unsplash.com/photo-1604654894610-df63bc536371?w=800',
            'telefone_principal' => '11977665544',
            'logradouro'        => 'Rua Augusta',
            'numero'            => '789',
            'bairro'            => 'Consolação',
            'cidade'            => 'São Paulo',
            'estado'            => 'SP',
            'latitude'          => -23.5534199,
            'longitude'         => -46.6581098,
        ]);

        $espacoBeleza = Estabelecimento::create([
            'nome_fantasia'     => 'Espaço Beleza & Bem-Estar',
            'descricao'         => 'Centro completo de beleza com serviços de cabelo, estética facial, massagem e depilação. Profissionais certificados.',
            'foto_capa_url'     => 'https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?w=800',
            'telefone_principal' => '11966554433',
            'telefone_secundario' => '1122334455',
            'logradouro'        => 'Rua Oscar Freire',
            'numero'            => '321',
            'bairro'            => 'Jardins',
            'cidade'            => 'São Paulo',
            'estado'            => 'SP',
            'latitude'          => -23.5631399,
            'longitude'         => -46.6697297,
        ]);

        $barberKing = Estabelecimento::create([
            'nome_fantasia'     => 'Barber King',
            'descricao'         => 'Barbearia premium com ambiente exclusivo. Cortes modernos, barba estilizada e tratamentos capilares.',
            'foto_capa_url'     => 'https://images.unsplash.com/photo-1585747860019-8e79b4c37c8a?w=800',
            'telefone_principal' => '11955443322',
            'logradouro'        => 'Rua da Mooca',
            'numero'            => '654',
            'bairro'            => 'Mooca',
            'cidade'            => 'São Paulo',
            'estado'            => 'SP',
            'latitude'          => -23.5576299,
            'longitude'         => -46.6011196,
        ]);

        $studioSobrancelhas = Estabelecimento::create([
            'nome_fantasia'     => 'Studio Sobrancelhas & Cílios',
            'descricao'         => 'Referência em design de sobrancelhas, micropigmentação e extensão de cílios. Resultado natural e duradouro.',
            'foto_capa_url'     => 'https://images.unsplash.com/photo-1516975080664-ed2fc6a32937?w=800',
            'telefone_principal' => '11944332211',
            'logradouro'        => 'Rua Pamplona',
            'numero'            => '987',
            'bairro'            => 'Jardim Paulista',
            'cidade'            => 'São Paulo',
            'estado'            => 'SP',
            'latitude'          => -23.5672899,
            'longitude'         => -46.6526398,
        ]);

        // ══════════════════════════════════════════════════════════════════
        //  4. PROFISSIONAIS (vinculando Users → Estabelecimentos)
        // ══════════════════════════════════════════════════════════════════
        $profCarlos = Profissional::create([
            'usuario_id'         => $profUser->id,
            'estabelecimento_id' => $barbeariaVintage->id,
            'nome_profissional'  => 'Carlos Barbosa',
            'especialidade'      => 'Cortes masculinos e barba',
            'foto_url'           => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400',
        ]);

        $profAna = Profissional::create([
            'usuario_id'         => $profUser2->id,
            'estabelecimento_id' => $studioElegance->id,
            'nome_profissional'  => 'Ana Oliveira',
            'especialidade'      => 'Cortes femininos e tratamentos capilares',
            'foto_url'           => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400',
        ]);

        // ══════════════════════════════════════════════════════════════════
        //  5. SERVIÇOS (catálogo global)
        // ══════════════════════════════════════════════════════════════════
        $sCorteF      = Servico::create(['nome' => 'Corte Feminino', 'descricao' => 'Corte personalizado com lavagem e finalização', 'categoria' => 'cabelo']);
        $sEscova      = Servico::create(['nome' => 'Escova Progressiva', 'descricao' => 'Alisamento com produtos de alta qualidade', 'categoria' => 'cabelo']);
        $sHidratacao  = Servico::create(['nome' => 'Hidratação Capilar', 'descricao' => 'Hidratação profunda com máscara nutritiva', 'categoria' => 'hidratacao']);
        $sCorteM      = Servico::create(['nome' => 'Corte Masculino', 'descricao' => 'Corte clássico ou moderno com finalização', 'categoria' => 'cabelo']);
        $sBarba       = Servico::create(['nome' => 'Barba Completa', 'descricao' => 'Aparar, modelar e hidratar com toalha quente', 'categoria' => 'barba']);
        $sCombo       = Servico::create(['nome' => 'Combo Corte + Barba', 'descricao' => 'Corte de cabelo e barba com acabamento perfeito', 'categoria' => 'cabelo']);
        $sManicure    = Servico::create(['nome' => 'Manicure com Esmaltação', 'descricao' => 'Cuidado completo com esmalte tradicional ou gel', 'categoria' => 'manicure']);
        $sPedicure    = Servico::create(['nome' => 'Pedicure Spa', 'descricao' => 'Pedicure com escalda-pés e hidratação profunda', 'categoria' => 'pedicure']);
        $sLimpeza     = Servico::create(['nome' => 'Limpeza de Pele', 'descricao' => 'Limpeza facial profunda com extração e máscara', 'categoria' => 'estetica']);
        $sMassagem    = Servico::create(['nome' => 'Massagem Relaxante', 'descricao' => 'Massagem corporal relaxante com óleos essenciais', 'categoria' => 'massagem']);
        $sDesign      = Servico::create(['nome' => 'Design de Sobrancelhas', 'descricao' => 'Design personalizado com técnica de visagismo', 'categoria' => 'sobrancelha']);
        $sDegrade     = Servico::create(['nome' => 'Corte Degradê', 'descricao' => 'Degradê com acabamento na navalha', 'categoria' => 'cabelo']);

        // ══════════════════════════════════════════════════════════════════
        //  6. SERVICOS_PROFISSIONAIS (preço + tempo por profissional)
        // ══════════════════════════════════════════════════════════════════
        // Ana (Studio Elegance)
        $spCorteF = ServicoProfissional::create(['profissional_id' => $profAna->id, 'servico_id' => $sCorteF->id, 'preco' => 85.00, 'tempo_execucao_minutos' => 60]);
        ServicoProfissional::create(['profissional_id' => $profAna->id, 'servico_id' => $sEscova->id, 'preco' => 200.00, 'tempo_execucao_minutos' => 120]);
        ServicoProfissional::create(['profissional_id' => $profAna->id, 'servico_id' => $sHidratacao->id, 'preco' => 65.00, 'tempo_execucao_minutos' => 45]);

        // Carlos (Barbearia Vintage)
        $spCorteM = ServicoProfissional::create(['profissional_id' => $profCarlos->id, 'servico_id' => $sCorteM->id, 'preco' => 55.00, 'tempo_execucao_minutos' => 30]);
        ServicoProfissional::create(['profissional_id' => $profCarlos->id, 'servico_id' => $sBarba->id, 'preco' => 40.00, 'tempo_execucao_minutos' => 30]);
        $spCombo = ServicoProfissional::create(['profissional_id' => $profCarlos->id, 'servico_id' => $sCombo->id, 'preco' => 80.00, 'tempo_execucao_minutos' => 60]);

        // ══════════════════════════════════════════════════════════════════
        //  7. HORÁRIOS DE FUNCIONAMENTO (grade semanal)
        // ══════════════════════════════════════════════════════════════════
        foreach ([$profCarlos, $profAna] as $prof) {
            // Seg-Sex: 09:00–18:00
            for ($dia = 1; $dia <= 5; $dia++) {
                HorarioFuncionamento::create([
                    'profissional_id' => $prof->id,
                    'dia_semana'      => $dia,
                    'hora_inicio'     => '09:00',
                    'hora_fim'        => '18:00',
                ]);
            }
            // Sáb: 09:00–14:00
            HorarioFuncionamento::create([
                'profissional_id' => $prof->id,
                'dia_semana'      => 6,
                'hora_inicio'     => '09:00',
                'hora_fim'        => '14:00',
            ]);
        }

        // ══════════════════════════════════════════════════════════════════
        //  8. AGENDAMENTOS + ITENS + HISTÓRICO
        // ══════════════════════════════════════════════════════════════════

        // Agendamento 1: João → Carlos — Corte Masculino (confirmado)
        $ag1 = Agendamento::create([
            'cliente_id'      => $joao->id,
            'profissional_id' => $profCarlos->id,
            'data_hora_inicio' => '2026-03-01 14:00:00',
            'data_hora_fim'    => '2026-03-01 14:30:00',
            'status'          => 'confirmado',
            'valor_total'     => 55.00,
        ]);
        ItemAgendamento::create([
            'agendamento_id'        => $ag1->id,
            'servico_id'            => $sCorteM->id,
            'nome_servico'          => 'Corte Masculino',
            'preco_praticado'       => 55.00,
            'tempo_execucao_minutos' => 30,
        ]);
        HistoricoAgendamento::create([
            'agendamento_id'  => $ag1->id,
            'status_anterior' => 'pendente',
            'status_novo'     => 'confirmado',
            'alterado_por_id' => $profUser->id,
        ]);

        // Agendamento 2: João → Ana — Massagem Relaxante (confirmado) — via Espaço Beleza & Bem-Estar (Ana tem vínculo com Studio Elegance, mas demonstra flexibilidade)
        // Na verdade o serviço de massagem não está vinculado à Ana no pivot — para manter coerência usamos o Corte Feminino
        $ag2 = Agendamento::create([
            'cliente_id'      => $joao->id,
            'profissional_id' => $profAna->id,
            'data_hora_inicio' => '2026-03-05 10:00:00',
            'data_hora_fim'    => '2026-03-05 11:00:00',
            'status'          => 'confirmado',
            'valor_total'     => 85.00,
        ]);
        ItemAgendamento::create([
            'agendamento_id'        => $ag2->id,
            'servico_id'            => $sCorteF->id,
            'nome_servico'          => 'Corte Feminino',
            'preco_praticado'       => 85.00,
            'tempo_execucao_minutos' => 60,
        ]);
        HistoricoAgendamento::create([
            'agendamento_id'  => $ag2->id,
            'status_anterior' => 'pendente',
            'status_novo'     => 'confirmado',
            'alterado_por_id' => $profUser2->id,
        ]);

        // Agendamento 3: João → Carlos — Combo (concluido)
        $ag3 = Agendamento::create([
            'cliente_id'      => $joao->id,
            'profissional_id' => $profCarlos->id,
            'data_hora_inicio' => '2026-01-10 16:30:00',
            'data_hora_fim'    => '2026-01-10 17:30:00',
            'status'          => 'concluido',
            'valor_total'     => 80.00,
        ]);
        ItemAgendamento::create([
            'agendamento_id'        => $ag3->id,
            'servico_id'            => $sCombo->id,
            'nome_servico'          => 'Combo Corte + Barba',
            'preco_praticado'       => 80.00,
            'tempo_execucao_minutos' => 60,
        ]);
        HistoricoAgendamento::create([
            'agendamento_id'  => $ag3->id,
            'status_anterior' => 'pendente',
            'status_novo'     => 'confirmado',
            'alterado_por_id' => $profUser->id,
        ]);
        HistoricoAgendamento::create([
            'agendamento_id'  => $ag3->id,
            'status_anterior' => 'confirmado',
            'status_novo'     => 'em_atendimento',
            'alterado_por_id' => $profUser->id,
        ]);
        HistoricoAgendamento::create([
            'agendamento_id'  => $ag3->id,
            'status_anterior' => 'em_atendimento',
            'status_novo'     => 'concluido',
            'alterado_por_id' => $profUser->id,
        ]);

        // Agendamento 4: João → Ana — Corte Feminino (concluido)
        $ag4 = Agendamento::create([
            'cliente_id'      => $joao->id,
            'profissional_id' => $profAna->id,
            'data_hora_inicio' => '2025-12-20 09:00:00',
            'data_hora_fim'    => '2025-12-20 10:00:00',
            'status'          => 'concluido',
            'valor_total'     => 85.00,
        ]);
        ItemAgendamento::create([
            'agendamento_id'        => $ag4->id,
            'servico_id'            => $sCorteF->id,
            'nome_servico'          => 'Corte Feminino',
            'preco_praticado'       => 85.00,
            'tempo_execucao_minutos' => 60,
        ]);
        HistoricoAgendamento::create([
            'agendamento_id'  => $ag4->id,
            'status_anterior' => 'pendente',
            'status_novo'     => 'concluido',
            'alterado_por_id' => $profUser2->id,
        ]);

        // Agendamento 5: João → Carlos — Corte Masculino (confirmado)
        $ag5 = Agendamento::create([
            'cliente_id'      => $joao->id,
            'profissional_id' => $profCarlos->id,
            'data_hora_inicio' => '2026-02-17 11:30:00',
            'data_hora_fim'    => '2026-02-17 12:00:00',
            'status'          => 'confirmado',
            'valor_total'     => 55.00,
        ]);
        ItemAgendamento::create([
            'agendamento_id'        => $ag5->id,
            'servico_id'            => $sCorteM->id,
            'nome_servico'          => 'Corte Masculino',
            'preco_praticado'       => 55.00,
            'tempo_execucao_minutos' => 30,
        ]);
        HistoricoAgendamento::create([
            'agendamento_id'  => $ag5->id,
            'status_anterior' => 'pendente',
            'status_novo'     => 'confirmado',
            'alterado_por_id' => $profUser->id,
        ]);

        // Agendamento 6: João → Ana — Hidratação (pendente)
        $ag6 = Agendamento::create([
            'cliente_id'      => $joao->id,
            'profissional_id' => $profAna->id,
            'data_hora_inicio' => '2026-02-24 13:00:00',
            'data_hora_fim'    => '2026-02-24 13:45:00',
            'status'          => 'pendente',
            'valor_total'     => 65.00,
        ]);
        ItemAgendamento::create([
            'agendamento_id'        => $ag6->id,
            'servico_id'            => $sHidratacao->id,
            'nome_servico'          => 'Hidratação Capilar',
            'preco_praticado'       => 65.00,
            'tempo_execucao_minutos' => 45,
        ]);
    }
}
