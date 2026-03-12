<template>
    <div class="space-y-8">
        <!-- Logo -->
        <div class="text-center">
            <Link href="/" class="inline-flex items-center gap-3">
                <i class="pi pi-calendar-clock text-3xl" style="color: var(--p-primary-color)"></i>
                <span class="text-gradient text-2xl font-bold">EstetiFlow</span>
            </Link>
        </div>

        <!-- Card de Cadastro -->
        <div class="glass-card p-8">
            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-white">Criar conta</h1>
                <p class="mt-2 text-sm text-zinc-400">Escolha seu perfil e comece a usar a plataforma</p>
            </div>

            <form @submit.prevent="handleRegistro" class="space-y-5">

                <!-- Seletor de Perfil -->
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-zinc-300">Você é:</label>
                    <div class="grid grid-cols-2 gap-3">
                        <button
                            type="button"
                            @click="form.tipo = 'cliente'"
                            :class="[
                                'flex flex-col items-center gap-2 rounded-xl border-2 p-4 text-sm font-medium transition-all duration-200',
                                form.tipo === 'cliente'
                                    ? 'border-primary-500 bg-primary-500/20 text-white'
                                    : 'border-white/10 bg-white/5 text-zinc-400 hover:border-white/20 hover:text-white'
                            ]"
                        >
                            <i class="pi pi-user text-xl"></i>
                            <span>Cliente</span>
                            <span class="text-xs opacity-70">Quero agendar serviços</span>
                        </button>

                        <button
                            type="button"
                            @click="form.tipo = 'profissional'"
                            :class="[
                                'flex flex-col items-center gap-2 rounded-xl border-2 p-4 text-sm font-medium transition-all duration-200',
                                form.tipo === 'profissional'
                                    ? 'border-primary-500 bg-primary-500/20 text-white'
                                    : 'border-white/10 bg-white/5 text-zinc-400 hover:border-white/20 hover:text-white'
                            ]"
                        >
                            <i class="pi pi-briefcase text-xl"></i>
                            <span>Profissional</span>
                            <span class="text-xs opacity-70">Quero oferecer serviços</span>
                        </button>
                    </div>
                    <Message v-if="form.errors.tipo" severity="error" :closable="false">{{ form.errors.tipo }}</Message>
                </div>

                <!-- Nome Completo -->
                <div class="space-y-2">
                    <label for="nome_completo" class="block text-sm font-medium text-zinc-300">Nome completo</label>
                    <IconField>
                        <InputIcon class="pi pi-user" />
                        <InputText
                            id="nome_completo"
                            v-model="form.nome_completo"
                            placeholder="Seu nome completo"
                            class="w-full"
                        />
                    </IconField>
                    <Message v-if="form.errors.nome_completo" severity="error" :closable="false">{{ form.errors.nome_completo }}</Message>
                </div>

                <!-- E-mail -->
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
                        />
                    </IconField>
                    <Message v-if="form.errors.email" severity="error" :closable="false">{{ form.errors.email }}</Message>
                </div>

                <!-- Telefone -->
                <div class="space-y-2">
                    <label for="telefone" class="block text-sm font-medium text-zinc-300">Telefone <span class="text-zinc-500">(opcional)</span></label>
                    <IconField>
                        <InputIcon class="pi pi-phone" />
                        <InputText
                            id="telefone"
                            v-model="form.telefone"
                            placeholder="(11) 99999-0000"
                            class="w-full"
                        />
                    </IconField>
                    <Message v-if="form.errors.telefone" severity="error" :closable="false">{{ form.errors.telefone }}</Message>
                </div>

                <!-- Campos exclusivos para Profissional -->
                <Transition name="slide-down">
                    <div v-if="form.tipo === 'profissional'" class="space-y-5 rounded-xl border border-white/10 bg-white/5 p-4">
                        <p class="text-xs font-semibold uppercase tracking-widest text-zinc-400">Dados do profissional</p>

                        <!-- Especialidade -->
                        <div class="space-y-2">
                            <label for="especialidade" class="block text-sm font-medium text-zinc-300">Especialidade</label>
                            <IconField>
                                <InputIcon class="pi pi-star" />
                                <InputText
                                    id="especialidade"
                                    v-model="form.especialidade"
                                    placeholder="Ex: Cabeleireiro, Esteticista, Barbeiro..."
                                    class="w-full"
                                />
                            </IconField>
                            <Message v-if="form.errors.especialidade" severity="error" :closable="false">{{ form.errors.especialidade }}</Message>
                        </div>
                    </div>
                </Transition>

                <!-- Senha -->
                <div class="space-y-2">
                    <label for="senha" class="block text-sm font-medium text-zinc-300">Senha</label>
                    <Password
                        id="senha"
                        v-model="form.password"
                        placeholder="Mín. 6 caracteres"
                        :feedback="false"
                        toggleMask
                        inputClass="w-full"
                        class="w-full"
                    />
                    <Message v-if="form.errors.password" severity="error" :closable="false">{{ form.errors.password }}</Message>
                </div>

                <!-- Confirmar Senha -->
                <div class="space-y-2">
                    <label for="senha_confirm" class="block text-sm font-medium text-zinc-300">Confirmar senha</label>
                    <Password
                        id="senha_confirm"
                        v-model="form.password_confirmation"
                        placeholder="Repita a senha"
                        :feedback="false"
                        toggleMask
                        inputClass="w-full"
                        class="w-full"
                    />
                </div>

                <!-- Submit -->
                <Button
                    type="submit"
                    :label="form.tipo === 'profissional' ? 'Cadastrar como Profissional' : 'Criar minha conta'"
                    :icon="form.tipo === 'profissional' ? 'pi pi-briefcase' : 'pi pi-user-plus'"
                    class="w-full"
                    :loading="form.processing"
                />
            </form>

            <Divider />

            <!-- Já tem conta -->
            <div class="text-center">
                <p class="text-sm text-zinc-400">
                    Já tem uma conta?
                    <Link href="/login" class="font-semibold transition-colors hover:text-white" style="color: var(--p-primary-color)">
                        Fazer login
                    </Link>
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
    tipo:                 'cliente',
    nome_completo:        '',
    email:                '',
    telefone:             '',
    especialidade:        '',
    password:             '',
    password_confirmation: '',
})

function handleRegistro() {
    form.post('/registro')
}
</script>

<style scoped>
.text-gradient {
    background: linear-gradient(135deg, var(--p-primary-color) 0%, #a78bfa 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Transição suave dos campos de profissional */
.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.3s ease;
    overflow: hidden;
}
.slide-down-enter-from,
.slide-down-leave-to {
    opacity: 0;
    max-height: 0;
    transform: translateY(-8px);
}
.slide-down-enter-to,
.slide-down-leave-from {
    opacity: 1;
    max-height: 300px;
    transform: translateY(0);
}
</style>
