<?php

namespace App\Services\Dashboard\Widgets;

use App\Models\User;
use App\Services\Dashboard\Contracts\WidgetDataProvider;
use App\Services\Dashboard\Support\DashboardLinks;

/**
 * Atajos a las tareas frecuentes. Solo incluye destinos que el usuario puede abrir.
 */
final class QuickActionsWidget implements WidgetDataProvider
{
    /** @var list<array{link: string, title: string, tag: string, icon: string}> En orden de prioridad. */
    private const ACTIONS = [
        ['link' => 'students', 'title' => 'Matricular estudiante', 'tag' => 'Registro', 'icon' => 'user-plus'],
        ['link' => 'admissions', 'title' => 'Revisar admisiones', 'tag' => 'Admisiones', 'icon' => 'clipboard-check'],
        ['link' => 'pos', 'title' => 'Terminal de cobro', 'tag' => 'Caja', 'icon' => 'credit-card'],
        ['link' => 'courses', 'title' => 'Programar sección', 'tag' => 'Académico', 'icon' => 'calendar-plus'],
        ['link' => 'finance', 'title' => 'Panel financiero', 'tag' => 'Finanzas', 'icon' => 'wallet'],
        ['link' => 'expenses', 'title' => 'Registrar gasto', 'tag' => 'Finanzas', 'icon' => 'receipt'],
        ['link' => 'ledger', 'title' => 'Libro mayor', 'tag' => 'Contabilidad', 'icon' => 'book-open'],
        ['link' => 'classrooms', 'title' => 'Espacios y aulas', 'tag' => 'Campus', 'icon' => 'building'],
        ['link' => 'reports', 'title' => 'Centro de reportes', 'tag' => 'Reportes', 'icon' => 'file-text'],
    ];

    private const LIMIT = 6;

    public function data(User $user): array
    {
        $items = [];

        foreach (self::ACTIONS as $action) {
            $href = DashboardLinks::url($user, $action['link']);
            if ($href !== null) {
                $items[] = ['key' => $action['link'], 'title' => $action['title'], 'tag' => $action['tag'], 'icon' => $action['icon'], 'href' => $href];
            }
            if (count($items) === self::LIMIT) {
                break;
            }
        }

        return ['items' => $items];
    }
}
