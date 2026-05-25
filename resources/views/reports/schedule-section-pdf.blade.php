@extends('reports.layouts.pdf')
@section('title', 'Horario Escolar')
@section('subtitle', ($section->gradeLevel?->name ?? '') . ' ' . $section->name . ' | ' . ($activeYear?->name ?? ''))

@section('styles')
    .schedule-cell { height: 60px; vertical-align: middle; }
    .schedule-cell .subject { font-weight: bold; font-size: 9pt; color: #1e3a8a; }
    .schedule-cell .teacher { font-size: 7pt; color: #6b7280; }
    .schedule-cell .room { font-size: 7pt; color: #9ca3af; }
    .break-row { background: #f3f4f6; }
    .break-row td { font-style: italic; color: #9ca3af; text-align: center; }
    .block-name { font-weight: bold; font-size: 9pt; }
    .block-time { font-size: 7pt; color: #6b7280; }
    th { background: #1e3a8a; color: white; padding: 8px; text-align: center; }
@endsection

@section('content')
    <table class="data-table" style="width: 100%;">
        <thead>
            <tr>
                <th style="width: 12%;">Hora</th>
                @foreach($dayLabels as $key => $label)
                    <th>{{ $label }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($grid as $row)
                <tr class="{{ $row['type'] !== 'class' ? 'break-row' : '' }}">
                    <td style="text-align: center; border-right: 2px solid #1e3a8a;">
                        <div class="block-name">{{ $row['name'] }}</div>
                        <div class="block-time">{{ $row['time_range'] }}</div>
                    </td>
                    @foreach($days as $day)
                        <td class="schedule-cell" style="text-align: center;">
                            @if($row['type'] === 'class')
                                @if($row['cells'][$day]['subject'])
                                    <div class="subject">{{ $row['cells'][$day]['subject'] }}</div>
                                    @if($row['cells'][$day]['teacher'])
                                        <div class="teacher">{{ $row['cells'][$day]['teacher'] }}</div>
                                    @endif
                                    @if($row['cells'][$day]['room'])
                                        <div class="room">📍 {{ $row['cells'][$day]['room'] }}</div>
                                    @endif
                                @else
                                    <span style="color: #d1d5db;">—</span>
                                @endif
                            @else
                                {{ \App\Models\TimeBlock::TYPES[$row['type']] ?? $row['type'] }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="width: 100%; margin-top: 25px;">
        <tr>
            <td style="text-align: center; width: 33%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 160px; padding-top: 5px; font-size: 8pt; color: #6b7280;">
                    Docente Titular
                </div>
            </td>
            <td style="text-align: center; width: 33%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 160px; padding-top: 5px; font-size: 8pt; color: #6b7280;">
                    Coordinador/a Académico
                </div>
            </td>
            <td style="text-align: center; width: 33%; padding-top: 25px;">
                <div style="border-top: 1px solid #374151; display: inline-block; min-width: 160px; padding-top: 5px; font-size: 8pt; color: #6b7280;">
                    Director/a
                </div>
            </td>
        </tr>
    </table>
@endsection
