<template>
    <div class="flex min-h-screen items-center justify-center p-5">
        <div class="w-full max-w-md">
            <slot />
        </div>

        <!-- Toast para mensagens de erro/sucesso no login -->
        <Toast position="top-right" />
    </div>
</template>

<script setup>
import { watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useToast } from 'primevue/usetoast'
import Toast from 'primevue/toast'

const page = usePage()
const toast = useToast()

// Exibe flash messages do backend (ex.: senha redefinida, link expirado) como toast
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            toast.add({ severity: 'success', summary: 'Sucesso', detail: flash.success, life: 5000 })
        }
        if (flash?.error) {
            toast.add({ severity: 'error', summary: 'Erro', detail: flash.error, life: 6000 })
        }
    },
    { immediate: true }
)
</script>
