<template>
    <div class="glass-card flex flex-col items-center p-8 text-center lg:p-12">
        <div
            class="mb-6 flex h-20 w-20 items-center justify-center rounded-full lg:h-28 lg:w-28"
            style="background: linear-gradient(135deg, rgba(var(--p-primary-500), 0.2), rgba(var(--p-primary-500), 0.05))"
        >
            <i :class="`pi ${icone}`" class="text-3xl lg:text-4xl" style="color: var(--p-primary-color)"></i>
        </div>

        <h3 class="mb-2 text-lg font-bold text-white lg:text-xl">{{ titulo }}</h3>
        <p class="mx-auto max-w-md text-sm text-zinc-400">{{ descricao }}</p>

        <Link v-if="rotaBotao" :href="rotaBotao" class="mt-6">
            <Button :label="labelBotao" :icon="iconeBotao" />
        </Link>
    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import Button from 'primevue/button'

defineProps({
    icone: { type: String, default: 'pi-inbox' },
    titulo: { type: String, default: 'Nenhum item encontrado' },
    descricao: { type: String, default: '' },
    labelBotao: { type: String, default: '' },
    iconeBotao: { type: String, default: 'pi pi-search' },
    rotaBotao: { type: String, default: '' }
})
</script>
