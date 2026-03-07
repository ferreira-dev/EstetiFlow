<template>
    <div class="glass-card">
        <div class="flex items-center justify-between p-4" @click="drawerVisivel = true" style="cursor: pointer">
            <!-- Info -->
            <div class="flex min-w-0 flex-col gap-2">
                <Tag :severity="tagSeverity" class="w-fit">
                    {{ labelStatus }}
                </Tag>
                <h3 class="font-semibold text-white">{{ nomeServico }}</h3>
                <div class="flex items-center gap-2">
                    <Avatar
                        :image="fotoEstabelecimento"
                        :label="nomeEstabelecimento?.charAt(0)"
                        shape="circle"
                        size="small"
                    />
                    <p class="truncate text-sm text-zinc-400">{{ nomeEstabelecimento }}</p>
                </div>
            </div>

            <!-- Data -->
            <div class="flex flex-col items-center justify-center border-l border-white/10 pl-5">
                <p class="text-sm capitalize text-zinc-400">{{ mes }}</p>
                <p class="text-2xl font-bold text-white">{{ dia }}</p>
                <p class="text-sm text-zinc-400">{{ hora }}</p>
            </div>
        </div>
    </div>

    <!-- Drawer de Detalhes -->
    <Drawer
        v-model:visible="drawerVisivel"
        position="right"
        class="w-[85%] sm:w-96"
        :showCloseIcon="true"
    >
        <template #header>
            <span class="text-lg font-semibold">Informações da Reserva</span>
        </template>

        <!-- Mapa placeholder -->
        <div class="relative mb-6 flex h-44 items-end overflow-hidden rounded-xl bg-zinc-800">
            <div class="absolute inset-0 flex items-center justify-center text-zinc-600">
                <i class="pi pi-map text-4xl"></i>
            </div>
            <div class="glass-card z-10 mx-4 mb-3 w-full p-3">
                <div class="flex items-center gap-3">
                    <Avatar
                        :image="fotoEstabelecimento"
                        :label="nomeEstabelecimento?.charAt(0)"
                        shape="circle"
                    />
                    <div class="min-w-0">
                        <h3 class="truncate font-semibold text-white">{{ nomeEstabelecimento }}</h3>
                        <p class="truncate text-xs text-zinc-400">{{ enderecoEstabelecimento }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status -->
        <Tag :severity="tagSeverity" class="mb-4">
            {{ labelStatus }}
        </Tag>

        <!-- Resumo -->
        <ResumoAgendamento
            :estabelecimento="nomeEstabelecimento"
            :servico="servicoResumo"
            :data="new Date(agendamento.data_hora_inicio)"
        />

        <!-- Ações -->
        <div class="mt-6 grid grid-cols-2 gap-3">
            <Button
                label="Voltar"
                severity="secondary"
                outlined
                @click="drawerVisivel = false"
            />
            <Button
                v-if="eAtivo"
                label="Cancelar Reserva"
                severity="danger"
                :loading="cancelando"
                @click="confirmarCancelamento"
            />
        </div>
    </Drawer>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { formatarMes, formatarDia, formatarHora, isFuture } from '@/Utils/formatters'
import { useConfirm } from 'primevue/useconfirm'
import { useToast } from 'primevue/usetoast'
import Tag from 'primevue/tag'
import Avatar from 'primevue/avatar'
import Button from 'primevue/button'
import Drawer from 'primevue/drawer'
import ResumoAgendamento from './ResumoAgendamento.vue'

const props = defineProps({
    agendamento: { type: Object, required: true }
})

const confirm = useConfirm()
const toast = useToast()
const drawerVisivel = ref(false)
const cancelando = ref(false)

// ── Dados derivados dos relacionamentos reais ──────────────────────
const nomeServico = computed(() =>
    props.agendamento.itens?.[0]?.nome_servico || 'Serviço'
)
const nomeEstabelecimento = computed(() =>
    props.agendamento.profissional?.estabelecimento?.nome_fantasia || ''
)
const fotoEstabelecimento = computed(() =>
    props.agendamento.profissional?.estabelecimento?.foto_capa_url || ''
)
const enderecoEstabelecimento = computed(() => {
    const est = props.agendamento.profissional?.estabelecimento
    if (!est) return ''
    return [est.logradouro, est.numero, est.bairro, est.cidade, est.estado]
        .filter(Boolean)
        .join(', ')
})

// Serviço para o ResumoAgendamento (precisa de nome e preco)
const servicoResumo = computed(() => ({
    nome: props.agendamento.itens?.[0]?.nome_servico || 'Serviço',
    preco: props.agendamento.valor_total || props.agendamento.itens?.[0]?.preco_praticado || 0,
}))

// ── Status ─────────────────────────────────────────────────────────

/** Pode cancelar: agendamento futuro e ativo */
const eAtivo = computed(() =>
    isFuture(new Date(props.agendamento.data_hora_inicio)) &&
    ['pendente', 'confirmado'].includes(props.agendamento.status)
)

const labelStatus = computed(() => {
    const map = {
        pendente:               'Pendente',
        confirmado:             'Confirmado',
        em_atendimento:         'Em Andamento',
        concluido:              'Concluído',
        cancelado_cliente:      'Cancelado',
        cancelado_profissional: 'Cancelado pelo Profissional',
        nao_compareceu:         'Não Compareceu',
    }
    return map[props.agendamento.status] || props.agendamento.status
})

/**
 * Severidade do Tag PrimeVue conforme status.
 * Cores semânticas: verde=ativo/concluído, amarelo=pendente,
 * azul=em andamento, vermelho=cancelado, laranja=não compareceu.
 */
const tagSeverity = computed(() => {
    const map = {
        pendente:               'warn',
        confirmado:             'success',
        em_atendimento:         'info',
        concluido:              'success',
        cancelado_cliente:      'danger',
        cancelado_profissional: 'danger',
        nao_compareceu:         'warn',
    }
    return map[props.agendamento.status] ?? 'secondary'
})

// ── Data/Hora formatadas ───────────────────────────────────────────
const mes = computed(() => formatarMes(props.agendamento.data_hora_inicio))
const dia = computed(() => formatarDia(props.agendamento.data_hora_inicio))
const hora = computed(() => formatarHora(props.agendamento.data_hora_inicio))

// ── Cancelamento ───────────────────────────────────────────────────
function confirmarCancelamento() {
    confirm.require({
        message: 'Ao cancelar, você perderá sua reserva e não poderá recuperá-la. Essa ação é irreversível.',
        header: 'Cancelar Reserva?',
        icon: 'pi pi-exclamation-triangle',
        rejectLabel: 'Voltar',
        acceptLabel: 'Confirmar',
        rejectProps: { severity: 'secondary', outlined: true },
        acceptProps: { severity: 'danger' },
        accept: () => {
            cancelando.value = true
            router.delete(`/agendamentos/${props.agendamento.id}`, {
                onSuccess: () => {
                    drawerVisivel.value = false
                    toast.add({
                        severity: 'success',
                        summary: 'Reserva cancelada',
                        detail: 'Sua reserva foi cancelada com sucesso.',
                        life: 4000
                    })
                },
                onError: () => {
                    toast.add({
                        severity: 'error',
                        summary: 'Erro',
                        detail: 'Não foi possível cancelar a reserva.',
                        life: 4000
                    })
                },
                onFinish: () => {
                    cancelando.value = false
                }
            })
        }
    })
}
</script>
