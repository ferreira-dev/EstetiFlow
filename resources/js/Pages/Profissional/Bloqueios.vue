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
                    <p class="text-sm text-zinc-400">Marque folgas, férias ou intervalos</p>
                </div>
            </div>
            <Button
                label="Novo Bloqueio"
                icon="pi pi-plus"
                @click="abrirDialog"
            />
        </div>

        <!-- Lista de bloqueios -->
        <div v-if="bloqueios.length > 0" class="space-y-3">
            <div
                v-for="b in bloqueios"
                :key="b.id"
                class="glass-card flex items-center gap-4 p-4"
            >
                <!-- Ícone do tipo -->
                <div :class="['flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg', tipoConfig[b.tipo]?.bg]">
                    <i :class="['text-base', tipoConfig[b.tipo]?.icon, tipoConfig[b.tipo]?.cor]"></i>
                </div>

                <!-- Dados -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <Tag :value="tipoConfig[b.tipo]?.label ?? b.tipo" :severity="tipoConfig[b.tipo]?.tag" />
                        <span v-if="b.motivo" class="text-sm text-zinc-300">{{ b.motivo }}</span>
                    </div>
                    <p class="mt-1 text-sm text-zinc-400">
                        <i class="pi pi-calendar mr-1 text-xs"></i>
                        {{ formatarDataCurta(b.data_inicio) }}
                        <span v-if="b.data_inicio !== b.data_fim"> → {{ formatarDataCurta(b.data_fim) }}</span>
                    </p>
                </div>

                <!-- Remover -->
                <Button
                    icon="pi pi-trash"
                    severity="danger"
                    text
                    rounded
                    v-tooltip.top="'Remover'"
                    @click="confirmarRemocao(b)"
                />
            </div>
        </div>

        <!-- Estado vazio -->
        <div v-else class="glass-card p-10 text-center">
            <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-full bg-orange-500/10">
                <i class="pi pi-calendar-times text-3xl text-orange-400"></i>
            </div>
            <h3 class="mb-1 text-base font-semibold text-white">Nenhum bloqueio cadastrado</h3>
            <p class="text-sm text-zinc-400">Adicione férias, folgas ou intervalos para bloquear horários na sua agenda.</p>
        </div>

        <!-- Dialog de criação -->
        <Dialog
            v-model:visible="dialogVisivel"
            header="Novo Bloqueio"
            modal
            :style="{ width: '420px' }"
        >
            <div class="flex flex-col gap-4 pt-2">
                <!-- Tipo -->
                <div class="flex flex-col gap-1">
                    <label class="text-sm text-zinc-300">Tipo</label>
                    <Select
                        v-model="novoForm.tipo"
                        :options="tiposOpcoes"
                        optionLabel="label"
                        optionValue="value"
                        placeholder="Selecione o tipo"
                        class="w-full"
                    />
                </div>

                <!-- Data Início -->
                <div class="flex flex-col gap-1">
                    <label class="text-sm text-zinc-300">Data Início</label>
                    <DatePicker
                        v-model="novoForm.data_inicio"
                        dateFormat="dd/mm/yy"
                        showIcon
                        class="w-full"
                        :minDate="hoje"
                    />
                </div>

                <!-- Data Fim -->
                <div class="flex flex-col gap-1">
                    <label class="text-sm text-zinc-300">Data Fim</label>
                    <DatePicker
                        v-model="novoForm.data_fim"
                        dateFormat="dd/mm/yy"
                        showIcon
                        class="w-full"
                        :minDate="novoForm.data_inicio ?? hoje"
                    />
                </div>

                <!-- Motivo -->
                <div class="flex flex-col gap-1">
                    <label class="text-sm text-zinc-300">Motivo <span class="text-zinc-500">(opcional)</span></label>
                    <InputText v-model="novoForm.motivo" placeholder="Ex: Consulta médica" class="w-full" maxlength="255" />
                </div>
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
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Select from 'primevue/select'
import DatePicker from 'primevue/datepicker'
import ConfirmDialog from 'primevue/confirmdialog'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { formatarDataCurta } from '@/Utils/formatters'

defineOptions({ layout: DashboardLayout })

const props = defineProps({
    bloqueios: { type: Array, default: () => [] },
})

const confirm = useConfirm()

// ── Dialog state ─────────────────────────────────────────────────────────────
const dialogVisivel = ref(false)
const salvando = ref(false)
const hoje = new Date()

const novoForm = ref({ tipo: 'folga', data_inicio: null, data_fim: null, motivo: '' })

function abrirDialog() {
    novoForm.value = { tipo: 'folga', data_inicio: null, data_fim: null, motivo: '' }
    dialogVisivel.value = true
}

const formValido = computed(() =>
    novoForm.value.tipo && novoForm.value.data_inicio && novoForm.value.data_fim
)

// ── Configurações visuais por tipo ─────────────────────────────────────────
const tipoConfig = {
    ferias:  { label: 'Férias',  tag: 'success', icon: 'pi pi-sun',      cor: 'text-green-400',  bg: 'bg-green-500/10'  },
    folga:   { label: 'Folga',   tag: 'warn',    icon: 'pi pi-home',     cor: 'text-yellow-400', bg: 'bg-yellow-500/10' },
    almoco:  { label: 'Almoço',  tag: 'info',    icon: 'pi pi-clock',    cor: 'text-blue-400',   bg: 'bg-blue-500/10'   },
    outro:   { label: 'Outro',   tag: 'secondary',icon: 'pi pi-ban',     cor: 'text-zinc-400',   bg: 'bg-zinc-500/10'   },
}

const tiposOpcoes = [
    { label: 'Folga',   value: 'folga'  },
    { label: 'Férias',  value: 'ferias' },
    { label: 'Almoço',  value: 'almoco' },
    { label: 'Outro',   value: 'outro'  },
]

// ── Salvar ───────────────────────────────────────────────────────────────────
function salvar() {
    salvando.value = true
    router.post(
        '/profissional/bloqueios',
        {
            tipo:         novoForm.value.tipo,
            data_inicio:  formatISO(novoForm.value.data_inicio),
            data_fim:     formatISO(novoForm.value.data_fim),
            motivo:       novoForm.value.motivo || null,
        },
        {
            onSuccess: () => { dialogVisivel.value = false },
            onFinish:  () => { salvando.value = false },
        }
    )
}

// ── Remover ──────────────────────────────────────────────────────────────────
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

// ── Util ─────────────────────────────────────────────────────────────────────
function formatISO(date) {
    if (!date) return null
    const d = new Date(date)
    return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`
}
</script>
