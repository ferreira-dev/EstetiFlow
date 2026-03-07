<template>
    <div class="glass-card overflow-hidden" style="min-width: 180px;">
        <!-- Imagem -->
        <div class="relative h-40 w-full overflow-hidden">
            <img
                :src="estabelecimento.foto_capa_url || 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=800'"
                :alt="estabelecimento.nome_fantasia"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-110"
                loading="lazy"
            />
            <!-- Badge de cidade -->
            <Tag
                v-if="estabelecimento.cidade"
                class="absolute top-2 left-2"
                severity="secondary"
            >
                <div class="flex items-center gap-1">
                    <i class="pi pi-map-marker text-xs" style="color: var(--p-primary-color)"></i>
                    <span class="text-xs font-semibold">{{ estabelecimento.cidade }}</span>
                </div>
            </Tag>
        </div>

        <!-- Conteúdo -->
        <div class="p-3">
            <h3 class="truncate text-sm font-semibold text-white">{{ estabelecimento.nome_fantasia }}</h3>
            <p class="mt-1 truncate text-xs text-zinc-400">
                {{ [estabelecimento.bairro, estabelecimento.cidade, estabelecimento.estado].filter(Boolean).join(' · ') }}
            </p>
            <Link :href="`/estabelecimentos/${estabelecimento.id}`">
                <Button
                    label="Agendar"
                    severity="secondary"
                    class="mt-3 w-full"
                    size="small"
                />
            </Link>
        </div>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Tag from 'primevue/tag'

defineProps({
    estabelecimento: {
        type: Object,
        required: true
    }
})
</script>
