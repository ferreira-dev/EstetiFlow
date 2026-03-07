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
import Toast from 'primevue/toast'
</script>
