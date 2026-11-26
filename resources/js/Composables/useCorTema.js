import { watch, onUnmounted, toValue } from 'vue'
import { updatePrimaryPalette } from '@primeuix/themes'
import { CORES_TEMA, COR_PADRAO } from '@/Constants/coresTema'

const TONS = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950]
const CORES_VALIDAS = CORES_TEMA.map(c => c.valor)

let corAtual = COR_PADRAO

/**
 * Troca a paleta primária do Aura globalmente (componentes PrimeVue,
 * var(--p-primary-*) e classes Tailwind primary-*).
 */
export function aplicarCorTema(nome) {
    const cor = CORES_VALIDAS.includes(nome) ? nome : COR_PADRAO
    if (cor === corAtual) return

    updatePrimaryPalette(Object.fromEntries(TONS.map(tom => [tom, `{${cor}.${tom}}`])))
    corAtual = cor
}

/**
 * Aplica a cor enquanto o componente estiver montado e restaura ao sair.
 * Necessário porque os layouts do Inertia são persistentes: sem restaurar,
 * a cor de um estabelecimento vazaria para as próximas páginas.
 *
 * @param {import('vue').MaybeRefOrGetter<string|null>} cor
 * @param {{ aoSair?: () => string|null }} opcoes
 */
export function useCorTema(cor, { aoSair = () => COR_PADRAO } = {}) {
    // Aplicado já no setup (antes do render) para evitar flash da cor padrão
    aplicarCorTema(toValue(cor))

    watch(() => toValue(cor), aplicarCorTema)

    onUnmounted(() => aplicarCorTema(aoSair()))
}
