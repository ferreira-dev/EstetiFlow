<template>
    <div class="container-app py-8 lg:py-12">
        <!-- Header -->
        <div class="mb-8 lg:mb-12">
            <h1 class="text-gradient text-2xl font-bold lg:text-3xl">Agendamentos</h1>
            <p class="mt-2 text-sm text-zinc-400">Confira seus agendamentos e histórico</p>
        </div>

        <!-- Stats -->
        <div class="mb-8 grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-6">
            <div class="stat-card stat-card--primary">
                <div class="relative z-10 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background: rgba(var(--p-primary-500), 0.2)">
                        <CalendarDays class="h-5 w-5" style="color: var(--p-primary-color)" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold" style="color: var(--p-primary-color)">{{ proximos.length }}</p>
                        <p class="text-xs text-zinc-400">Próximos</p>
                    </div>
                </div>
            </div>
            <div class="stat-card stat-card--success">
                <div class="relative z-10 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-500/20">
                        <CheckCircle class="h-5 w-5 text-green-500" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-green-500">{{ concluidos.length }}</p>
                        <p class="text-xs text-zinc-400">Concluídos</p>
                    </div>
                </div>
            </div>
            <div class="stat-card stat-card--warning">
                <div class="relative z-10 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-yellow-500/20">
                        <DollarSign class="h-5 w-5 text-yellow-500" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-yellow-500">{{ totalInvestidoFormatado }}</p>
                        <p class="text-xs text-zinc-400">Total Investido</p>
                    </div>
                </div>
            </div>
            <div class="stat-card stat-card--info">
                <div class="relative z-10 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/20">
                        <Heart class="h-5 w-5 text-blue-500" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-blue-500 truncate">{{ estabelecimentoFavorito?.nome || '-' }}</p>
                        <p class="text-xs text-zinc-400">Favorito</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <Tabs v-model:value="abaSelecionada">
            <TabList>
                <!-- Aba: agendamentos futuros e ativos -->
                <Tab value="proximos">
                    Próximos
                    <Tag v-if="proximos.length > 0" severity="secondary" class="ml-2">
                        {{ proximos.length }}
                    </Tag>
                </Tab>
                <!-- Aba: todo o histórico (concluídos, cancelados, expirados) -->
                <Tab value="historico">
                    Histórico
                    <Tag v-if="historico.length > 0" severity="secondary" class="ml-2">
                        {{ historico.length }}
                    </Tag>
                </Tab>
            </TabList>

            <TabPanels>
                <!-- Próximos: pendente ou confirmado, data futura -->
                <TabPanel value="proximos">
                    <div v-if="proximos.length > 0" class="mt-6 grid gap-4 lg:grid-cols-2">
                        <AgendamentoCard
                            v-for="agendamento in proximos"
                            :key="agendamento.id"
                            :agendamento="agendamento"
                            class="animate-fade-in-up"
                        />
                    </div>
                    <EmptyState
                        v-else
                        icone="pi-calendar"
                        titulo="Nenhum agendamento próximo"
                        descricao="Você não tem agendamentos futuros. Que tal agendar um serviço?"
                        labelBotao="Buscar Estabelecimentos"
                        rotaBotao="/estabelecimentos"
                    />
                </TabPanel>

                <!-- Histórico: concluídos, cancelados, não compareceu, e "expirados" (data passada sem status final) -->
                <TabPanel value="historico">
                    <div v-if="historico.length > 0" class="mt-6 grid gap-4 lg:grid-cols-2">
                        <AgendamentoCard
                            v-for="agendamento in historico"
                            :key="agendamento.id"
                            :agendamento="agendamento"
                            class="animate-fade-in-up"
                        />
                    </div>
                    <EmptyState
                        v-else
                        icone="pi-clock"
                        titulo="Nenhum histórico"
                        descricao="Seu histórico de agendamentos aparecerá aqui após finalizar seus serviços."
                    />
                </TabPanel>
            </TabPanels>
        </Tabs>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { isFuture } from 'date-fns'
import { formatarMoeda } from '@/Utils/formatters'
import { CalendarDays, CheckCircle, DollarSign, Heart } from 'lucide-vue-next'
import Tabs from 'primevue/tabs'
import Tab from 'primevue/tab'
import TabList from 'primevue/tablist'
import TabPanels from 'primevue/tabpanels'
import TabPanel from 'primevue/tabpanel'
import Tag from 'primevue/tag'
import AgendamentoCard from '@/Components/Features/AgendamentoCard.vue'
import EmptyState from '@/Components/Features/EmptyState.vue'
import DefaultLayout from '@/Layouts/DefaultLayout.vue'

defineOptions({ layout: DefaultLayout })

const props = defineProps({
    agendamentos: { type: Array, default: () => [] },
})

const abaSelecionada = ref('proximos')

const STATUS_ATIVOS = ['pendente', 'confirmado']
const STATUS_HISTORICO = ['concluido', 'cancelado_cliente', 'cancelado_profissional', 'nao_compareceu', 'em_atendimento']

/**
 * "Próximos": pendente ou confirmado com data futura.
 * São os que o cliente ainda pode ver/cancelar.
 */
const proximos = computed(() =>
    props.agendamentos.filter(a =>
        STATUS_ATIVOS.includes(a.status) &&
        isFuture(new Date(a.data_hora_inicio))
    )
)

/**
 * "Histórico": tudo que é passado ou tem status terminal.
 * Inclui agendamentos com data passada mas status ainda "ativo"
 * (expirados — não houve transição automática de status no MVP).
 */
const historico = computed(() =>
    props.agendamentos.filter(a =>
        STATUS_HISTORICO.includes(a.status) ||
        (!isFuture(new Date(a.data_hora_inicio)) && !STATUS_ATIVOS.includes(a.status)) ||
        (!isFuture(new Date(a.data_hora_inicio)) && STATUS_ATIVOS.includes(a.status))
    ).filter(a => !proximos.value.includes(a))
)

// Somente concluídos para a stat de "Concluídos"
const concluidos = computed(() =>
    props.agendamentos.filter(a => a.status === 'concluido')
)

// Total investido = soma dos concluídos
const totalInvestido = computed(() =>
    concluidos.value.reduce((total, a) => total + Number(a.valor_total || 0), 0)
)
const totalInvestidoFormatado = computed(() => formatarMoeda(totalInvestido.value))

// Estabelecimento mais frequentado (baseado em todos os agendamentos)
const estabelecimentoFavorito = computed(() => {
    const contagem = {}
    props.agendamentos.forEach(a => {
        const nome = a.profissional?.estabelecimento?.nome_fantasia
        if (nome) contagem[nome] = (contagem[nome] || 0) + 1
    })
    const ordenados = Object.entries(contagem).sort(([, a], [, b]) => b - a)
    return ordenados[0] ? { nome: ordenados[0][0], visitas: ordenados[0][1] } : null
})
</script>
