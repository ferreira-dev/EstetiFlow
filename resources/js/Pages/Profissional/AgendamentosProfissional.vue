<template>
    <div class="mx-auto max-w-4xl space-y-6">

        <!-- Header -->
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-500/20">
                <i class="pi pi-calendar text-lg text-primary-400"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">Agendamentos</h2>
                <p class="text-sm text-zinc-400">Gerencie seus atendimentos e atualize os status</p>
            </div>
        </div>

        <!-- Filtros de status -->
        <div class="flex flex-wrap gap-2">
            <button
                v-for="filtro in filtros"
                :key="filtro.value"
                :class="[
                    'rounded-full px-4 py-1.5 text-sm font-medium transition-all duration-150',
                    filtroAtual === filtro.value
                        ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/25'
                        : 'bg-white/5 text-zinc-400 hover:bg-white/10 hover:text-zinc-200',
                ]"
                @click="aplicarFiltro(filtro.value)"
            >
                {{ filtro.label }}
            </button>
        </div>

        <!-- Lista de agendamentos -->
        <div v-if="agendamentos.length > 0" class="space-y-3">
            <div
                v-for="ag in agendamentos"
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
        </div>

        <!-- Estado vazio -->
        <div v-else class="glass-card p-10 text-center">
            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-primary-500/10">
                <i class="pi pi-calendar text-3xl text-primary-400"></i>
            </div>
            <h3 class="mb-1 text-base font-semibold text-white">Nenhum agendamento encontrado</h3>
            <p class="text-sm text-zinc-400">
                {{ filtroAtual === 'todos' ? 'Você ainda não possui agendamentos.' : 'Nenhum agendamento com este status.' }}
            </p>
        </div>

        <ConfirmDialog />
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { useConfirm } from 'primevue/useconfirm'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import ConfirmDialog from 'primevue/confirmdialog'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatarData, formatarHora, formatarMoeda } from '@/Utils/formatters'

defineOptions({ layout: DashboardLayout })

const props = defineProps({
    agendamentos: { type: Array, default: () => [] },
    filtroAtual:  { type: String, default: 'todos' },
})

const confirm = useConfirm()

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

// ── Filtros disponíveis ──────────────────────────────────────────────────────
const filtros = [
    { value: 'todos',              label: 'Todos'           },
    { value: 'pendente',           label: 'Pendentes'       },
    { value: 'confirmado',         label: 'Confirmados'     },
    { value: 'em_atendimento',     label: 'Em Andamento'    },
    { value: 'concluido',          label: 'Concluídos'      },
    { value: 'cancelado_profissional', label: 'Cancelados'  },
]

function aplicarFiltro(status) {
    router.get('/profissional/agendamentos', { status }, { preserveState: true, replace: true })
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
