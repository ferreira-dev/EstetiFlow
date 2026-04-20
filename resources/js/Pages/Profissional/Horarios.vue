<template>
    <div class="mx-auto max-w-2xl space-y-6">

        <!-- Header -->
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-500/20">
                <i class="pi pi-clock text-lg text-primary-400"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">Horários de Funcionamento</h2>
                <p class="text-sm text-zinc-400">Defina sua grade de atendimento por dia da semana</p>
            </div>
        </div>

        <!-- Grade semanal -->
        <div class="glass-card divide-y divide-white/5">
            <div
                v-for="dia in diasSemana"
                :key="dia.value"
                class="flex items-center gap-4 px-5 py-4"
            >
                <!-- Toggle + Label -->
                <div class="flex w-32 items-center gap-3 flex-shrink-0">
                    <ToggleSwitch v-model="form[dia.value].ativo" />
                    <span :class="['text-sm font-medium', form[dia.value].ativo ? 'text-white' : 'text-zinc-500']">
                        {{ dia.label }}
                    </span>
                </div>

                <!-- Horários -->
                <div
                    :class="['flex flex-1 items-center gap-3 transition-opacity duration-200', !form[dia.value].ativo && 'pointer-events-none opacity-30']"
                >
                    <div class="flex flex-1 flex-col gap-1">
                        <label class="text-xs text-zinc-500">Início</label>
                        <InputText
                            v-model="form[dia.value].hora_inicio"
                            placeholder="08:00"
                            :disabled="!form[dia.value].ativo"
                            class="w-full text-sm"
                            maxlength="5"
                            @blur="mascaraHora(dia.value, 'hora_inicio')"
                        />
                    </div>

                    <span class="mt-5 text-zinc-600">→</span>

                    <div class="flex flex-1 flex-col gap-1">
                        <label class="text-xs text-zinc-500">Fim</label>
                        <InputText
                            v-model="form[dia.value].hora_fim"
                            placeholder="18:00"
                            :disabled="!form[dia.value].ativo"
                            class="w-full text-sm"
                            maxlength="5"
                            @blur="mascaraHora(dia.value, 'hora_fim')"
                        />
                    </div>

                    <!-- Erro de validação -->
                    <div class="mt-5 w-5 flex-shrink-0">
                        <i
                            v-if="form[dia.value].ativo && erroHorario(dia.value)"
                            class="pi pi-exclamation-circle text-red-400"
                            v-tooltip.top="erroHorario(dia.value)"
                        ></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botão salvar -->
        <div class="flex justify-end">
            <Button
                label="Salvar Horários"
                icon="pi pi-check"
                :loading="salvando"
                :disabled="temErros"
                @click="salvar"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useToast } from 'primevue/usetoast'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import ToggleSwitch from 'primevue/toggleswitch'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

defineOptions({ layout: DashboardLayout })

const props = defineProps({
    horarios: { type: Object, default: () => ({}) },
})

const toast = useToast()
const salvando = ref(false)

// Dias da semana — 0=Dom … 6=Sáb
const diasSemana = [
    { value: 1, label: 'Segunda' },
    { value: 2, label: 'Terça' },
    { value: 3, label: 'Quarta' },
    { value: 4, label: 'Quinta' },
    { value: 5, label: 'Sexta' },
    { value: 6, label: 'Sábado' },
    { value: 0, label: 'Domingo' },
]

// Estado local — inicializa a partir das props
const form = ref(
    Object.fromEntries(
        diasSemana.map(({ value }) => {
            const salvo = props.horarios[value]
            return [
                value,
                {
                    ativo:       !!salvo,
                    hora_inicio: salvo?.hora_inicio?.slice(0, 5) ?? '08:00',
                    hora_fim:    salvo?.hora_fim?.slice(0, 5)    ?? '18:00',
                },
            ]
        })
    )
)

// Aplica máscara HH:MM simples no blur
function mascaraHora(dia, campo) {
    let val = form.value[dia][campo].replace(/\D/g, '').slice(0, 4)
    if (val.length >= 3) val = val.slice(0, 2) + ':' + val.slice(2)
    form.value[dia][campo] = val
}

// Valida hora_fim > hora_inicio
function erroHorario(dia) {
    const { ativo, hora_inicio, hora_fim } = form.value[dia]
    if (!ativo) return null
    if (!hora_inicio || !hora_fim) return 'Preencha os horários'
    if (hora_fim <= hora_inicio) return 'Fim deve ser após o início'
    return null
}

const temErros = computed(() =>
    diasSemana.some(({ value }) => erroHorario(value) !== null)
)

function salvar() {
    salvando.value = true

    const horarios = diasSemana.map(({ value }) => ({
        dia_semana:  value,
        ativo:       form.value[value].ativo,
        hora_inicio: form.value[value].hora_inicio || null,
        hora_fim:    form.value[value].hora_fim    || null,
    }))

    router.post(
        '/profissional/horarios',
        { horarios },
        {
            onFinish: () => { salvando.value = false },
        }
    )
}
</script>
