<template>
    <div
        class="flex items-center gap-3 rounded-lg px-3 py-2 transition-colors hover:bg-white/5"
        @click="copiarTelefone"
        role="button"
        :title="`Copiar ${telefoneFormatado}`"
    >
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5">
            <i class="pi pi-phone text-sm text-zinc-400"></i>
        </div>
        <span class="text-sm text-zinc-300">{{ telefoneFormatado }}</span>
        <i class="pi pi-copy ml-auto text-xs text-zinc-500"></i>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useToast } from 'primevue/usetoast'
import { formatarTelefone } from '@/Utils/formatters'

const props = defineProps({
    telefone: { type: String, required: true }
})

const toast = useToast()

const telefoneFormatado = computed(() => formatarTelefone(props.telefone))

async function copiarTelefone() {
    try {
        await navigator.clipboard.writeText(props.telefone)
        toast.add({
            severity: 'info',
            summary: 'Copiado!',
            detail: `Telefone ${telefoneFormatado.value} copiado.`,
            life: 2000
        })
    } catch {
        // Fallback
        toast.add({
            severity: 'info',
            summary: 'Telefone',
            detail: telefoneFormatado.value,
            life: 3000
        })
    }
}
</script>
