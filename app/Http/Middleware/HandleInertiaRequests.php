<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Setting;
use App\Support\BrandingDefaults;
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
            'csrf_token' => fn () => csrf_token(),
            'admissions_open' => fn () => in_array((string) Setting::get('admissions_open', '0'), ['1', 'true'], true),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'roles' => method_exists($user, 'getRoleNames') ? $user->getRoleNames() : [],
                    'permissions' => method_exists($user, 'getAllPermissions') ? $user->getAllPermissions()->pluck('name') : [],
                ] : null,
            ],
            'branding' => BrandingDefaults::fromDatabase(),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'message' => fn () => $request->session()->get('message'),
                'status' => fn () => $request->session()->get('status'),
            ],
            'notifications' => fn () => $user ? $user->notifications()->take(30)->get()->map(function ($n) use ($user) {
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
                    'url' => $this->normalizeNotificationUrl($data['url'] ?? null, $user),
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

    /**
     * Normalize legacy or full-domain notification URLs to valid relative paths.
     */
    private function normalizeNotificationUrl(?string $rawUrl, $user): ?string
    {
        if (empty($rawUrl)) {
            return null;
        }

        $url = trim($rawUrl);

        // Convert absolute URLs (e.g. https://atest.gridbase.com.do/...) to relative path + query
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            $path = parse_url($url, PHP_URL_PATH) ?: '/';
            $query = parse_url($url, PHP_URL_QUERY);
            $url = $path . ($query ? '?' . $query : '');
        }

        if ($url === '/portal') {
            if ($user && method_exists($user, 'hasRole')) {
                if ($user->hasRole('Estudiante')) {
                    return '/student/dashboard';
                }
                if ($user->hasRole('Solicitante')) {
                    return '/portal-aspirante';
                }
            }
            return '/dashboard';
        }

        if ($url === '/applicant/portal') {
            return '/portal-aspirante';
        }

        // Formato legado invertido (/admin/students/{id}/profile) → ruta real /admin/students/profile/{id}
        if (preg_match('#^/admin/students/(\d+)/profile/?$#', $url, $m)) {
            return "/admin/students/profile/{$m[1]}";
        }

        return $url;
    }
}
