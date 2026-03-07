<template>
    <div class="space-y-8">
        <!-- Logo -->
        <div class="text-center">
            <Link href="/" class="inline-flex items-center gap-3">
                <i class="pi pi-calendar-clock text-3xl" style="color: var(--p-primary-color)"></i>
                <span class="text-gradient text-2xl font-bold">Agendamento</span>
            </Link>
        </div>

        <!-- Card de Login -->
        <div class="glass-card p-8">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-white">Bem-vindo de volta!</h1>
                <p class="mt-2 text-sm text-zinc-400">Faça login para gerenciar seus agendamentos</p>
            </div>

            <form @submit.prevent="handleLogin" class="space-y-5">
                <!-- Email -->
                <div class="space-y-2">
                    <label for="email" class="block text-sm font-medium text-zinc-300">E-mail</label>
                    <IconField>
                        <InputIcon class="pi pi-envelope" />
                        <InputText
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="seu@email.com"
                            class="w-full"
                            required
                        />
                    </IconField>
                </div>

                <!-- Senha -->
                <div class="space-y-2">
                    <label for="senha" class="block text-sm font-medium text-zinc-300">Senha</label>
                    <Password
                        id="senha"
                        v-model="form.password"
                        placeholder="Sua senha"
                        :feedback="false"
                        toggleMask
                        inputClass="w-full"
                        class="w-full"
                        required
                    />
                </div>

                <!-- Erro -->
                <Message v-if="form.errors.email" severity="error" :closable="false">
                    {{ form.errors.email }}
                </Message>

                <!-- Submit -->
                <Button
                    type="submit"
                    label="Entrar"
                    icon="pi pi-sign-in"
                    class="w-full"
                    :loading="form.processing"
                />
            </form>

            <!-- Divider -->
            <Divider />

            <!-- Criar conta -->
            <div class="text-center">
                <p class="text-sm text-zinc-400">
                    Não tem uma conta?
                    <a href="#" class="font-semibold transition-colors hover:text-white" style="color: var(--p-primary-color)">
                        Cadastre-se
                    </a>
                </p>
            </div>
        </div>

        <!-- Voltar -->
        <div class="text-center">
            <Link href="/" class="text-sm text-zinc-400 transition-colors hover:text-white">
                <i class="pi pi-arrow-left mr-1"></i>
                Voltar ao início
            </Link>
        </div>
    </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import Divider from 'primevue/divider'
import Message from 'primevue/message'
import AuthLayout from '@/Layouts/AuthLayout.vue'

defineOptions({ layout: AuthLayout })

const form = useForm({
    email: '',
    password: '',
})

function handleLogin() {
    form.post('/login')
}
</script>
