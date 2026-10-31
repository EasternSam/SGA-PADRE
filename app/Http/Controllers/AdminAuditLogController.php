<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class AdminAuditLogController extends Controller
{
    /**
     * Display system audit log timeline.
     */
    public function index(Request $request): Response
    {
        $search = $request->query('search');
        $event = $request->query('event');

        $query = Activity::with('causer')->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('log_name', 'like', "%{$search}%")
                  ->orWhere('subject_type', 'like', "%{$search}%");
            });
        }

        if ($event && $event !== 'all') {
            $query->where('event', $event);
        }

        $logs = $query->take(100)->get()->map(fn($log) => [
            'id' => $log->id,
            'log_name' => $log->log_name ?? 'default',
            'description' => $log->description,
            'event' => $log->event ?? 'action',
            'causer_name' => $log->causer?->name ?? 'Sistema Automatizado',
            'causer_email' => $log->causer?->email ?? 'system@sga.edu.do',
            'subject_type' => class_basename($log->subject_type ?? 'Registro'),
            'subject_id' => $log->subject_id,
            'properties' => $log->properties ? $log->properties->toArray() : null,
            'created_at' => $log->created_at?->format('d/m/Y H:i:s'),
        ]);

        $events = Activity::distinct()->whereNotNull('event')->pluck('event')->toArray();
        if (empty($events)) {
            $events = ['created', 'updated', 'deleted', 'login'];
        }

        return Inertia::render('Admin/AuditLogs/Index', [
            'logs' => $logs,
            'events' => $events,
            'filters' => [
                'search' => $search,
                'event' => $event ?? 'all',
            ],
        ]);
    }
}
