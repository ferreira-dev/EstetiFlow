/**
 * Comodidades que o dono do estabelecimento pode marcar no painel.
 * Manter em sincronia com Estabelecimento::CARACTERISTICAS (PHP), na mesma ordem.
 */
export const CARACTERISTICAS = [
    { valor: 'wifi',           label: 'Wi-Fi gratuito',         icone: 'pi pi-wifi' },
    { valor: 'estacionamento', label: 'Estacionamento',         icone: 'pi pi-car' },
    { valor: 'cartao',         label: 'Aceita cartão',          icone: 'pi pi-credit-card' },
    { valor: 'pix',            label: 'Aceita Pix',             icone: 'pi pi-qrcode' },
    { valor: 'climatizado',    label: 'Ambiente climatizado',   icone: 'pi pi-sun' },
    { valor: 'acessivel',      label: 'Acessível',              icone: 'pi pi-check-circle' },
    { valor: 'domicilio',      label: 'Atende a domicílio',     icone: 'pi pi-map-marker' },
    { valor: 'kids',           label: 'Atende crianças',        icone: 'pi pi-face-smile' },
    { valor: 'cortesia',       label: 'Café ou bebida cortesia', icone: 'pi pi-gift' },
]

/** Retorna as comodidades conhecidas a partir das chaves salvas (ignora valores desconhecidos). */
export function caracteristicasSelecionadas(chaves = []) {
    return CARACTERISTICAS.filter(c => (chaves ?? []).includes(c.valor))
}
