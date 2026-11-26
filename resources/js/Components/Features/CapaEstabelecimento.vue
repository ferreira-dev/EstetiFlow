<template>
    <!-- Foto enviada pelo dono -->
    <img
        v-if="estabelecimento?.foto_capa_url && !falhouCarregar"
        :src="estabelecimento.foto_capa_url"
        :alt="estabelecimento.nome_fantasia"
        class="h-full w-full object-cover"
        :loading="lazy ? 'lazy' : 'eager'"
        @error="falhouCarregar = true"
    />

    <!-- Sem foto: capa intencional com a cor da marca + iniciais -->
    <div
        v-else
        class="relative flex h-full w-full items-center justify-center overflow-hidden"
        :style="{ background: `linear-gradient(135deg, var(--p-${cor}-500) 0%, var(--p-${cor}-800) 55%, var(--p-${cor}-950) 100%)` }"
        role="img"
        :aria-label="estabelecimento?.nome_fantasia"
    >
        <div
            class="absolute -top-1/4 -right-1/4 h-3/4 w-3/4 rounded-full opacity-20 blur-3xl"
            :style="{ background: `var(--p-${cor}-300)` }"
        ></div>
        <span
            class="relative select-none font-bold tracking-wider text-white/90"
            :class="tamanhoIniciais"
        >
            {{ iniciais }}
        </span>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { CORES_TEMA, COR_PADRAO } from '@/Constants/coresTema'

const props = defineProps({
    estabelecimento: { type: Object, default: null },
    // Tamanho das iniciais no fallback: 'md' (cards) ou 'lg' (hero da página pública)
    tamanho:         { type: String, default: 'md' },
    lazy:            { type: Boolean, default: false },
})

const falhouCarregar = ref(false)
watch(() => props.estabelecimento?.foto_capa_url, () => { falhouCarregar.value = false })

// Só aceita paletas conhecidas (evita CSS arbitrário vindo do banco)
const cor = computed(() => {
    const valor = props.estabelecimento?.cor_primaria
    return CORES_TEMA.some(c => c.valor === valor) ? valor : COR_PADRAO
})

const iniciais = computed(() => {
    const palavras = (props.estabelecimento?.nome_fantasia ?? '')
        .split(/\s+/)
        .filter(p => p.length > 2 || /^[A-Z0-9]/.test(p))
    return palavras.slice(0, 2).map(p => p[0].toUpperCase()).join('') || '•'
})

const tamanhoIniciais = computed(() => props.tamanho === 'lg' ? 'text-6xl lg:text-8xl' : 'text-4xl')
</script>
