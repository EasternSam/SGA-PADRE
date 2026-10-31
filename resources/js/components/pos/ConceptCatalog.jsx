import React, { useState, useMemo } from 'react';
import { Search, Tag, Check, Sparkles, AlertCircle } from 'lucide-react';
import { formatMoney } from './utils';

export default function ConceptCatalog({
    concepts = [],
    selectedConcept,
    onSelectConcept,
    customName,
    onChangeCustomName,
    customAmount,
    onChangeCustomAmount,
}) {
    const [searchFilter, setSearchFilter] = useState('');
    const [activeCategory, setActiveCategory] = useState('Todos');

    // Categorización semántica de conceptos
    const categories = useMemo(() => {
        const cats = new Set(['Todos']);
        concepts.forEach((c) => {
            const name = c.name.toLowerCase();
            if (name.includes('matrícula') || name.includes('cuota') || name.includes('mensual')) {
                cats.add('Matrículas & Cuotas');
            } else if (name.includes('record') || name.includes('certificado') || name.includes('constancia')) {
                cats.add('Certificaciones');
            } else if (name.includes('carnet') || name.includes('duplicado')) {
                cats.add('Carnets');
            } else if (name.includes('laboratorio') || name.includes('examen')) {
                cats.add('Laboratorios');
            } else {
                cats.add('Otros');
            }
        });
        return Array.from(cats);
    }, [concepts]);

    const getConceptCategory = (name) => {
        const n = name.toLowerCase();
        if (n.includes('matrícula') || n.includes('cuota') || n.includes('mensual')) return 'Matrículas & Cuotas';
        if (n.includes('record') || n.includes('certificado') || n.includes('constancia')) return 'Certificaciones';
        if (n.includes('carnet') || n.includes('duplicado')) return 'Carnets';
        if (n.includes('laboratorio') || n.includes('examen')) return 'Laboratorios';
        return 'Otros';
    };

    const filteredConcepts = useMemo(() => {
        const query = searchFilter.toLowerCase().trim();
        return concepts.filter((c) => {
            const matchesCat = activeCategory === 'Todos' || getConceptCategory(c.name) === activeCategory;
            const matchesQuery = !query || c.name.toLowerCase().includes(query) || (c.description && c.description.toLowerCase().includes(query));
            return matchesCat && matchesQuery;
        });
    }, [concepts, activeCategory, searchFilter]);

    return (
        <div className="flex flex-col h-full space-y-4">
            {/* Cabecera con Búsqueda y Filtros de Categoría */}
            <div className="space-y-3">
                <div className="relative">
                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-neutral-400 pointer-events-none" />
                    <input
                        type="text"
                        value={searchFilter}
                        onChange={(e) => setSearchFilter(e.target.value)}
                        placeholder="Buscar concepto o servicio..."
                        className="w-full rounded-xl border border-neutral-200 dark:border-[#262626] bg-white dark:bg-[#171717] pl-9 pr-4 py-2 text-sm text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-[#bb3902] transition-shadow"
                    />
                </div>

                {/* Chips de Categorías con scroll horizontal suave */}
                <div className="flex items-center gap-1.5 overflow-x-auto pb-1 scrollbar-none text-xs font-medium">
                    {categories.map((cat) => {
                        const isActive = activeCategory === cat;
                        return (
                            <button
                                key={cat}
                                type="button"
                                onClick={() => setActiveCategory(cat)}
                                className={`shrink-0 rounded-full px-3 py-1.5 transition-colors ${
                                    isActive
                                        ? 'bg-[#bb3902] text-white shadow-sm'
                                        : 'bg-neutral-100 dark:bg-[#1f1f1f] text-neutral-600 dark:text-neutral-400 hover:bg-neutral-200 dark:hover:bg-[#262626] hover:text-neutral-900 dark:hover:text-neutral-100'
                                }`}
                            >
                                {cat}
                            </button>
                        );
                    })}
                </div>
            </div>

            {/* Cuadrícula de Conceptos */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 overflow-y-auto pr-1 flex-1">
                {filteredConcepts.map((concept) => {
                    const isSelected = selectedConcept?.id === concept.id;
                    const isOpen = concept.is_open;

                    return (
                        <button
                            key={concept.id}
                            type="button"
                            onClick={() => onSelectConcept(concept)}
                            className={`group relative flex flex-col justify-between rounded-xl border p-4 text-left transition-all ${
                                isSelected
                                    ? 'border-[#bb3902] bg-[#bb3902]/5 dark:bg-[#bb3902]/10 ring-2 ring-[#bb3902]/20'
                                    : 'border-neutral-200 dark:border-[#262626] bg-white dark:bg-[#171717] hover:border-neutral-300 dark:hover:border-[#333333] hover:shadow-sm'
                            }`}
                        >
                            <div>
                                <div className="flex items-start justify-between gap-2">
                                    <span className="text-xs font-semibold uppercase tracking-wider text-neutral-400 dark:text-neutral-500">
                                        {getConceptCategory(concept.name)}
                                    </span>
                                    {isSelected && (
                                        <span className="flex h-5 w-5 items-center justify-center rounded-full bg-[#bb3902] text-white">
                                            <Check className="h-3 w-3 stroke-[3]" />
                                        </span>
                                    )}
                                </div>

                                <div className="mt-2 font-semibold text-neutral-900 dark:text-neutral-100 text-sm group-hover:text-[#bb3902] transition-colors line-clamp-2">
                                    {concept.name}
                                </div>

                                {concept.description && (
                                    <p className="mt-1 text-xs text-neutral-500 dark:text-neutral-400 line-clamp-2">
                                        {concept.description}
                                    </p>
                                )}
                            </div>

                            <div className="mt-4 pt-3 border-t border-neutral-100 dark:border-[#262626] flex items-center justify-between">
                                <span className="text-sm font-bold text-neutral-900 dark:text-neutral-100">
                                    {isOpen ? 'Precio libre' : formatMoney(concept.amount)}
                                </span>
                                <span className={`text-[10px] font-medium px-2 py-0.5 rounded-md ${
                                    concept.is_fixed
                                        ? 'bg-neutral-100 dark:bg-[#262626] text-neutral-600 dark:text-neutral-400'
                                        : 'bg-amber-500/10 text-amber-600 dark:text-amber-400'
                                }`}>
                                    {concept.is_fixed ? 'Tarifa fija' : isOpen ? 'Abierto' : 'Variable'}
                                </span>
                            </div>
                        </button>
                    );
                })}
            </div>

            {/* Panel de configuración si el concepto seleccionado es "Cobro Diverso" o Variable */}
            {selectedConcept?.is_open && (
                <div className="rounded-xl border border-amber-200 dark:border-amber-900/40 bg-amber-50/50 dark:bg-amber-950/20 p-3.5 space-y-3">
                    <div className="flex items-center gap-2 text-xs font-semibold text-amber-700 dark:text-amber-400">
                        <Sparkles className="h-4 w-4" />
                        <span>Detalles del Cobro Diverso (Concepto Abierto)</span>
                    </div>

                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label className="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                Descripción del concepto *
                            </label>
                            <input
                                type="text"
                                value={customName}
                                onChange={(e) => onChangeCustomName(e.target.value)}
                                placeholder="Ej: Venta de materiales, Fotocopias..."
                                className="w-full rounded-lg border border-neutral-300 dark:border-[#333333] bg-white dark:bg-[#171717] px-3 py-1.5 text-xs text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-[#bb3902]"
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-medium text-neutral-700 dark:text-neutral-300 mb-1">
                                Precio unitario (RD$) *
                            </label>
                            <input
                                type="number"
                                min="0.01"
                                step="0.01"
                                value={customAmount || ''}
                                onChange={(e) => onChangeCustomAmount(e.target.value)}
                                placeholder="0.00"
                                className="w-full rounded-lg border border-neutral-300 dark:border-[#333333] bg-white dark:bg-[#171717] px-3 py-1.5 text-xs text-neutral-900 dark:text-neutral-100 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-[#bb3902]"
                            />
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
