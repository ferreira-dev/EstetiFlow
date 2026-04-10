<template>
    <Dialog
        :visible="visible"
        @update:visible="$emit('update:visible', $event)"
        :header="isEditing ? 'Editar Serviço' : 'Adicionar Serviço'"
        modal
        :style="{ width: '32rem' }"
        :closable="!form.processing"
    >
        <form @submit.prevent="salvar" class="space-y-5">

            <!-- ── SELEÇÃO DO SERVIÇO (modo adicionar) ──────────────────── -->
            <template v-if="!isEditing">
                <!-- Toggle: catálogo vs. criar novo -->
                <div class="flex items-center gap-3 rounded-lg border border-white/10 bg-white/5 p-3">
                    <ToggleSwitch v-model="criarNovo" inputId="toggle-criar-novo" />
                    <label for="toggle-criar-novo" class="cursor-pointer text-sm text-zinc-300">
                        {{ criarNovo ? 'Criando serviço personalizado' : 'Selecionar do catálogo' }}
                    </label>
                </div>

                <!-- Select do catálogo -->
                <div v-if="!criarNovo" class="space-y-2">
                    <label for="servico_id" class="block text-sm font-medium text-zinc-300">
                        Serviço do catálogo <span class="text-red-400">*</span>
                    </label>
                    <Select
                        id="servico_id"
                        v-model="form.servico_id"
                        :options="catalogoAgrupado"
                        optionLabel="nome"
                        optionValue="id"
                        optionGroupLabel="label"
                        optionGroupChildren="items"
                        placeholder="Busque ou selecione um serviço..."
                        filter
                        :filterPlaceholder="'Buscar serviço...'"
                        class="w-full"
                        :invalid="!!form.errors.servico_id"
                    />
                    <Message v-if="form.errors.servico_id" severity="error" :closable="false">
                        {{ form.errors.servico_id }}
                    </Message>
                </div>

                <!-- Campos de criação de novo serviço -->
                <template v-if="criarNovo">
                    <div class="space-y-2">
                        <label for="novo_nome" class="block text-sm font-medium text-zinc-300">
                            Nome do serviço <span class="text-red-400">*</span>
                        </label>
                        <InputText
                            id="novo_nome"
                            v-model="form.novo_nome"
                            placeholder="Ex: Corte Navalhado, Escova Modeladora..."
                            class="w-full"
                            :invalid="!!form.errors.novo_nome"
                        />
                        <Message v-if="form.errors.novo_nome" severity="error" :closable="false">
                            {{ form.errors.novo_nome }}
                        </Message>
                    </div>

                    <div class="space-y-2">
                        <label for="novo_categoria" class="block text-sm font-medium text-zinc-300">
                            Categoria <span class="text-red-400">*</span>
                        </label>
                        <Select
                            id="novo_categoria"
                            v-model="form.novo_categoria"
                            :options="categoriaOptions"
                            optionLabel="label"
                            optionValue="value"
                            placeholder="Selecione a categoria"
                            class="w-full"
                            :invalid="!!form.errors.novo_categoria"
                        />
                        <Message v-if="form.errors.novo_categoria" severity="error" :closable="false">
                            {{ form.errors.novo_categoria }}
                        </Message>
                    </div>

                    <div class="space-y-2">
                        <label for="novo_descricao" class="block text-sm font-medium text-zinc-300">
                            Descrição <span class="text-zinc-500">(opcional)</span>
                        </label>
                        <Textarea
                            id="novo_descricao"
                            v-model="form.novo_descricao"
                            placeholder="Descreva o serviço brevemente..."
                            class="w-full"
                            rows="2"
                            autoResize
                        />
                    </div>
                </template>
            </template>

            <!-- ── NOME DO SERVIÇO (modo editar — readonly) ─────────────── -->
            <div v-if="isEditing" class="flex items-center gap-3 rounded-lg border border-white/10 bg-white/5 p-3">
                <i class="pi pi-tag text-primary-400"></i>
                <div>
                    <p class="text-sm font-semibold text-white">{{ editingServico?.servico?.nome }}</p>
                    <p class="text-xs text-zinc-400">{{ formatCategoria(editingServico?.servico?.categoria) }}</p>
                </div>
            </div>

            <!-- ── PREÇO E TEMPO (sempre visíveis) ──────────────────────── -->
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-2">
                    <label for="preco" class="block text-sm font-medium text-zinc-300">
                        Preço (R$) <span class="text-red-400">*</span>
                    </label>
                    <InputNumber
                        id="preco"
                        v-model="form.preco"
                        mode="currency"
                        currency="BRL"
                        locale="pt-BR"
                        :minFractionDigits="2"
                        :min="0.01"
                        class="w-full"
                        :invalid="!!form.errors.preco"
                    />
                    <Message v-if="form.errors.preco" severity="error" :closable="false">
                        {{ form.errors.preco }}
                    </Message>
                </div>

                <div class="space-y-2">
                    <label for="tempo" class="block text-sm font-medium text-zinc-300">
                        Tempo (minutos) <span class="text-red-400">*</span>
                    </label>
                    <InputNumber
                        id="tempo"
                        v-model="form.tempo_execucao_minutos"
                        :min="5"
                        :max="480"
                        :step="5"
                        suffix=" min"
                        class="w-full"
                        :invalid="!!form.errors.tempo_execucao_minutos"
                    />
                    <Message v-if="form.errors.tempo_execucao_minutos" severity="error" :closable="false">
                        {{ form.errors.tempo_execucao_minutos }}
                    </Message>
                </div>
            </div>

            <!-- ── AÇÕES ─────────────────────────────────────────────────── -->
            <div class="flex items-center justify-end gap-3 border-t border-white/10 pt-4">
                <Button
                    type="button"
                    label="Cancelar"
                    severity="secondary"
                    text
                    @click="$emit('update:visible', false)"
                    :disabled="form.processing"
                />
                <Button
                    type="submit"
                    :label="isEditing ? 'Salvar alterações' : 'Adicionar serviço'"
                    :icon="isEditing ? 'pi pi-save' : 'pi pi-plus'"
                    :loading="form.processing"
                />
            </div>
        </form>
    </Dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import InputNumber from 'primevue/inputnumber'
import Textarea from 'primevue/textarea'
import Select from 'primevue/select'
import ToggleSwitch from 'primevue/toggleswitch'
import Message from 'primevue/message'
import { CATEGORIAS_SERVICO } from '@/Constants/categorias'

const props = defineProps({
    visible: { type: Boolean, default: false },
    catalogoServicos: { type: Array, default: () => [] },
    editingServico: { type: Object, default: null },
})

defineEmits(['update:visible'])

const isEditing = computed(() => !!props.editingServico)
const criarNovo = ref(false)

// Agrupar catálogo por categoria para o select
const catalogoAgrupado = computed(() => {
    const grupos = {}
    for (const s of props.catalogoServicos) {
        const cat = formatCategoria(s.categoria)
        if (!grupos[cat]) {
            grupos[cat] = { label: cat, items: [] }
        }
        grupos[cat].items.push(s)
    }
    return Object.values(grupos)
})

// Opções de categoria (para criação de novo)
const categoriaOptions = CATEGORIAS_SERVICO.map(c => ({
    label: c.titulo,
    value: c.id,
}))

// ── Form ────────────────────────────────────────────────────────────────────
const form = useForm({
    servico_id: null,
    novo_nome: '',
    novo_descricao: '',
    novo_categoria: null,
    preco: null,
    tempo_execucao_minutos: null,
})

// Resetar form ao abrir/fechar ou trocar modo
watch(() => props.visible, (val) => {
    if (val && props.editingServico) {
        // Modo editar: pré-popular com dados existentes
        form.preco = parseFloat(props.editingServico.preco)
        form.tempo_execucao_minutos = props.editingServico.tempo_execucao_minutos
        form.servico_id = null
        form.novo_nome = ''
        form.novo_descricao = ''
        form.novo_categoria = null
        criarNovo.value = false
    } else if (val) {
        // Modo adicionar: limpar
        form.reset()
        form.clearErrors()
        criarNovo.value = false
    }
})

function formatCategoria(cat) {
    const found = CATEGORIAS_SERVICO.find(c => c.id === cat)
    return found?.titulo ?? cat ?? ''
}

function salvar() {
    if (isEditing.value) {
        form.put(`/profissional/servicos/${props.editingServico.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset()
            },
        })
    } else {
        // Limpar campos não usados conforme o modo
        if (criarNovo.value) {
            form.servico_id = null
        } else {
            form.novo_nome = ''
            form.novo_descricao = ''
            form.novo_categoria = null
        }

        form.post('/profissional/servicos', {
            preserveScroll: true,
            onSuccess: () => {
                form.reset()
                criarNovo.value = false
            },
        })
    }
}
</script>
