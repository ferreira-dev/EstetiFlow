<template>
    <div>
        <!-- Hero Section -->
        <section class="container-app py-8 lg:py-16">
            <!-- Saudação -->
            <div class="mb-8 lg:mb-12">
                <h1 class="text-gradient text-2xl font-bold lg:text-4xl">
                    {{ saudacaoTexto }}, {{ nomeUsuario }}
                </h1>
                <p class="mt-2 text-sm text-zinc-400 lg:text-base">
                    {{ dataAtual }}
                </p>
            </div>

            <!-- Stats Desktop -->
            <div class="mb-8 hidden grid-cols-3 gap-6 lg:grid">
                <div class="stat-card stat-card--warning">
                    <div class="relative z-10 flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-yellow-500/20">
                            <Star class="h-6 w-6 text-yellow-500" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-yellow-500">4.8</p>
                            <p class="text-sm text-zinc-400">Avaliação Média</p>
                        </div>
                    </div>
                </div>
                <div class="stat-card stat-card--info">
                    <div class="relative z-10 flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/20">
                            <Building class="h-6 w-6 text-blue-500" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-blue-500">{{ estabelecimentos.length }}</p>
                            <p class="text-sm text-zinc-400">Estabelecimentos</p>
                        </div>
                    </div>
                </div>
                <div class="stat-card stat-card--primary">
                    <div class="relative z-10 flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl" style="background: rgba(var(--p-primary-500), 0.2)">
                            <CalendarDays class="h-6 w-6" style="color: var(--p-primary-color)" />
                        </div>
                        <div>
                            <p class="text-2xl font-bold" style="color: var(--p-primary-color)">{{ agendamentos.length }}</p>
                            <p class="text-sm text-zinc-400">Agendamentos</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Busca -->
            <div class="mb-8 lg:mb-12">
                <SearchBar />
            </div>

            <!-- Categorias -->
            <div class="mb-8 lg:mb-12">
                <h2 class="mb-4 text-lg font-semibold text-white">Busca Rápida</h2>
                <div class="hide-scrollbar flex gap-3 overflow-x-auto pb-2">
                    <CategoriaItem
                        v-for="cat in categorias"
                        :key="cat.id"
                        :categoria="cat"
                    />
                </div>
            </div>
        </section>

        <!-- Agendamentos do Usuário -->
        <section v-if="$page.props.auth?.user && agendamentosConfirmados.length > 0" class="container-app mb-8 lg:mb-12">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white">Seus Agendamentos</h2>
                <Link href="/agendamentos" class="text-sm font-medium transition-colors hover:text-white" style="color: var(--p-primary-color)">
                    Ver todos
                </Link>
            </div>
            <div class="hide-scrollbar flex gap-4 overflow-x-auto pb-2">
                <div v-for="agendamento in agendamentosConfirmados" :key="agendamento.id" class="min-w-[90%] sm:min-w-[45%] lg:min-w-[30%]">
                    <AgendamentoCard :agendamento="agendamento" />
                </div>
            </div>
        </section>

        <!-- Estabelecimentos Recomendados -->
        <section class="container-app mb-8 lg:mb-12">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white">Recomendados</h2>
                <Link href="/estabelecimentos" class="text-sm font-medium transition-colors hover:text-white" style="color: var(--p-primary-color)">
                    Ver todos
                </Link>
            </div>
            <div class="hide-scrollbar flex gap-4 overflow-x-auto pb-2">
                <div v-for="est in recomendados" :key="est.id" class="group min-w-[180px]">
                    <EstabelecimentoCard :estabelecimento="est" />
                </div>
            </div>
        </section>

        <!-- Estabelecimentos Populares -->
        <section class="container-app mb-12 lg:mb-20">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white">Populares</h2>
                <Link href="/estabelecimentos" class="text-sm font-medium transition-colors hover:text-white" style="color: var(--p-primary-color)">
                    Ver todos
                </Link>
            </div>
            <div class="hide-scrollbar flex gap-4 overflow-x-auto pb-2">
                <div v-for="est in populares" :key="est.id" class="group min-w-[180px]">
                    <EstabelecimentoCard :estabelecimento="est" />
                </div>
            </div>
        </section>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { saudacao, formatarData } from '@/Utils/formatters'
import { CATEGORIAS_SERVICO } from '@/Constants/categorias'
import { Star, Building, CalendarDays } from 'lucide-vue-next'
import SearchBar from '@/Components/Features/SearchBar.vue'
import CategoriaItem from '@/Components/Features/CategoriaItem.vue'
import EstabelecimentoCard from '@/Components/Features/EstabelecimentoCard.vue'
import AgendamentoCard from '@/Components/Features/AgendamentoCard.vue'
import DefaultLayout from '@/Layouts/DefaultLayout.vue'

defineOptions({ layout: DefaultLayout })

const props = defineProps({
    estabelecimentos: { type: Array, default: () => [] },
    agendamentos: { type: Array, default: () => [] },
})

const saudacaoTexto = computed(() => saudacao())
const nomeUsuario = computed(() => usePage().props.auth?.user?.nome || 'visitante')
const dataAtual = computed(() => formatarData(new Date()))
const categorias = CATEGORIAS_SERVICO

const recomendados = computed(() => props.estabelecimentos.slice(0, 3))
const populares = computed(() => props.estabelecimentos.slice(3, 6))
// Próximos agendamentos ativos para exibir na Home
const agendamentosConfirmados = computed(() =>
    props.agendamentos
        .filter(a => ['pendente', 'confirmado'].includes(a.status))
        .slice(0, 3)
)
</script>
