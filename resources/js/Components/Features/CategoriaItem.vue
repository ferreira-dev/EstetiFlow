<template>
    <Link
        :href="`/estabelecimentos?categoria=${categoria.id}`"
        class="glass-card flex flex-col items-center gap-2 px-4 py-3 text-center transition-all hover:bg-white/5"
        style="min-width: 100px;"
    >
        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/5">
            <i :class="categoria.icone" class="text-lg" style="color: var(--p-primary-color)"></i>
        </div>
        <span class="text-xs font-medium text-zinc-300">{{ categoria.titulo }}</span>
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    categoria: { type: Object, required: true }
})
</script>
