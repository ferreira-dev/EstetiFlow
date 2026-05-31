<template>
    <div class="mx-auto max-w-4xl space-y-6">

        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-500/20">
                    <i class="pi pi-calendar text-lg text-primary-400"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white">Agendamentos</h2>
                    <p class="text-sm text-zinc-400">Gerencie seus atendimentos e atualize os status</p>
                </div>
            </div>
            
            <Button
                label="Novo Agendamento"
                icon="pi pi-plus"
                @click="dialogNovoAgendamentoVisible = true"
            />
        </div>

        <!-- Filtros -->
        <div class="flex flex-col gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <div class="w-full sm:w-auto">
                    <InputText v-model="filtroBusca.cliente" placeholder="Nome, E-mail ou Telefone..." class="w-full sm:w-64" />
                </div>
                <div class="w-full sm:w-auto">
                    <DatePicker 
                        v-model="filtroBusca.datas" 
                        selectionMode="range" 
                        showIcon 
                        placeholder="Período" 
                        dateFormat="dd/mm/yy"
                        class="w-full sm:w-64"
                    />
                </div>
                <Button label="Filtrar" icon="pi pi-filter" @click="aplicarBusca" class="w-full sm:w-auto" />
                <Button label="Limpar" icon="pi pi-filter-slash" text @click="limparBusca" class="w-full sm:w-auto" v-if="hasActiveFilters" />
            </div>

            <!-- Filtros de status -->
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="filtro in filtrosItems"
                    :key="filtro.value"
                    :class="[
                        'rounded-full px-4 py-1.5 text-sm font-medium transition-all duration-150',
                        props.filtros?.status === filtro.value || (!props.filtros?.status && filtro.value === 'todos')
                            ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/25'
                            : 'bg-white/5 text-zinc-400 hover:bg-white/10 hover:text-zinc-200',
                    ]"
                    @click="aplicarFiltroStatus(filtro.value)"
                >
                    {{ filtro.label }}
                </button>
            </div>
        </div>

        <!-- Lista de agendamentos -->
        <div v-if="agendamentos.data && agendamentos.data.length > 0" class="space-y-3">
            <div
                v-for="ag in agendamentos.data"
                :key="ag.id"
                class="glass-card p-5 transition-all duration-200 hover:border-white/10"
            >
                <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                    <!-- Info do agendamento -->
                    <div class="flex-1 min-w-0 space-y-2">
                        <!-- Cliente + status -->
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-base font-semibold text-white">
                                {{ ag.cliente?.nome_completo ?? '—' }}
                            </span>
                            <Button 
                                v-if="ag.cliente"
                                icon="pi pi-address-book" 
                                text 
                                rounded 
                                size="small" 
                                class="h-6 w-6 p-0 text-zinc-400 hover:text-white" 
                                @click="toggleContato($event, ag)"
                                aria-haspopup="true"
                                aria-controls="overlay_panel"
                            />
                            <Tag
                                :value="statusConfig[ag.status]?.label ?? ag.status"
                                :severity="statusConfig[ag.status]?.severity"
                            />
                        </div>

                        <!-- Serviços -->
                        <div class="flex flex-wrap gap-1">
                            <span
                                v-for="item in ag.itens"
                                :key="item.id"
                                class="rounded-md bg-white/5 px-2 py-0.5 text-xs text-zinc-300"
                            >
                                {{ item.nome_servico }}
                            </span>
                        </div>

                        <!-- Data e valor -->
                        <div class="flex flex-wrap items-center gap-4 text-sm text-zinc-400">
                            <span>
                                <i class="pi pi-calendar mr-1 text-xs"></i>
                                {{ formatarData(ag.data_hora_inicio) }}
                            </span>
                            <span>
                                <i class="pi pi-clock mr-1 text-xs"></i>
                                {{ formatarHora(ag.data_hora_inicio) }} – {{ formatarHora(ag.data_hora_fim) }}
                            </span>
                            <span class="font-semibold text-green-400">
                                {{ formatarMoeda(ag.valor_total) }}
                            </span>
                        </div>
                    </div>

                    <!-- Ações contextuais -->
                    <div class="flex flex-shrink-0 flex-wrap gap-2">
                        <!-- pendente: confirmar | recusar -->
                        <template v-if="ag.status === 'pendente'">
                            <Button
                                label="Confirmar"
                                icon="pi pi-check"
                                size="small"
                                severity="success"
                                @click="alterarStatus(ag, 'confirmado')"
                            />
                            <Button
                                label="Recusar"
                                icon="pi pi-times"
                                size="small"
                                severity="danger"
                                text
                                @click="confirmarAcao(ag, 'cancelado_profissional', 'Recusar este agendamento?')"
                            />
                        </template>

                        <!-- confirmado: iniciar | cancelar | não compareceu -->
                        <template v-else-if="ag.status === 'confirmado'">
                            <Button
                                v-if="!isFuture(new Date(ag.data_hora_inicio))"
                                label="Iniciar"
                                icon="pi pi-play"
                                size="small"
                                @click="alterarStatus(ag, 'em_atendimento')"
                            />
                            <Button
                                label="Cancelar"
                                icon="pi pi-times"
                                size="small"
                                severity="danger"
                                text
                                @click="confirmarAcao(ag, 'cancelado_profissional', 'Cancelar este agendamento?')"
                            />
                            <Button
                                v-if="!isFuture(new Date(ag.data_hora_inicio))"
                                label="Não compareceu"
                                icon="pi pi-user-minus"
                                size="small"
                                severity="warn"
                                text
                                @click="confirmarAcao(ag, 'nao_compareceu', 'Marcar cliente como não comparecido?')"
                            />
                        </template>

                        <!-- em_atendimento: concluir -->
                        <template v-else-if="ag.status === 'em_atendimento'">
                            <Button
                                label="Concluir"
                                icon="pi pi-check-circle"
                                size="small"
                                severity="success"
                                @click="alterarStatus(ag, 'concluido')"
                            />
                        </template>

                        <!-- Status terminais: badge informativo -->
                        <template v-else>
                            <span class="flex items-center gap-1 text-xs text-zinc-500">
                                <i class="pi pi-lock text-xs"></i>
                                Encerrado
                            </span>
                        </template>
                    </div>
                </div>
            </div>
            
            <Paginator 
                v-if="agendamentos.total > 0"
                :rows="agendamentos.per_page"
                :totalRecords="agendamentos.total"
                :first="(agendamentos.current_page - 1) * agendamentos.per_page"
                @page="onPageChange"
                class="mt-4 border-none bg-transparent justify-center"
            />
        </div>

        <!-- Estado vazio -->
        <div v-else class="glass-card p-10 text-center">
            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-primary-500/10">
                <i class="pi pi-calendar text-3xl text-primary-400"></i>
            </div>
            <h3 class="mb-1 text-base font-semibold text-white">Nenhum agendamento encontrado</h3>
            <p class="text-sm text-zinc-400">
                {{ props.filtros?.status === 'todos' ? 'Você ainda não possui agendamentos correspondentes aos filtros.' : 'Nenhum agendamento com este status ou filtro.' }}
            </p>
        </div>

        <ConfirmDialog />

        <!-- Popover de Contato do Cliente -->
        <Popover ref="op">
            <div class="flex flex-col gap-4 p-2 w-[280px]" v-if="selectedAgendamento?.cliente">
                <div class="flex items-center gap-2">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-500/20">
                        <i class="pi pi-user text-xl text-primary-400"></i>
                    </div>
                    <div>
                        <span class="block text-sm font-bold text-white">{{ selectedAgendamento.cliente.nome_completo }}</span>
                        <span class="block text-xs text-zinc-400">Cliente desde {{ formatarAno(selectedAgendamento.cliente.created_at) }}</span>
                    </div>
                </div>
                
                <div class="border-t border-white/10 my-1"></div>
                
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <i class="pi pi-phone text-zinc-400"></i>
                        <span class="text-sm text-zinc-200">
                            {{ selectedAgendamento.cliente.perfil?.telefone ? formatarTelefone(selectedAgendamento.cliente.perfil.telefone) : 'Não informado' }}
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <i class="pi pi-envelope text-zinc-400"></i>
                        <span class="text-sm text-zinc-200 truncate" :title="selectedAgendamento.cliente.email">
                            {{ selectedAgendamento.cliente.email }}
                        </span>
                    </div>
                    <div class="flex items-center gap-3">
                        <i class="pi pi-calendar text-zinc-400"></i>
                        <span class="text-sm text-zinc-200">
                            <template v-if="selectedAgendamento.cliente.perfil?.data_nascimento">
                                {{ formatarDataCurta(selectedAgendamento.cliente.perfil.data_nascimento) }} 
                                <span class="text-zinc-500">({{ calcularIdade(selectedAgendamento.cliente.perfil.data_nascimento) }} anos)</span>
                            </template>
                            <template v-else>Não informado</template>
                        </span>
                    </div>
                </div>
            </div>
        </Popover>

        <!-- Dialog de Novo Agendamento (Profissional) -->
        <NovoAgendamentoDialog
            v-model:visible="dialogNovoAgendamentoVisible"
            :servicos="servicos"
            :horariosFuncionamento="horariosFuncionamento"
            :bloqueios="bloqueios"
        />

    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useConfirm } from 'primevue/useconfirm'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import ConfirmDialog from 'primevue/confirmdialog'
import Popover from 'primevue/popover'
import InputText from 'primevue/inputtext'
import DatePicker from 'primevue/datepicker'
import Paginator from 'primevue/paginator'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import NovoAgendamentoDialog from '@/Components/Profissional/NovoAgendamentoDialog.vue'
import { formatarData, formatarHora, formatarMoeda, isFuture, formatarTelefone, calcularIdade, formatarDataCurta } from '@/Utils/formatters'

defineOptions({ layout: DashboardLayout })

const props = defineProps({
    agendamentos: { type: Object, default: () => ({ data: [], total: 0, current_page: 1, per_page: 15 }) },
    filtros: { type: Object, default: () => ({ status: 'todos' }) },
    servicos: { type: Array, default: () => [] },
    horariosFuncionamento: { type: Array, default: () => [] },
    bloqueios: { type: Array, default: () => [] },
})

const confirm = useConfirm()

// ── Estado do Popover ────────────────────────────────────────────────────────
const op = ref()
const selectedAgendamento = ref(null)
const modalSucessoVisivel = ref(false)
const agendamentoParaStatus = ref(null)
const erroGlobal = ref('')
const dialogNovoAgendamentoVisible = ref(false)

const toggleContato = (event, ag) => {
    selectedAgendamento.value = ag
    op.value.toggle(event)
}

function formatarAno(data) {
    if (!data) return ''
    return new Date(data).getFullYear()
}

// ── Configuração visual por status ───────────────────────────────────────────
const statusConfig = {
    pendente:              { label: 'Pendente',                  severity: 'warn'      },
    confirmado:            { label: 'Confirmado',                severity: 'success'   },
    em_atendimento:        { label: 'Em Andamento',              severity: 'info'      },
    concluido:             { label: 'Concluído',                 severity: 'secondary' },
    cancelado_cliente:     { label: 'Cancelado pelo Cliente',    severity: 'danger'    },
    cancelado_profissional:{ label: 'Cancelado',                 severity: 'danger'    },
    nao_compareceu:        { label: 'Não Compareceu',            severity: 'warn'      },
}

// ── Busca e Filtros Avançados ────────────────────────────────────────────────
const filtrosItems = [
    { value: 'todos',                  label: 'Todos'           },
    { value: 'pendente',               label: 'Pendentes'       },
    { value: 'confirmado',             label: 'Confirmados'     },
    { value: 'em_atendimento',         label: 'Em Andamento'    },
    { value: 'concluido',              label: 'Concluídos'      },
    { value: 'cancelado_profissional', label: 'Cancelados'      },
]

// Inicializar estado dos filtros a partir das props
const filtroBusca = ref({
    cliente: props.filtros?.cliente || '',
    datas: null
})

// Traduzir as strings de datas em objetos Date para o DatePicker
if (props.filtros?.data_inicio && props.filtros?.data_fim) {
    filtroBusca.value.datas = [
        new Date(props.filtros.data_inicio + 'T12:00:00'),
        new Date(props.filtros.data_fim + 'T12:00:00')
    ]
} else if (props.filtros?.data_inicio) {
    filtroBusca.value.datas = [new Date(props.filtros.data_inicio + 'T12:00:00'), null]
}

const hasActiveFilters = computed(() => {
    return filtroBusca.value.cliente !== '' || (filtroBusca.value.datas !== null && filtroBusca.value.datas.length > 0)
})

function dispararFiltros(novosFiltros) {
    const payload = { ...props.filtros, ...novosFiltros }
    
    // Formatar datas para o envio
    if (filtroBusca.value.datas && filtroBusca.value.datas[0]) {
        const d1 = filtroBusca.value.datas[0]
        payload.data_inicio = `${d1.getFullYear()}-${String(d1.getMonth()+1).padStart(2, '0')}-${String(d1.getDate()).padStart(2, '0')}`
        
        if (filtroBusca.value.datas[1]) {
            const d2 = filtroBusca.value.datas[1]
            payload.data_fim = `${d2.getFullYear()}-${String(d2.getMonth()+1).padStart(2, '0')}-${String(d2.getDate()).padStart(2, '0')}`
        } else {
            delete payload.data_fim
        }
    } else {
        delete payload.data_inicio
        delete payload.data_fim
    }
    
    payload.cliente = filtroBusca.value.cliente || undefined
    
    // Reseta paginação ao mudar filtros se a origem nao for o onPageChange
    if (!novosFiltros.page) {
        payload.page = 1 
    }

    router.get('/profissional/agendamentos', payload, { preserveState: true, replace: true })
}

function aplicarBusca() {
    dispararFiltros({})
}

function limparBusca() {
    filtroBusca.value.cliente = ''
    filtroBusca.value.datas = null
    dispararFiltros({})
}

function aplicarFiltroStatus(status) {
    dispararFiltros({ status })
}

function onPageChange(event) {
    const page = event.page + 1
    dispararFiltros({ page })
}

// ── Transições de status ─────────────────────────────────────────────────────

/** Transição direta sem confirmação */
function alterarStatus(ag, novoStatus) {
    router.put(
        `/profissional/agendamentos/${ag.id}/status`,
        { status: novoStatus },
        { preserveScroll: true }
    )
}

/** Transição com ConfirmDialog antes de executar */
function confirmarAcao(ag, novoStatus, mensagem) {
    confirm.require({
        message:     mensagem,
        header:      'Confirmar Ação',
        icon:        'pi pi-exclamation-triangle',
        rejectLabel: 'Cancelar',
        rejectProps: { severity: 'secondary', text: true },
        acceptLabel: 'Confirmar',
        acceptProps: { severity: 'danger' },
        accept:      () => alterarStatus(ag, novoStatus),
    })
}
</script>
