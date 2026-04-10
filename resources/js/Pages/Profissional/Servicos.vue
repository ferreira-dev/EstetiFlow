<template>
    <div class="mx-auto max-w-4xl space-y-6">

        <!-- Header da seção -->
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-500/20">
                    <i class="pi pi-list text-lg text-primary-400"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white">Serviços Oferecidos</h2>
                    <p class="text-sm text-zinc-400">
                        {{ servicos.length }} {{ servicos.length === 1 ? 'serviço cadastrado' : 'serviços cadastrados' }}
                    </p>
                </div>
            </div>
            <Button
                label="Adicionar"
                icon="pi pi-plus"
                @click="abrirDialogAdicionar"
            />
        </div>

        <!-- Estado vazio -->
        <div v-if="servicos.length === 0" class="glass-card p-8 text-center lg:p-12">
            <div
                class="mx-auto mb-6 flex h-24 w-24 items-center justify-center rounded-full"
                style="background: linear-gradient(135deg, rgba(var(--p-primary-500), 0.2), rgba(var(--p-primary-500), 0.1))"
            >
                <i class="pi pi-list text-4xl" style="color: var(--p-primary-color)"></i>
            </div>
            <h3 class="mb-2 text-xl font-bold text-white">Nenhum serviço cadastrado</h3>
            <p class="mx-auto mb-6 max-w-md text-zinc-400">
                Comece adicionando os serviços que você oferece. Seus clientes verão esses serviços ao agendar.
            </p>
            <Button
                label="Adicionar primeiro serviço"
                icon="pi pi-plus"
                @click="abrirDialogAdicionar"
            />
        </div>

        <!-- Grid de serviços -->
        <div v-else class="grid gap-4 sm:grid-cols-2">
            <div
                v-for="sp in servicos"
                :key="sp.id"
                class="glass-card group relative overflow-hidden p-5 transition-all duration-200 hover:border-primary-500/30"
            >
                <!-- Badge de categoria -->
                <div class="mb-3 flex items-center justify-between">
                    <Tag :value="formatCategoria(sp.servico?.categoria)" severity="secondary" />
                    <div class="flex gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                        <Button
                            icon="pi pi-pencil"
                            severity="secondary"
                            text
                            rounded
                            size="small"
                            v-tooltip.top="'Editar'"
                            @click="abrirDialogEditar(sp)"
                        />
                        <Button
                            icon="pi pi-trash"
                            severity="danger"
                            text
                            rounded
                            size="small"
                            v-tooltip.top="'Remover'"
                            @click="confirmarRemocao(sp)"
                        />
                    </div>
                </div>

                <!-- Nome e descrição -->
                <h3 class="mb-1 text-base font-semibold text-white">{{ sp.servico?.nome }}</h3>
                <p v-if="sp.servico?.descricao" class="mb-4 line-clamp-2 text-sm text-zinc-400">
                    {{ sp.servico.descricao }}
                </p>

                <!-- Preço e tempo -->
                <div class="mt-auto flex items-center gap-4 border-t border-white/10 pt-3">
                    <div class="flex items-center gap-2">
                        <i class="pi pi-money-bill text-sm text-green-400"></i>
                        <span class="text-sm font-semibold text-green-400">{{ formatarMoeda(sp.preco) }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-clock text-sm text-blue-400"></i>
                        <span class="text-sm text-blue-400">{{ sp.tempo_execucao_minutos }} min</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dialog de adicionar/editar -->
        <ServicoFormDialog
            v-model:visible="dialogVisivel"
            :catalogoServicos="catalogoServicos"
            :editingServico="servicoEditando"
        />

        <!-- ConfirmDialog p/ remoção -->
        <ConfirmDialog />
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useConfirm } from 'primevue/useconfirm'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import ConfirmDialog from 'primevue/confirmdialog'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import ServicoFormDialog from '@/Components/Profissional/ServicoFormDialog.vue'
import { CATEGORIAS_SERVICO } from '@/Constants/categorias'

defineOptions({ layout: DashboardLayout })

const props = defineProps({
    servicos: { type: Array, default: () => [] },
    catalogoServicos: { type: Array, default: () => [] },
})

const confirm = useConfirm()

// ── Dialog state ────────────────────────────────────────────────────────────
const dialogVisivel = ref(false)
const servicoEditando = ref(null)

function abrirDialogAdicionar() {
    servicoEditando.value = null
    dialogVisivel.value = true
}

function abrirDialogEditar(sp) {
    servicoEditando.value = sp
    dialogVisivel.value = true
}

// ── Remoção ─────────────────────────────────────────────────────────────────
function confirmarRemocao(sp) {
    confirm.require({
        message: `Tem certeza que deseja remover o serviço "${sp.servico?.nome}" da sua lista?`,
        header: 'Confirmar Remoção',
        icon: 'pi pi-exclamation-triangle',
        rejectLabel: 'Cancelar',
        rejectProps: {
            severity: 'secondary',
            text: true,
        },
        acceptLabel: 'Remover',
        acceptProps: {
            severity: 'danger',
        },
        accept: () => {
            router.delete(`/profissional/servicos/${sp.id}`, {
                preserveScroll: true,
            })
        },
    })
}

// ── Helpers ─────────────────────────────────────────────────────────────────
function formatCategoria(cat) {
    const found = CATEGORIAS_SERVICO.find(c => c.id === cat)
    return found?.titulo ?? cat ?? ''
}

function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(valor)
}
</script>
