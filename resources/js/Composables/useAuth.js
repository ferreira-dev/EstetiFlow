import { computed } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useAuth() {
    const page = usePage()

    const user        = computed(() => page.props.auth?.user)
    const permissions = computed(() => page.props.auth?.permissions ?? [])
    const roles       = computed(() => page.props.auth?.roles ?? [])

    const can = (permission) => permissions.value.includes(permission)
    const is  = (role)       => roles.value.includes(role)

    const isAdmin        = computed(() => is('admin'))
    const isProfissional = computed(() => is('profissional'))
    const isCliente      = computed(() => is('cliente'))

    return {
        user,
        permissions,
        roles,
        can,
        is,
        isAdmin,
        isProfissional,
        isCliente,
    }
}
