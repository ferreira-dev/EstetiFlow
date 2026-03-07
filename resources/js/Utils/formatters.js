import { format, formatDistanceToNow, isToday, isTomorrow, isFuture, isPast } from 'date-fns'
import { ptBR } from 'date-fns/locale'

/**
 * Formata valor monetário em BRL
 * @param {number} valor
 * @returns {string} Ex: "R$ 45,00"
 */
export function formatarMoeda(valor) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(Number(valor))
}

/**
 * Formata data completa
 * @param {string|Date} data
 * @returns {string} Ex: "15 de fevereiro de 2026"
 */
export function formatarData(data) {
    return format(new Date(data), "d 'de' MMMM 'de' yyyy", { locale: ptBR })
}

/**
 * Formata data curta
 * @param {string|Date} data
 * @returns {string} Ex: "15/02/2026"
 */
export function formatarDataCurta(data) {
    return format(new Date(data), 'dd/MM/yyyy', { locale: ptBR })
}

/**
 * Formata horário
 * @param {string|Date} data
 * @returns {string} Ex: "14:30"
 */
export function formatarHora(data) {
    return format(new Date(data), 'HH:mm', { locale: ptBR })
}

/**
 * Retorna mês capitalizado
 * @param {string|Date} data
 * @returns {string} Ex: "Fevereiro"
 */
export function formatarMes(data) {
    const mes = format(new Date(data), 'MMMM', { locale: ptBR })
    return mes.charAt(0).toUpperCase() + mes.slice(1)
}

/**
 * Retorna dia do mês
 * @param {string|Date} data
 * @returns {string} Ex: "15"
 */
export function formatarDia(data) {
    return format(new Date(data), 'dd', { locale: ptBR })
}

/**
 * Retorna "há X tempo"
 * @param {string|Date} data
 * @returns {string} Ex: "há 2 dias"
 */
export function formatarTempoRelativo(data) {
    return formatDistanceToNow(new Date(data), { addSuffix: true, locale: ptBR })
}

/**
 * Formata número de telefone
 * @param {string} telefone
 * @returns {string} Ex: "(14) 99667-4489"
 */
export function formatarTelefone(telefone) {
    const numeros = telefone.replace(/\D/g, '')
    if (numeros.length === 11) {
        return `(${numeros.slice(0, 2)}) ${numeros.slice(2, 7)}-${numeros.slice(7)}`
    }
    if (numeros.length === 10) {
        return `(${numeros.slice(0, 2)}) ${numeros.slice(2, 6)}-${numeros.slice(6)}`
    }
    return telefone
}

/**
 * Retorna saudação baseada na hora do dia
 * @returns {string} Ex: "Boa tarde"
 */
export function saudacao() {
    const hora = new Date().getHours()
    if (hora < 12) return 'Bom dia'
    if (hora < 18) return 'Boa tarde'
    return 'Boa noite'
}

// Reexporta funções de date-fns usadas nos componentes
export { isToday, isTomorrow, isFuture, isPast }
