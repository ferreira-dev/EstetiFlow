<template>
    <div class="glass-card p-3">
        <div class="flex items-center gap-3">
            <!-- Imagem -->
            <div class="flex h-28 w-28 shrink-0 items-center justify-center overflow-hidden rounded-lg" style="background: color-mix(in srgb, var(--p-primary-500) 10%, transparent)">
                <i :class="iconeCategoria(servico.categoria)" class="text-3xl" style="color: var(--p-primary-color)"></i>
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
                :maxDate="dataMaxima"
                :disabledDates="datasBlockeadas"
                class="calendario-reserva w-full"
            />
        </div>

        <!-- Horários -->
        <div v-if="dataSelecionada" class="border-b border-white/10 py-5">
            <p v-if="!diaHabilitado" class="text-sm text-zinc-400">
                O profissional não atende neste dia.
            </p>
            <div v-else-if="horariosDisponiveis.length > 0" class="hide-scrollbar flex flex-wrap gap-2 overflow-x-auto">
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
        header="Entre para reservar"
        class="w-[90%] max-w-md"
    >
        <p class="mb-4 text-sm text-zinc-400">
            Para reservar <strong class="text-zinc-200">{{ servico.nome }}</strong>, entre ou crie sua conta.
            Depois você volta direto para escolher o horário.
        </p>
        <div class="flex flex-col gap-2">
            <!-- Button renderizado como Link (um único <a>, sem <button> aninhado) -->
            <Button
                :as="Link"
                :href="`/login?redirect=${urlRetornoCodificada}`"
                label="Entrar"
                icon="pi pi-sign-in"
                class="w-full"
            />
            <Button
                :as="Link"
                :href="`/registro?redirect=${urlRetornoCodificada}`"
                label="Criar conta"
                icon="pi pi-user-plus"
                severity="secondary"
                outlined
                class="w-full"
            />
        </div>
    </Dialog>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { formatarMoeda } from '@/Utils/formatters'
import { iconeCategoria } from '@/Constants/categorias'
import { isPast, isToday, set as setDate, format } from 'date-fns'
import Button from 'primevue/button'
import Drawer from 'primevue/drawer'
import DatePicker from 'primevue/datepicker'
import Dialog from 'primevue/dialog'
import ResumoAgendamento from './ResumoAgendamento.vue'
import { useToast } from 'primevue/usetoast'

const props = defineProps({
    servico:               { type: Object,           required: true },
    nomeEstabelecimento:   { type: String,           default: '' },
    estabelecimentoId:     { type: [String, Number], default: '' },
    horariosFuncionamento: { type: Array,            default: () => [] },
    bloqueios:             { type: Array,            default: () => [] },
    // Intervalos já reservados do profissional ({ data_hora_inicio, data_hora_fim }), sem dados do cliente
    ocupados:              { type: Array,            default: () => [] },
    janelaReservaDias:     { type: Number,           default: 30 },
    agendaOnline:          { type: Boolean,          default: true },
    telefoneWhatsapp:      { type: String,           default: '' },
})

const page  = usePage()
const toast = useToast()

// ── State ─────────────────────────────────────────────────────────────────
const drawerVisivel      = ref(false)
const loginDialogVisivel = ref(false)
const dataSelecionada    = ref(null)
const horaSelecionada    = ref(null)
const enviando           = ref(false)
const hoje               = new Date()

// Último dia reservável (mesma janela validada no backend)
const dataMaxima = computed(() => {
    const d = new Date(hoje)
    d.setDate(d.getDate() + props.janelaReservaDias)
    return d
})

// Intervalos ocupados convertidos uma única vez para Date
const intervalosOcupados = computed(() =>
    props.ocupados.map(o => ({ inicio: new Date(o.data_hora_inicio), fim: new Date(o.data_hora_fim) }))
)

// ── Gera slots de 30 min entre horaInicio e horaFim - duração ─────────────
function gerarSlots(horaInicio, horaFim, duracaoMinutos) {
    const slots = []
    const [hI, mI] = horaInicio.slice(0, 5).split(':').map(Number)
    const [hF, mF] = horaFim.slice(0, 5).split(':').map(Number)

    let cur = hI * 60 + mI
    const fim = hF * 60 + mF - duracaoMinutos // O slot deve terminar dentro do horário

    while (cur <= fim) {
        slots.push(`${String(Math.floor(cur / 60)).padStart(2, '0')}:${String(cur % 60).padStart(2, '0')}`)
        cur += 30
    }
    return slots
}

// ── Horário do dia selecionado para ESTE profissional ─────────────────────
const horarioDia = computed(() => {
    if (!dataSelecionada.value) return null
    const dia = dataSelecionada.value.getDay() // 0=Dom … 6=Sáb
    return props.horariosFuncionamento.find(h => h.dia_semana === dia) ?? null
})

const diaHabilitado = computed(() => !!horarioDia.value)

// ── Slots filtrados: horários + bloqueios ─────────────────────────────────
const horariosDisponiveis = computed(() => {
    if (!dataSelecionada.value || !horarioDia.value) return []

    const duracao  = props.servico.tempo_execucao_minutos
    const diaSem   = dataSelecionada.value.getDay()
    const slots    = gerarSlots(horarioDia.value.hora_inicio, horarioDia.value.hora_fim, duracao)

    return slots.filter(hora => {
        const [h, m] = hora.split(':').map(Number)
        const slotInicioMin = h * 60 + m
        const slotFimMin    = slotInicioMin + duracao

        // Filtra horários já passados (quando hoje está selecionado)
        if (isToday(dataSelecionada.value)) {
            const slotDate = setDate(new Date(), { hours: h, minutes: m, seconds: 0 })
            if (isPast(slotDate)) return false
        }

        // ── Filtra horários já reservados (considera a duração do serviço) ─
        const slotInicio = setDate(new Date(dataSelecionada.value), { hours: h, minutes: m, seconds: 0, milliseconds: 0 })
        const slotFim    = new Date(slotInicio.getTime() + duracao * 60_000)
        if (intervalosOcupados.value.some(o => slotInicio < o.fim && slotFim > o.inicio)) return false

        // ── Filtra bloqueios ─────────────────────────────────────────
        for (const b of props.bloqueios) {
            if (!b.recorrente) {
                // Bloqueio pontual: compara datetime completo
                if (!b.data_inicio || !b.data_fim) continue

                const bInicio = new Date(b.data_inicio)
                const bFim    = new Date(b.data_fim)

                // Sobreposição: NOT (slotFim <= bInicio OR slotInicio >= bFim)
                if (!(slotFim <= bInicio || slotInicio >= bFim)) return false
            } else {
                // Bloqueio recorrente: verifica dia da semana
                if (b.dias_semana !== null && !b.dias_semana.includes(diaSem)) continue

                // Verifica sobreposição de horário
                if (!b.hora_inicio || !b.hora_fim) continue
                const [bhi, bhm] = b.hora_inicio.slice(0, 5).split(':').map(Number)
                const [bhfi, bhfm] = b.hora_fim.slice(0, 5).split(':').map(Number)
                const bInicioMin = bhi * 60 + bhm
                const bFimMin    = bhfi * 60 + bhfm

                if (!(slotFimMin <= bInicioMin || slotInicioMin >= bFimMin)) return false
            }
        }

        return true
    })
})

// ── Datas inteiramente bloqueadas (para desabilitar no calendar) ──────────
const datasBlockeadas = computed(() => {
    const datas = []
    const diasSemFuncionamento = [0, 1, 2, 3, 4, 5, 6].filter(
        d => !props.horariosFuncionamento.some(h => h.dia_semana === d)
    )

    // Dias sem funcionamento dentro da janela de reserva
    for (let i = 0; i <= props.janelaReservaDias; i++) {
        const d = new Date(hoje)
        d.setDate(d.getDate() + i)
        if (diasSemFuncionamento.includes(d.getDay())) {
            datas.push(new Date(d))
        }
    }

    // Adiciona datas de bloqueios não-recorrentes que cobrem o dia inteiro
    props.bloqueios
        .filter(b => !b.recorrente && b.data_inicio && b.data_fim)
        .forEach(b => {
            const ini = new Date(b.data_inicio)
            const fim = new Date(b.data_fim)
            // Só bloqueia no picker datas em que o profissional está fechado o dia todo
            // (início 00:00 e fim >= 23:59, ou range de múltiplos dias)
            const iniH = ini.getHours() * 60 + ini.getMinutes()
            const fimH = fim.getHours() * 60 + fim.getMinutes()
            if (iniH === 0 && fimH >= 23 * 60 + 59) {
                for (let d = new Date(ini); d <= fim; d.setDate(d.getDate() + 1)) {
                    datas.push(new Date(d))
                }
            }
        })

    return datas
})

// ── Data+hora completa ────────────────────────────────────────────────────
const dataHoraCompleta = computed(() => {
    if (!dataSelecionada.value || !horaSelecionada.value) return null
    const [h, m] = horaSelecionada.value.split(':').map(Number)
    return setDate(new Date(dataSelecionada.value), { hours: h, minutes: m, seconds: 0 })
})

// Reset hora ao mudar data
watch(dataSelecionada, () => { horaSelecionada.value = null })

// Se os slots forem recarregados (ex.: conflito) e o horário escolhido sumir, limpa a seleção
watch(horariosDisponiveis, (slots) => {
    if (horaSelecionada.value && !slots.includes(horaSelecionada.value)) {
        horaSelecionada.value = null
    }
})

// ── Retorno pós-login/registro ────────────────────────────────────────────
// Página atual + ?servico=ID: após entrar, o drawer deste serviço reabre sozinho
const urlRetornoCodificada = computed(() => {
    const caminho = page.url.split('?')[0]
    return encodeURIComponent(`${caminho}?servico=${props.servico.servico_profissional_id}`)
})

onMounted(() => {
    const servicoNaUrl = new URLSearchParams(window.location.search).get('servico')
    if (props.agendaOnline && servicoNaUrl && Number(servicoNaUrl) === Number(props.servico.servico_profissional_id)) {
        abrirAgendamento()
    }
})

// ── Actions ───────────────────────────────────────────────────────────────
function abrirAgendamento() {
    // Se agenda online desativada → redireciona para WhatsApp
    if (!props.agendaOnline) {
        const telefone = props.telefoneWhatsapp.replace(/\D/g, '')
        const url = `https://wa.me/55${telefone}`
        window.open(url, '_blank', 'noopener,noreferrer')
        return
    }

    if (!page.props.auth?.user) {
        loginDialogVisivel.value = true
        return
    }
    drawerVisivel.value = true
}

function confirmarAgendamento() {
    if (!dataHoraCompleta.value) return
    enviando.value = true

    // Preço, duração e nome são definidos pelo backend a partir do cadastro do serviço
    router.post('/agendamentos', {
        servico_profissional_id: props.servico.servico_profissional_id,
        data_hora_inicio:        format(dataHoraCompleta.value, 'yyyy-MM-dd HH:mm:ss'),
    }, {
        // Em erro (ex.: horário acabou de ser reservado) o back() devolve a página com os
        // intervalos ocupados atualizados; preservar o estado mantém o drawer aberto.
        preserveState:  true,
        preserveScroll: true,
        // O sucesso é anunciado pelo flash do backend (toast global do layout)
        onSuccess: () => {
            drawerVisivel.value  = false
            dataSelecionada.value = null
            horaSelecionada.value = null
        },
        onError: (errors) => {
            const msg = Object.values(errors).flat().join(', ') || 'Não foi possível criar a reserva.'
            toast.add({ severity: 'error', summary: 'Erro', detail: msg, life: 5000 })
        },
        onFinish: () => { enviando.value = false },
    })
}
</script>

<style scoped>
/* Calendário inline ocupa a largura do drawer sem cortar a coluna de sábado */
.calendario-reserva :deep(.p-datepicker-panel) {
    width: 100%;
    min-width: 0;
}

.calendario-reserva :deep(.p-datepicker-day-view) {
    width: 100%;
    table-layout: fixed;
}

.calendario-reserva :deep(.p-datepicker-day-cell) {
    padding: 0.125rem;
}

.calendario-reserva :deep(.p-datepicker-day) {
    width: 2.25rem;
    height: 2.25rem;
    margin: 0 auto;
}
</style>
