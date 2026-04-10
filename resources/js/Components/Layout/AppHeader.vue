<template>
    <header class="sticky top-0 z-50 border-b border-white/5 bg-[#0a0a0f]/95 backdrop-blur-md">
        <div class="container-app flex items-center justify-between py-4 lg:py-5">
            <!-- Logo -->
            <Link href="/" class="flex items-center gap-2 text-lg font-bold text-white lg:text-xl">
                <i class="pi pi-calendar-clock text-xl lg:text-2xl" style="color: var(--p-primary-color)"></i>
                <span class="text-gradient">Agendamento</span>
            </Link>

            <!-- Nav Desktop -->
            <nav class="hidden items-center gap-6 lg:flex">
                <Link
                    v-for="item in navItems"
                    :key="item.rota"
                    :href="item.rota"
                    class="flex items-center gap-2 text-sm font-medium text-zinc-400 transition-colors hover:text-white"
                >
                    <i :class="item.icone" class="text-sm"></i>
                    {{ item.titulo }}
                </Link>
            </nav>

            <!-- Ações Desktop -->
            <div class="hidden items-center gap-3 lg:flex">
                <!-- Busca rápida desktop -->
                <Button
                    v-if="!mostrarBusca"
                    icon="pi pi-search"
                    severity="secondary"
                    text
                    rounded
                    @click="mostrarBusca = true"
                    v-tooltip.bottom="'Buscar'"
                />

                <div v-if="mostrarBusca" class="animate-fade-in flex items-center gap-2">
                    <IconField>
                        <InputIcon class="pi pi-search" />
                        <InputText
                            ref="campoBuscaRef"
                            v-model="termoBusca"
                            placeholder="Buscar estabelecimento..."
                            class="w-64"
                            @keyup.enter="executarBusca"
                            @keyup.escape="fecharBusca"
                        />
                    </IconField>
                    <Button
                        icon="pi pi-times"
                        severity="secondary"
                        text
                        rounded
                        size="small"
                        @click="fecharBusca"
                    />
                </div>

                <!-- Menu do usuário ou Login -->
                <template v-if="$page.props.auth?.user">
                    <Button
                        type="button"
                        severity="secondary"
                        text
                        rounded
                        @click="toggleMenuUsuario"
                        aria-haspopup="true"
                        aria-controls="menu-usuario"
                    >
                        <Avatar
                            :label="$page.props.auth.user.nome_completo?.charAt(0)?.toUpperCase()"
                            shape="circle"
                            class="mr-2"
                            size="small"
                        />
                        <span class="text-sm">{{ $page.props.auth.user.nome_completo }}</span>
                        <i class="pi pi-chevron-down ml-2 text-xs"></i>
                    </Button>
                    <Menu
                        id="menu-usuario"
                        ref="menuUsuarioRef"
                        :model="opcoesMenuUsuario"
                        :popup="true"
                    />
                </template>
                <template v-else>
                    <Link href="/login">
                        <Button label="Entrar" icon="pi pi-sign-in" size="small" />
                    </Link>
                </template>
            </div>

            <!-- Menu Mobile -->
            <Button
                icon="pi pi-bars"
                severity="secondary"
                text
                rounded
                class="lg:hidden"
                @click="sidebarVisivel = true"
            />
        </div>

        <!-- Sidebar Mobile -->
        <AppSidebar v-model:visible="sidebarVisivel" />
    </header>
</template>

<script setup>
import { ref, computed, nextTick, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import Button from 'primevue/button'
import Avatar from 'primevue/avatar'
import Menu from 'primevue/menu'
import InputText from 'primevue/inputtext'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import AppSidebar from './AppSidebar.vue'

// Nav
const navItems = [
    { titulo: 'Início', rota: '/', icone: 'pi pi-home' },
    { titulo: 'Estabelecimentos', rota: '/estabelecimentos', icone: 'pi pi-building' },
    { titulo: 'Agendamentos', rota: '/agendamentos', icone: 'pi pi-calendar' }
]

// Busca
const mostrarBusca = ref(false)
const termoBusca = ref('')
const campoBuscaRef = ref(null)

watch(mostrarBusca, async (val) => {
    if (val) {
        await nextTick()
        campoBuscaRef.value?.$el?.focus()
    }
})

function executarBusca() {
    if (termoBusca.value.trim()) {
        router.visit('/estabelecimentos', { data: { q: termoBusca.value.trim() } })
        fecharBusca()
    }
}

function fecharBusca() {
    mostrarBusca.value = false
    termoBusca.value = ''
}

// Sidebar
const sidebarVisivel = ref(false)

// Menu do usuário
const page = usePage()
const menuUsuarioRef = ref(null)

function toggleMenuUsuario(event) {
    menuUsuarioRef.value.toggle(event)
}

// Constrói o menu dinamicamente conforme os roles do usuário logado
const opcoesMenuUsuario = computed(() => {
    const roles = page.props.auth?.roles ?? []
    const isProfissional = roles.includes('profissional')

    return [
        // Visível apenas para profissionais
        ...(isProfissional ? [
            {
                label: 'Painel Profissional',
                icon: 'pi pi-building',
                command: () => router.visit('/profissional/estabelecimento')
            },
            { separator: true }
        ] : []),
        {
            label: 'Meus Agendamentos',
            icon: 'pi pi-calendar',
            command: () => router.visit('/agendamentos')
        },
        { separator: true },
        {
            label: 'Sair',
            icon: 'pi pi-sign-out',
            command: () => router.post('/logout')
        }
    ]
})
</script>
