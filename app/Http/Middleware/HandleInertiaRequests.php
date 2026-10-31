<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Setting;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => method_exists($user, 'getRoleNames') ? $user->getRoleNames() : [],
                    'permissions' => method_exists($user, 'getAllPermissions') ? $user->getAllPermissions()->pluck('name') : [],
                ] : null,
            ],
            'branding' => [
                'name' => Setting::get('institution_name') ?? config('app.name', 'SGA'),
                'logo' => Setting::get('institution_logo') ?? '/branding/logo_1787814439.png',
                'primary_color' => Setting::get('brand_primary_color') ?? '#bb3902',
                'secondary_color' => Setting::get('brand_secondary_color') ?? '#1c1917',
                'accent_color' => Setting::get('brand_accent_color') ?? '#ea580c',
                'neutral_color' => Setting::get('brand_neutral_color') ?? '#78716c',
                'palette' => [
                    'primary' => Setting::get('brand_primary_color') ?? '#bb3902',
                    'secondary' => Setting::get('brand_secondary_color') ?? '#1c1917',
                    'accent' => Setting::get('brand_accent_color') ?? '#ea580c',
                    'neutral' => Setting::get('brand_neutral_color') ?? '#78716c',
                ],
                'navbar_type' => Setting::get('navbar_type') ?? 'solid',
                'navbar_gradient_start' => Setting::get('navbar_gradient_start') ?? '#bb3902',
                'navbar_gradient_end' => Setting::get('navbar_gradient_end') ?? '#1a0a53',
                'navbar_gradient_direction' => Setting::get('navbar_gradient_direction') ?? 'to bottom right',
                'login_banner' => Setting::get('login_banner') ?? '/branding/login_banner_1787811955.jpg',
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'message' => fn () => $request->session()->get('message'),
                'status' => fn () => $request->session()->get('status'),
            ],
            'notifications' => fn () => $user ? $user->notifications()->take(30)->get()->map(function ($n) {
                $data = $n->data ?? [];
                $tone = match ($data['type'] ?? 'info') {
                    'success' => 'success',
                    'warning' => 'warning',
                    'error', 'danger' => 'warning',
                    default => 'info',
                };
                return [
                    'id' => (string) $n->id,
                    'title' => $data['title'] ?? 'Notificación del Sistema',
                    'description' => $data['message'] ?? ($data['description'] ?? ''),
                    'time' => $n->created_at ? $n->created_at->diffForHumans() : '',
                    'read' => !is_null($n->read_at),
                    'tone' => $tone,
                    'url' => $data['url'] ?? null,
                    'actor' => isset($data['actor']) ? $data['actor'] : null,
                ];
            }) : [],
            'unreadNotificationsCount' => fn () => $user ? $user->unreadNotifications()->count() : 0,
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
