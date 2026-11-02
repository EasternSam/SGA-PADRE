import React, { useState } from 'react';
import { Head, Link, router, useForm } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PoweredByBills from '@/components/bills/PoweredByBills';

export default function PaymentConceptsIndex({ auth, concepts, filters }) {
    const [view, setView] = useState('list'); // 'list' | 'form'
    const [search, setSearch] = useState(filters?.search || '');
    const [editingConcept, setEditingConcept] = useState(null);
    const [conceptToDelete, setConceptToDelete] = useState(null);
    const [isMassDeleteModalOpen, setIsMassDeleteModalOpen] = useState(false);

    const { data, setData, post, processing, errors, reset, clearErrors } = useForm({
        id: '',
        name: '',
        description: '',
        amount: '0.00',
        is_fixed_amount: true,
    });

    const formatCurrency = (val) => {
        const num = Number(val || 0);
        return `RD$ ${num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    };

    const handleSearchInput = (val) => {
        setSearch(val);
        router.get(
            '/admin/finance/payment-concepts',
            { search: val || undefined },
            { preserveState: true, preserveScroll: true, replace: true }
        );
    };

    const openCreateForm = () => {
        clearErrors();
        reset();
        setEditingConcept(null);
        setData({
            id: '',
            name: '',
            description: '',
            amount: '0.00',
            is_fixed_amount: true,
        });
        setView('form');
    };

    const openEditForm = (concept) => {
        clearErrors();
        setEditingConcept(concept);
        setData({
            id: concept.id,
            name: concept.name,
            description: concept.description || '',
            amount: Number(concept.amount) > 0 ? Number(concept.amount).toFixed(2) : '0.00',
            is_fixed_amount: Number(concept.amount) > 0 || Boolean(concept.is_fixed_amount),
        });
        setView('form');
    };

    const handleSubmitConcept = (e) => {
        e.preventDefault();
        post('/admin/finance/payment-concepts', {
            preserveScroll: true,
            onSuccess: () => {
                setView('list');
                setEditingConcept(null);
                reset();
            },
        });
    };

    const handleDelete = () => {
        if (!conceptToDelete) return;
        router.delete(`/admin/finance/payment-concepts/${conceptToDelete.id}`, {
            preserveScroll: true,
            onSuccess: () => setConceptToDelete(null),
        });
    };

    const handleMassDelete = () => {
        router.post('/admin/finance/payment-concepts/mass-delete', {}, {
            preserveScroll: true,
            onSuccess: () => setIsMassDeleteModalOpen(false),
        });
    };

    const rows = concepts?.data || [];

    return (
        <AuthenticatedLayout user={auth?.user} title="Conceptos de Pago">
            <Head title="Artículos y Servicios (Conceptos de Pago) - Gridbase Bills" />

            <div className="bills-scope">
                <div className="workspace-panel">
                    {view === 'list' ? (
                        <>
                            {/* 1:1 Gridbase Bills Header (`items.js` line 13) */}
                            <div className="page-header">
                                <div>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: '12px', flexWrap: 'wrap' }}>
                                        <h1 className="page-title">Artículos y Servicios (Conceptos de Pago)</h1>
                                        <PoweredByBills variant="badge" />
                                    </div>
                                    <p className="page-subtitle">Define tus conceptos preestablecidos y precios</p>
                                </div>
                                <div className="btn-group">
                                    <button
                                        type="button"
                                        className="btn btn-secondary"
                                        onClick={() => setIsMassDeleteModalOpen(true)}
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                            <polyline points="3 6 5 6 21 6" />
                                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        </svg>
                                        Limpiar Sin Uso
                                    </button>
                                    <button
                                        type="button"
                                        className="btn btn-primary"
                                        onClick={openCreateForm}
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                            <line x1="12" y1="5" x2="12" y2="19" />
                                            <line x1="5" y1="12" x2="19" y2="12" />
                                        </svg>
                                        Nuevo Concepto
                                    </button>
                                </div>
                            </div>

                            {/* 1:1 Gridbase Bills Table Outer (`items.js` line 24) */}
                            <div className="table-outer">
                                <div className="table-toolbar" style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: '12px', flexWrap: 'wrap' }}>
                                    <div className="search-wrapper" style={{ maxWidth: '300px', display: 'flex', width: '100%' }}>
                                        <svg className="search-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                            <circle cx="11" cy="11" r="8" />
                                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                                        </svg>
                                        <input
                                            type="text"
                                            id="items-search"
                                            className="search-input"
                                            placeholder="Buscar artículos o conceptos..."
                                            value={search}
                                            onChange={(e) => handleSearchInput(e.target.value)}
                                        />
                                    </div>
                                    <span className="text-muted" style={{ fontSize: '12px' }}>
                                        Mostrando <strong>{rows.length}</strong> de <strong>{concepts?.total || 0}</strong> conceptos
                                    </span>
                                </div>

                                <div className="table-wrapper">
                                    <table className="data-table">
                                        <thead>
                                            <tr>
                                                <th>Nombre</th>
                                                <th>Descripción</th>
                                                <th>Precio Base</th>
                                                <th>Estado / Uso</th>
                                                <th style={{ width: '100px', textAlign: 'right' }}>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody id="items-table-body">
                                            {rows.length === 0 ? (
                                                <tr>
                                                    <td colSpan={5} style={{ textAlign: 'center', padding: '32px', color: 'var(--color-text-muted)' }}>
                                                        No hay artículos registrados
                                                    </td>
                                                </tr>
                                            ) : (
                                                rows.map((concept) => (
                                                    <tr key={concept.id}>
                                                        <td style={{ fontWeight: 500, color: 'var(--color-text-primary)' }}>
                                                            {concept.name}
                                                        </td>
                                                        <td>
                                                            <span className="truncate" style={{ maxWidth: '300px', display: 'inline-block' }} title={concept.description || ''}>
                                                                {concept.description || <span style={{ color: 'var(--color-text-muted)' }}>Sin descripción</span>}
                                                            </span>
                                                        </td>
                                                        <td style={{ fontWeight: 600 }}>
                                                            {Number(concept.amount) > 0 ? (
                                                                formatCurrency(concept.amount)
                                                            ) : (
                                                                <span className="badge badge-draft">Variable</span>
                                                            )}
                                                        </td>
                                                        <td>
                                                            <span className={`badge ${concept.payments_count > 0 ? 'badge-active' : 'badge-draft'}`}>
                                                                {concept.payments_count > 0
                                                                    ? `Activo (${concept.payments_count} pagos)`
                                                                    : 'Activo'}
                                                            </span>
                                                        </td>
                                                        <td style={{ textAlign: 'right' }}>
                                                            <div className="row-actions" style={{ justifyContent: 'flex-end', opacity: 1 }}>
                                                                <button
                                                                    type="button"
                                                                    className="btn-icon"
                                                                    style={{ width: '28px', height: '28px' }}
                                                                    title="Editar"
                                                                    onClick={() => openEditForm(concept)}
                                                                >
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                                                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                                                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                                                                    </svg>
                                                                </button>
                                                                <button
                                                                    type="button"
                                                                    className="btn-icon"
                                                                    style={{ width: '28px', height: '28px' }}
                                                                    title="Eliminar"
                                                                    onClick={() => setConceptToDelete(concept)}
                                                                >
                                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--color-danger-icon)" strokeWidth="2">
                                                                        <polyline points="3 6 5 6 21 6" />
                                                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                                                    </svg>
                                                                </button>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                ))
                                            )}
                                        </tbody>
                                    </table>
                                </div>

                                {concepts?.links && concepts.links.length > 3 && (
                                    <div className="pagination">
                                        <div className="pagination-info">
                                            Página {concepts.current_page} de {concepts.last_page}
                                        </div>
                                        <div className="pagination-controls">
                                            {concepts.links.map((link, idx) =>
                                                link.url ? (
                                                    <Link
                                                        key={idx}
                                                        href={link.url}
                                                        className={`btn btn-sm ${link.active ? 'btn-primary' : 'btn-secondary'}`}
                                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                                        preserveScroll
                                                    />
                                                ) : null
                                            )}
                                        </div>
                                    </div>
                                )}
                            </div>
                        </>
                    ) : (
                        /* 1:1 Gridbase Bills Form (`items.js` line 121) */
                        <>
                            <div style={{ marginBottom: '12px' }}>
                                <button
                                    type="button"
                                    onClick={() => setView('list')}
                                    style={{ background: 'none', border: 'none', padding: 0, cursor: 'pointer', color: 'var(--color-text-muted)', fontSize: '13px' }}
                                >
                                    ← Artículos y Conceptos
                                </button>
                            </div>
                            <div className="page-header">
                                <div>
                                    <div style={{ display: 'flex', alignItems: 'center', gap: '12px', flexWrap: 'wrap' }}>
                                        <h1 className="page-title">{editingConcept ? 'Editar Artículo / Concepto' : 'Nuevo Artículo / Concepto'}</h1>
                                        <PoweredByBills variant="badge" />
                                    </div>
                                    <p className="page-subtitle">
                                        {editingConcept ? 'Modifica los datos del artículo' : 'Registra un nuevo concepto para facturar'}
                                    </p>
                                </div>
                                <button type="button" className="btn btn-secondary" onClick={() => setView('list')}>
                                    Cancelar
                                </button>
                            </div>

                            <form id="item-form" className="form-card" onSubmit={handleSubmitConcept}>
                                <div className="grid-2">
                                    <div className="form-group" style={{ gridColumn: 'span 2' }}>
                                        <label className="form-label">Nombre del Artículo / Servicio *</label>
                                        <input
                                            type="text"
                                            id="i_name"
                                            className="form-control"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            placeholder="Ej: Mensualidad Academic+, Emisión de Diploma..."
                                            required
                                        />
                                        {errors.name && <small style={{ color: '#dc2626', fontSize: '11px' }}>{errors.name}</small>}
                                    </div>

                                    <div className="form-group">
                                        <label className="form-label">Precio por Defecto (DOP)</label>
                                        <input
                                            type="number"
                                            id="i_price"
                                            className="form-control"
                                            value={data.is_fixed_amount ? data.amount : '0.00'}
                                            onChange={(e) => {
                                                setData('amount', e.target.value);
                                                setData('is_fixed_amount', Number(e.target.value) > 0);
                                            }}
                                            min="0"
                                            step="0.01"
                                            required
                                        />
                                        {errors.amount && <small style={{ color: '#dc2626', fontSize: '11px' }}>{errors.amount}</small>}
                                    </div>

                                    <div className="form-group">
                                        <label className="form-label">Modalidad de Precio</label>
                                        <select
                                            id="i_status"
                                            className="form-select"
                                            value={data.is_fixed_amount ? '1' : '0'}
                                            onChange={(e) => {
                                                const isFixed = e.target.value === '1';
                                                setData('is_fixed_amount', isFixed);
                                                if (!isFixed) setData('amount', '0.00');
                                            }}
                                        >
                                            <option value="1">Monto Fijo Preestablecido</option>
                                            <option value="0">Monto Variable (Al momento del cobro)</option>
                                        </select>
                                    </div>

                                    <div className="form-group" style={{ gridColumn: 'span 2' }}>
                                        <label className="form-label">Descripción Detallada (Opcional)</label>
                                        <textarea
                                            id="i_description"
                                            className="form-control"
                                            rows={3}
                                            placeholder="Descripción que aparecerá en la factura..."
                                            value={data.description}
                                            onChange={(e) => setData('description', e.target.value)}
                                        />
                                    </div>
                                </div>

                                <div className="mt-24">
                                    <button type="submit" className="btn btn-primary" disabled={processing}>
                                        {processing ? 'Guardando...' : editingConcept ? 'Guardar Cambios' : 'Crear Artículo'}
                                    </button>
                                </div>
                            </form>
                        </>
                    )}

                    {/* Modal Confirmación Eliminación Individual */}
                    {conceptToDelete && (
                        <div className="modal-overlay" onClick={() => setConceptToDelete(null)}>
                            <div className="modal" style={{ maxWidth: '440px' }} onClick={(e) => e.stopPropagation()}>
                                <div className="modal-header">
                                    <h3>¿Eliminar Artículo / Concepto?</h3>
                                    <button type="button" className="modal-close" onClick={() => setConceptToDelete(null)}>✕</button>
                                </div>
                                <div className="modal-body">
                                    <p style={{ fontSize: '13px', color: 'var(--color-text-secondary)', marginBottom: '12px' }}>
                                        Estás a punto de eliminar el concepto <strong>"{conceptToDelete.name}"</strong>.
                                    </p>
                                    {conceptToDelete.payments_count > 0 ? (
                                        <div style={{ padding: '12px', background: 'var(--color-warning-bg)', color: 'var(--color-warning)', borderRadius: '8px', fontSize: '12px' }}>
                                            <strong>Acción Bloqueada:</strong> Este concepto tiene {conceptToDelete.payments_count} transacción(es) registrada(s). Por integridad contable no se permite su eliminación.
                                        </div>
                                    ) : (
                                        <p style={{ fontSize: '12px', color: 'var(--color-text-muted)' }}>
                                            Esta acción eliminará el concepto permanentemente ya que no posee transacciones vinculadas.
                                        </p>
                                    )}
                                </div>
                                <div className="modal-footer">
                                    <button type="button" className="btn btn-secondary" onClick={() => setConceptToDelete(null)}>
                                        Cancelar
                                    </button>
                                    {conceptToDelete.payments_count === 0 && (
                                        <button type="button" className="btn btn-danger" onClick={handleDelete}>
                                            Sí, Eliminar
                                        </button>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Modal Confirmación Limpieza Masiva */}
                    {isMassDeleteModalOpen && (
                        <div className="modal-overlay" onClick={() => setIsMassDeleteModalOpen(false)}>
                            <div className="modal" style={{ maxWidth: '440px' }} onClick={(e) => e.stopPropagation()}>
                                <div className="modal-header">
                                    <h3>Limpieza de Conceptos Sin Uso</h3>
                                    <button type="button" className="modal-close" onClick={() => setIsMassDeleteModalOpen(false)}>✕</button>
                                </div>
                                <div className="modal-body">
                                    <p style={{ fontSize: '13px', color: 'var(--color-text-secondary)', marginBottom: '12px' }}>
                                        Esta acción eliminará de forma segura <strong>únicamente</strong> aquellos conceptos que <strong>nunca</strong> han sido utilizados en un pago.
                                    </p>
                                    <p style={{ fontSize: '12px', color: 'var(--color-text-muted)' }}>
                                        Los conceptos con historial contable se conservarán protegidos.
                                    </p>
                                </div>
                                <div className="modal-footer">
                                    <button type="button" className="btn btn-secondary" onClick={() => setIsMassDeleteModalOpen(false)}>
                                        Cancelar
                                    </button>
                                    <button type="button" className="btn btn-danger" onClick={handleMassDelete}>
                                        Ejecutar Limpieza
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
