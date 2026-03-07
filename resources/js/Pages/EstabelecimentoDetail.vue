<template>
    <div>
        <!-- Hero Image -->
        <div class="relative h-72 w-full lg:h-[500px]">
            <img
                v-if="estabelecimento"
                :src="estabelecimento.foto_capa_url || 'https://images.unsplash.com/photo-1560066984-138dadb4c035?w=800'"
                :alt="estabelecimento.nome_fantasia"
                class="h-full w-full object-cover"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-black/20"></div>

            <!-- Nav buttons -->
            <div class="absolute top-4 left-4 lg:top-8 lg:left-8">
                <Button icon="pi pi-chevron-left" severity="secondary" rounded @click="voltar" />
            </div>

            <!-- Info overlay -->
            <div v-if="estabelecimento" class="absolute right-0 bottom-0 left-0 p-5 lg:p-8">
                <div class="mx-auto max-w-7xl">
                    <div class="mb-2 flex items-center gap-2">
                        <Tag>
                            <div class="flex items-center gap-1">
                                <Star :size="12" class="fill-white text-white" />
                                {{ estabelecimento.avaliacao || 4.9 }}
                            </div>
                        </Tag>
                        <Tag severity="secondary">
                            {{ estabelecimento.totalAvaliacoes || 0 }} avaliações
                        </Tag>
                    </div>
                    <h1 class="mb-2 text-2xl font-bold text-white lg:text-4xl">{{ estabelecimento.nome_fantasia }}</h1>
                    <div class="flex items-center gap-2 text-white/90">
                        <MapPin :size="16" />
                        <p class="text-sm lg:text-base">
                            {{ [estabelecimento.logradouro, estabelecimento.numero, estabelecimento.bairro, estabelecimento.cidade, estabelecimento.estado].filter(Boolean).join(', ') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Conteúdo -->
        <div v-if="estabelecimento" class="mx-auto max-w-7xl p-5 lg:px-8 lg:py-12">
            <div class="lg:grid lg:grid-cols-12 lg:gap-12">

                <!-- Coluna Principal -->
                <div class="space-y-8 lg:col-span-8">
                    <!-- Stats mobile -->
                    <div class="grid grid-cols-3 gap-3 lg:hidden">
                        <div class="glass-card p-4 text-center">
                            <Star class="mx-auto mb-1 h-5 w-5 text-yellow-500" />
                            <p class="text-lg font-bold text-white">{{ estabelecimento.avaliacao }}</p>
                            <p class="text-xs text-zinc-400">Avaliação</p>
                        </div>
                        <div class="glass-card p-4 text-center">
                            <Users class="mx-auto mb-1 h-5 w-5 text-blue-500" />
                            <p class="text-lg font-bold text-white">{{ estabelecimento.totalAvaliacoes }}</p>
                            <p class="text-xs text-zinc-400">Clientes</p>
                        </div>
                        <div class="glass-card p-4 text-center">
                            <Award class="mx-auto mb-1 h-5 w-5 text-green-500" />
                            <p class="text-lg font-bold text-white">5+</p>
                            <p class="text-xs text-zinc-400">Anos</p>
                        </div>
                    </div>

                    <!-- Sobre -->
                    <div class="glass-card p-6">
                        <h2 class="mb-4 flex items-center gap-2 text-lg font-semibold text-white">
                            <Info class="h-5 w-5" style="color: var(--p-primary-color)" />
                            Sobre o Estabelecimento
                        </h2>
                        <p class="leading-relaxed text-zinc-400 lg:text-lg">{{ estabelecimento.descricao }}</p>

                        <div class="mt-6 grid grid-cols-2 gap-4 lg:grid-cols-3">
                            <div v-for="item in caracteristicas" :key="item" class="flex items-center gap-2 text-sm text-zinc-300">
                                <CheckCircle class="h-4 w-4 text-green-500" />
                                <span>{{ item }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Serviços -->
                    <div class="glass-card p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="flex items-center gap-2 text-lg font-semibold text-white">
                                <CalendarDays class="h-5 w-5" style="color: var(--p-primary-color)" />
                                Nossos Serviços
                            </h2>
                            <Tag severity="secondary">{{ servicos.length }} serviços</Tag>
                        </div>
                        <div class="grid gap-4 lg:gap-6">
                            <template v-for="prof in estabelecimento.profissionais" :key="prof.id">
                                <ServicoItem
                                    v-for="sp in prof.servicos_profissionais"
                                    :key="sp.id"
                                    :servico="{ ...sp.servico, preco: sp.preco, tempo_execucao_minutos: sp.tempo_execucao_minutos }"
                                    :nome-estabelecimento="estabelecimento.nome_fantasia"
                                    :estabelecimento-id="estabelecimento.id"
                                    :profissional-id="prof.id"
                                />
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="mt-8 space-y-6 lg:col-span-4 lg:mt-0">
                    <!-- Stats Desktop -->
                    <div class="hidden gap-4 lg:grid lg:grid-cols-2">
                        <div class="stat-card stat-card--warning">
                            <div class="relative z-10 flex flex-col items-center gap-2 text-center">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-yellow-500/20">
                                    <Star class="h-6 w-6 text-yellow-500" />
                                </div>
                                <p class="text-2xl font-bold text-yellow-500">{{ estabelecimento.avaliacao }}</p>
                                <p class="text-xs text-zinc-400">Avaliação Média</p>
                            </div>
                        </div>
                        <div class="stat-card stat-card--info">
                            <div class="relative z-10 flex flex-col items-center gap-2 text-center">
                                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/20">
                                    <Users class="h-6 w-6 text-blue-500" />
                                </div>
                                <p class="text-2xl font-bold text-blue-500">+{{ estabelecimento.totalAvaliacoes }}</p>
                                <p class="text-xs text-zinc-400">Clientes Atendidos</p>
                            </div>
                        </div>
                    </div>

                    <!-- Horários -->
                    <div class="glass-card p-6">
                        <h3 class="mb-4 flex items-center gap-2 font-semibold text-white">
                            <Clock class="h-5 w-5" style="color: var(--p-primary-color)" />
                            Horário de Funcionamento
                        </h3>
                        <div class="space-y-3">
                            <div
                                v-for="(horario, dia) in horarios"
                                :key="dia"
                                class="flex items-center justify-between border-b border-white/5 py-2 last:border-0"
                            >
                                <span class="text-sm font-medium text-zinc-300">{{ dia }}</span>
                                <span :class="horario === 'Fechado' ? 'text-red-400' : 'text-zinc-400'" class="text-sm">
                                    {{ horario }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Contato -->
                    <div class="glass-card p-6">
                        <h3 class="mb-4 flex items-center gap-2 font-semibold text-white">
                            <Phone class="h-5 w-5" style="color: var(--p-primary-color)" />
                            Contato
                        </h3>
                        <div class="space-y-2">
                            <TelefoneItem
                                v-if="estabelecimento.telefone_principal"
                                :telefone="estabelecimento.telefone_principal"
                            />
                            <TelefoneItem
                                v-if="estabelecimento.telefone_secundario"
                                :telefone="estabelecimento.telefone_secundario"
                            />
                        </div>
                    </div>

                    <!-- CTA -->
                    <div class="glass-card p-6 text-center" style="border-color: rgba(var(--p-primary-500), 0.2)">
                        <h3 class="mb-2 flex items-center justify-center gap-2 text-lg font-semibold text-white">
                            <CalendarDays class="h-5 w-5" />
                            Pronto para agendar?
                        </h3>
                        <p class="text-sm text-zinc-400">
                            Escolha um dos nossos serviços e agende seu horário
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div v-if="!estabelecimento" class="flex items-center justify-center py-20">
            <i class="pi pi-spin pi-spinner text-4xl" style="color: var(--p-primary-color)"></i>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { Star, MapPin, Users, Award, Info, CheckCircle, CalendarDays, Clock, Phone } from 'lucide-vue-next'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import ServicoItem from '@/Components/Features/ServicoItem.vue'
import TelefoneItem from '@/Components/Features/TelefoneItem.vue'
import DefaultLayout from '@/Layouts/DefaultLayout.vue'

defineOptions({ layout: DefaultLayout })

const props = defineProps({
    estabelecimento: { type: Object, default: null },
})

const servicos = computed(() => {
    if (!props.estabelecimento?.profissionais) return []
    return props.estabelecimento.profissionais.flatMap(
        prof => (prof.servicos_profissionais || []).map(sp => ({
            ...sp.servico,
            preco: sp.preco,
            tempo_execucao_minutos: sp.tempo_execucao_minutos,
        }))
    )
})

const caracteristicas = [
    'Wi-Fi Gratuito', 'Estacionamento', 'Cartão de Crédito',
    'Produtos Premium', 'Ambiente Climatizado', 'Profissionais Qualificados'
]

const horarios = {
    'Segunda-feira': '08:00 - 18:00',
    'Terça-feira': '08:00 - 18:00',
    'Quarta-feira': '08:00 - 18:00',
    'Quinta-feira': '08:00 - 18:00',
    'Sexta-feira': '08:00 - 20:00',
    'Sábado': '09:00 - 16:00',
    'Domingo': 'Fechado'
}

function voltar() {
    window.history.back()
}
</script>
