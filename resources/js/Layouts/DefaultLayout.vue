<template>
    <div class="flex min-h-screen flex-col">
        <AppHeader />

        <main class="flex-1">
            <slot />
        </main>

        <AppFooter />

        <!-- Globais: Toast e Confirm Dialog para toda a aplicação -->
        <Toast position="top-right" />
        <ConfirmDialog />
    </div>
</template>

<script setup>
import { watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useToast } from 'primevue/usetoast'
import AppHeader from '@/Components/Layout/AppHeader.vue'
import AppFooter from '@/Components/Layout/AppFooter.vue'
import Toast from 'primevue/toast'
import ConfirmDialog from 'primevue/confirmdialog'

const page = usePage()
const toast = useToast()

// Exibe flash messages do backend (sessão Laravel) como toast
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            toast.add({
                severity: 'success',
                summary: 'Sucesso',
                detail: flash.success,
                life: 4000,
            })
        }
        if (flash?.error) {
            toast.add({
                severity: 'error',
                summary: 'Erro',
                detail: flash.error,
                life: 5000,
            })
        }
    },
    { immediate: true }
)
</script>
