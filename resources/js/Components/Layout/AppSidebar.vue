<template>
    <Drawer v-model:visible="visivel" position="right" class="w-80" :showCloseIcon="false">
        <template #header>
            <span class="text-lg font-bold">Menu</span>
        </template>

        <!-- Usuário -->
        <div class="flex items-center justify-between gap-3 border-b border-white/10 pb-5">
            <template v-if="$page.props.auth?.user">
                <div class="flex items-center gap-3">
                    <Avatar
                        :label="$page.props.auth.user.nome_completo?.charAt(0)?.toUpperCase()"
                        shape="circle"
                    />
                    <div>
                        <p class="font-semibold text-white">{{ $page.props.auth.user.nome_completo }}</p>
                        <p class="text-xs text-zinc-400">{{ $page.props.auth.user.email }}</p>
                    </div>
                </div>
            </template>
            <template v-else>
                <p class="font-semibold text-white">Olá, faça seu login!</p>
                <Link href="/login" @click="fechar">
                    <Button icon="pi pi-sign-in" size="small" />
                </Link>
            </template>
        </div>

        <!-- Navegação -->
        <div class="flex flex-col gap-1 border-b border-white/10 py-4">
            <!-- Link para o painel profissional (apenas profissionais) -->
            <Link
                v-if="isProfissional"
                href="/profissional/estabelecimento"
                @click="fechar"
                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-300 transition-colors hover:bg-white/5 hover:text-white"
            >
                <i class="pi pi-building text-base"></i>
                Painel Profissional
            </Link>
            <Link
                v-for="item in navItems"
                :key="item.rota"
                :href="item.rota"
                @click="fechar"
                class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-zinc-300 transition-colors hover:bg-white/5 hover:text-white"
            >
                <i :class="item.icone" class="text-base"></i>
                {{ item.titulo }}
            </Link>
        </div>

        <!-- Categorias -->
        <div class="flex flex-col gap-1 border-b border-white/10 py-4">
            <p class="mb-2 px-3 text-xs font-semibold uppercase tracking-wider text-zinc-500">Categorias</p>
            <Link
                v-for="cat in categorias"
                :key="cat.id"
                :href="`/estabelecimentos?categoria=${cat.id}`"
                @click="fechar"
                class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm text-zinc-400 transition-colors hover:bg-white/5 hover:text-white"
            >
                <i :class="cat.icone" class="text-sm"></i>
                {{ cat.titulo }}
            </Link>
        </div>

        <!-- Logout -->
        <div v-if="$page.props.auth?.user" class="pt-4">
            <Button
                label="Sair da conta"
                icon="pi pi-sign-out"
                severity="secondary"
                text
                class="w-full justify-start"
                @click="handleLogout"
            />
        </div>
    </Drawer>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { CATEGORIAS_SERVICO } from '@/Constants/categorias'
import Drawer from 'primevue/drawer'
import Avatar from 'primevue/avatar'
import Button from 'primevue/button'

const props = defineProps({
    visible: Boolean
})
const emit = defineEmits(['update:visible'])

const visivel = computed({
    get: () => props.visible,
    set: (val) => emit('update:visible', val)
})

const navItems = [
    { titulo: 'Início', rota: '/', icone: 'pi pi-home' },
    { titulo: 'Estabelecimentos', rota: '/estabelecimentos', icone: 'pi pi-building' },
    { titulo: 'Agendamentos', rota: '/agendamentos', icone: 'pi pi-calendar' }
]

const page = usePage()
const isProfissional = computed(() =>
    (page.props.auth?.roles ?? []).includes('profissional')
)

const categorias = CATEGORIAS_SERVICO.slice(0, 6)

function fechar() {
    visivel.value = false
}

function handleLogout() {
    fechar()
    router.post('/logout')
}
</script>
