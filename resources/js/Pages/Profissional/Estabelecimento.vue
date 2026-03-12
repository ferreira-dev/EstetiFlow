<template>
    <div class="mx-auto max-w-3xl space-y-6">

        <!-- Banner de primeiro preenchimento -->
        <div v-if="isFirstTime" class="flex items-start gap-4 rounded-xl border border-amber-500/30 bg-amber-500/10 p-4">
            <i class="pi pi-info-circle mt-0.5 text-xl text-amber-400"></i>
            <div>
                <p class="font-semibold text-amber-300">Bem-vindo ao painel profissional!</p>
                <p class="mt-1 text-sm text-amber-400/80">
                    Para começar a receber agendamentos, preencha os dados do seu estabelecimento abaixo.
                </p>
            </div>
        </div>

        <!-- Header da seção -->
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-500/20">
                <i class="pi pi-building text-lg text-primary-400"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">Dados do Estabelecimento</h2>
                <p class="text-sm text-zinc-400">Informações públicas que os clientes verão ao agendar</p>
            </div>
        </div>

        <form @submit.prevent="salvar" class="space-y-6">

            <!-- ── INFORMAÇÕES BÁSICAS ──────────────────────────────────── -->
            <div class="glass-card p-6 space-y-5">
                <h3 class="text-sm font-semibold uppercase tracking-widest text-zinc-400">Informações Básicas</h3>

                <!-- Nome Fantasia -->
                <div class="space-y-2">
                    <label for="nome_fantasia" class="block text-sm font-medium text-zinc-300">
                        Nome do estabelecimento <span class="text-red-400">*</span>
                    </label>
                    <IconField>
                        <InputIcon class="pi pi-building" />
                        <InputText
                            id="nome_fantasia"
                            v-model="form.nome_fantasia"
                            placeholder="Ex: Barbearia Vintage, Studio Elegance..."
                            class="w-full"
                        />
                    </IconField>
                    <Message v-if="form.errors.nome_fantasia" severity="error" :closable="false">{{ form.errors.nome_fantasia }}</Message>
                </div>

                <!-- CNPJ -->
                <div class="space-y-2">
                    <label for="cnpj" class="block text-sm font-medium text-zinc-300">
                        CNPJ <span class="text-zinc-500">(opcional)</span>
                    </label>
                    <IconField>
                        <InputIcon class="pi pi-id-card" />
                        <InputText
                            id="cnpj"
                            v-model="form.cnpj"
                            placeholder="00.000.000/0000-00"
                            class="w-full"
                            maxlength="14"
                        />
                    </IconField>
                    <Message v-if="form.errors.cnpj" severity="error" :closable="false">{{ form.errors.cnpj }}</Message>
                </div>

                <!-- Descrição -->
                <div class="space-y-2">
                    <label for="descricao" class="block text-sm font-medium text-zinc-300">
                        Descrição <span class="text-zinc-500">(opcional)</span>
                    </label>
                    <Textarea
                        id="descricao"
                        v-model="form.descricao"
                        placeholder="Descreva seu estabelecimento, diferenciais, ambiente..."
                        class="w-full"
                        rows="3"
                        autoResize
                    />
                    <Message v-if="form.errors.descricao" severity="error" :closable="false">{{ form.errors.descricao }}</Message>
                </div>

                <!-- Telefones -->
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="telefone_principal" class="block text-sm font-medium text-zinc-300">
                            Telefone principal <span class="text-red-400">*</span>
                        </label>
                        <IconField>
                            <InputIcon class="pi pi-phone" />
                            <InputText
                                id="telefone_principal"
                                v-model="form.telefone_principal"
                                placeholder="(11) 99999-0000"
                                class="w-full"
                            />
                        </IconField>
                        <Message v-if="form.errors.telefone_principal" severity="error" :closable="false">{{ form.errors.telefone_principal }}</Message>
                    </div>

                    <div class="space-y-2">
                        <label for="telefone_secundario" class="block text-sm font-medium text-zinc-300">
                            Telefone secundário <span class="text-zinc-500">(opcional)</span>
                        </label>
                        <IconField>
                            <InputIcon class="pi pi-phone" />
                            <InputText
                                id="telefone_secundario"
                                v-model="form.telefone_secundario"
                                placeholder="(11) 3333-0000"
                                class="w-full"
                            />
                        </IconField>
                    </div>
                </div>
            </div>

            <!-- ── ENDEREÇO ─────────────────────────────────────────────── -->
            <div class="glass-card p-6 space-y-5">
                <h3 class="text-sm font-semibold uppercase tracking-widest text-zinc-400">Endereço</h3>

                <!-- CEP com busca automática -->
                <div class="space-y-2">
                    <label for="cep" class="block text-sm font-medium text-zinc-300">
                        CEP <span class="text-red-400">*</span>
                    </label>
                    <div class="flex gap-2">
                        <IconField class="flex-1">
                            <InputIcon class="pi pi-map-marker" />
                            <InputText
                                id="cep"
                                v-model="form.cep"
                                placeholder="00000-000"
                                class="w-full"
                                maxlength="8"
                                @blur="buscarCep"
                            />
                        </IconField>
                        <Button
                            type="button"
                            icon="pi pi-search"
                            label="Buscar"
                            outlined
                            :loading="buscandoCep"
                            @click="buscarCep"
                        />
                    </div>
                    <Message v-if="cepErro" severity="warn" :closable="false">{{ cepErro }}</Message>
                    <Message v-if="form.errors.cep" severity="error" :closable="false">{{ form.errors.cep }}</Message>
                </div>

                <!-- Logradouro + Número -->
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="space-y-2 sm:col-span-2">
                        <label for="logradouro" class="block text-sm font-medium text-zinc-300">
                            Logradouro <span class="text-red-400">*</span>
                        </label>
                        <InputText
                            id="logradouro"
                            v-model="form.logradouro"
                            placeholder="Rua, Av., Travessa..."
                            class="w-full"
                        />
                        <Message v-if="form.errors.logradouro" severity="error" :closable="false">{{ form.errors.logradouro }}</Message>
                    </div>

                    <div class="space-y-2">
                        <label for="numero" class="block text-sm font-medium text-zinc-300">
                            Número <span class="text-red-400">*</span>
                        </label>
                        <InputText
                            id="numero"
                            v-model="form.numero"
                            placeholder="123"
                            class="w-full"
                        />
                        <Message v-if="form.errors.numero" severity="error" :closable="false">{{ form.errors.numero }}</Message>
                    </div>
                </div>

                <!-- Complemento + Bairro -->
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="space-y-2">
                        <label for="complemento" class="block text-sm font-medium text-zinc-300">
                            Complemento <span class="text-zinc-500">(opcional)</span>
                        </label>
                        <InputText
                            id="complemento"
                            v-model="form.complemento"
                            placeholder="Sala 2, Bloco A..."
                            class="w-full"
                        />
                    </div>

                    <div class="space-y-2">
                        <label for="bairro" class="block text-sm font-medium text-zinc-300">
                            Bairro <span class="text-red-400">*</span>
                        </label>
                        <InputText
                            id="bairro"
                            v-model="form.bairro"
                            placeholder="Nome do bairro"
                            class="w-full"
                        />
                        <Message v-if="form.errors.bairro" severity="error" :closable="false">{{ form.errors.bairro }}</Message>
                    </div>
                </div>

                <!-- Cidade + Estado -->
                <div class="grid gap-4 sm:grid-cols-3">
                    <div class="space-y-2 sm:col-span-2">
                        <label for="cidade" class="block text-sm font-medium text-zinc-300">
                            Cidade <span class="text-red-400">*</span>
                        </label>
                        <InputText
                            id="cidade"
                            v-model="form.cidade"
                            placeholder="Nome da cidade"
                            class="w-full"
                        />
                        <Message v-if="form.errors.cidade" severity="error" :closable="false">{{ form.errors.cidade }}</Message>
                    </div>

                    <div class="space-y-2">
                        <label for="estado" class="block text-sm font-medium text-zinc-300">
                            Estado <span class="text-red-400">*</span>
                        </label>
                        <Select
                            id="estado"
                            v-model="form.estado"
                            :options="ufs"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="UF"
                            class="w-full"
                        />
                        <Message v-if="form.errors.estado" severity="error" :closable="false">{{ form.errors.estado }}</Message>
                    </div>
                </div>
            </div>

            <!-- ── AÇÕES ─────────────────────────────────────────────────── -->
            <div class="flex items-center justify-between">
                <p class="text-xs text-zinc-500">
                    <span class="text-red-400">*</span> Campos obrigatórios
                </p>
                <Button
                    type="submit"
                    :label="isFirstTime ? 'Cadastrar estabelecimento' : 'Salvar alterações'"
                    :icon="isFirstTime ? 'pi pi-check' : 'pi pi-save'"
                    :loading="form.processing"
                />
            </div>
        </form>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import Select from 'primevue/select'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import Message from 'primevue/message'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

defineOptions({ layout: DashboardLayout })

const props = defineProps({
    estabelecimento: { type: Object, default: null },
    isFirstTime:     { type: Boolean, default: false },
})

// ── Form (pré-populado se já existe estabelecimento) ──────────────────────────
const form = useForm({
    nome_fantasia:       props.estabelecimento?.nome_fantasia       ?? '',
    cnpj:                props.estabelecimento?.cnpj                ?? '',
    descricao:           props.estabelecimento?.descricao           ?? '',
    telefone_principal:  props.estabelecimento?.telefone_principal  ?? '',
    telefone_secundario: props.estabelecimento?.telefone_secundario ?? '',
    cep:                 props.estabelecimento?.cep                 ?? '',
    logradouro:          props.estabelecimento?.logradouro          ?? '',
    numero:              props.estabelecimento?.numero              ?? '',
    complemento:         props.estabelecimento?.complemento         ?? '',
    bairro:              props.estabelecimento?.bairro              ?? '',
    cidade:              props.estabelecimento?.cidade              ?? '',
    estado:              props.estabelecimento?.estado              ?? '',
})

// ── Busca de CEP via ViaCEP ────────────────────────────────────────────────────
const buscandoCep = ref(false)
const cepErro     = ref('')

async function buscarCep() {
    const cepLimpo = form.cep.replace(/\D/g, '')
    if (cepLimpo.length !== 8) return

    buscandoCep.value = true
    cepErro.value     = ''

    try {
        const res  = await fetch(`https://viacep.com.br/ws/${cepLimpo}/json/`)
        const data = await res.json()

        if (data.erro) {
            cepErro.value = 'CEP não encontrado. Preencha o endereço manualmente.'
            return
        }

        form.logradouro = data.logradouro ?? form.logradouro
        form.bairro     = data.bairro     ?? form.bairro
        form.cidade     = data.localidade ?? form.cidade
        form.estado     = data.uf         ?? form.estado
    } catch {
        cepErro.value = 'Não foi possível consultar o CEP. Verifique sua conexão.'
    } finally {
        buscandoCep.value = false
    }
}

function salvar() {
    form.post('/profissional/estabelecimento')
}

// ── UFs do Brasil ─────────────────────────────────────────────────────────────
const ufs = [
    { label: 'AC', value: 'AC' }, { label: 'AL', value: 'AL' }, { label: 'AM', value: 'AM' },
    { label: 'AP', value: 'AP' }, { label: 'BA', value: 'BA' }, { label: 'CE', value: 'CE' },
    { label: 'DF', value: 'DF' }, { label: 'ES', value: 'ES' }, { label: 'GO', value: 'GO' },
    { label: 'MA', value: 'MA' }, { label: 'MG', value: 'MG' }, { label: 'MS', value: 'MS' },
    { label: 'MT', value: 'MT' }, { label: 'PA', value: 'PA' }, { label: 'PB', value: 'PB' },
    { label: 'PE', value: 'PE' }, { label: 'PI', value: 'PI' }, { label: 'PR', value: 'PR' },
    { label: 'RJ', value: 'RJ' }, { label: 'RN', value: 'RN' }, { label: 'RO', value: 'RO' },
    { label: 'RR', value: 'RR' }, { label: 'RS', value: 'RS' }, { label: 'SC', value: 'SC' },
    { label: 'SE', value: 'SE' }, { label: 'SP', value: 'SP' }, { label: 'TO', value: 'TO' },
]
</script>
