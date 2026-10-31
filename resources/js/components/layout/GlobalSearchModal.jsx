import React, { useState, useEffect, useRef } from 'react';
import { router } from '@inertiajs/react';
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
} from 'lucide-react';

const SYSTEM_MODULES = [
    { title: 'Dashboard General', category: 'General', href: '/admin/dashboard', icon: LayoutDashboard },
    { title: 'Terminal de Caja (Punto de Venta)', category: 'Finanzas', href: '/admin/pos', icon: CreditCard },
    { title: 'Estudiantes & Expedientes', category: 'Académico', href: '/admin/students', icon: GraduationCap },
    { title: 'Docentes & Carga Horaria', category: 'Académico', href: '/admin/teachers', icon: UserCheck },
    { title: 'Cursos & Talleres', category: 'Académico', href: '/admin/courses', icon: BookOpen },
    { title: 'Carreras Universitarias', category: 'Académico', href: '/admin/careers', icon: Landmark },
    { title: 'Panel Financiero & Ingresos', category: 'Finanzas', href: '/admin/finance/dashboard', icon: TrendingUp },
    { title: 'Conceptos de Pago & Aranceles', category: 'Finanzas', href: '/admin/finance/payment-concepts', icon: Tag },
    { title: 'Cuentas por Pagar & Gastos', category: 'Finanzas', href: '/admin/finance/expenses', icon: Receipt },
    { title: 'Reportes DGII (606 / 607 / 608)', category: 'Finanzas', href: '/admin/finance/dgii-reports', icon: Landmark },
    { title: 'Asistencias & Reloj', category: 'RRHH', href: '/admin/hr/attendances', icon: Clock },
    { title: 'Nómina de Personal', category: 'RRHH', href: '/admin/hr/payroll', icon: Banknote },
    { title: 'Empleados & Expedientes', category: 'RRHH', href: '/admin/hr/employees', icon: Users },
    { title: 'Centro de Reportes', category: 'Reportes', href: '/admin/reports', icon: FileText },
    { title: 'Gestión de Usuarios & Roles', category: 'Sistema', href: '/admin/users', icon: ShieldCheck },
    { title: 'Ajustes del Sistema & Branding', category: 'Sistema', href: '/admin/settings', icon: Settings },
];

const STUDENT_MODULES = [
    { title: 'Mi Horario y Asignaturas', category: 'Académico', href: '/student/dashboard', icon: LayoutDashboard },
    { title: 'Selección de Asignaturas', category: 'Académico', href: '/student/selection', icon: BookOpen },
    { title: 'Pensum & Avance de Carrera', category: 'Académico', href: '/student/projection', icon: GraduationCap },
    { title: 'Aula Virtual (Moodle SSO)', category: 'Académico', href: '/student/moodle-auth', icon: BookOpen },
    { title: 'Mis Pagos & Facturas', category: 'Finanzas', href: '/student/payments', icon: CreditCard },
    { title: 'Solicitudes & Récords', category: 'Trámites', href: '/student/requests', icon: FileText },
    { title: 'Mi Perfil de Estudiante', category: 'Cuenta', href: '/student/profile', icon: Users },
];

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
                setStudentResults(data || []);
            } catch (err) {
                console.error('Error buscando estudiantes:', err);
            } finally {
                setSearchingStudents(false);
            }
        }, 200);

        return () => clearTimeout(timer);
    }, [query, isStudent]);

    if (!isOpen) return null;

    const modulePool = isStudent ? STUDENT_MODULES : SYSTEM_MODULES;
    const filteredModules = query.trim() 
        ? modulePool.filter(m => 
            m.title.toLowerCase().includes(query.toLowerCase()) || 
            m.category.toLowerCase().includes(query.toLowerCase())
          )
        : modulePool.slice(0, 6);

    const handleSelect = (href) => {
        onClose();
        if (href.startsWith('/admin/pos') || href.startsWith('/student/')) {
            router.visit(href);
        } else {
            window.location.href = href;
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
                        placeholder={isStudent ? "Buscar en mi portal, asignaturas, trámites, pagos... (Esc para salir)" : "Buscar módulos, estudiantes, finanzas... (Esc para salir)"}
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
                                                {s.name.charAt(0)}
                                            </div>
                                            <div className="truncate">
                                                <p className="text-xs font-bold text-gray-900 dark:text-white truncate">{s.name}</p>
                                                <p className="text-[11px] text-gray-400">{s.cedula} • {s.code}</p>
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
                    <div className="py-2 px-2">
                        <p className="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-1.5 px-2">
                            {query ? 'Módulos coincidentes' : 'Acceso Rápido'}
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
                </div>

                {/* Footer hints */}
                <div className="p-2.5 bg-gray-50 dark:bg-[#121212] border-t border-gray-100 dark:border-[#262626] flex items-center justify-between text-[11px] text-gray-400 px-4">
                    <span>Navega con flechas y selecciona con Enter</span>
                    <span className="font-semibold text-gray-500 dark:text-gray-400">SGA Search Engine</span>
                </div>
            </div>
        </div>
    );
}
