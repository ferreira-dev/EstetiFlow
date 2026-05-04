<template>
    <div class="mx-auto max-w-5xl space-y-8">

        <!-- Header -->
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500/20">
                <i class="pi pi-chart-bar text-lg text-emerald-400"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">Relatório Financeiro</h2>
                <p class="text-sm text-zinc-400">Visualize seus ganhos por período</p>
            </div>
        </div>

        <!-- Filtros de período -->
        <div class="glass-card p-4">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="p in periodos"
                        :key="p.value"
                        :class="[
                            'rounded-full px-4 py-1.5 text-sm font-medium transition-all duration-150',
                            periodoAtual === p.value
                                ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-500/25'
                                : 'bg-white/5 text-zinc-400 hover:bg-white/10 hover:text-zinc-200',
                        ]"
                        @click="aplicarPeriodo(p.value)"
                    >
                        {{ p.label }}
                    </button>
                </div>

                <!-- Período personalizado -->
                <div v-if="periodoAtual === 'custom'" class="flex flex-wrap items-center gap-2">
                    <DatePicker
                        v-model="customInicio"
                        dateFormat="dd/mm/yy"
                        showIcon
                        placeholder="Data início"
                        class="w-36"
                    />
                    <span class="text-zinc-500">→</span>
                    <DatePicker
                        v-model="customFim"
                        dateFormat="dd/mm/yy"
                        showIcon
                        placeholder="Data fim"
                        class="w-36"
                    />
                    <Button
                        label="Filtrar"
                        icon="pi pi-search"
                        size="small"
                        severity="success"
                        @click="aplicarCustom"
                    />
                </div>
            </div>

            <!-- Período exibido -->
            <p class="mt-3 text-xs text-zinc-500">
                <i class="pi pi-calendar mr-1"></i>
                {{ formatarDataCurta(dataInicio) }} → {{ formatarDataCurta(dataFim) }}
            </p>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="glass-card p-5 relative overflow-hidden border border-emerald-500/20">
                <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-emerald-500/10 blur-xl"></div>
                <p class="mb-1 relative text-xs font-medium uppercase tracking-wider text-zinc-500">Receita Realizada</p>
                <p class="text-3xl relative font-bold text-emerald-400">{{ formatarMoeda(stats.receita_total) }}</p>
                <p class="mt-1 relative text-xs text-zinc-500">apenas concluídos</p>
            </div>
            <div class="glass-card p-5 relative overflow-hidden border border-sky-500/20">
                <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full bg-sky-500/10 blur-xl"></div>
                <p class="mb-1 relative text-xs font-medium uppercase tracking-wider text-zinc-500">Receita Prevista</p>
                <p class="text-3xl relative font-bold text-sky-400">{{ formatarMoeda(stats.receita_prevista) }}</p>
                <p class="mt-1 relative text-xs text-zinc-500">confirmados e em andamento</p>
            </div>
            <div class="glass-card p-5">
                <p class="mb-1 text-xs font-medium uppercase tracking-wider text-zinc-500">Atendimentos</p>
                <p class="text-3xl font-bold text-white">{{ stats.total_atendimentos }}</p>
                <p class="mt-1 text-xs text-zinc-500">no período</p>
            </div>
            <div class="glass-card p-5">
                <p class="mb-1 text-xs font-medium uppercase tracking-wider text-zinc-500">Ticket Médio</p>
                <p class="text-3xl font-bold text-white">{{ formatarMoeda(stats.ticket_medio) }}</p>
                <p class="mt-1 text-xs text-zinc-500">por atendimento</p>
            </div>
        </div>

        <!-- Tabela de agendamentos -->
        <div class="glass-card overflow-hidden">
            <div class="border-b border-white/5 px-5 py-4">
                <h3 class="font-semibold text-white">Detalhamento</h3>
            </div>

            <div v-if="agendamentos.length > 0">
                <!-- Cabeçalho da tabela (desktop) -->
                <div class="hidden grid-cols-5 border-b border-white/5 px-5 py-2 text-xs font-medium uppercase tracking-wider text-zinc-500 sm:grid">
                    <span>Data</span>
                    <span class="col-span-2">Cliente / Serviço</span>
                    <span>Status</span>
                    <span class="text-right">Valor</span>
                </div>

                <!-- Linhas -->
                <div class="divide-y divide-white/5">
                    <div
                        v-for="ag in agendamentos"
                        :key="ag.id"
                        class="grid grid-cols-1 gap-1 px-5 py-4 text-sm transition-colors hover:bg-white/2 sm:grid-cols-5 sm:items-center sm:gap-3"
                    >
                        <span class="text-zinc-400">{{ formatarData(ag.data_hora_inicio) }}<br><span class="text-xs text-zinc-600">{{ formatarHora(ag.data_hora_inicio) }}</span></span>

                        <div class="col-span-2">
                            <p class="font-medium text-white">{{ ag.cliente?.nome_completo ?? '—' }}</p>
                            <div class="mt-0.5 flex flex-wrap gap-1">
                                <span
                                    v-for="item in ag.itens"
                                    :key="item.id"
                                    class="rounded bg-white/5 px-1.5 py-0.5 text-xs text-zinc-400"
                                >
                                    {{ item.nome_servico }}
                                </span>
                            </div>
                        </div>

                        <span>
                            <Tag
                                :value="statusConfig[ag.status]?.label ?? ag.status"
                                :severity="statusConfig[ag.status]?.severity"
                            />
                        </span>

                        <span class="text-right font-semibold text-emerald-400">{{ formatarMoeda(ag.valor_total) }}</span>
                    </div>
                </div>
            </div>

            <!-- Estado vazio -->
            <div v-else class="p-10 text-center">
                <div class="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-500/10">
                    <i class="pi pi-chart-bar text-2xl text-emerald-400"></i>
                </div>
                <h4 class="text-sm font-semibold text-white mb-1">Nenhum registro</h4>
                <p class="text-xs text-zinc-500">Não há atendimentos confirmados ou concluídos neste período.</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import DatePicker from 'primevue/datepicker'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatarData, formatarHora, formatarMoeda } from '@/Utils/formatters'

defineOptions({ layout: DashboardLayout })

const props = defineProps({
    agendamentos: { type: Array,  default: () => [] },
    stats:        { type: Object, default: () => ({}) },
    periodoAtual: { type: String, default: 'mes' },
    dataInicio:   { type: String, default: '' },
    dataFim:      { type: String, default: '' },
})

const periodos = [
    { value: 'hoje',   label: 'Hoje'         },
    { value: 'semana', label: 'Esta Semana'   },
    { value: 'mes',    label: 'Este Mês'      },
    { value: 'custom', label: 'Personalizado' },
]

const customInicio = ref(null)
const customFim    = ref(null)

function aplicarPeriodo(periodo) {
    if (periodo === 'custom') {
        // Apenas altera o filtro visual, sem navegar
        router.get('/profissional/financeiro', { periodo }, { preserveState: true, replace: true })
        return
    }
    router.get('/profissional/financeiro', { periodo }, { preserveState: false })
}

function aplicarCustom() {
    if (!customInicio.value || !customFim.value) return
    router.get('/profissional/financeiro', {
        periodo:      'custom',
        data_inicio:  customInicio.value.toISOString().split('T')[0],
        data_fim:     customFim.value.toISOString().split('T')[0],
    })
}

function formatarDataCurta(iso) {
    if (!iso) return '—'
    const [year, month, day] = iso.split('-')
    return `${day}/${month}/${year}`
}

const statusConfig = {
    confirmado:  { label: 'Confirmado', severity: 'success'   },
    concluido:   { label: 'Concluído',  severity: 'secondary' },
}
</script>
