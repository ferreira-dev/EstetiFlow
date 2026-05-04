<template>
    <div class="mx-auto max-w-5xl space-y-8">

        <!-- Saudação -->
        <div>
            <h2 class="text-2xl font-bold text-white">{{ saudacao }}, {{ primeiroNome }} 👋</h2>
            <p class="mt-1 text-sm text-zinc-400 capitalize">{{ dataHoje }}</p>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
            <div class="glass-card flex flex-col gap-3 p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium uppercase tracking-wider text-zinc-500">Hoje</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary-500/20">
                        <i class="pi pi-calendar text-sm text-primary-400"></i>
                    </div>
                </div>
                <div>
                    <p class="text-3xl font-bold text-white">{{ stats.totalHoje }}</p>
                    <p class="text-xs text-zinc-500">agendamentos</p>
                </div>
                <div v-if="stats.pendentesHoje > 0" class="flex items-center gap-1 text-xs text-amber-400">
                    <i class="pi pi-clock text-xs"></i>
                    {{ stats.pendentesHoje }} pendente{{ stats.pendentesHoje > 1 ? 's' : '' }}
                </div>
                <div v-else class="text-xs text-zinc-600">Todos confirmados</div>
            </div>

            <div class="glass-card flex flex-col gap-3 p-5 relative overflow-hidden border border-emerald-500/20">
                <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-emerald-500/10 blur-xl"></div>
                <div class="flex items-center justify-between relative">
                    <span class="text-xs font-medium uppercase tracking-wider text-emerald-400">Receita Realizada</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/20">
                        <i class="pi pi-dollar text-sm text-emerald-400"></i>
                    </div>
                </div>
                <div class="relative">
                    <p class="text-2xl lg:text-3xl font-bold text-emerald-400">{{ formatarMoeda(stats.receitaRealizadaHoje) }}</p>
                    <p class="text-xs text-zinc-500">apenas concluídas</p>
                </div>
            </div>

            <div class="glass-card flex flex-col gap-3 p-5 relative overflow-hidden border border-sky-500/20">
                <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-sky-500/10 blur-xl"></div>
                <div class="flex items-center justify-between relative">
                    <span class="text-xs font-medium uppercase tracking-wider text-sky-400">Receita Prevista</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/20">
                        <i class="pi pi-wallet text-sm text-sky-400"></i>
                    </div>
                </div>
                <div class="relative">
                    <p class="text-2xl lg:text-3xl font-bold text-sky-400">{{ formatarMoeda(stats.receitaPrevistaHoje) }}</p>
                    <p class="text-xs text-zinc-500">em andamento e conf.</p>
                </div>
            </div>

            <div class="glass-card flex flex-col gap-3 p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium uppercase tracking-wider text-zinc-500">Esta Semana</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-violet-500/20">
                        <i class="pi pi-chart-bar text-sm text-violet-400"></i>
                    </div>
                </div>
                <div>
                    <p class="text-3xl font-bold text-white">{{ stats.totalSemana }}</p>
                    <p class="text-xs text-zinc-500">atendimentos</p>
                </div>
            </div>

            <div class="glass-card flex flex-col gap-3 p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium uppercase tracking-wider text-zinc-500">Clientes</span>
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-sky-500/20">
                        <i class="pi pi-users text-sm text-sky-400"></i>
                    </div>
                </div>
                <div>
                    <p class="text-3xl font-bold text-white">{{ stats.totalClientes }}</p>
                    <p class="text-xs text-zinc-500">atendidos no total</p>
                </div>
            </div>
        </div>

        <!-- Grid: Agenda do dia + Próximos -->
        <div class="grid gap-6 lg:grid-cols-5">

            <!-- Agenda do Dia (3/5) -->
            <div class="lg:col-span-3 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-white">Agenda de Hoje</h3>
                    <Link href="/profissional/agendamentos" class="text-xs text-primary-400 hover:text-primary-300 transition-colors">
                        Ver todos →
                    </Link>
                </div>

                <!-- Lista -->
                <div v-if="agendamentosHoje.length > 0" class="space-y-3">
                    <div
                        v-for="ag in agendamentosHoje"
                        :key="ag.id"
                        class="glass-card flex items-start gap-4 p-4 transition-all duration-200 hover:border-white/10"
                    >
                        <!-- Horário -->
                        <div class="flex flex-col items-center text-center flex-shrink-0 w-12">
                            <span class="text-xs text-zinc-500">{{ formatarHora(ag.data_hora_inicio) }}</span>
                            <div class="my-1 h-full w-px bg-white/5"></div>
                            <span class="text-xs text-zinc-600">{{ formatarHora(ag.data_hora_fim) }}</span>
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                <span class="font-medium text-white">{{ ag.cliente?.nome_completo ?? '—' }}</span>
                                <Tag
                                    :value="statusConfig[ag.status]?.label ?? ag.status"
                                    :severity="statusConfig[ag.status]?.severity"
                                />
                            </div>
                            <div class="flex flex-wrap gap-1 mb-2">
                                <span
                                    v-for="item in ag.itens"
                                    :key="item.id"
                                    class="rounded bg-white/5 px-1.5 py-0.5 text-xs text-zinc-400"
                                >
                                    {{ item.nome_servico }}
                                </span>
                            </div>
                            <span class="text-sm font-semibold text-emerald-400">{{ formatarMoeda(ag.valor_total) }}</span>
                        </div>

                        <!-- Ação rápida -->
                        <div class="flex-shrink-0 flex gap-1">
                            <Button
                                v-if="ag.status === 'pendente'"
                                icon="pi pi-check"
                                size="small"
                                severity="success"
                                text
                                rounded
                                v-tooltip.left="'Confirmar'"
                                @click="confirmarRapido(ag)"
                            />
                            <Button
                                v-if="ag.status === 'em_atendimento'"
                                icon="pi pi-check-circle"
                                size="small"
                                severity="success"
                                text
                                rounded
                                v-tooltip.left="'Concluir'"
                                @click="concluirRapido(ag)"
                            />
                        </div>
                    </div>
                </div>

                <!-- Estado vazio -->
                <div v-else class="glass-card p-10 text-center">
                    <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-primary-500/10">
                        <i class="pi pi-sun text-2xl text-primary-400"></i>
                    </div>
                    <h4 class="text-sm font-semibold text-white mb-1">Dia livre!</h4>
                    <p class="text-xs text-zinc-500">Nenhum agendamento para hoje.</p>
                </div>
            </div>

            <!-- Próximos Atendimentos (2/5) -->
            <div class="lg:col-span-2 space-y-4">
                <h3 class="font-semibold text-white">Próximos Atendimentos</h3>

                <div v-if="proximosAtendimentos.length > 0" class="space-y-2">
                    <div
                        v-for="ag in proximosAtendimentos"
                        :key="ag.id"
                        class="glass-card p-3 transition-all duration-200 hover:border-white/10"
                    >
                        <div class="flex items-start gap-3">
                            <!-- Avatar inicial -->
                            <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-primary-500/20 text-xs font-bold text-primary-300">
                                {{ (ag.cliente?.nome_completo ?? '?').charAt(0).toUpperCase() }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-white truncate">{{ ag.cliente?.nome_completo ?? '—' }}</p>
                                <p class="text-xs text-zinc-500">{{ formatarDataHora(ag.data_hora_inicio) }}</p>
                                <p v-if="ag.itens?.[0]" class="mt-0.5 text-xs text-zinc-400">{{ ag.itens[0].nome_servico }}</p>
                            </div>
                            <Tag
                                :value="statusConfig[ag.status]?.label ?? ag.status"
                                :severity="statusConfig[ag.status]?.severity"
                                class="flex-shrink-0 text-xs"
                            />
                        </div>
                    </div>
                </div>

                <div v-else class="glass-card p-6 text-center">
                    <i class="pi pi-calendar-times text-2xl text-zinc-600 mb-2"></i>
                    <p class="text-xs text-zinc-500">Nenhum atendimento agendado para os próximos dias.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatarHora, formatarMoeda } from '@/Utils/formatters'

defineOptions({ layout: DashboardLayout })

const props = defineProps({
    agendamentosHoje:     { type: Array,  default: () => [] },
    proximosAtendimentos: { type: Array,  default: () => [] },
    stats:                { type: Object, default: () => ({}) },
})

const page = usePage()
const user = computed(() => page.props.auth?.user)

const primeiroNome = computed(() => {
    const nome = user.value?.nome_completo ?? ''
    return nome.split(' ')[0]
})

const saudacao = computed(() => {
    const h = new Date().getHours()
    if (h < 12) return 'Bom dia'
    if (h < 18) return 'Boa tarde'
    return 'Boa noite'
})

const dataHoje = computed(() =>
    new Intl.DateTimeFormat('pt-BR', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date())
)

// ── Formatters ──────────────────────────────────────────────────────────────

function formatarDataHora(iso) {
    if (!iso) return '—'
    return new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    }).format(new Date(iso))
}

// ── Config de status ────────────────────────────────────────────────────────

const statusConfig = {
    pendente:               { label: 'Pendente',                severity: 'warn'      },
    confirmado:             { label: 'Confirmado',              severity: 'success'   },
    em_atendimento:         { label: 'Em Andamento',            severity: 'info'      },
    concluido:              { label: 'Concluído',               severity: 'secondary' },
    cancelado_cliente:      { label: 'Canc. Cliente',           severity: 'danger'    },
    cancelado_profissional: { label: 'Cancelado',               severity: 'danger'    },
    nao_compareceu:         { label: 'Não Compareceu',          severity: 'warn'      },
}

// ── Ações rápidas ────────────────────────────────────────────────────────────

function confirmarRapido(ag) {
    router.put(`/profissional/agendamentos/${ag.id}/status`, { status: 'confirmado' }, {
        preserveScroll: true,
    })
}

function concluirRapido(ag) {
    router.put(`/profissional/agendamentos/${ag.id}/status`, { status: 'concluido' }, {
        preserveScroll: true,
    })
}
</script>
