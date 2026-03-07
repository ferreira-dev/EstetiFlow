<template>
    <footer class="border-t border-white/5 bg-[#0a0a0f]/80 backdrop-blur-sm">
        <div class="container-app py-6 lg:py-12">
            <!-- Mobile -->
            <div class="text-center lg:hidden">
                <p class="text-sm text-zinc-400">
                    © {{ anoAtual }} <span class="font-semibold text-white">Agendamento Estético</span>
                </p>
            </div>

            <!-- Desktop -->
            <div class="hidden lg:block">
                <div class="mb-8 grid grid-cols-4 gap-8">
                    <!-- Marca -->
                    <div class="space-y-4">
                        <h3 class="flex items-center gap-2 text-lg font-bold text-white">
                            <i class="pi pi-calendar-clock" style="color: var(--p-primary-color)"></i>
                            Agendamento
                        </h3>
                        <p class="text-sm leading-relaxed text-zinc-400">
                            A melhor plataforma para agendar seu horário com os melhores profissionais de estética da cidade.
                        </p>
                    </div>

                    <!-- Serviços -->
                    <div class="space-y-4">
                        <h4 class="font-semibold text-white">Serviços</h4>
                        <ul class="space-y-2 text-sm text-zinc-400">
                            <li v-for="servico in servicosFooter" :key="servico.nome">
                                <Link
                                    :href="`/estabelecimentos?categoria=${servico.id}`"
                                    class="transition-colors hover:text-white"
                                >
                                    {{ servico.nome }}
                                </Link>
                            </li>
                        </ul>
                    </div>

                    <!-- Empresa -->
                    <div class="space-y-4">
                        <h4 class="font-semibold text-white">Empresa</h4>
                        <ul class="space-y-2 text-sm text-zinc-400">
                            <li><a href="#" class="transition-colors hover:text-white">Sobre nós</a></li>
                            <li><a href="#" class="transition-colors hover:text-white">Carreiras</a></li>
                            <li><a href="#" class="transition-colors hover:text-white">Parceiros</a></li>
                            <li><a href="#" class="transition-colors hover:text-white">Contato</a></li>
                        </ul>
                    </div>

                    <!-- Contato -->
                    <div class="space-y-4">
                        <h4 class="font-semibold text-white">Contato</h4>
                        <div class="space-y-3 text-sm text-zinc-400">
                            <div class="flex items-center gap-2">
                                <i class="pi pi-phone text-xs"></i>
                                <span>(11) 99999-9999</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="pi pi-envelope text-xs"></i>
                                <span>contato@agendamento.com</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom -->
                <div class="flex items-center justify-between border-t border-white/5 pt-8">
                    <p class="text-sm text-zinc-500">
                        © {{ anoAtual }} <span class="font-semibold">Agendamento Estético</span>. Todos os direitos reservados.
                    </p>
                    <div class="flex items-center gap-6 text-sm text-zinc-500">
                        <a href="#" class="transition-colors hover:text-white">Privacidade</a>
                        <a href="#" class="transition-colors hover:text-white">Termos</a>
                        <a href="#" class="transition-colors hover:text-white">Cookies</a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const anoAtual = new Date().getFullYear()

const servicosFooter = [
    { id: 'cabelo', nome: 'Corte de Cabelo' },
    { id: 'barba', nome: 'Barba' },
    { id: 'manicure', nome: 'Manicure' },
    { id: 'estetica', nome: 'Estética Facial' }
]
</script>
