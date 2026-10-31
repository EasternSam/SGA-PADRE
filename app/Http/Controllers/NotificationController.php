<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Alterna o marca una notificación como leída / no leída.
     */
    public function markAsRead(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $read = $request->input('read', true);
            if ($read) {
                $notification->markAsRead();
            } else {
                $notification->markAsUnread();
            }
        }

        if ($request->wantsJson() || $request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ]);
        }

        return back();
    }

    /**
     * Marca todas las notificaciones pendientes como leídas.
     */
    public function markAllAsRead(Request $request): JsonResponse|RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        if ($request->wantsJson() || $request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
            ]);
        }

        return back();
    }

    /**
     * Elimina una notificación específica.
     */
    public function destroy(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $notification = $request->user()->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->delete();
        }

        if ($request->wantsJson() || $request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ]);
        }

        return back();
    }

    /**
     * Elimina todas las notificaciones ya leídas del usuario.
     */
    public function clearRead(Request $request): JsonResponse|RedirectResponse
    {
        $request->user()->readNotifications()->delete();

        if ($request->wantsJson() || $request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => $request->user()->unreadNotifications()->count(),
            ]);
        }

        return back();
    }
}
