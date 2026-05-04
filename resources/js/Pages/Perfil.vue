<template>
    <div class="mx-auto max-w-3xl space-y-8">

        <!-- Header -->
        <div class="flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-primary-500/20 text-2xl font-bold text-primary-300">
                {{ iniciais }}
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">{{ usuario.nome_completo }}</h2>
                <p class="text-sm text-zinc-400">{{ usuario.email }}</p>
            </div>
        </div>

        <!-- Dados Pessoais -->
        <form @submit.prevent="salvarPerfil" class="space-y-6">
            <div class="glass-card space-y-5 p-6">
                <h3 class="font-semibold text-white">Dados Pessoais</h3>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <!-- Nome -->
                    <div class="flex flex-col gap-1 sm:col-span-2">
                        <label class="text-xs font-medium text-zinc-400">Nome Completo</label>
                        <InputText
                            v-model="form.nome_completo"
                            placeholder="Seu nome completo"
                            :invalid="!!erros.nome_completo"
                            class="w-full"
                        />
                        <span v-if="erros.nome_completo" class="text-xs text-red-400">{{ erros.nome_completo }}</span>
                    </div>

                    <!-- Telefone -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-zinc-400">Telefone</label>
                        <InputText
                            v-model="form.telefone"
                            placeholder="(11) 99999-9999"
                            class="w-full"
                        />
                    </div>

                    <!-- Data de Nascimento -->
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-zinc-400">Data de Nascimento</label>
                        <DatePicker
                            v-model="dataNascimentoDate"
                            dateFormat="dd/mm/yy"
                            showIcon
                            class="w-full"
                            :maxDate="hoje"
                        />
                    </div>

                    <!-- Gênero -->
                    <div class="flex flex-col gap-1 sm:col-span-2">
                        <label class="text-xs font-medium text-zinc-400">Gênero</label>
                        <Select
                            v-model="form.genero"
                            :options="opcoesGenero"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Prefiro não informar"
                            class="w-full sm:w-64"
                        />
                    </div>
                </div>
            </div>

            <!-- Endereço -->
            <div class="glass-card space-y-5 p-6">
                <h3 class="font-semibold text-white">Endereço</h3>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-6">
                    <div class="flex flex-col gap-1 sm:col-span-2">
                        <label class="text-xs font-medium text-zinc-400">CEP</label>
                        <InputText v-model="form.cep" placeholder="00000-000" class="w-full" maxlength="9" />
                    </div>

                    <div class="flex flex-col gap-1 sm:col-span-4">
                        <label class="text-xs font-medium text-zinc-400">Logradouro</label>
                        <InputText v-model="form.logradouro" placeholder="Rua, Avenida..." class="w-full" />
                    </div>

                    <div class="flex flex-col gap-1 sm:col-span-1">
                        <label class="text-xs font-medium text-zinc-400">Número</label>
                        <InputText v-model="form.numero" placeholder="123" class="w-full" />
                    </div>

                    <div class="flex flex-col gap-1 sm:col-span-2">
                        <label class="text-xs font-medium text-zinc-400">Complemento</label>
                        <InputText v-model="form.complemento" placeholder="Apto, Bloco..." class="w-full" />
                    </div>

                    <div class="flex flex-col gap-1 sm:col-span-3">
                        <label class="text-xs font-medium text-zinc-400">Bairro</label>
                        <InputText v-model="form.bairro" placeholder="Bairro" class="w-full" />
                    </div>

                    <div class="flex flex-col gap-1 sm:col-span-4">
                        <label class="text-xs font-medium text-zinc-400">Cidade</label>
                        <InputText v-model="form.cidade" placeholder="Cidade" class="w-full" />
                    </div>

                    <div class="flex flex-col gap-1 sm:col-span-2">
                        <label class="text-xs font-medium text-zinc-400">Estado</label>
                        <InputText v-model="form.estado" placeholder="SP" class="w-full" maxlength="2" style="text-transform:uppercase" />
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <Button
                    type="submit"
                    label="Salvar Alterações"
                    icon="pi pi-check"
                    :loading="salvandoPerfil"
                />
            </div>
        </form>

        <!-- Alterar Senha -->
        <form @submit.prevent="alterarSenha" class="space-y-6">
            <div class="glass-card space-y-5 p-6">
                <h3 class="font-semibold text-white">Segurança</h3>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div class="flex flex-col gap-1 sm:col-span-2">
                        <label class="text-xs font-medium text-zinc-400">Senha Atual</label>
                        <Password
                            v-model="senhaForm.senha_atual"
                            :feedback="false"
                            toggleMask
                            :invalid="!!errosSenha.senha_atual"
                            class="w-full"
                            inputClass="w-full"
                            placeholder="Digite a senha atual"
                        />
                        <span v-if="errosSenha.senha_atual" class="text-xs text-red-400">{{ errosSenha.senha_atual }}</span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-zinc-400">Nova Senha</label>
                        <Password
                            v-model="senhaForm.nova_senha"
                            toggleMask
                            :invalid="!!errosSenha.nova_senha"
                            class="w-full"
                            inputClass="w-full"
                            placeholder="Mínimo 8 caracteres"
                        />
                        <span v-if="errosSenha.nova_senha" class="text-xs text-red-400">{{ errosSenha.nova_senha }}</span>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-medium text-zinc-400">Confirmar Nova Senha</label>
                        <Password
                            v-model="senhaForm.nova_senha_confirmation"
                            :feedback="false"
                            toggleMask
                            class="w-full"
                            inputClass="w-full"
                            placeholder="Repita a nova senha"
                        />
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <Button
                    type="submit"
                    label="Alterar Senha"
                    icon="pi pi-lock"
                    severity="secondary"
                    :loading="salvandoSenha"
                />
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useToast } from 'primevue/usetoast'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import DatePicker from 'primevue/datepicker'
import Select from 'primevue/select'
import Password from 'primevue/password'
import DefaultLayout from '@/Layouts/DefaultLayout.vue'

defineOptions({ layout: DefaultLayout })

const props = defineProps({
    usuario: { type: Object, required: true },
    perfil:  { type: Object, default: () => ({}) },
})

const toast = useToast()
const hoje = new Date()

// ── Form de perfil ──────────────────────────────────────────────────────────

const form = ref({
    nome_completo:   props.usuario.nome_completo ?? '',
    telefone:        props.perfil?.telefone ?? '',
    data_nascimento: props.perfil?.data_nascimento ?? null,
    genero:          props.perfil?.genero ?? null,
    cep:             props.perfil?.cep ?? '',
    logradouro:      props.perfil?.logradouro ?? '',
    numero:          props.perfil?.numero ?? '',
    complemento:     props.perfil?.complemento ?? '',
    bairro:          props.perfil?.bairro ?? '',
    cidade:          props.perfil?.cidade ?? '',
    estado:          props.perfil?.estado ?? '',
})

// DatePicker trabalha com objetos Date
const dataNascimentoDate = computed({
    get: () => form.value.data_nascimento ? new Date(form.value.data_nascimento) : null,
    set: (v) => { form.value.data_nascimento = v ? v.toISOString().split('T')[0] : null },
})

const erros = ref({})
const salvandoPerfil = ref(false)

function salvarPerfil() {
    salvandoPerfil.value = true
    erros.value = {}

    router.put('/perfil', form.value, {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Sucesso', detail: 'Perfil atualizado!', life: 3000 })
        },
        onError: (e) => {
            erros.value = e
            toast.add({ severity: 'error', summary: 'Erro', detail: 'Verifique os campos.', life: 4000 })
        },
        onFinish: () => { salvandoPerfil.value = false },
        preserveScroll: true,
    })
}

// ── Form de senha ───────────────────────────────────────────────────────────

const senhaForm = ref({ senha_atual: '', nova_senha: '', nova_senha_confirmation: '' })
const errosSenha = ref({})
const salvandoSenha = ref(false)

function alterarSenha() {
    salvandoSenha.value = true
    errosSenha.value = {}

    router.put('/perfil/senha', senhaForm.value, {
        onSuccess: () => {
            senhaForm.value = { senha_atual: '', nova_senha: '', nova_senha_confirmation: '' }
            toast.add({ severity: 'success', summary: 'Sucesso', detail: 'Senha alterada!', life: 3000 })
        },
        onError: (e) => {
            errosSenha.value = e
        },
        onFinish: () => { salvandoSenha.value = false },
        preserveScroll: true,
    })
}

// ── Computed ────────────────────────────────────────────────────────────────

const iniciais = computed(() => {
    return (props.usuario.nome_completo ?? '?')
        .split(' ')
        .slice(0, 2)
        .map(n => n[0])
        .join('')
        .toUpperCase()
})

const opcoesGenero = [
    { label: 'Masculino',            value: 'masculino' },
    { label: 'Feminino',             value: 'feminino' },
    { label: 'Outro',                value: 'outro' },
    { label: 'Prefiro não informar', value: 'prefiro_nao_informar' },
]
</script>
