<template>
    <div class="glass-card space-y-4 p-5" style="border-color: color-mix(in srgb, var(--p-primary-500) 25%, transparent)">
        <div class="flex items-start gap-3">
            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary-500/20">
                <i class="pi pi-link text-lg text-primary-400"></i>
            </div>
            <div class="min-w-0">
                <p class="font-semibold text-white">Seu link de agendamento</p>
                <p class="text-xs text-zinc-400">Coloque na bio do Instagram e envie aos clientes pelo WhatsApp.</p>
            </div>
        </div>

        <a
            :href="url"
            target="_blank"
            rel="noopener"
            class="block break-all rounded-lg bg-zinc-900/60 px-3 py-2 text-sm text-primary-300 hover:underline"
        >
            {{ url }}
        </a>

        <div class="flex flex-wrap gap-2">
            <Button label="Copiar meu link" icon="pi pi-copy" size="small" @click="copiar" />
            <Button
                label="Compartilhar no WhatsApp"
                icon="pi pi-whatsapp"
                size="small"
                severity="success"
                outlined
                @click="compartilharWhatsapp"
            />
        </div>
    </div>
</template>

<script setup>
import Button from 'primevue/button'
import { useToast } from 'primevue/usetoast'

const props = defineProps({
    url:  { type: String, required: true },
    nome: { type: String, default: '' },
})

const emit  = defineEmits(['compartilhado'])
const toast = useToast()

const TEMPO_LIMITE_COPIA_MS = 2000

async function copiar() {
    try {
        await copiarComClipboardApi(props.url)
    } catch {
        try {
            copiarComFallback(props.url)
        } catch {
            toast.add({ severity: 'error', summary: 'Não foi possível copiar', detail: 'Selecione o link e copie manualmente.', life: 5000 })
            return
        }
    }
    emit('compartilhado')
    toast.add({ severity: 'success', summary: 'Link copiado!', detail: 'Agora é só colar onde quiser.', life: 3000 })
}

// Clipboard API só existe em contexto seguro (https/localhost). Alguns navegadores deixam
// a promise pendente (permissão negada ou sem resposta), por isso o tempo limite.
function copiarComClipboardApi(texto) {
    if (!navigator.clipboard || !window.isSecureContext) {
        return Promise.reject(new Error('clipboard indisponível'))
    }
    return Promise.race([
        navigator.clipboard.writeText(texto),
        new Promise((_, rejeitar) => setTimeout(() => rejeitar(new Error('timeout')), TEMPO_LIMITE_COPIA_MS)),
    ])
}

// Fallback para contexto não seguro ou Clipboard API indisponível
function copiarComFallback(texto) {
    const campo = document.createElement('textarea')
    campo.value = texto
    campo.setAttribute('readonly', '')
    campo.style.position = 'fixed'
    campo.style.opacity = '0'
    document.body.appendChild(campo)
    campo.select()
    const ok = document.execCommand('copy')
    document.body.removeChild(campo)
    if (!ok) throw new Error('copy falhou')
}

function compartilharWhatsapp() {
    // "em" evita errar o gênero do nome ("no Barbearia", "na Studio")
    const local = props.nome ? ` em ${props.nome}` : ''
    const mensagem = `Agende seu horário${local} pelo link: ${props.url}`
    const janela = window.open(`https://wa.me/?text=${encodeURIComponent(mensagem)}`, '_blank')
    if (janela) janela.opener = null
    emit('compartilhado')
}
</script>
