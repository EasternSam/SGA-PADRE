import { Tv, X } from 'lucide-react';
import React, { useState } from 'react';
import { createPortal } from 'react-dom';
import { Head, useForm, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function ClassroomsIndex({ buildings = [], rawBuildings = [], stats = {} }) {
    // Modals
    const [buildingModalOpen, setBuildingModalOpen] = useState(false);
    const [classroomModalOpen, setClassroomModalOpen] = useState(false);
    const [reservationModalOpen, setReservationModalOpen] = useState(false);
    const [scheduleModalOpen, setScheduleModalOpen] = useState(false);

    // Selected items for modals
    const [editingClassroom, setEditingClassroom] = useState(null);
    const [targetClassroom, setTargetClassroom] = useState(null);
    const [scheduleData, setScheduleData] = useState(null);
    const [loadingSchedule, setLoadingSchedule] = useState(false);

    // Filter by building
    const [selectedBuildingFilter, setSelectedBuildingFilter] = useState('all');

    // Forms
    const buildingForm = useForm({
        name: '',
    });

    const classroomForm = useForm({
        name: '',
        building_id: rawBuildings[0]?.id || '',
        capacity: 30,
        pc_count: 0,
        type: 'Aula',
        has_tv: false,
    });

    const reservationForm = useForm({
        classroom_id: '',
        title: '',
        description: '',
        reserved_date: new Date().toISOString().split('T')[0],
        start_time: '08:00',
        end_time: '10:00',
    });

    // Handlers
    const openNewBuilding = () => {
        buildingForm.reset();
        buildingForm.clearErrors();
        setBuildingModalOpen(true);
    };

    const handleCreateBuilding = (e) => {
        e.preventDefault();
        buildingForm.post('/admin/classrooms/buildings', {
            onSuccess: () => {
                setBuildingModalOpen(false);
                buildingForm.reset();
            },
        });
    };

    const openNewClassroom = () => {
        setEditingClassroom(null);
        classroomForm.reset({
            name: '',
            building_id: rawBuildings[0]?.id || '',
            capacity: 30,
            pc_count: 0,
            type: 'Aula',
            has_tv: false,
        });
        classroomForm.clearErrors();
        setClassroomModalOpen(true);
    };

    const openEditClassroom = (classroom) => {
        setEditingClassroom(classroom);
        classroomForm.setData({
            name: classroom.name,
            building_id: classroom.building_id,
            capacity: classroom.capacity,
            pc_count: classroom.pc_count,
            type: classroom.type,
            has_tv: classroom.has_tv,
        });
        classroomForm.clearErrors();
        setClassroomModalOpen(true);
    };

    const handleSaveClassroom = (e) => {
        e.preventDefault();
        if (editingClassroom) {
            classroomForm.put(`/admin/classrooms/${editingClassroom.id}`, {
                onSuccess: () => {
                    setClassroomModalOpen(false);
                    setEditingClassroom(null);
                },
            });
        } else {
            classroomForm.post('/admin/classrooms', {
                onSuccess: () => {
                    setClassroomModalOpen(false);
                    classroomForm.reset();
                },
            });
        }
    };

    const handleDeleteClassroom = (classroom) => {
        if (confirm(`¿Estás seguro de que deseas eliminar el aula "${classroom.name}"?`)) {
            router.delete(`/admin/classrooms/${classroom.id}`);
        }
    };

    const openReservation = (classroom) => {
        setTargetClassroom(classroom);
        reservationForm.reset({
            classroom_id: classroom.id,
            title: '',
            description: '',
            reserved_date: new Date().toISOString().split('T')[0],
            start_time: '08:00',
            end_time: '10:00',
        });
        reservationForm.clearErrors();
        setReservationModalOpen(true);
    };

    const handleCreateReservation = (e) => {
        e.preventDefault();
        reservationForm.post('/admin/classrooms/reservations', {
            onSuccess: () => {
                setReservationModalOpen(false);
                reservationForm.reset();
            },
        });
    };

    const handleDeleteReservation = (reservationId) => {
        if (confirm('¿Deseas cancelar esta reserva?')) {
            router.delete(`/admin/classrooms/reservations/${reservationId}`, {
                onSuccess: () => {
                    if (targetClassroom) {
                        loadScheduleModal(targetClassroom);
                    }
                },
            });
        }
    };

    const loadScheduleModal = async (classroom) => {
        setTargetClassroom(classroom);
        setScheduleModalOpen(true);
        setLoadingSchedule(true);
        try {
            const res = await fetch(`/admin/classrooms/${classroom.id}/schedule`);
            const data = await res.json();
            setScheduleData(data);
        } catch (err) {
            console.error(err);
        } finally {
            setLoadingSchedule(false);
        }
    };

    const filteredBuildings = selectedBuildingFilter === 'all'
        ? buildings
        : buildings.filter(b => b.id.toString() === selectedBuildingFilter);

    return (
        <AuthenticatedLayout>
            <Head title="Aulas y Espacios Físicos - SGA" />

            <div className="space-y-6">
                {/* Header */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2.5">
                            <span className="p-2 rounded-xl bg-brand-primary/10 dark:bg-brand-primary/30 text-brand-primary dark:text-blue-400">
                                <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </span>
                            Gestión de Aulas y Espacios Físicos
                        </h1>
                        
                    </div>

                    <div className="flex flex-wrap items-center gap-2.5">
                        <button
                            type="button"
                            onClick={openNewBuilding}
                            className="inline-flex items-center gap-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#161622] px-4 py-2.5 text-sm font-semibold text-gray-700 dark:text-gray-200 shadow-sm hover:bg-gray-50 dark:hover:bg-gray-800 transition active:scale-[0.98]"
                        >
                            <svg className="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Nuevo Edificio
                        </button>
                        <button
                            type="button"
                            onClick={openNewClassroom}
                            className="inline-flex items-center gap-2 rounded-xl bg-brand-primary hover:bg-brand-dark px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition active:scale-[0.98]"
                        >
                            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Registrar Nueva Aula
                        </button>
                    </div>
                </div>

                {/* KPI Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Espacios</span>
                            <span className="p-2 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-brand-primary dark:text-blue-400">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            {stats.total_classrooms || 0}
                        </div>
                        <div className="text-xs text-gray-500 mt-1">
                            Distribuidos en {stats.total_buildings || 0} edificios
                        </div>
                    </div>

                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-400">Capacidad Total</span>
                            <span className="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            {stats.total_capacity || 0}
                        </div>
                        <div className="text-xs text-emerald-600 dark:text-emerald-400 mt-1">
                            Asientos para estudiantes
                        </div>
                    </div>

                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-400">Equipos & PCs</span>
                            <span className="p-2 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            {stats.total_pcs || 0}
                        </div>
                        <div className="text-xs text-purple-600 dark:text-purple-400 mt-1">
                            Estaciones en laboratorios
                        </div>
                    </div>

                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-gray-100 dark:border-gray-800 shadow-sm">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-semibold uppercase tracking-wider text-gray-400">Estado En Vivo</span>
                            <span className="p-2 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                        </div>
                        <div className="mt-2 flex items-baseline gap-2">
                            <span className="text-2xl font-bold text-gray-900 dark:text-white">
                                {stats.occupied_count || 0}
                            </span>
                            <span className="text-xs text-gray-500">ocupadas ahora</span>
                        </div>
                        <div className="text-xs text-emerald-600 dark:text-emerald-400 mt-1 flex items-center gap-1">
                            <span className="relative flex h-2 w-2">
                                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                <span className="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                            </span>
                            {(stats.total_classrooms || 0) - (stats.occupied_count || 0)} libres para reserva
                        </div>
                    </div>
                </div>

                {/* Filter Selector */}
                {rawBuildings.length > 0 && (
                    <div className="flex items-center gap-2 overflow-x-auto pb-1">
                        <button
                            type="button"
                            onClick={() => setSelectedBuildingFilter('all')}
                            className={`px-3.5 py-1.5 rounded-xl text-xs font-semibold transition ${
                                selectedBuildingFilter === 'all'
                                    ? 'bg-brand-primary text-white shadow-sm'
                                    : 'bg-white dark:bg-[#161622] text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-800 hover:bg-gray-50'
                            }`}
                        >
                            Todos los Edificios ({buildings.length})
                        </button>
                        {rawBuildings.map(b => (
                            <button
                                key={b.id}
                                type="button"
                                onClick={() => setSelectedBuildingFilter(b.id.toString())}
                                className={`px-3.5 py-1.5 rounded-xl text-xs font-semibold whitespace-nowrap transition ${
                                    selectedBuildingFilter === b.id.toString()
                                        ? 'bg-brand-primary text-white shadow-sm'
                                        : 'bg-white dark:bg-[#161622] text-gray-600 dark:text-gray-400 border border-gray-200 dark:border-gray-800 hover:bg-gray-50'
                                }`}
                            >
                                {b.name}
                            </button>
                        ))}
                    </div>
                )}

                {/* Buildings and Classrooms Content */}
                <div className="space-y-6">
                    {filteredBuildings.length === 0 ? (
                        <div className="rounded-2xl border-2 border-dashed border-gray-200 dark:border-gray-800 p-12 text-center bg-white dark:bg-[#161622]">
                            <svg className="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <h3 className="mt-3 text-base font-semibold text-gray-900 dark:text-white">No hay edificios registrados</h3>
                            <p className="mt-1 text-sm text-gray-500">Comienza registrando tu primer edificio y sus aulas.</p>
                            <div className="mt-4">
                                <button
                                    type="button"
                                    onClick={openNewBuilding}
                                    className="rounded-xl bg-brand-primary px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-dark"
                                >
                                    + Registrar Edificio
                                </button>
                            </div>
                        </div>
                    ) : (
                        filteredBuildings.map(building => (
                            <div key={building.id} className="bg-white dark:bg-[#161622] rounded-2xl p-6 border border-gray-100 dark:border-gray-800 shadow-sm space-y-4">
                                <div className="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3">
                                    <div className="flex items-center gap-3">
                                        <div className="p-2 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-brand-primary dark:text-blue-400">
                                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                            </svg>
                                        </div>
                                        <div>
                                            <h2 className="text-lg font-bold text-gray-900 dark:text-white">{building.name}</h2>
                                            <p className="text-xs text-gray-500">{building.classrooms.length} aulas y espacios registrados</p>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={openNewClassroom}
                                        className="text-xs font-semibold text-brand-primary dark:text-blue-400 hover:underline"
                                    >
                                        + Agregar aula aquí
                                    </button>
                                </div>

                                {building.classrooms.length === 0 ? (
                                    <div className="p-6 text-center text-sm text-gray-400 border border-dashed rounded-xl border-gray-200 dark:border-gray-800">
                                        No hay aulas registradas en este edificio aún.
                                    </div>
                                ) : (
                                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                        {building.classrooms.map(classroom => (
                                            <div
                                                key={classroom.id}
                                                className={`rounded-xl border p-4 transition-all duration-200 hover:shadow-md flex flex-col justify-between ${
                                                    classroom.is_occupied
                                                        ? 'bg-amber-50/40 dark:bg-amber-950/10 border-amber-200 dark:border-amber-800/40'
                                                        : 'bg-white dark:bg-[#1a1a2e] border-gray-200 dark:border-gray-700/60'
                                                }`}
                                            >
                                                <div>
                                                    {/* Header Card */}
                                                    <div className="flex items-start justify-between gap-2">
                                                        <div>
                                                            <h3 className="font-bold text-base text-gray-900 dark:text-white leading-tight">
                                                                {classroom.name}
                                                            </h3>
                                                            <div className="flex items-center gap-1.5 mt-1 flex-wrap">
                                                                <span className="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-100 text-brand-primary dark:bg-blue-900/40 dark:text-blue-300">
                                                                    {classroom.type}
                                                                </span>
                                                                {classroom.has_tv && (
                                                                    <span className="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-semibold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                                                        <Tv className="w-3 h-3 mr-1 inline" /> Smart TV
                                                                    </span>
                                                                )}
                                                            </div>
                                                        </div>

                                                        {/* Status Pill */}
                                                        {classroom.is_occupied ? (
                                                            <span className="inline-flex items-center gap-1.5 px-2 py-1 rounded-full text-[11px] font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-400 shrink-0">
                                                                <span className="relative flex h-2 w-2">
                                                                    <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                                                    <span className="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                                                                </span>
                                                                Ocupado
                                                            </span>
                                                        ) : (
                                                            <span className="inline-flex items-center gap-1 px-2 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-400 shrink-0">
                                                                <span className="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                                                Libre
                                                            </span>
                                                        )}
                                                    </div>

                                                    {/* Live Occupant Detail */}
                                                    <div className="mt-3 p-2 rounded-lg bg-gray-50 dark:bg-gray-800/60 text-xs">
                                                        <span className="text-gray-400 text-[10px] uppercase font-bold tracking-wider block">Estado actual:</span>
                                                        <p className={`font-semibold truncate mt-0.5 ${classroom.is_occupied ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400'}`}>
                                                            {classroom.occupant_label || 'Libre'}
                                                        </p>
                                                    </div>

                                                    {/* Metrics */}
                                                    <div className="mt-3 grid grid-cols-2 gap-2 text-xs text-gray-600 dark:text-gray-300">
                                                        <div className="flex items-center gap-1.5">
                                                            <svg className="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                                            </svg>
                                                            <span>Capacidad: <strong>{classroom.capacity}</strong></span>
                                                        </div>
                                                        <div className="flex items-center gap-1.5">
                                                            <svg className="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                            </svg>
                                                            <span>PCs: <strong>{classroom.pc_count}</strong></span>
                                                        </div>
                                                    </div>
                                                </div>

                                                {/* Actions */}
                                                <div className="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/60 flex items-center justify-between gap-1">
                                                    <button
                                                        type="button"
                                                        onClick={() => loadScheduleModal(classroom)}
                                                        className="px-2.5 py-1.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-brand-primary dark:text-blue-300 text-xs font-semibold hover:bg-blue-100 transition"
                                                    >
                                                        Horarios ({classroom.schedules_count})
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => openReservation(classroom)}
                                                        className="px-2.5 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 text-xs font-semibold hover:bg-emerald-100 transition"
                                                    >
                                                        Reservar
                                                    </button>
                                                    <div className="flex items-center gap-1">
                                                        <button
                                                            type="button"
                                                            onClick={() => openEditClassroom(classroom)}
                                                            className="p-1.5 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                                            title="Editar"
                                                        >
                                                            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                            </svg>
                                                        </button>
                                                        <button
                                                            type="button"
                                                            onClick={() => handleDeleteClassroom(classroom)}
                                                            className="p-1.5 text-red-400 hover:text-red-600"
                                                            title="Eliminar"
                                                        >
                                                            <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                )}
                            </div>
                        ))
                    )}
                </div>
            </div>

            {/* Modal: Nuevo Edificio */}
            {buildingModalOpen && (
                createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm animate-in fade-in duration-200" onClick={() => setBuildingModalOpen(false)}>
                    <div className="w-full max-w-md rounded-2xl bg-white dark:bg-[#161622] p-6 shadow-2xl border border-gray-100 dark:border-gray-800 my-auto max-h-[calc(100vh-2rem)] overflow-y-auto" onClick={(e) => e.stopPropagation()}>
                        <div className="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                            <h3 className="text-lg font-bold text-gray-900 dark:text-white">Registrar Nuevo Edificio</h3>
                            <button
                                type="button"
                                onClick={() => setBuildingModalOpen(false)}
                                className="text-gray-400 hover:text-gray-500 text-sm"
                            ><X className="w-4 h-4" /></button>
                        </div>
                        <form onSubmit={handleCreateBuilding} className="space-y-4 mt-4">
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                    Nombre del Edificio *
                                </label>
                                <input
                                    type="text"
                                    value={buildingForm.data.name}
                                    onChange={e => buildingForm.setData('name', e.target.value)}
                                    placeholder="Ej: Edificio Académico 1, Edificio Innovación"
                                    required
                                    className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                />
                                {buildingForm.errors.name && (
                                    <p className="text-xs text-red-500 mt-1">{buildingForm.errors.name}</p>
                                )}
                            </div>
                            <div className="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <button
                                    type="button"
                                    onClick={() => setBuildingModalOpen(false)}
                                    className="rounded-xl px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={buildingForm.processing}
                                    className="rounded-xl bg-brand-primary hover:bg-brand-dark px-4 py-2 text-sm font-semibold text-white shadow-sm disabled:opacity-50"
                                >
                                    {buildingForm.processing ? 'Guardando...' : 'Crear Edificio'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>, document.body)
            )}

            {/* Modal: Crear / Editar Aula */}
            {classroomModalOpen && (
                createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm animate-in fade-in duration-200" onClick={() => setClassroomModalOpen(false)}>
                    <div className="w-full max-w-lg rounded-2xl bg-white dark:bg-[#161622] p-6 shadow-2xl border border-gray-100 dark:border-gray-800 my-auto max-h-[calc(100vh-2rem)] overflow-y-auto" onClick={(e) => e.stopPropagation()}>
                        <div className="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                            <h3 className="text-lg font-bold text-gray-900 dark:text-white">
                                {editingClassroom ? `Editar Aula: ${editingClassroom.name}` : 'Registrar Nueva Aula'}
                            </h3>
                            <button
                                type="button"
                                onClick={() => setClassroomModalOpen(false)}
                                className="text-gray-400 hover:text-gray-500 text-sm"
                            ><X className="w-4 h-4" /></button>
                        </div>
                        <form onSubmit={handleSaveClassroom} className="space-y-4 mt-4">
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                    Edificio Asignado *
                                </label>
                                <select
                                    value={classroomForm.data.building_id}
                                    onChange={e => classroomForm.setData('building_id', e.target.value)}
                                    required
                                    className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                >
                                    <option value="">Seleccione un edificio...</option>
                                    {rawBuildings.map(b => (
                                        <option key={b.id} value={b.id}>{b.name}</option>
                                    ))}
                                </select>
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                        Nombre / Identificador *
                                    </label>
                                    <input
                                        type="text"
                                        value={classroomForm.data.name}
                                        onChange={e => classroomForm.setData('name', e.target.value)}
                                        placeholder="Ej: A-101, Lab Software 2"
                                        required
                                        className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                        Tipo de Espacio *
                                    </label>
                                    <select
                                        value={classroomForm.data.type}
                                        onChange={e => classroomForm.setData('type', e.target.value)}
                                        className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                    >
                                        <option value="Aula">Aula Regular</option>
                                        <option value="Laboratorio">Laboratorio Especializado</option>
                                        <option value="Auditorio">Auditorio / Sala Magistral</option>
                                    </select>
                                </div>
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                        Capacidad (Asientos) *
                                    </label>
                                    <input
                                        type="number"
                                        min="1"
                                        max="500"
                                        value={classroomForm.data.capacity}
                                        onChange={e => classroomForm.setData('capacity', e.target.value)}
                                        required
                                        className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                    />
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                        Cantidad de Computadoras
                                    </label>
                                    <input
                                        type="number"
                                        min="0"
                                        max="100"
                                        value={classroomForm.data.pc_count}
                                        onChange={e => classroomForm.setData('pc_count', e.target.value)}
                                        className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                    />
                                </div>
                            </div>

                            <div className="flex items-center gap-2 pt-2">
                                <input
                                    type="checkbox"
                                    id="has_tv"
                                    checked={classroomForm.data.has_tv}
                                    onChange={e => classroomForm.setData('has_tv', e.target.checked)}
                                    className="h-4 w-4 rounded border-gray-300 text-brand-primary focus:ring-brand-primary"
                                />
                                <label htmlFor="has_tv" className="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Equipado con Smart TV / Pantalla de Proyección interactiva
                                </label>
                            </div>

                            <div className="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <button
                                    type="button"
                                    onClick={() => setClassroomModalOpen(false)}
                                    className="rounded-xl px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={classroomForm.processing}
                                    className="rounded-xl bg-brand-primary hover:bg-brand-dark px-4 py-2 text-sm font-semibold text-white shadow-sm disabled:opacity-50"
                                >
                                    {classroomForm.processing ? 'Guardando...' : (editingClassroom ? 'Actualizar Aula' : 'Crear Aula')}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>, document.body)
            )}

            {/* Modal: Nueva Reserva de Espacio */}
            {reservationModalOpen && targetClassroom && (
                createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm animate-in fade-in duration-200" onClick={() => setReservationModalOpen(false)}>
                    <div className="w-full max-w-lg rounded-2xl bg-white dark:bg-[#161622] p-6 shadow-2xl border border-gray-100 dark:border-gray-800 my-auto max-h-[calc(100vh-2rem)] overflow-y-auto" onClick={(e) => e.stopPropagation()}>
                        <div className="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                            <div>
                                <h3 className="text-lg font-bold text-gray-900 dark:text-white">
                                    Reservar Espacio: {targetClassroom.name}
                                </h3>
                                <p className="text-xs text-gray-500 mt-0.5">
                                    {targetClassroom.building_name} • Capacidad {targetClassroom.capacity} personas
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setReservationModalOpen(false)}
                                className="text-gray-400 hover:text-gray-500 text-sm"
                            ><X className="w-4 h-4" /></button>
                        </div>
                        <form onSubmit={handleCreateReservation} className="space-y-4 mt-4">
                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                    Título / Motivo de la Reserva *
                                </label>
                                <input
                                    type="text"
                                    value={reservationForm.data.title}
                                    onChange={e => reservationForm.setData('title', e.target.value)}
                                    placeholder="Ej: Conferencia Robótica, Examen Extraordinario, Reunión Docente"
                                    required
                                    className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                />
                                {reservationForm.errors.title && (
                                    <p className="text-xs text-red-500 mt-1">{reservationForm.errors.title}</p>
                                )}
                            </div>

                            <div className="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                        Fecha *
                                    </label>
                                    <input
                                        type="date"
                                        value={reservationForm.data.reserved_date}
                                        onChange={e => reservationForm.setData('reserved_date', e.target.value)}
                                        required
                                        className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                    />
                                    {reservationForm.errors.reserved_date && (
                                        <p className="text-xs text-red-500 mt-1">{reservationForm.errors.reserved_date}</p>
                                    )}
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                        Hora Inicio *
                                    </label>
                                    <input
                                        type="time"
                                        value={reservationForm.data.start_time}
                                        onChange={e => reservationForm.setData('start_time', e.target.value)}
                                        required
                                        className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                    />
                                    {reservationForm.errors.start_time && (
                                        <p className="text-xs text-red-500 mt-1">{reservationForm.errors.start_time}</p>
                                    )}
                                </div>
                                <div>
                                    <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                        Hora Fin *
                                    </label>
                                    <input
                                        type="time"
                                        value={reservationForm.data.end_time}
                                        onChange={e => reservationForm.setData('end_time', e.target.value)}
                                        required
                                        className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                    />
                                    {reservationForm.errors.end_time && (
                                        <p className="text-xs text-red-500 mt-1">{reservationForm.errors.end_time}</p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <label className="block text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-1">
                                    Detalles Adicionales
                                </label>
                                <textarea
                                    rows="2"
                                    value={reservationForm.data.description}
                                    onChange={e => reservationForm.setData('description', e.target.value)}
                                    placeholder="Requerimientos de proyectores, micrófonos o notas para intendencia..."
                                    className="w-full rounded-xl border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-sm px-3.5 py-2.5 text-gray-900 dark:text-white focus:ring-2 focus:ring-brand-primary"
                                ></textarea>
                            </div>

                            <div className="flex items-center justify-end gap-2.5 pt-3 border-t border-gray-100 dark:border-gray-800">
                                <button
                                    type="button"
                                    onClick={() => setReservationModalOpen(false)}
                                    className="rounded-xl px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                                >
                                    Cancelar
                                </button>
                                <button
                                    type="submit"
                                    disabled={reservationForm.processing}
                                    className="rounded-xl bg-emerald-600 hover:bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm disabled:opacity-50"
                                >
                                    {reservationForm.processing ? 'Validando...' : 'Confirmar Reserva'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>, document.body)
            )}

            {/* Modal: Horario & Reservas del Aula */}
            {scheduleModalOpen && targetClassroom && (
                createPortal(<div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/60 backdrop-blur-sm animate-in fade-in duration-200" onClick={() => setScheduleModalOpen(false)}>
                    <div className="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-2xl bg-white dark:bg-[#161622] p-6 shadow-2xl border border-gray-100 dark:border-gray-800 space-y-4 my-auto" onClick={(e) => e.stopPropagation()}>
                        <div className="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-800">
                            <div>
                                <h3 className="text-lg font-bold text-gray-900 dark:text-white">
                                    Ocupación & Horario: {targetClassroom.name}
                                </h3>
                                <p className="text-xs text-gray-500 mt-0.5">
                                    {targetClassroom.building_name} • Capacidad {targetClassroom.capacity} asientos
                                </p>
                            </div>
                            <button
                                type="button"
                                onClick={() => setScheduleModalOpen(false)}
                                className="text-gray-400 hover:text-gray-500 text-sm"
                            ><X className="w-4 h-4" /></button>
                        </div>

                        {loadingSchedule ? (
                            <div className="py-12 text-center text-sm text-gray-400">
                                Cargando información de horario...
                            </div>
                        ) : (
                            <div className="space-y-6">
                                {/* Clases Regulares Activas */}
                                <div>
                                    <h4 className="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                        <svg className="w-4 h-4 text-brand-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                        </svg>
                                        Clases Asignadas Regularmente ({scheduleData?.schedules?.length || 0})
                                    </h4>
                                    {scheduleData?.schedules?.length === 0 ? (
                                        <div className="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/40 text-xs text-gray-400 text-center">
                                            No hay clases regulares asignadas a esta aula.
                                        </div>
                                    ) : (
                                        <div className="space-y-2">
                                            {scheduleData.schedules.map(sch => (
                                                <div key={sch.id} className="p-3 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50/60 dark:bg-gray-800/40 flex items-center justify-between gap-4">
                                                    <div>
                                                        <h5 className="text-xs font-bold text-gray-900 dark:text-white">
                                                            {sch.course_name}
                                                        </h5>
                                                        <p className="text-[11px] text-gray-500 mt-0.5">
                                                            Prof. {sch.teacher_name}
                                                        </p>
                                                    </div>
                                                    <div className="text-right">
                                                        <div className="text-xs font-semibold text-brand-primary dark:text-blue-400">
                                                            {sch.start_time} - {sch.end_time}
                                                        </div>
                                                        <div className="text-[10px] text-gray-400 mt-0.5">
                                                            {Array.isArray(sch.days_of_week) ? sch.days_of_week.join(', ') : sch.days_of_week}
                                                        </div>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>

                                {/* Reservas Especiales Próximas */}
                                <div>
                                    <div className="flex items-center justify-between mb-2">
                                        <h4 className="text-xs font-bold text-gray-400 uppercase tracking-wider flex items-center gap-1.5">
                                            <svg className="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            Próximas Reservas Puntuales ({scheduleData?.reservations?.length || 0})
                                        </h4>
                                        <button
                                            type="button"
                                            onClick={() => {
                                                setScheduleModalOpen(false);
                                                openReservation(targetClassroom);
                                            }}
                                            className="text-xs font-semibold text-emerald-600 dark:text-emerald-400 hover:underline"
                                        >
                                            + Agregar Reserva
                                        </button>
                                    </div>

                                    {scheduleData?.reservations?.length === 0 ? (
                                        <div className="p-4 rounded-xl bg-gray-50 dark:bg-gray-800/40 text-xs text-gray-400 text-center">
                                            No hay reservas especiales programadas.
                                        </div>
                                    ) : (
                                        <div className="space-y-2">
                                            {scheduleData.reservations.map(res => (
                                                <div key={res.id} className="p-3 rounded-xl border border-emerald-100 dark:border-emerald-900/30 bg-emerald-50/40 dark:bg-emerald-950/20 flex items-center justify-between gap-4">
                                                    <div>
                                                        <h5 className="text-xs font-bold text-emerald-950 dark:text-emerald-200">
                                                            {res.title}
                                                        </h5>
                                                        {res.description && (
                                                            <p className="text-[11px] text-gray-500 mt-0.5">{res.description}</p>
                                                        )}
                                                    </div>
                                                    <div className="flex items-center gap-3">
                                                        <div className="text-right">
                                                            <div className="text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                                                                {res.reserved_date}
                                                            </div>
                                                            <div className="text-[10px] text-gray-500 mt-0.5">
                                                                {res.start_time} - {res.end_time}
                                                            </div>
                                                        </div>
                                                        <button
                                                            type="button"
                                                            onClick={() => handleDeleteReservation(res.id)}
                                                            className="p-1 text-red-400 hover:text-red-600"
                                                            title="Cancelar Reserva"
                                                        ><X className="w-4 h-4" /></button>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    )}
                                </div>
                            </div>
                        )}

                        <div className="flex justify-end pt-3 border-t border-gray-100 dark:border-gray-800">
                            <button
                                type="button"
                                onClick={() => setScheduleModalOpen(false)}
                                className="rounded-xl px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
                            >
                                Cerrar
                            </button>
                        </div>
                    </div>
                </div>, document.body)
            )}
        </AuthenticatedLayout>
    );
}
