<template>
    <form @submit.prevent="executarBusca" class="flex gap-2 lg:gap-3">
        <IconField class="w-full">
            <InputIcon class="pi pi-search" />
            <InputText
                v-model="termo"
                placeholder="Buscar estabelecimento pelo nome..."
                class="w-full bg-white/5 backdrop-blur-sm transition-shadow focus:shadow-md lg:h-14 lg:rounded-xl lg:px-6 lg:text-base"
            />
        </IconField>
        <Button
            type="submit"
            icon="pi pi-search"
            class="shrink-0 shadow-sm transition-all hover:scale-105 hover:shadow-md lg:h-14 lg:rounded-xl lg:px-8"
            :disabled="!termo.trim()"
        >
            <span class="ml-2 hidden lg:inline">Buscar</span>
        </Button>
    </form>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'

const termo = ref('')

function executarBusca() {
    if (termo.value.trim()) {
        router.visit('/estabelecimentos', { data: { q: termo.value.trim() } })
        termo.value = ''
    }
}
</script>
