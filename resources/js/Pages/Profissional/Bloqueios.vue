<template>
    <div class="mx-auto max-w-3xl space-y-6">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-500/20">
                    <i class="pi pi-ban text-lg text-orange-400"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white">Bloqueios de Agenda</h2>
                    <p class="text-sm text-zinc-400">Bloqueie períodos, folgas ou intervalos recorrentes</p>
                </div>
            </div>
            <Button label="Novo Bloqueio" icon="pi pi-plus" @click="abrirDialog" />
        </div>

        <!-- Lista -->
        <div v-if="bloqueios.length > 0" class="space-y-3">
            <div
                v-for="b in bloqueios"
                :key="b.id"
                class="glass-card flex items-start gap-4 p-4"
            >
                <!-- Ícone tipo -->
                <div :class="['flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg', tipoConfig[b.tipo]?.bg]">
                    <i :class="['text-base', tipoConfig[b.tipo]?.icon, tipoConfig[b.tipo]?.cor]"></i>
                </div>

                <!-- Dados -->
                <div class="flex-1 min-w-0 space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <Tag :value="tipoConfig[b.tipo]?.label ?? b.tipo" :severity="tipoConfig[b.tipo]?.tag" />
                        <Tag v-if="b.recorrente" value="Recorrente" severity="info" icon="pi pi-refresh" />
                        <span v-if="b.motivo" class="text-sm text-zinc-300">{{ b.motivo }}</span>
                    </div>

                    <!-- Período / Horário -->
                    <p class="text-sm text-zinc-400">
                        <template v-if="!b.recorrente">
                            <i class="pi pi-calendar mr-1 text-xs"></i>
                            {{ formatarDataHora(b.data_inicio) }}
                            <span v-if="b.data_fim && b.data_inicio !== b.data_fim"> → {{ formatarDataHora(b.data_fim) }}</span>
                        </template>
                        <template v-else>
                            <i class="pi pi-clock mr-1 text-xs"></i>
                            {{ b.hora_inicio?.slice(0,5) }} – {{ b.hora_fim?.slice(0,5) }}
                            <span class="ml-2 text-zinc-500">
                                {{ b.dias_semana ? formatarDias(b.dias_semana) : 'Todos os dias' }}
                            </span>
                        </template>
                    </p>
                </div>

                <!-- Remover -->
                <Button icon="pi pi-trash" severity="danger" text rounded v-tooltip.top="'Remover'" @click="confirmarRemocao(b)" />
            </div>
        </div>

        <!-- Estado vazio -->
        <div v-else class="glass-card p-10 text-center">
            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-orange-500/10">
                <i class="pi pi-calendar-times text-3xl text-orange-400"></i>
            </div>
            <h3 class="mb-1 text-base font-semibold text-white">Nenhum bloqueio cadastrado</h3>
            <p class="text-sm text-zinc-400">Adicione férias, folgas ou intervalos para bloquear horários.</p>
        </div>

        <!-- Dialog de criação -->
        <Dialog v-model:visible="dialogVisivel" header="Novo Bloqueio" modal :style="{ width: '460px' }">
            <div class="flex flex-col gap-4 pt-2">

                <!-- Toggle recorrente -->
                <div class="glass-card flex items-center justify-between p-4">
                    <div>
                        <p class="text-sm font-medium text-white">Bloqueio Recorrente</p>
                        <p class="text-xs text-zinc-400">Ex: intervalo de almoço todo dia</p>
                    </div>
                    <ToggleSwitch v-model="form.recorrente" />
                </div>

                <!-- Tipo -->
                <div class="flex flex-col gap-1">
                    <label class="text-sm text-zinc-300">Tipo</label>
                    <Select v-model="form.tipo" :options="tiposOpcoes" optionLabel="label" optionValue="value" placeholder="Selecione" class="w-full" />
                </div>

                <!-- Motivo -->
                <div class="flex flex-col gap-1">
                    <label class="text-sm text-zinc-300">Motivo <span class="text-zinc-500">(opcional)</span></label>
                    <InputText v-model="form.motivo" placeholder="Ex: Consulta médica" class="w-full" maxlength="255" />
                </div>

                <!-- ── Não-recorrente: data+hora início e fim ── -->
                <template v-if="!form.recorrente">
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="text-sm text-zinc-300">Início</label>
                            <DatePicker
                                v-model="form.data_inicio"
                                showTime hourFormat="24"
                                dateFormat="dd/mm/yy"
                                showIcon
                                class="w-full"
                                :minDate="hoje"
                                placeholder="Data e hora"
                            />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm text-zinc-300">Fim</label>
                            <DatePicker
                                v-model="form.data_fim"
                                showTime hourFormat="24"
                                dateFormat="dd/mm/yy"
                                showIcon
                                class="w-full"
                                :minDate="form.data_inicio ?? hoje"
                                placeholder="Data e hora"
                            />
                        </div>
                    </div>
                </template>

                <!-- ── Recorrente: faixa horária + dias da semana ── -->
                <template v-else>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="flex flex-col gap-1">
                            <label class="text-sm text-zinc-300">Hora Início</label>
                            <InputMask v-model="form.hora_inicio" mask="99:99" placeholder="13:00" class="w-full" />
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="text-sm text-zinc-300">Hora Fim</label>
                            <InputMask v-model="form.hora_fim" mask="99:99" placeholder="14:00" class="w-full" />
                        </div>
                    </div>

                    <!-- Dias da semana -->
                    <div class="flex flex-col gap-2">
                        <label class="text-sm text-zinc-300">Dias <span class="text-zinc-500">(vazio = todos os dias)</span></label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="d in diasOpcoes"
                                :key="d.value"
                                type="button"
                                :class="[
                                    'rounded-full px-3 py-1 text-xs font-medium transition-all',
                                    form.dias_semana.includes(d.value)
                                        ? 'bg-primary-500 text-white'
                                        : 'bg-white/5 text-zinc-400 hover:bg-white/10',
                                ]"
                                @click="toggleDia(d.value)"
                            >
                                {{ d.label }}
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <template #footer>
                <Button label="Cancelar" severity="secondary" text @click="dialogVisivel = false" />
                <Button label="Salvar" icon="pi pi-check" :loading="salvando" :disabled="!formValido" @click="salvar" />
            </template>
        </Dialog>

        <ConfirmDialog />
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useConfirm } from 'primevue/useconfirm'
import { format } from 'date-fns'
import { ptBR } from 'date-fns/locale'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import InputMask from 'primevue/inputmask'
import Select from 'primevue/select'
import DatePicker from 'primevue/datepicker'
import ToggleSwitch from 'primevue/toggleswitch'
import ConfirmDialog from 'primevue/confirmdialog'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

defineOptions({ layout: DashboardLayout })

const props = defineProps({
    bloqueios: { type: Array, default: () => [] },
})

const confirm = useConfirm()
const hoje    = new Date()

// ── Dialog state ─────────────────────────────────────────────────────────
const dialogVisivel = ref(false)
const salvando      = ref(false)

function formVazio() {
    return { recorrente: false, tipo: 'folga', motivo: '', data_inicio: null, data_fim: null, hora_inicio: '', hora_fim: '', dias_semana: [] }
}
const form = ref(formVazio())

function abrirDialog() {
    form.value = formVazio()
    dialogVisivel.value = true
}

// ── Configurações visuais ────────────────────────────────────────────────
const tipoConfig = {
    ferias: { label: 'Férias',  tag: 'success', icon: 'pi pi-sun',   cor: 'text-green-400',  bg: 'bg-green-500/10'  },
    folga:  { label: 'Folga',   tag: 'warn',    icon: 'pi pi-home',  cor: 'text-yellow-400', bg: 'bg-yellow-500/10' },
    almoco: { label: 'Almoço',  tag: 'info',    icon: 'pi pi-clock', cor: 'text-blue-400',   bg: 'bg-blue-500/10'   },
    outro:  { label: 'Outro',   tag: 'secondary',icon: 'pi pi-ban',  cor: 'text-zinc-400',   bg: 'bg-zinc-500/10'   },
}

const tiposOpcoes = [
    { label: 'Folga',   value: 'folga'  },
    { label: 'Férias',  value: 'ferias' },
    { label: 'Almoço',  value: 'almoco' },
    { label: 'Outro',   value: 'outro'  },
]

const diasOpcoes = [
    { label: 'Dom', value: 0 }, { label: 'Seg', value: 1 }, { label: 'Ter', value: 2 },
    { label: 'Qua', value: 3 }, { label: 'Qui', value: 4 }, { label: 'Sex', value: 5 },
    { label: 'Sáb', value: 6 },
]

function toggleDia(v) {
    const idx = form.value.dias_semana.indexOf(v)
    if (idx >= 0) form.value.dias_semana.splice(idx, 1)
    else form.value.dias_semana.push(v)
}

// ── Validação do form ─────────────────────────────────────────────────────
const formValido = computed(() => {
    if (!form.value.tipo) return false
    if (!form.value.recorrente) return !!(form.value.data_inicio && form.value.data_fim)
    return !!(form.value.hora_inicio && form.value.hora_fim)
})

// ── Salvar ────────────────────────────────────────────────────────────────
function salvar() {
    salvando.value = true
    const payload = {
        recorrente:   form.value.recorrente,
        tipo:         form.value.tipo,
        motivo:       form.value.motivo || null,
        dias_semana:  form.value.recorrente && form.value.dias_semana.length ? form.value.dias_semana : null,
    }

    if (!form.value.recorrente) {
        payload.data_inicio = formatISO(form.value.data_inicio)
        payload.data_fim    = formatISO(form.value.data_fim)
    } else {
        payload.hora_inicio = form.value.hora_inicio
        payload.hora_fim    = form.value.hora_fim
    }

    router.post('/profissional/bloqueios', payload, {
        onSuccess: () => { dialogVisivel.value = false },
        onFinish:  () => { salvando.value = false },
    })
}

// ── Remover ───────────────────────────────────────────────────────────────
function confirmarRemocao(b) {
    confirm.require({
        message:     `Remover bloqueio "${tipoConfig[b.tipo]?.label ?? b.tipo}"?`,
        header:      'Confirmar Remoção',
        icon:        'pi pi-exclamation-triangle',
        rejectLabel: 'Cancelar',
        rejectProps: { severity: 'secondary', text: true },
        acceptLabel: 'Remover',
        acceptProps: { severity: 'danger' },
        accept:      () => router.delete(`/profissional/bloqueios/${b.id}`, { preserveScroll: true }),
    })
}

// ── Helpers ───────────────────────────────────────────────────────────────
function formatISO(date) {
    if (!date) return null
    return format(new Date(date), "yyyy-MM-dd HH:mm:ss")
}

function formatarDataHora(dt) {
    if (!dt) return '—'
    const d = new Date(dt)
    return format(d, "dd/MM/yyyy 'às' HH:mm", { locale: ptBR })
}

function formatarDias(dias) {
    const nomes = { 0: 'Dom', 1: 'Seg', 2: 'Ter', 3: 'Qua', 4: 'Qui', 5: 'Sex', 6: 'Sáb' }
    return [...dias].sort().map(d => nomes[d]).join(', ')
}
</script>
