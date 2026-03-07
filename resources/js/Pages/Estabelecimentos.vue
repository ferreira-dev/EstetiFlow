<template>
    <div class="container-app py-8 lg:py-12">
        <!-- Breadcrumb + Header -->
        <div class="mb-8 lg:mb-12">
            <div class="mb-4 flex items-center gap-2 text-sm text-zinc-400">
                <Link href="/" class="transition-colors hover:text-white">Início</Link>
                <span>/</span>
                <span>Estabelecimentos</span>
            </div>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background: rgba(var(--p-primary-500), 0.1);">
                        <Search class="h-5 w-5" style="color: var(--p-primary-color)" />
                    </div>
                    <div>
                        <h1 class="text-gradient text-2xl font-bold lg:text-3xl">Encontre seu Estabelecimento</h1>
                        <p class="mt-1 text-sm text-zinc-400">{{ totalResultados }} estabelecimentos encontrados</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Busca -->
        <div class="glass-card mb-8 p-6 lg:p-8">
            <h3 class="mb-2 text-lg font-semibold text-white">Refine sua busca</h3>
            <p class="mb-4 text-sm text-zinc-400">Encontre exatamente o que você procura</p>
            <SearchBar />
        </div>

        <!-- Stats -->
        <div v-if="resultados.length > 0" class="mb-8 grid grid-cols-2 gap-3 lg:grid-cols-4 lg:gap-6">
            <div class="stat-card stat-card--primary">
                <div class="relative z-10 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl" style="background: rgba(var(--p-primary-500), 0.2)">
                        <Search class="h-5 w-5" style="color: var(--p-primary-color)" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold" style="color: var(--p-primary-color)">{{ totalResultados }}</p>
                        <p class="text-xs text-zinc-400">Encontrados</p>
                    </div>
                </div>
            </div>
            <div class="stat-card stat-card--warning">
                <div class="relative z-10 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-yellow-500/20">
                        <Star class="h-5 w-5 text-yellow-500" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-yellow-500">4.8</p>
                        <p class="text-xs text-zinc-400">Avaliação</p>
                    </div>
                </div>
            </div>
            <div class="stat-card stat-card--success">
                <div class="relative z-10 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-500/20">
                        <MapPin class="h-5 w-5 text-green-500" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-green-500">{{ totalRegioes }}</p>
                        <p class="text-xs text-zinc-400">Regiões</p>
                    </div>
                </div>
            </div>
            <div class="stat-card stat-card--info">
                <div class="relative z-10 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-500/20">
                        <TrendingUp class="h-5 w-5 text-blue-500" />
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-blue-500">98%</p>
                        <p class="text-xs text-zinc-400">Satisfação</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resultados -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-white lg:text-2xl">
                    Resultados para "{{ termoBusca }}"
                </h2>
                <p class="text-sm text-zinc-400">
                    {{ totalResultados }} {{ totalResultados === 1 ? 'encontrado' : 'encontrados' }}
                </p>
            </div>
        </div>

        <!-- Estado vazio -->
        <div v-if="resultados.length === 0" class="glass-card p-8 text-center lg:p-16">
            <div class="mx-auto mb-6 flex h-28 w-28 items-center justify-center rounded-full" style="background: linear-gradient(135deg, rgba(var(--p-primary-500), 0.2), rgba(var(--p-primary-500), 0.1))">
                <Search class="h-14 w-14" style="color: var(--p-primary-color)" />
            </div>
            <h3 class="mb-3 text-2xl font-bold text-white">Nenhum estabelecimento encontrado</h3>
            <p class="mx-auto mb-6 max-w-md text-zinc-400">
                Não encontramos estabelecimentos com o termo "{{ termoBusca }}". Tente ajustar sua busca.
            </p>
            <div class="flex flex-col justify-center gap-3 sm:flex-row">
                <Link href="/estabelecimentos">
                    <Button label="Ver Todos" icon="pi pi-list" />
                </Link>
                <Link href="/">
                    <Button label="Voltar ao Início" severity="secondary" outlined />
                </Link>
            </div>
        </div>

        <!-- Grid -->
        <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <div v-for="est in resultados" :key="est.id" class="group animate-fade-in-up">
                <EstabelecimentoCard :estabelecimento="est" />
            </div>
        </div>

        <!-- CTA -->
        <div v-if="resultados.length > 0" class="mt-16">
            <div class="glass-card p-8 text-center lg:p-12" style="border-color: rgba(var(--p-primary-500), 0.2)">
                <h3 class="mb-3 text-2xl font-bold text-white">Não encontrou o que procurava?</h3>
                <p class="mx-auto mb-6 max-w-2xl text-zinc-400">
                    Entre em contato para sugerir novos estabelecimentos ou serviços.
                </p>
                <div class="flex flex-col justify-center gap-3 sm:flex-row">
                    <Button label="Sugerir Estabelecimento" icon="pi pi-plus" />
                    <Button label="Falar Conosco" severity="secondary" outlined icon="pi pi-comments" />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Search, Star, MapPin, TrendingUp } from 'lucide-vue-next'
import Button from 'primevue/button'
import SearchBar from '@/Components/Features/SearchBar.vue'
import EstabelecimentoCard from '@/Components/Features/EstabelecimentoCard.vue'
import DefaultLayout from '@/Layouts/DefaultLayout.vue'

defineOptions({ layout: DefaultLayout })

const props = defineProps({
    estabelecimentos: { type: Array, default: () => [] },
    q: { type: String, default: '' },
    categoria: { type: String, default: '' },
})

const termoBusca = computed(() => props.q || props.categoria || 'todos')
const totalResultados = computed(() => resultados.value.length)
const totalRegioes = computed(() => {
    const regioes = new Set(resultados.value.map(e => e.cidade).filter(Boolean))
    return regioes.size || 1
})

const resultados = computed(() => {
    if (!props.q && !props.categoria) return props.estabelecimentos

    const q = (props.q || '').toLowerCase()
    const cat = (props.categoria || '').toLowerCase()

    return props.estabelecimentos.filter(e => {
        if (q) return e.nome_fantasia?.toLowerCase().includes(q)
        return true
    })
})
</script>
