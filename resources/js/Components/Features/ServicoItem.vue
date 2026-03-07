<template>
    <div class="glass-card p-3">
        <div class="flex items-center gap-3">
            <!-- Imagem -->
            <div class="flex h-28 w-28 shrink-0 items-center justify-center overflow-hidden rounded-lg" style="background: rgba(var(--p-primary-500), 0.1)">
                <i class="pi pi-scissors text-3xl" style="color: var(--p-primary-color)"></i>
            </div>

            <!-- Info -->
            <div class="flex flex-1 flex-col space-y-2 overflow-hidden">
                <h3 class="text-sm font-semibold text-white">{{ servico.nome }}</h3>
                <p class="truncate text-sm text-zinc-400">{{ servico.descricao }}</p>

                <!-- Preço + Botão -->
                <div class="flex items-center justify-between">
                    <span class="text-sm font-bold" style="color: var(--p-primary-color)">
                        {{ formatarMoeda(servico.preco) }}
                    </span>
                    <Button
                        label="Reservar"
                        severity="secondary"
                        size="small"
                        class="ml-auto"
                        @click="abrirAgendamento"
                    />
                </div>
            </div>
        </div>
    </div>

    <!-- Drawer de Agendamento -->
    <Drawer
        v-model:visible="drawerVisivel"
        position="right"
        class="w-full sm:w-96"
        :showCloseIcon="true"
    >
        <template #header>
            <span class="text-lg font-semibold">Fazer Reserva</span>
        </template>

        <!-- Calendário -->
        <div class="border-b border-white/10 pb-5">
            <DatePicker
                v-model="dataSelecionada"
                inline
                :minDate="hoje"
                :locale="localePtBR"
                class="w-full"
            />
        </div>

        <!-- Horários -->
        <div v-if="dataSelecionada" class="border-b border-white/10 py-5">
            <div v-if="horariosDisponiveis.length > 0" class="hide-scrollbar flex gap-2 overflow-x-auto">
                <Button
                    v-for="hora in horariosDisponiveis"
                    :key="hora"
                    :label="hora"
                    :severity="horaSelecionada === hora ? undefined : 'secondary'"
                    :outlined="horaSelecionada !== hora"
                    size="small"
                    rounded
                    @click="horaSelecionada = hora"
                />
            </div>
            <p v-else class="text-sm text-zinc-400">
                Não há horários disponíveis para este dia.
            </p>
        </div>

        <!-- Resumo -->
        <div v-if="dataHoraCompleta" class="py-5">
            <ResumoAgendamento
                :estabelecimento="nomeEstabelecimento"
                :servico="servico"
                :data="dataHoraCompleta"
            />
        </div>

        <!-- Botão Confirmar -->
        <div class="mt-auto pt-5">
            <Button
                label="Confirmar"
                icon="pi pi-check"
                class="w-full"
                :disabled="!dataSelecionada || !horaSelecionada"
                :loading="enviando"
                @click="confirmarAgendamento"
            />
        </div>
    </Drawer>

    <!-- Dialog de Login -->
    <Dialog
        v-model:visible="loginDialogVisivel"
        modal
        header="Faça login para continuar"
        class="w-[90%] max-w-md"
    >
        <p class="mb-4 text-sm text-zinc-400">
            Você precisa estar logado para fazer uma reserva.
        </p>
        <Link href="/login">
            <Button label="Ir para Login" icon="pi pi-sign-in" class="w-full" />
        </Link>
    </Dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { formatarMoeda } from '@/Utils/formatters'
import { HORARIOS_DISPONIVEIS } from '@/Constants/categorias'
import { isPast, isToday, set as setDate, format } from 'date-fns'
import Button from 'primevue/button'
import Drawer from 'primevue/drawer'
import DatePicker from 'primevue/datepicker'
import Dialog from 'primevue/dialog'
import ResumoAgendamento from './ResumoAgendamento.vue'
import { useToast } from 'primevue/usetoast'

const props = defineProps({
    servico: { type: Object, required: true },
    nomeEstabelecimento: { type: String, default: '' },
    estabelecimentoId: { type: [String, Number], default: '' },
    profissionalId: { type: [String, Number], default: '' }
})

const page = usePage()
const toast = useToast()

// State
const drawerVisivel = ref(false)
const loginDialogVisivel = ref(false)
const dataSelecionada = ref(null)
const horaSelecionada = ref(null)
const enviando = ref(false)
const agendamentosDoDia = ref([])
const hoje = new Date()

// PT-BR locale placeholder
const localePtBR = {}

// Horários disponíveis (filtrados)
const horariosDisponiveis = computed(() => {
    if (!dataSelecionada.value) return []

    return HORARIOS_DISPONIVEIS.filter(hora => {
        const [h, m] = hora.split(':').map(Number)
        const horarioDate = setDate(new Date(), { hours: h, minutes: m })

        if (isToday(dataSelecionada.value) && isPast(horarioDate)) {
            return false
        }

        const ocupado = agendamentosDoDia.value.some(a => {
            const d = new Date(a.data)
            return d.getHours() === h && d.getMinutes() === m
        })
        return !ocupado
    })
})

// Data+hora completa
const dataHoraCompleta = computed(() => {
    if (!dataSelecionada.value || !horaSelecionada.value) return null
    const [h, m] = horaSelecionada.value.split(':').map(Number)
    return setDate(new Date(dataSelecionada.value), { hours: h, minutes: m })
})

// Reset ao mudar data
watch(dataSelecionada, () => {
    horaSelecionada.value = null
    agendamentosDoDia.value = []
})

// Abrir agendamento
function abrirAgendamento() {
    if (!page.props.auth?.user) {
        loginDialogVisivel.value = true
        return
    }
    drawerVisivel.value = true
}

// Confirmar agendamento via Inertia
function confirmarAgendamento() {
    if (!dataHoraCompleta.value) return
    enviando.value = true

    // Formata data para ISO (yyyy-MM-dd HH:mm:ss)
    const dataFormatada = format(dataHoraCompleta.value, 'yyyy-MM-dd HH:mm:ss')

    router.post('/agendamentos', {
        profissional_id: props.profissionalId,
        servico_id: props.servico.id,
        data_hora_inicio: dataFormatada,
        preco: props.servico.preco,
        tempo_execucao_minutos: props.servico.tempo_execucao_minutos,
        nome_servico: props.servico.nome,
    }, {
        onSuccess: () => {
            toast.add({
                severity: 'success',
                summary: 'Reserva criada!',
                detail: 'Seu agendamento foi confirmado com sucesso.',
                life: 5000
            })
            drawerVisivel.value = false
            dataSelecionada.value = null
            horaSelecionada.value = null
        },
        onError: (errors) => {
            const mensagem = Object.values(errors).flat().join(', ') || 'Não foi possível criar a reserva. Tente novamente.'
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: mensagem,
                life: 5000
            })
        },
        onFinish: () => {
            enviando.value = false
        }
    })
}
</script>
