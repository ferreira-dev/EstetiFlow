<template>
    <Dialog
        v-model:visible="visible"
        modal
        header="Novo Agendamento"
        class="w-[90%] max-w-2xl"
        :closable="true"
        @hide="resetForm"
    >
        <div class="mt-4 space-y-6">
            <!-- Stepper Indicators -->
            <div class="flex items-center justify-between px-4 pb-4">
                <div class="flex flex-col items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold transition-colors"
                         :class="step >= 1 ? 'bg-primary-500 text-black' : 'bg-white/10 text-zinc-500'">1</div>
                    <span class="text-xs font-semibold text-zinc-400">Cliente</span>
                </div>
                <div class="h-px flex-1 bg-white/10 mx-4"></div>
                <div class="flex flex-col items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold transition-colors"
                         :class="step >= 2 ? 'bg-primary-500 text-black' : 'bg-white/10 text-zinc-500'">2</div>
                    <span class="text-xs font-semibold text-zinc-400">Serviço</span>
                </div>
                <div class="h-px flex-1 bg-white/10 mx-4"></div>
                <div class="flex flex-col items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold transition-colors"
                         :class="step >= 3 ? 'bg-primary-500 text-black' : 'bg-white/10 text-zinc-500'">3</div>
                    <span class="text-xs font-semibold text-zinc-400">Data/Hora</span>
                </div>
            </div>

            <!-- Content Container -->
            <div class="min-h-[300px]">
                
                <!-- STEP 1: CLIENTE -->
                <div v-show="step === 1" class="space-y-5 animate-fade-in">
                    <div v-if="!clienteSelecionado && !mostrarFormPreCadastro">
                        <label class="block text-sm font-medium text-zinc-300 mb-2">Buscar cliente existente</label>
                        <AutoComplete
                            v-model="termoBuscaCliente"
                            :suggestions="clientesEncontrados"
                            @complete="buscarCliente"
                            field="nome_completo"
                            placeholder="Digite o nome, e-mail ou telefone..."
                            class="w-full"
                            inputClass="w-full"
                            :delay="500"
                            forceSelection
                            @item-select="selecionarCliente"
                        >
                            <template #item="slotProps">
                                <div class="flex flex-col py-1">
                                    <span class="font-semibold">{{ slotProps.item.nome_completo }}</span>
                                    <span class="text-xs text-zinc-400">{{ slotProps.item.email || slotProps.item.telefone || 'Sem contato extra' }}</span>
                                </div>
                            </template>
                        </AutoComplete>
                        
                        <div class="mt-6 flex justify-center">
                            <Button label="Cadastrar novo cliente" link icon="pi pi-plus" size="small" @click="mostrarFormPreCadastro = true" />
                        </div>
                    </div>

                    <!-- Formulário de Pré-cadastro -->
                    <div v-else-if="!clienteSelecionado && mostrarFormPreCadastro" class="space-y-4 rounded-xl border border-white/10 bg-white/5 p-5 relative">
                        <Button icon="pi pi-times" class="p-button-rounded p-button-text p-button-sm absolute top-2 right-2 text-zinc-400" @click="mostrarFormPreCadastro = false" />
                        <h4 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Pré-cadastro rápido</h4>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="flex flex-col gap-1 sm:col-span-2">
                                <label class="text-xs font-medium text-zinc-400">Nome Completo <span class="text-red-400">*</span></label>
                                <InputText v-model="formPreCadastro.nome_completo" class="w-full" :invalid="!!errosPreCadastro.nome_completo" />
                                <span v-if="errosPreCadastro.nome_completo" class="text-xs text-red-400">{{ errosPreCadastro.nome_completo[0] }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-medium text-zinc-400">Telefone <span class="text-red-400">*</span></label>
                                <InputText v-model="formPreCadastro.telefone" class="w-full" placeholder="(11) 99999-9999" :invalid="!!errosPreCadastro.telefone" />
                                <span v-if="errosPreCadastro.telefone" class="text-xs text-red-400">{{ errosPreCadastro.telefone[0] }}</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <label class="text-xs font-medium text-zinc-400">Data de Nascimento <span class="text-red-400">*</span></label>
                                <DatePicker v-model="formPreCadastro.data_nascimento" dateFormat="dd/mm/yy" class="w-full" :maxDate="new Date()" :invalid="!!errosPreCadastro.data_nascimento" />
                                <span v-if="errosPreCadastro.data_nascimento" class="text-xs text-red-400">{{ errosPreCadastro.data_nascimento[0] }}</span>
                            </div>
                        </div>

                        <div class="flex justify-end mt-4">
                            <Button label="Salvar e Continuar" size="small" icon="pi pi-check" :loading="salvandoPreCadastro" @click="salvarPreCadastro" />
                        </div>
                    </div>

                    <!-- Cliente Selecionado -->
                    <div v-if="clienteSelecionado" class="glass-card flex items-center justify-between p-4 bg-primary-500/10 border-primary-500/30">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary-500/20 text-primary-400 font-bold">
                                {{ clienteSelecionado.nome_completo.charAt(0).toUpperCase() }}
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-bold text-white">{{ clienteSelecionado.nome_completo }}</span>
                                <span class="text-xs text-zinc-400">{{ clienteSelecionado.email || clienteSelecionado.telefone }}</span>
                            </div>
                        </div>
                        <Button icon="pi pi-pencil" text rounded aria-label="Alterar" @click="limparCliente" />
                    </div>
                </div>

                <!-- STEP 2: SERVIÇO -->
                <div v-show="step === 2" class="space-y-4 animate-fade-in">
                    <p class="text-sm text-zinc-400 mb-2">Selecione o serviço para agendar:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[300px] overflow-y-auto hide-scrollbar pr-2">
                        <div v-for="sp in servicos" :key="sp.id" 
                             class="rounded-xl border p-3 cursor-pointer transition-all hover:bg-white/5"
                             :class="servicoSelecionado?.id === sp.id ? 'border-primary-500 bg-primary-500/5' : 'border-white/10 glass-card'"
                             @click="selecionarServico(sp)">
                            <div class="flex justify-between items-start">
                                <h4 class="font-semibold text-sm text-white">{{ sp.servico.nome }}</h4>
                                <span class="text-xs font-bold text-primary-400">{{ formatarMoeda(sp.preco) }}</span>
                            </div>
                            <div class="flex items-center gap-2 mt-2 text-xs text-zinc-400">
                                <i class="pi pi-clock" style="font-size: 0.7rem"></i>
                                <span>{{ sp.tempo_execucao_minutos }} min</span>
                            </div>
                        </div>
                    </div>
                    <div v-if="servicos.length === 0" class="text-center p-6 text-zinc-500">
                        Nenhum serviço cadastrado ainda.
                    </div>
                </div>

                <!-- STEP 3: DATA E HORA -->
                <div v-show="step === 3" class="space-y-5 animate-fade-in">
                    <div v-if="servicoSelecionado" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Calendário -->
                        <div>
                            <DatePicker
                                v-model="dataSelecionada"
                                inline
                                :minDate="new Date()"
                                :disabledDates="datasBlockeadas"
                                class="w-full"
                            />
                        </div>
                        
                        <!-- Horários -->
                        <div>
                            <h4 class="text-sm font-semibold text-white mb-3 flex items-center gap-2">
                                <i class="pi pi-clock text-primary-500"></i> Horários Disponíveis
                            </h4>
                            
                            <div v-if="!diaHabilitado" class="text-sm text-zinc-500 p-4 rounded-lg bg-red-500/10 border border-red-500/20 text-center">
                                Você não atende neste dia.
                            </div>
                            <div v-else-if="horariosDisponiveis.length > 0" class="flex flex-wrap gap-2 max-h-[250px] overflow-y-auto">
                                <Button
                                    v-for="hora in horariosDisponiveis"
                                    :key="hora"
                                    :label="hora"
                                    :severity="horaSelecionada === hora ? 'primary' : 'secondary'"
                                    :outlined="horaSelecionada !== hora"
                                    size="small"
                                    class="px-4"
                                    @click="horaSelecionada = hora"
                                />
                            </div>
                            <div v-else class="text-sm text-zinc-500 p-4 rounded-lg bg-orange-500/10 border border-orange-500/20 text-center">
                                Não há mais horários disponíveis.
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Ações Flutuantes da Modal -->
            <div class="flex justify-between items-center pt-4 border-t border-white/10 mt-6">
                <!-- Voltar -->
                <Button 
                    v-if="step > 1" 
                    label="Voltar" 
                    icon="pi pi-arrow-left" 
                    severity="secondary" 
                    text 
                    @click="step--" 
                />
                <div v-else></div>

                <!-- Avançar / Concluir -->
                <Button 
                    v-if="step < 3" 
                    label="Próximo" 
                    icon="pi pi-arrow-right" 
                    iconPos="right" 
                    :disabled="(step === 1 && !clienteSelecionado) || (step === 2 && !servicoSelecionado)"
                    @click="step++" 
                />
                
                <Button 
                    v-if="step === 3" 
                    label="Confirmar Agendamento" 
                    icon="pi pi-check" 
                    :disabled="!dataSelecionada || !horaSelecionada"
                    :loading="enviandoAgendamento"
                    @click="confirmarAgendamento" 
                />
            </div>
        </div>
    </Dialog>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { isPast, isToday, set as setDate, format } from 'date-fns'
import { useToast } from 'primevue/usetoast'
import axios from 'axios'
import Select from 'primevue/select'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import AutoComplete from 'primevue/autocomplete'
import InputText from 'primevue/inputtext'
import DatePicker from 'primevue/datepicker'
import { formatarMoeda } from '@/Utils/formatters'

const props = defineProps({
    servicos: { type: Array, required: true },
    horariosFuncionamento: { type: Array, required: true },
    bloqueios: { type: Array, required: true },
})

const visible = defineModel('visible', { type: Boolean, default: false })
const emit = defineEmits(['success'])
const toast = useToast()

const step = ref(1)

// -- Step 1: Cliente --
const termoBuscaCliente = ref('')
const clientesEncontrados = ref([])
const clienteSelecionado = ref(null)
const mostrarFormPreCadastro = ref(false)

const formPreCadastro = ref({
    nome_completo: '',
    telefone: '',
    data_nascimento: null
})
const errosPreCadastro = ref({})
const salvandoPreCadastro = ref(false)

async function buscarCliente(event) {
    if (!event.query.trim()) {
        clientesEncontrados.value = []
        return
    }
    try {
        const { data } = await axios.get('/profissional/agendamentos/buscar-cliente', { params: { q: event.query } })
        clientesEncontrados.value = data
    } catch (e) {
        console.error('Erro ao buscar clientes', e)
    }
}

function selecionarCliente(event) {
    clienteSelecionado.value = event.value
}

function limparCliente() {
    clienteSelecionado.value = null
    termoBuscaCliente.value = ''
}

async function salvarPreCadastro() {
    errosPreCadastro.value = {}
    salvandoPreCadastro.value = true
    try {
        const dataNascStr = formPreCadastro.value.data_nascimento 
            ? format(formPreCadastro.value.data_nascimento, 'yyyy-MM-dd') 
            : null

        const { data } = await axios.post('/profissional/agendamentos/pre-cadastro', {
            ...formPreCadastro.value,
            data_nascimento: dataNascStr
        })
        
        toast.add({ severity: 'success', summary: 'Cliente Cadastrado!', life: 3000 })
        clienteSelecionado.value = data
        mostrarFormPreCadastro.value = false
        step.value = 2
    } catch (e) {
        if (e.response?.status === 422) {
            errosPreCadastro.value = e.response.data.errors
        } else {
            toast.add({ severity: 'error', summary: 'Erro', detail: 'Erro ao cadastrar.', life: 4000 })
        }
    } finally {
        salvandoPreCadastro.value = false
    }
}


// -- Step 2: Serviço --
const servicoSelecionado = ref(null)

function selecionarServico(sp) {
    servicoSelecionado.value = sp
}


// -- Step 3: Data/Hora (Reaproveitado a lógica do ServicoItem.vue) --
const dataSelecionada = ref(null)
const horaSelecionada = ref(null)
const enviandoAgendamento = ref(false)
const hoje = new Date()

watch(dataSelecionada, () => { horaSelecionada.value = null })

function gerarSlots(horaInicio, horaFim, duracaoMinutos) {
    const slots = []
    const [hI, mI] = horaInicio.slice(0, 5).split(':').map(Number)
    const [hF, mF] = horaFim.slice(0, 5).split(':').map(Number)

    let cur = hI * 60 + mI
    const fim = hF * 60 + mF - duracaoMinutos

    while (cur <= fim) {
        slots.push(`${String(Math.floor(cur / 60)).padStart(2, '0')}:${String(cur % 60).padStart(2, '0')}`)
        cur += 30 // hardcoded step = 30min (pode ser iterado no futuro)
    }
    return slots
}

const horarioDia = computed(() => {
    if (!dataSelecionada.value) return null
    const dia = dataSelecionada.value.getDay()
    return props.horariosFuncionamento.find(h => h.dia_semana === dia) ?? null
})

const diaHabilitado = computed(() => !!horarioDia.value)

const horariosDisponiveis = computed(() => {
    if (!dataSelecionada.value || !horarioDia.value || !servicoSelecionado.value) return []

    const duracao  = servicoSelecionado.value.tempo_execucao_minutos
    const diaSem   = dataSelecionada.value.getDay()
    const slots    = gerarSlots(horarioDia.value.hora_inicio, horarioDia.value.hora_fim, duracao)

    return slots.filter(hora => {
        const [h, m] = hora.split(':').map(Number)
        const slotInicioMin = h * 60 + m
        const slotFimMin    = slotInicioMin + duracao

        if (isToday(dataSelecionada.value)) {
            const slotDate = setDate(new Date(), { hours: h, minutes: m, seconds: 0 })
            if (isPast(slotDate)) return false
        }

        for (const b of props.bloqueios) {
            if (!b.recorrente) {
                if (!b.data_inicio || !b.data_fim) continue
                const bInicio = new Date(b.data_inicio)
                const bFim    = new Date(b.data_fim)
                const slotInicio = setDate(new Date(dataSelecionada.value), { hours: h, minutes: m, seconds: 0 })
                const slotFim    = new Date(slotInicio.getTime() + duracao * 60_000)

                if (!(slotFim <= bInicio || slotInicio >= bFim)) return false
            } else {
                if (b.dias_semana !== null && !b.dias_semana.includes(diaSem)) continue
                if (!b.hora_inicio || !b.hora_fim) continue
                const [bhi, bhm] = b.hora_inicio.slice(0, 5).split(':').map(Number)
                const [bhfi, bhfm] = b.hora_fim.slice(0, 5).split(':').map(Number)
                const bInicioMin = bhi * 60 + bhm
                const bFimMin    = bhfi * 60 + bhfm

                if (!(slotFimMin <= bInicioMin || slotInicioMin >= bFimMin)) return false
            }
        }
        return true
    })
})

const datasBlockeadas = computed(() => {
    const datas = []
    const diasSemFuncionamento = [0, 1, 2, 3, 4, 5, 6].filter(
        d => !props.horariosFuncionamento.some(h => h.dia_semana === d)
    )

    for (let i = 0; i <= 90; i++) {
        const d = new Date(hoje)
        d.setDate(d.getDate() + i)
        if (diasSemFuncionamento.includes(d.getDay())) {
            datas.push(new Date(d))
        }
    }

    props.bloqueios
        .filter(b => !b.recorrente && b.data_inicio && b.data_fim)
        .forEach(b => {
            const ini = new Date(b.data_inicio)
            const fim = new Date(b.data_fim)
            const iniH = ini.getHours() * 60 + ini.getMinutes()
            const fimH = fim.getHours() * 60 + fim.getMinutes()
            if (iniH === 0 && fimH >= 23 * 60 + 59) {
                for (let d = new Date(ini); d <= fim; d.setDate(d.getDate() + 1)) {
                    datas.push(new Date(d))
                }
            }
        })

    return datas
})

const dataHoraCompleta = computed(() => {
    if (!dataSelecionada.value || !horaSelecionada.value) return null
    const [h, m] = horaSelecionada.value.split(':').map(Number)
    return setDate(new Date(dataSelecionada.value), { hours: h, minutes: m, seconds: 0 })
})

function confirmarAgendamento() {
    if (!dataHoraCompleta.value || !clienteSelecionado.value || !servicoSelecionado.value) return
    
    enviandoAgendamento.value = true
    router.post('/profissional/agendamentos', {
        cliente_id:             clienteSelecionado.value.id,
        servico_id:             servicoSelecionado.value.servico_id,
        data_hora_inicio:       format(dataHoraCompleta.value, 'yyyy-MM-dd HH:mm:ss'),
        preco:                  servicoSelecionado.value.preco,
        tempo_execucao_minutos: servicoSelecionado.value.tempo_execucao_minutos,
        nome_servico:           servicoSelecionado.value.servico.nome,
    }, {
        onSuccess: () => {
            toast.add({ severity: 'success', summary: 'Agendamento Confirmado!', life: 4000 })
            visible.value = false
            emit('success')
            resetForm()
        },
        onError: (errors) => {
            const msg = Object.values(errors).flat().join(', ') || 'Erro ao agendar.'
            toast.add({ severity: 'error', summary: 'Erro', detail: msg, life: 5000 })
        },
        onFinish: () => enviandoAgendamento.value = false
    })
}

function resetForm() {
    step.value = 1
    termoBuscaCliente.value = ''
    clienteSelecionado.value = null
    mostrarFormPreCadastro.value = false
    servicoSelecionado.value = null
    dataSelecionada.value = null
    horaSelecionada.value = null
    formPreCadastro.value = { nome_completo: '', telefone: '', data_nascimento: null }
}
</script>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-out forwards;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
