import React, { useState, useEffect, useRef } from 'react';
import { router, usePage } from '@inertiajs/react';
import {
    LayoutDashboard,
    CreditCard,
    GraduationCap,
    UserCheck,
    BookOpen,
    Landmark,
    TrendingUp,
    Tag,
    Receipt,
    Clock,
    Banknote,
    Users,
    FileText,
    ShieldCheck,
    Settings,
    Calendar,
    Award,
    Building2,
    UserPlus,
} from 'lucide-react';

const SYSTEM_MODULES = [
    { title: 'Panel de Control (Dashboard)', category: 'General', href: '/admin/dashboard', keywords: 'inicio home kpi resumen', icon: LayoutDashboard },
    { title: 'Terminal de Caja (Punto de Venta)', category: 'Finanzas', href: '/admin/pos', keywords: 'caja pos cobro facturacion pagos recibo', icon: CreditCard },
    { title: 'Estudiantes y Expedientes', category: 'Académico', href: '/admin/students', keywords: 'alumnos matriculas directorio kardex', icon: GraduationCap },
    { title: 'Docentes y Carga Horaria', category: 'Académico', href: '/admin/teachers', keywords: 'profesores maestros claustro', icon: UserCheck },
    { title: 'Cursos Técnicos y Secciones', category: 'Académico', href: '/admin/courses', keywords: 'talleres modulos oferta academica moodle', icon: BookOpen },
    { title: 'Carreras Universitarias y Pensum', category: 'Académico', href: '/admin/careers', keywords: 'grado malla curricular asignaturas materias', icon: Landmark },
    { title: 'Calendario Académico', category: 'Académico', href: '/admin/calendar', keywords: 'agenda eventos fechas feriados horario', icon: Calendar },
    { title: 'Trámites y Solicitudes', category: 'Académico', href: '/admin/requests', keywords: 'cartas records constancias retiros', icon: FileText },
    { title: 'Admisiones y Nuevo Ingreso', category: 'Académico', href: '/admin/admissions', keywords: 'aspirantes prospectos inscripciones pipeline', icon: UserPlus },
    { title: 'Aulas y Espacios Físicos', category: 'Académico', href: '/admin/classrooms', keywords: 'edificios laboratorios reservas salones', icon: Building2 },
    { title: 'Panel Financiero e Ingresos', category: 'Finanzas', href: '/admin/finance/dashboard', keywords: 'cuentas por cobrar morosidad becas aranceles', icon: TrendingUp },
    { title: 'Conceptos de Pago y Aranceles', category: 'Finanzas', href: '/admin/finance/payment-concepts', keywords: 'tarifas precios mensualidad inscripcion', icon: Tag },
    { title: 'Catálogo de Cuentas Contables', category: 'Contabilidad', href: '/admin/finance/chart-of-accounts', keywords: 'activos pasivos capital ingresos gastos contabilidad', icon: Landmark },
    { title: 'Asiento Contable Manual', category: 'Contabilidad', href: '/admin/finance/manual-entry', keywords: 'diario partida doble debito credito', icon: FileText },
    { title: 'Libro Mayor General', category: 'Contabilidad', href: '/admin/finance/ledger', keywords: 'movimientos contables saldos', icon: BookOpen },
    { title: 'Estados Financieros', category: 'Contabilidad', href: '/admin/finance/statements', keywords: 'balance general estado de resultados comprobacion', icon: TrendingUp },
    { title: 'Cierre de Período Contable', category: 'Contabilidad', href: '/admin/finance/period-closing', keywords: 'bloqueo contable cierre mensual anual', icon: ShieldCheck },
    { title: 'Cuentas por Pagar y Gastos', category: 'Finanzas', href: '/admin/finance/expenses', keywords: 'suplidores proveedores compras facturas caja chica', icon: Receipt },
    { title: 'Reportes Fiscales DGII (606 / 607)', category: 'Finanzas', href: '/admin/finance/dgii-reports', keywords: 'impuestos ncf comprobantes fiscales', icon: Landmark },
    { title: 'Empleados y Expedientes', category: 'RRHH', href: '/admin/hr/employees', keywords: 'personal recursos humanos contratacion', icon: Users },
    { title: 'Control de Asistencia (Reloj)', category: 'RRHH', href: '/admin/hr/attendances', keywords: 'ponche biometrico entradas salidas tardanzas', icon: Clock },
    { title: 'Nómina de Pago y TSS', category: 'RRHH', href: '/admin/hr/payroll', keywords: 'salarios sueldos afp sfs isr dispersion', icon: Banknote },
    { title: 'Centro de Reportes', category: 'Reportes', href: '/admin/reports', keywords: 'estadisticas exportar csv pdf', icon: FileText },
    { title: 'Plantillas de Certificados y Diplomas', category: 'Sistema', href: '/admin/certificates', keywords: 'diplomas certificados editor diseno', icon: Award },
    { title: 'Gestión de Usuarios y Roles', category: 'Sistema', href: '/admin/users', keywords: 'administradores permisos accesos cuentas', icon: ShieldCheck },
    { title: 'Registro de Auditoría y Logs', category: 'Sistema', href: '/admin/activity-logs', keywords: 'historial bitacora seguridad cambios', icon: FileText },
    { title: 'Configuración Global', category: 'Sistema', href: '/admin/settings', keywords: 'ajustes marca colores logo moodle wordpress smtp', icon: Settings },
    { title: 'Mi Perfil y Seguridad', category: 'Cuenta', href: '/admin/profile', keywords: 'contrasena clave cuenta usuario', icon: Users },
];

const STUDENT_MODULES = [
    { title: 'Mi Horario y Asignaturas', category: 'Académico', href: '/student/dashboard', keywords: 'inicio portal clases', icon: LayoutDashboard },
    { title: 'Selección de Asignaturas', category: 'Académico', href: '/student/selection', keywords: 'inscripcion materias prematricula', icon: BookOpen },
    { title: 'Pensum y Avance de Carrera', category: 'Académico', href: '/student/projection', keywords: 'malla kardex notas', icon: GraduationCap },
    { title: 'Aula Virtual (Moodle)', category: 'Académico', href: '/student/moodle-auth', keywords: 'moodle tareas virtual', icon: BookOpen },
    { title: 'Mis Pagos y Facturas', category: 'Finanzas', href: '/student/payments', keywords: 'cuotas balance tarjeta', icon: CreditCard },
    { title: 'Solicitudes y Récords', category: 'Trámites', href: '/student/requests', keywords: 'cartas constancias', icon: FileText },
    { title: 'Mi Perfil de Estudiante', category: 'Cuenta', href: '/student/profile', keywords: 'clave datos', icon: Users },
];

function normalizeSearchText(value = '') {
    return String(value)
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
}

export default function GlobalSearchModal({ isOpen, onClose, isStudent = false }) {
    const [query, setQuery] = useState('');
    const [studentResults, setStudentResults] = useState([]);
    const [searchingStudents, setSearchingStudents] = useState(false);
    const inputRef = useRef(null);

    useEffect(() => {
        if (isOpen) {
            setQuery('');
            setStudentResults([]);
            setTimeout(() => inputRef.current?.focus(), 50);
        }
    }, [isOpen]);

    // Búsqueda en vivo de estudiantes (solo para personal administrativo)
    useEffect(() => {
        if (isStudent) return;

        const trimmed = query.trim();
        if (trimmed.length < 2) {
            setStudentResults([]);
            return;
        }

        const timer = setTimeout(async () => {
            setSearchingStudents(true);
            try {
                const res = await fetch(`/admin/pos/search-student?q=${encodeURIComponent(trimmed)}`);
                const data = await res.json();
                setStudentResults(Array.isArray(data) ? data : []);
            } catch (err) {
                console.error('Error buscando estudiantes:', err);
            } finally {
                setSearchingStudents(false);
            }
        }, 200);

        return () => clearTimeout(timer);
    }, [query, isStudent]);

    if (!isOpen) return null;

    const admissionsOpen = Boolean(usePage().props?.admissions_open);
    const activeStudentModules = admissionsOpen
        ? STUDENT_MODULES
        : STUDENT_MODULES.filter((m) => m.href !== '/student/selection');
    const modulePool = isStudent ? activeStudentModules : SYSTEM_MODULES;
    const normalizedQuery = normalizeSearchText(query);
    const searchTokens = normalizedQuery ? normalizedQuery.split(/\s+/).filter(Boolean) : [];

    const filteredModules = searchTokens.length > 0
        ? modulePool.filter((m) => {
            const haystack = normalizeSearchText(`${m.title} ${m.category} ${m.keywords || ''}`);
            return searchTokens.every((token) => haystack.includes(token));
        })
        : modulePool.slice(0, 7);

    const handleSelect = (href) => {
        onClose();
        if (href === '/student/moodle-auth') {
            window.open(href, '_blank');
            return;
        }
        router.visit(href);
    };

    const handleKeyDown = (e) => {
        if (e.key === 'Escape') {
            e.preventDefault();
            onClose();
        } else if (e.key === 'Enter') {
            if (studentResults.length > 0) {
                e.preventDefault();
                handleSelect(`/admin/students/profile/${studentResults[0].id}`);
            } else if (filteredModules.length > 0) {
                e.preventDefault();
                handleSelect(filteredModules[0].href);
            }
        }
    };

    return (
        <div 
            className="fixed inset-0 z-50 flex items-start justify-center pt-16 sm:pt-24 px-4 bg-gray-950/60 backdrop-blur-sm animate-in fade-in duration-150"
            onClick={onClose}
        >
            <div 
                className="bg-white dark:bg-[#171717] rounded-2xl w-full max-w-xl shadow-2xl border border-gray-200 dark:border-[#262626] overflow-hidden animate-in zoom-in-95 duration-150"
                onClick={(e) => e.stopPropagation()}
            >
                {/* Search Bar Input */}
                <div className="p-3.5 border-b border-gray-100 dark:border-[#262626] flex items-center gap-3">
                    <svg className="w-5 h-5 text-gray-400 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                        <path fillRule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clipRule="evenodd" />
                    </svg>
                    <input
                        ref={inputRef}
                        type="text"
                        value={query}
                        onChange={(e) => setQuery(e.target.value)}
                        onKeyDown={handleKeyDown}
                        placeholder={isStudent ? "Buscar en mi portal, asignaturas, trámites, pagos... (Esc para salir)" : "Buscar módulos, estudiantes, matrículas, finanzas... (Esc para salir)"}
                        className="w-full text-sm bg-transparent border-0 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-0"
                    />
                    <kbd className="hidden sm:inline-block px-2 py-0.5 text-[10px] font-bold text-gray-400 bg-gray-100 dark:bg-gray-800 rounded border border-gray-200 dark:border-[#262626]">
                        ESC
                    </kbd>
                </div>

                {/* Results Container */}
                <div className="max-h-[60vh] overflow-y-auto divide-y divide-gray-100 dark:divide-[#262626] p-2">
                    {/* Estudiantes Encontrados */}
                    {studentResults.length > 0 && (
                        <div className="py-2 px-2">
                            <p className="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5 px-2">
                                Estudiantes
                            </p>
                            <div className="space-y-1">
                                {studentResults.map((s) => (
                                    <button
                                        key={`student-${s.id}`}
                                        onClick={() => handleSelect(`/admin/students/profile/${s.id}`)}
                                        className="w-full flex items-center justify-between p-2 rounded-xl text-left hover:bg-brand/10 dark:hover:bg-brand/20 transition-colors group"
                                    >
                                        <div className="flex items-center gap-3 min-w-0">
                                            <div className="w-8 h-8 rounded-full bg-brand text-white flex items-center justify-center font-bold text-xs shrink-0">
                                                {s.name?.charAt(0) || 'E'}
                                            </div>
                                            <div className="truncate">
                                                <p className="text-xs font-bold text-gray-900 dark:text-white truncate">{s.name}</p>
                                                <p className="text-[11px] text-gray-400">{s.cedula || 'Sin cédula'} • {s.code}</p>
                                            </div>
                                        </div>
                                        <span className="text-[11px] font-semibold text-brand dark:text-brand-accent opacity-0 group-hover:opacity-100 transition-opacity">
                                            Ver Perfil →
                                        </span>
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Módulos del Sistema */}
                    {filteredModules.length > 0 && (
                        <div className="py-2 px-2">
                            <p className="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5 px-2">
                                {searchTokens.length > 0 ? 'Módulos coincidentes' : 'Acceso Rápido'}
                            </p>
                            <div className="space-y-1">
                                {filteredModules.map((m) => {
                                    const IconComponent = m.icon;
                                    return (
                                        <button
                                            key={m.href}
                                            onClick={() => handleSelect(m.href)}
                                            className="w-full flex items-center justify-between p-2 rounded-xl text-left hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors group"
                                        >
                                            <div className="flex items-center gap-2.5">
                                                {IconComponent && <IconComponent className="w-4 h-4 text-gray-500 dark:text-gray-400 shrink-0" />}
                                                <span className="text-xs font-semibold text-gray-800 dark:text-gray-200">{m.title}</span>
                                            </div>
                                            <span className="text-[10px] font-medium text-gray-400 bg-gray-100 dark:bg-gray-800/80 px-2 py-0.5 rounded">
                                                {m.category}
                                            </span>
                                        </button>
                                    );
                                })}
                            </div>
                        </div>
                    )}

                    {/* Estado sin resultados */}
                    {searchTokens.length > 0 && !searchingStudents && studentResults.length === 0 && filteredModules.length === 0 && (
                        <div className="py-8 px-4 text-center">
                            <p className="text-xs font-medium text-gray-500 dark:text-gray-400">
                                No se encontraron módulos ni estudiantes para «{query.trim()}».
                            </p>
                        </div>
                    )}
                </div>

                {/* Footer hints */}
                <div className="p-2.5 bg-gray-50 dark:bg-[#121212] border-t border-gray-100 dark:border-[#262626] flex items-center justify-between text-[11px] text-gray-400 px-4">
                    <span>Presiona Enter para ir al primer resultado o Esc para cerrar</span>
                </div>
            </div>
        </div>
    );
}
