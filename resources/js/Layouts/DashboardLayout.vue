<template>
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="sidebar flex w-64 flex-shrink-0 flex-col">
            <!-- Logo -->
            <div class="flex items-center gap-3 border-b border-white/10 px-6 py-5">
                <i class="pi pi-calendar-clock text-2xl" style="color: var(--p-primary-color)"></i>
                <span class="text-gradient text-xl font-bold">EstetiFlow</span>
            </div>

            <!-- User Info -->
            <div class="border-b border-white/10 px-6 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-500/30 text-sm font-bold text-white">
                        {{ userInitials }}
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-white">{{ user?.nome_completo }}</p>
                        <p class="text-xs text-zinc-400">Profissional</p>
                    </div>
                </div>
            </div>

            <!-- Nav -->
            <nav class="flex-1 space-y-1 px-3 py-4">
                <Link
                    v-for="item in navItems"
                    :key="item.href"
                    :href="item.href"
                    :class="[
                        'nav-item group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200',
                        isActive(item.href)
                            ? 'bg-primary-500/20 text-white'
                            : 'text-zinc-400 hover:bg-white/5 hover:text-white'
                    ]"
                >
                    <i :class="[item.icon, 'text-base', isActive(item.href) ? 'text-primary-400' : 'text-zinc-500 group-hover:text-zinc-300']"></i>
                    {{ item.label }}
                    <span v-if="item.badge" class="ml-auto rounded-full bg-primary-500/30 px-2 py-0.5 text-xs text-primary-300">
                        {{ item.badge }}
                    </span>
                </Link>
            </nav>

            <!-- Footer Actions -->
            <div class="border-t border-white/10 px-3 py-4 space-y-1">
                <Link
                    href="/"
                    class="nav-item group flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-400 transition-all duration-200 hover:bg-white/5 hover:text-white"
                >
                    <i class="pi pi-home text-base text-zinc-500 group-hover:text-zinc-300"></i>
                    Ir para o início
                </Link>
                <button
                    type="button"
                    @click="logout"
                    class="nav-item group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-400 transition-all duration-200 hover:bg-red-500/10 hover:text-red-400"
                >
                    <i class="pi pi-sign-out text-base text-zinc-500 group-hover:text-red-400"></i>
                    Sair
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex flex-1 flex-col overflow-hidden">
            <!-- Topbar -->
            <header class="flex items-center justify-between border-b border-white/10 bg-black/20 px-6 py-4">
                <h1 class="text-lg font-semibold text-white">{{ pageTitle }}</h1>
                <div class="flex items-center gap-3">
                    <span class="text-sm text-zinc-400">{{ formattedDate }}</span>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 overflow-auto p-6">
                <slot />
            </main>
        </div>

        <!-- Toast Global -->
        <Toast position="top-right" />
        <ConfirmDialog />
    </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { useToast } from 'primevue/usetoast'
import Toast from 'primevue/toast'
import ConfirmDialog from 'primevue/confirmdialog'

const page = usePage()
const toast = useToast()

const user = computed(() => page.props.auth?.user)

const userInitials = computed(() => {
    const nome = user.value?.nome_completo ?? ''
    return nome.split(' ').slice(0, 2).map(n => n[0]).join('').toUpperCase()
})

const navItems = [
    { href: '/profissional/estabelecimento', icon: 'pi pi-building', label: 'Meu Estabelecimento' },
    // Futuras entradas:
    // { href: '/profissional/agenda', icon: 'pi pi-calendar', label: 'Agenda' },
    // { href: '/profissional/servicos', icon: 'pi pi-list', label: 'Serviços' },
    // { href: '/profissional/financeiro', icon: 'pi pi-chart-bar', label: 'Financeiro' },
]

const pageTitle = computed(() => {
    const current = navItems.find(item => isActive(item.href))
    return current?.label ?? 'Painel Profissional'
})

function isActive(href) {
    return page.url.startsWith(href)
}

const formattedDate = computed(() => {
    return new Intl.DateTimeFormat('pt-BR', { weekday: 'long', day: 'numeric', month: 'long' }).format(new Date())
})

function logout() {
    router.post('/logout')
}

// Flash messages como toast
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            toast.add({ severity: 'success', summary: 'Sucesso', detail: flash.success, life: 4000 })
        }
        if (flash?.error) {
            toast.add({ severity: 'error', summary: 'Erro', detail: flash.error, life: 5000 })
        }
    },
    { immediate: true }
)
</script>

<style scoped>
.sidebar {
    background: rgba(0, 0, 0, 0.4);
    backdrop-filter: blur(16px);
    border-right: 1px solid rgba(255, 255, 255, 0.08);
}

.text-gradient {
    background: linear-gradient(135deg, var(--p-primary-color) 0%, #a78bfa 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.nav-item {
    cursor: pointer;
}
</style>
