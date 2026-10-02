/**
 * Paletas do tema Aura (PrimeVue) disponíveis como cor primária do estabelecimento.
 * Manter em sincronia com Estabelecimento::CORES_PRIMARIAS (app/Models/Estabelecimento.php).
 *
 * Ficam de fora: red (confunde com severity "danger"), neutros (slate, zinc, gray,
 * stone, neutral — somem sobre a superfície zinc) e yellow/lime (brilho excessivo no dark).
 */
export const COR_PADRAO = 'emerald'

export const CORES_TEMA = [
    { valor: 'emerald', label: 'Esmeralda' },
    { valor: 'green',   label: 'Verde' },
    { valor: 'teal',    label: 'Turquesa' },
    { valor: 'cyan',    label: 'Ciano' },
    { valor: 'sky',     label: 'Céu' },
    { valor: 'blue',    label: 'Azul' },
    { valor: 'indigo',  label: 'Índigo' },
    { valor: 'violet',  label: 'Violeta' },
    { valor: 'purple',  label: 'Roxo' },
    { valor: 'fuchsia', label: 'Fúcsia' },
    { valor: 'pink',    label: 'Rosa' },
    { valor: 'rose',    label: 'Rosê' },
    { valor: 'orange',  label: 'Laranja' },
    { valor: 'amber',   label: 'Âmbar' },
]
