import React, { useState, useEffect, useRef } from 'react';
import { Search, UserCheck, Users, X, Loader2 } from 'lucide-react';

export default function StudentPicker({
    selectedStudent,
    onSelectStudent,
    isWalkIn,
    onToggleWalkIn,
    walkInAvailable = true,
}) {
    const [searchQuery, setSearchQuery] = useState('');
    const [searchResults, setSearchResults] = useState([]);
    const [searching, setSearching] = useState(false);
    const [isOpen, setIsOpen] = useState(false);
    const containerRef = useRef(null);

    // Búsqueda en vivo con debounce de 220ms
    useEffect(() => {
        if (isWalkIn || searchQuery.trim().length < 2) {
            setSearchResults([]);
            return;
        }

        const timer = setTimeout(async () => {
            setSearching(true);
            try {
                const res = await fetch(`/admin/pos/search-student?q=${encodeURIComponent(searchQuery.trim())}`);
                if (res.ok) {
                    const data = await res.json();
                    setSearchResults(data);
                    setIsOpen(true);
                }
            } catch (err) {
                console.error('Error buscando estudiantes:', err);
            } finally {
                setSearching(false);
            }
        }, 220);

        return () => clearTimeout(timer);
    }, [searchQuery, isWalkIn]);

    // Cerrar dropdown al hacer clic fuera
    useEffect(() => {
        const handleClickOutside = (e) => {
            if (containerRef.current && !containerRef.current.contains(e.target)) {
                setIsOpen(false);
            }
        };
        document.addEventListener('mousedown', handleClickOutside);
        return () => document.removeEventListener('mousedown', handleClickOutside);
    }, []);

    const handleSelect = (student) => {
        onSelectStudent(student);
        setSearchQuery('');
        setIsOpen(false);
    };

    const handleClear = () => {
        onSelectStudent(null);
        setSearchQuery('');
    };

    return (
        <div ref={containerRef} className="relative w-full">
            {/* Si ya hay un estudiante seleccionado o está activo el cliente general */}
            {isWalkIn ? (
                <div className="flex items-center justify-between rounded-xl border border-neutral-200 dark:border-[#262626] bg-neutral-50 dark:bg-[#171717] p-3 text-sm">
                    <div className="flex items-center gap-3">
                        <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-[#bb3902]/10 text-[#bb3902]">
                            <Users className="h-5 w-5" />
                        </div>
                        <div>
                            <div className="font-semibold text-neutral-900 dark:text-neutral-100">
                                Cliente General (Público)
                            </div>
                            <div className="text-xs text-neutral-500 dark:text-neutral-400">
                                Venta directa de mostrador sin registro de expediente
                            </div>
                        </div>
                    </div>
                    <button
                        type="button"
                        onClick={() => onToggleWalkIn(false)}
                        className="rounded-lg p-1.5 text-neutral-400 hover:bg-neutral-200 dark:hover:bg-[#262626] hover:text-neutral-700 dark:hover:text-neutral-200 transition-colors"
                        title="Cambiar cliente"
                    >
                        <X className="h-4 w-4" />
                    </button>
                </div>
            ) : selectedStudent ? (
                <div className="flex items-center justify-between rounded-xl border border-neutral-200 dark:border-[#262626] bg-neutral-50 dark:bg-[#171717] p-3 text-sm">
                    <div className="flex items-center gap-3 min-w-0">
                        <div className="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                            <UserCheck className="h-5 w-5" />
                        </div>
                        <div className="min-w-0">
                            <div className="font-semibold text-neutral-900 dark:text-neutral-100 truncate">
                                {selectedStudent.name}
                            </div>
                            <div className="flex items-center gap-2 text-xs text-neutral-500 dark:text-neutral-400">
                                <span>Matrícula: {selectedStudent.code || 'N/A'}</span>
                                {selectedStudent.cedula && (
                                    <>
                                        <span>•</span>
                                        <span>Céd: {selectedStudent.cedula}</span>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>
                    <button
                        type="button"
                        onClick={handleClear}
                        className="rounded-lg p-1.5 text-neutral-400 hover:bg-neutral-200 dark:hover:bg-[#262626] hover:text-neutral-700 dark:hover:text-neutral-200 transition-colors"
                        title="Quitar estudiante"
                    >
                        <X className="h-4 w-4" />
                    </button>
                </div>
            ) : (
                /* Barra de búsqueda y botón de Cliente General */
                <div className="flex items-center gap-2">
                    <div className="relative flex-1">
                        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-400 pointer-events-none" />
                        <input
                            type="text"
                            value={searchQuery}
                            onChange={(e) => setSearchQuery(e.target.value)}
                            onFocus={() => { if (searchResults.length > 0) setIsOpen(true); }}
                            placeholder="Buscar estudiante por nombre, matrícula o cédula..."
                            className="w-full rounded-xl border border-neutral-200 dark:border-[#262626] bg-white dark:bg-[#171717] pl-9 pr-9 py-2.5 text-sm text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-[#bb3902] transition-shadow"
                        />
                        {searching && (
                            <Loader2 className="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 animate-spin text-neutral-400" />
                        )}
                        {!searching && searchQuery && (
                            <button
                                type="button"
                                onClick={() => { setSearchQuery(''); setSearchResults([]); }}
                                className="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600 dark:hover:text-neutral-200"
                            >
                                <X className="h-4 w-4" />
                            </button>
                        )}
                    </div>

                    {walkInAvailable && (
                        <button
                            type="button"
                            onClick={() => onToggleWalkIn(true)}
                            className="flex items-center gap-1.5 whitespace-nowrap rounded-xl border border-dashed border-neutral-300 dark:border-[#333333] hover:border-[#bb3902] dark:hover:border-[#bb3902] bg-neutral-50 dark:bg-[#171717] px-3 py-2.5 text-xs font-medium text-neutral-700 dark:text-neutral-300 hover:text-[#bb3902] dark:hover:text-[#bb3902] transition-colors"
                        >
                            <Users className="h-3.5 w-3.5" />
                            <span>Público General</span>
                        </button>
                    )}
                </div>
            )}

            {/* Resultados desplegables de búsqueda */}
            {isOpen && searchResults.length > 0 && (
                <div className="absolute left-0 right-0 top-full mt-1.5 z-40 max-h-60 overflow-y-auto rounded-xl border border-neutral-200 dark:border-[#262626] bg-white dark:bg-[#1f1f1f] py-1 shadow-lg">
                    {searchResults.map((student) => (
                        <button
                            key={student.id}
                            type="button"
                            onClick={() => handleSelect(student)}
                            className="w-full px-3.5 py-2.5 text-left flex items-center justify-between hover:bg-neutral-100 dark:hover:bg-[#262626] transition-colors"
                        >
                            <div>
                                <div className="text-sm font-medium text-neutral-900 dark:text-neutral-100">
                                    {student.name}
                                </div>
                                <div className="text-xs text-neutral-500 dark:text-neutral-400">
                                    Matrícula: {student.code || 'Sin Matrícula'} {student.cedula ? `• Céd: ${student.cedula}` : ''}
                                </div>
                            </div>
                            <span className="text-xs text-[#bb3902] font-medium">Seleccionar</span>
                        </button>
                    ))}
                </div>
            )}
        </div>
    );
}
