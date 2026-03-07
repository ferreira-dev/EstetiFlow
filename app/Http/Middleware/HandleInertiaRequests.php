<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * Template raiz que é carregado no primeiro load de página.
     */
    protected $rootView = 'app';

    /**
     * Determina a versão dos assets (para cache-busting automático).
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Dados compartilhados com todas as pages — disponíveis via usePage().props
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id'             => $request->user()->id,
                    'nome_completo'  => $request->user()->nome_completo,
                    'email'          => $request->user()->email,
                    'tipo'           => $request->user()->tipo,
                ] : null,
                'roles'       => $request->user()?->getRoleNames(),
                'permissions' => $request->user()?->getAllPermissions()->pluck('name'),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}
