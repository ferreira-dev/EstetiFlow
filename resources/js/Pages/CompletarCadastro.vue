<template>
    <div class="space-y-8">
        <!-- Logo -->
        <div class="text-center">
            <Link href="/" class="inline-flex items-center gap-3">
                <i class="pi pi-calendar-clock text-3xl" style="color: var(--p-primary-color)"></i>
                <span class="text-gradient text-2xl font-bold">Agendamento</span>
            </Link>
        </div>

        <!-- Card principal -->
        <div class="glass-card p-8">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-white">Olá, {{ nome }}!</h1>
                <p class="mt-2 text-sm text-zinc-400">
                    Sua conta foi pré-cadastrada.
                    Preencha os campos abaixo para completar o registro e acessar a plataforma.
                </p>
            </div>

            <form @submit.prevent="submit" class="space-y-5">
                <!-- E-mail -->
                <div class="space-y-2">
                    <label for="email" class="block text-sm font-medium text-zinc-300">Seu E-mail principal</label>
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
                    <Message v-if="form.errors.email" severity="error" :closable="false">{{ form.errors.email }}</Message>
                </div>

                <!-- Nova Senha -->
                <div class="space-y-2">
                    <label for="password" class="block text-sm font-medium text-zinc-300">Crie uma Senha</label>
                    <Password
                        id="password"
                        v-model="form.password"
                        placeholder="Mín. 6 caracteres"
                        :feedback="true"
                        toggleMask
                        inputClass="w-full"
                        class="w-full"
                        required
                    />
                    <Message v-if="form.errors.password" severity="error" :closable="false">{{ form.errors.password }}</Message>
                </div>

                <!-- Confirmar Senha -->
                <div class="space-y-2">
                    <label for="password_confirmation" class="block text-sm font-medium text-zinc-300">Confirme a Senha</label>
                    <Password
                        id="password_confirmation"
                        v-model="form.password_confirmation"
                        placeholder="Repita a senha"
                        :feedback="false"
                        toggleMask
                        inputClass="w-full"
                        class="w-full"
                        required
                    />
                </div>

                <!-- Submit -->
                <Button
                    type="submit"
                    label="Completar Cadastro"
                    icon="pi pi-check"
                    class="w-full"
                    :loading="form.processing"
                />
            </form>
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
import Message from 'primevue/message'
import AuthLayout from '@/Layouts/AuthLayout.vue'

defineOptions({ layout: AuthLayout })

const props = defineProps({
    token: { type: String, required: true },
    nome: { type: String, required: true },
    telefone: { type: String, default: null },
})

const form = useForm({
    token: props.token,
    email: '',
    password: '',
    password_confirmation: '',
})

function submit() {
    form.post('/completar-cadastro')
}
</script>
