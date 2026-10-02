/** @type {import('tailwindcss').Config} */

// Ponte Tailwind ↔ PrimeVue: primary-* segue a paleta primária ativa do Aura
// (inclusive a cor escolhida pelo estabelecimento), com suporte a opacidade (/20).
const tonsPrimary = Object.fromEntries(
    [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950].map(tom => [
        tom,
        `color-mix(in srgb, var(--p-primary-${tom}) calc(<alpha-value> * 100%), transparent)`,
    ])
)

export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            colors: {
                primary: tonsPrimary,
            },
        },
    },
    plugins: [],
}
