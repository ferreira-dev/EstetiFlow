/**
 * Categorias de serviço genéricas para serviços estéticos.
 * No futuro, essas categorias virão dinâmicas do banco (servicos.categoria).
 * Por hora, servem como fallback/seed.
 */
export const CATEGORIAS_SERVICO = [
    {
        id: 'cabelo',
        titulo: 'Cabelo',
        icone: 'pi pi-sparkles',
        descricao: 'Cortes, penteados e tratamentos capilares'
    },
    {
        id: 'barba',
        titulo: 'Barba',
        icone: 'pi pi-sparkles',
        descricao: 'Aparar, modelar e hidratar barba'
    },
    {
        id: 'manicure',
        titulo: 'Manicure',
        icone: 'pi pi-sparkles',
        descricao: 'Unhas das mãos, esmaltação e nail art'
    },
    {
        id: 'pedicure',
        titulo: 'Pedicure',
        icone: 'pi pi-sparkles',
        descricao: 'Unhas dos pés, hidratação e cuidados'
    },
    {
        id: 'sobrancelha',
        titulo: 'Sobrancelha',
        icone: 'pi pi-sparkles',
        descricao: 'Design, micropigmentação e manutenção'
    },
    {
        id: 'maquiagem',
        titulo: 'Maquiagem',
        icone: 'pi pi-sparkles',
        descricao: 'Produção para eventos, dia a dia e editorial'
    },
    {
        id: 'depilacao',
        titulo: 'Depilação',
        icone: 'pi pi-sparkles',
        descricao: 'Cera, laser e métodos diversos'
    },
    {
        id: 'estetica',
        titulo: 'Estética',
        icone: 'pi pi-sparkles',
        descricao: 'Limpeza de pele, peeling e tratamentos faciais'
    },
    {
        id: 'massagem',
        titulo: 'Massagem',
        icone: 'pi pi-sparkles',
        descricao: 'Relaxante, modeladora e terapêutica'
    },
    {
        id: 'hidratacao',
        titulo: 'Hidratação',
        icone: 'pi pi-sparkles',
        descricao: 'Tratamentos de hidratação capilar e corporal'
    }
]

/**
 * Horários disponíveis para agendamento (30min cada slot)
 */
export const HORARIOS_DISPONIVEIS = [
    '08:00', '08:30', '09:00', '09:30',
    '10:00', '10:30', '11:00', '11:30',
    '12:00', '12:30', '13:00', '13:30',
    '14:00', '14:30', '15:00', '15:30',
    '16:00', '16:30', '17:00', '17:30',
    '18:00'
]
