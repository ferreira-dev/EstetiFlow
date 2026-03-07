<template>
    <div class="glass-card space-y-3 p-4">
        <div class="flex items-center justify-between border-b border-white/10 pb-3">
            <span class="text-sm text-zinc-400">Estabelecimento</span>
            <span class="text-sm font-semibold text-white">{{ estabelecimento }}</span>
        </div>
        <div class="flex items-center justify-between border-b border-white/10 pb-3">
            <span class="text-sm text-zinc-400">Serviço</span>
            <span class="text-sm font-semibold text-white">{{ servico?.nome }}</span>
        </div>
        <div class="flex items-center justify-between border-b border-white/10 pb-3">
            <span class="text-sm text-zinc-400">Data</span>
            <span class="text-sm font-semibold text-white">{{ dataFormatada }}</span>
        </div>
        <div class="flex items-center justify-between border-b border-white/10 pb-3">
            <span class="text-sm text-zinc-400">Horário</span>
            <span class="text-sm font-semibold text-white">{{ horaFormatada }}</span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-sm text-zinc-400">Valor</span>
            <span class="text-sm font-bold" style="color: var(--p-primary-color)">
                {{ precoFormatado }}
            </span>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { formatarData, formatarHora, formatarMoeda } from '@/Utils/formatters'

const props = defineProps({
    estabelecimento: { type: String, default: '' },
    servico: { type: Object, default: () => ({}) },
    data: { type: [Date, String], default: null }
})

const dataFormatada = computed(() => props.data ? formatarData(props.data) : '-')
const horaFormatada = computed(() => props.data ? formatarHora(props.data) : '-')
const precoFormatado = computed(() => formatarMoeda(props.servico?.preco || 0))
</script>
