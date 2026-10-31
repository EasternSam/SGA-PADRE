import React, { useState, useEffect, useMemo } from 'react';
import { usePage, Link, router } from '@inertiajs/react';
import GlobalSearchModal from '../components/layout/GlobalSearchModal';
import ToastContainer from '../components/layout/ToastContainer';
import ModalStandardizer from '../components/layout/ModalStandardizer';
import {
    SidebarProvider,
    Sidebar,
    SidebarHeader,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuItem,
    SidebarMenuButton,
    SidebarMenuSub,
    SidebarMenuSubItem,
    SidebarMenuSubButton,
    SidebarRail,
    SidebarTrigger,
    SidebarInset,
    CollapsibleSidebarMenu,
    useSidebar,
} from '../components/ui/sidebar';
import { NotificationCenter } from '@/components/ui/NotificationCenter';
import {
    LayoutDashboard,
    TrendingUp,
    Zap,
    Calendar,
    Users,
    UserPlus,
    UserCheck,
    BookOpen,
    DollarSign,
    CreditCard,
    Receipt,
    Briefcase,
    GraduationCap,
    Award,
    FileText,
} from 'lucide-react';
import { TabBar } from '@/components/godui';
import ThemeSwitch from '@/components/ui/ThemeSwitch';


function getDynamicContextualTabs(rawUrl = '') {
    const cleanUrl = rawUrl ? rawUrl.split('?')[0] : '';

    // 1. Dashboard principal
    if (cleanUrl === '/admin/dashboard' || cleanUrl === '/admin' || cleanUrl === '/' || cleanUrl === '') {
        return {
            active: 'dashboard',
            tabs: [
                { value: 'dashboard', label: 'Inicio', icon: <LayoutDashboard className="size-4" />, action: () => window.scrollTo({ top: 0, behavior: 'smooth' }) },
                { value: 'academic', label: 'Carga Académica', icon: <TrendingUp className="size-4" />, action: () => {
                    const el = document.getElementById('carga-academica-section');
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    else router.visit('/admin/dashboard#carga-academica-section');
                }},
                { value: 'quick', label: 'Acciones', icon: <Zap className="size-4" />, action: () => {
                    const el = document.getElementById('acciones-rapidas-section');
                    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    else router.visit('/admin/dashboard#acciones-rapidas-section');
                }},
                { value: 'calendar', label: 'Calendario', icon: <Calendar className="size-4" />, action: () => router.visit('/admin/calendar') },
            ]
        };
    }

    // 2. Estudiantes
    if (cleanUrl.startsWith('/admin/students')) {
        return {
            active: 'students',
            tabs: [
                { value: 'students', label: 'Alumnos', icon: <Users className="size-4" />, action: () => cleanUrl === '/admin/students' ? window.scrollTo({ top: 0, behavior: 'smooth' }) : router.visit('/admin/students') },
                {
                    value: 'new',
                    label: 'Nuevo Alumno',
                    icon: <UserPlus className="size-4" />,
                    action: () => {
                        if (cleanUrl === '/admin/students') {
                            window.dispatchEvent(new CustomEvent('sga:open-create-student'));
                        } else {
                            router.visit('/admin/students?create=1');
                        }
                    },
                },
                { value: 'pos', label: 'Caja POS', icon: <DollarSign className="size-4" />, action: () => router.visit('/admin/pos') },
                { value: 'dashboard', label: 'Dashboard', icon: <LayoutDashboard className="size-4" />, action: () => router.visit('/admin/dashboard') },
            ]
        };
    }

    // 3. Docentes / Claustro
    if (cleanUrl.startsWith('/admin/teachers')) {
        return {
            active: 'teachers',
            tabs: [
                { value: 'teachers', label: 'Docentes', icon: <UserCheck className="size-4" />, action: () => cleanUrl === '/admin/teachers' ? window.scrollTo({ top: 0, behavior: 'smooth' }) : router.visit('/admin/teachers') },
                {
                    value: 'new',
                    label: 'Nuevo Docente',
                    icon: <UserPlus className="size-4" />,
                    action: () => {
                        if (cleanUrl === '/admin/teachers') {
                            window.dispatchEvent(new CustomEvent('sga:open-create-teacher'));
                        } else {
                            router.visit('/admin/teachers?create=1');
                        }
                    },
                },
                { value: 'courses', label: 'Cursos', icon: <BookOpen className="size-4" />, action: () => router.visit('/admin/courses') },
                { value: 'dashboard', label: 'Dashboard', icon: <LayoutDashboard className="size-4" />, action: () => router.visit('/admin/dashboard') },
            ]
        };
    }

    // 4. Calendario
    if (cleanUrl.startsWith('/admin/calendar')) {
        return {
            active: 'calendar',
            tabs: [
                { value: 'calendar', label: 'Agenda', icon: <Calendar className="size-4" />, action: () => window.scrollTo({ top: 0, behavior: 'smooth' }) },
                { value: 'courses', label: 'Cursos', icon: <BookOpen className="size-4" />, action: () => router.visit('/admin/courses') },
                { value: 'pos', label: 'Caja POS', icon: <DollarSign className="size-4" />, action: () => router.visit('/admin/pos') },
                { value: 'dashboard', label: 'Dashboard', icon: <LayoutDashboard className="size-4" />, action: () => router.visit('/admin/dashboard') },
            ]
        };
    }

    // 5. Caja POS / Finanzas / Facturación
    if (cleanUrl.startsWith('/admin/pos')) {
        return {
            active: 'pos',
            tabs: [
                { value: 'pos', label: 'Caja POS', icon: <DollarSign className="size-4" />, action: () => window.scrollTo({ top: 0, behavior: 'smooth' }) },
                { value: 'students', label: 'Alumnos', icon: <Users className="size-4" />, action: () => router.visit('/admin/students') },
                { value: 'finance', label: 'Finanzas', icon: <Receipt className="size-4" />, action: () => router.visit('/admin/finance/dashboard') },
                { value: 'dashboard', label: 'Dashboard', icon: <LayoutDashboard className="size-4" />, action: () => router.visit('/admin/dashboard') },
            ]
        };
    }

    // 6. Oferta Académica (Cursos, Carreras, Secciones)
    if (cleanUrl.startsWith('/admin/courses') || cleanUrl.startsWith('/admin/careers') || cleanUrl.startsWith('/admin/sections')) {
        return {
            active: cleanUrl.startsWith('/admin/careers') ? 'careers' : 'courses',
            tabs: [
                { value: 'courses', label: 'Cursos', icon: <BookOpen className="size-4" />, action: () => router.visit('/admin/courses') },
                { value: 'careers', label: 'Carreras', icon: <GraduationCap className="size-4" />, action: () => router.visit('/admin/careers') },
                { value: 'teachers', label: 'Docentes', icon: <UserCheck className="size-4" />, action: () => router.visit('/admin/teachers') },
                { value: 'dashboard', label: 'Dashboard', icon: <LayoutDashboard className="size-4" />, action: () => router.visit('/admin/dashboard') },
            ]
        };
    }

    // 7. Finanzas & Contabilidad
    if (cleanUrl.startsWith('/admin/finance') || cleanUrl.startsWith('/admin/accounting') || cleanUrl.startsWith('/admin/invoices')) {
        const isAccountingSubroute = cleanUrl.startsWith('/admin/finance/chart-of-accounts')
            || cleanUrl.startsWith('/admin/finance/manual-entry')
            || cleanUrl.startsWith('/admin/finance/ledger')
            || cleanUrl.startsWith('/admin/finance/statements')
            || cleanUrl.startsWith('/admin/finance/period-closing');
        return {
            active: isAccountingSubroute ? 'accounting' : 'finance',
            tabs: [
                { value: 'finance', label: 'Finanzas', icon: <DollarSign className="size-4" />, action: () => router.visit('/admin/finance/dashboard') },
                { value: 'accounting', label: 'Contabilidad', icon: <Receipt className="size-4" />, action: () => router.visit('/admin/finance/chart-of-accounts') },
                { value: 'pos', label: 'Caja POS', icon: <CreditCard className="size-4" />, action: () => router.visit('/admin/pos') },
                { value: 'dashboard', label: 'Dashboard', icon: <LayoutDashboard className="size-4" />, action: () => router.visit('/admin/dashboard') },
            ]
        };
    }

    // 8. Recursos Humanos / Nómina
    if (cleanUrl.startsWith('/admin/hr') || cleanUrl.startsWith('/admin/payroll') || cleanUrl.startsWith('/admin/employees')) {
        return {
            active: cleanUrl.startsWith('/admin/hr/payroll') ? 'payroll' : 'hr',
            tabs: [
                { value: 'hr', label: 'Personal', icon: <Briefcase className="size-4" />, action: () => router.visit('/admin/hr/employees') },
                { value: 'payroll', label: 'Nómina', icon: <DollarSign className="size-4" />, action: () => router.visit('/admin/hr/payroll') },
                { value: 'teachers', label: 'Docentes', icon: <UserCheck className="size-4" />, action: () => router.visit('/admin/teachers') },
                { value: 'dashboard', label: 'Dashboard', icon: <LayoutDashboard className="size-4" />, action: () => router.visit('/admin/dashboard') },
            ]
        };
    }

    // 9. Calificaciones / Evaluaciones / Asistencia
    if (cleanUrl.startsWith('/admin/grades') || cleanUrl.startsWith('/admin/evaluations') || cleanUrl.startsWith('/admin/attendance')) {
        return {
            active: 'courses',
            tabs: [
                { value: 'courses', label: 'Cursos', icon: <BookOpen className="size-4" />, action: () => router.visit('/admin/courses') },
                { value: 'students', label: 'Alumnos', icon: <Users className="size-4" />, action: () => router.visit('/admin/students') },
                { value: 'dashboard', label: 'Dashboard', icon: <LayoutDashboard className="size-4" />, action: () => router.visit('/admin/dashboard') },
            ]
        };
    }

    // 10. Certificados & Diplomas
    if (cleanUrl.startsWith('/admin/certificates') || cleanUrl.startsWith('/admin/certificate-templates')) {
        return {
            active: cleanUrl.startsWith('/admin/certificate-templates') ? 'templates' : 'certificates',
            tabs: [
                { value: 'certificates', label: 'Certificados', icon: <Award className="size-4" />, action: () => router.visit('/admin/certificates') },
                { value: 'templates', label: 'Plantillas', icon: <FileText className="size-4" />, action: () => router.visit('/admin/certificate-templates') },
                { value: 'students', label: 'Alumnos', icon: <Users className="size-4" />, action: () => router.visit('/admin/students') },
                { value: 'dashboard', label: 'Dashboard', icon: <LayoutDashboard className="size-4" />, action: () => router.visit('/admin/dashboard') },
            ]
        };
    }

    // 11. Portal Estudiante
    if (cleanUrl.startsWith('/student')) {
        return {
            active: cleanUrl.startsWith('/student/selection') ? 'student_selection'
                : cleanUrl.startsWith('/student/projection') ? 'student_projection'
                : cleanUrl.startsWith('/student/payments') ? 'student_payments'
                : cleanUrl.startsWith('/student/requests') ? 'student_requests'
                : cleanUrl.startsWith('/student/profile') ? 'student_profile'
                : 'student_dash',
            tabs: [
                { value: 'student_dash', label: 'Mi Portal', icon: <GraduationCap className="size-4" />, action: () => router.visit('/student/dashboard') },
                { value: 'student_selection', label: 'Selección', icon: <BookOpen className="size-4" />, action: () => router.visit('/student/selection') },
                { value: 'student_projection', label: 'Pensum', icon: <Award className="size-4" />, action: () => router.visit('/student/projection') },
                { value: 'student_payments', label: 'Mis Pagos', icon: <DollarSign className="size-4" />, action: () => router.visit('/student/payments') },
                { value: 'student_requests', label: 'Trámites', icon: <FileText className="size-4" />, action: () => router.visit('/student/requests') },
            ]
        };
    }

    // 12. Portal Docente
    if (cleanUrl.startsWith('/teacher')) {
        return {
            active: cleanUrl.includes('/grades') ? 'teacher_grades' : cleanUrl.includes('/attendance') ? 'teacher_classes' : 'teacher_dash',
            tabs: [
                { value: 'teacher_dash', label: 'Claustro', icon: <UserCheck className="size-4" />, action: () => router.visit('/teacher/dashboard') },
                { value: 'teacher_classes', label: 'Asistencia', icon: <BookOpen className="size-4" />, action: () => router.visit('/teacher/attendance') },
                { value: 'teacher_grades', label: 'Calificar', icon: <Award className="size-4" />, action: () => router.visit('/teacher/grades') },
            ]
        };
    }

    // Fallback general
    return {
        active: 'dashboard',
        tabs: [
            { value: 'dashboard', label: 'Inicio', icon: <LayoutDashboard className="size-4" />, action: () => router.visit('/admin/dashboard') },
            { value: 'students', label: 'Alumnos', icon: <Users className="size-4" />, action: () => router.visit('/admin/students') },
            { value: 'pos', label: 'Caja POS', icon: <DollarSign className="size-4" />, action: () => router.visit('/admin/pos') },
            { value: 'calendar', label: 'Calendario', icon: <Calendar className="size-4" />, action: () => router.visit('/admin/calendar') },
        ]
    };
}

function resolveSectionTitle(explicitTitle, rawUrl = '', institutionName = 'Academic+') {
    if (explicitTitle && String(explicitTitle).trim() !== '' && String(explicitTitle).trim() !== 'SGA') {
        return explicitTitle;
    }

    const cleanUrl = rawUrl ? rawUrl.split('?')[0].split('#')[0] : '';

    const routeMatchers = [
        { prefix: '/admin/dashboard', label: 'Panel de Control' },
        { prefix: '/admin/students/profile', label: 'Expediente del Estudiante' },
        { prefix: '/admin/students', label: 'Gestión de Estudiantes' },
        { prefix: '/admin/teachers/profile', label: 'Ficha del Docente' },
        { prefix: '/admin/teachers', label: 'Directorio de Docentes' },
        { prefix: '/admin/courses', label: 'Cursos Técnicos' },
        { prefix: '/admin/careers', label: 'Carreras Universitarias' },
        { prefix: '/admin/sections', label: 'Secciones Escolares' },
        { prefix: '/admin/schedules', label: 'Constructor de Horarios' },
        { prefix: '/admin/calendar', label: 'Calendario Académico' },
        { prefix: '/admin/requests', label: 'Trámites y Solicitudes' },
        { prefix: '/admin/admissions', label: 'Admisiones y Nuevo Ingreso' },
        { prefix: '/admin/classrooms', label: 'Aulas y Espacios Físicos' },
        { prefix: '/admin/grades/locks', label: 'Cierre de Actas' },
        { prefix: '/admin/grades', label: 'Sábana de Calificaciones' },
        { prefix: '/admin/attendance/justifications', label: 'Excusas y Justificaciones' },
        { prefix: '/admin/attendance', label: 'Pase de Lista y Asistencia' },
        { prefix: '/admin/pos', label: 'Terminal de Caja' },
        { prefix: '/admin/finance/dashboard', label: 'Panel Financiero' },
        { prefix: '/admin/finance/payment-concepts', label: 'Conceptos de Pago' },
        { prefix: '/admin/finance/chart-of-accounts', label: 'Catálogo de Cuentas' },
        { prefix: '/admin/finance/manual-entry', label: 'Asiento Contable Manual' },
        { prefix: '/admin/finance/ledger', label: 'Libro Mayor' },
        { prefix: '/admin/finance/statements', label: 'Estados Financieros' },
        { prefix: '/admin/finance/period-closing', label: 'Cierre de Período' },
        { prefix: '/admin/finance/expenses', label: 'Cuentas por Pagar y Gastos' },
        { prefix: '/admin/finance/dgii-reports', label: 'Reportes Fiscales DGII' },
        { prefix: '/admin/hr/employees', label: 'Directorio de Empleados' },
        { prefix: '/admin/hr/attendances', label: 'Control de Asistencia (Reloj)' },
        { prefix: '/admin/hr/payroll', label: 'Nómina de Pago' },
        { prefix: '/admin/reports', label: 'Centro de Reportes' },
        { prefix: '/admin/certificate-editor', label: 'Diseñador de Certificados' },
        { prefix: '/admin/certificates', label: 'Plantillas de Certificados' },
        { prefix: '/admin/certificate-templates', label: 'Plantillas de Certificados' },
        { prefix: '/admin/users', label: 'Gestión de Usuarios' },
        { prefix: '/admin/activity-logs', label: 'Registro de Auditoría' },
        { prefix: '/admin/settings', label: 'Configuración Global' },
        { prefix: '/admin/profile', label: 'Mi Perfil' },
        { prefix: '/student/selection', label: 'Selección de Asignaturas' },
        { prefix: '/student/projection', label: 'Pensum y Avance Académico' },
        { prefix: '/student/payments', label: 'Mis Pagos y Facturas' },
        { prefix: '/student/requests', label: 'Solicitudes y Récords' },
        { prefix: '/student/profile', label: 'Mi Perfil' },
        { prefix: '/student', label: 'Portal del Estudiante' },
        { prefix: '/teacher/grades', label: 'Libro de Calificaciones' },
        { prefix: '/teacher/attendance', label: 'Pase de Lista Docente' },
        { prefix: '/teacher/payroll', label: 'Mis Volantes de Nómina' },
        { prefix: '/teacher', label: 'Portal del Docente' },
        { prefix: '/applicant', label: 'Portal del Aspirante' },
    ];

    for (const matcher of routeMatchers) {
        if (cleanUrl === matcher.prefix || cleanUrl.startsWith(matcher.prefix + '/')) {
            return matcher.label;
        }
    }

    return institutionName || 'Academic+';
}

function AuthenticatedContent({ title, children }) {
    const { auth, branding, notifications: serverNotifications = [] } = usePage().props;
    const url = usePage().url;
        const { isMobile, state, openMobile, setOpenMobile, toggleSidebar } = useSidebar();

    const contextualNav = React.useMemo(() => getDynamicContextualTabs(url), [url]);
    const [activeTab, setActiveTab] = useState(contextualNav.active);

    useEffect(() => {
        setActiveTab(contextualNav.active);
    }, [url, contextualNav.active]);

    // Dynamic Synchronization of the 4-Color Institutional Palette
    useEffect(() => {
        const palette = branding?.palette || {};
        const p = palette.primary || branding?.primary_color || '#211170';
        const s = palette.secondary || branding?.secondary_color || '#1a0a53';
        const a = palette.accent || branding?.accent_color || '#f7931e';
        const n = palette.neutral || branding?.neutral_color || '#64748b';

        const hexToRgb = (hex, def) => {
            if (!hex) return def;
            let c = hex.replace('#', '').trim();
            if (c.length === 3) c = c.split('').map(x => x + x).join('');
            if (c.length !== 6) return def;
            const num = parseInt(c, 16);
            if (isNaN(num)) return def;
            return `${(num >> 16) & 255} ${(num >> 8) & 255} ${num & 255}`;
        };

        const root = document.documentElement;
        root.style.setProperty('--brand-primary', p);
        root.style.setProperty('--brand-secondary', s);
        root.style.setProperty('--brand-accent', a);
        root.style.setProperty('--brand-neutral', n);

        root.style.setProperty('--palette-primary', p);
        root.style.setProperty('--palette-secondary', s);
        root.style.setProperty('--palette-accent', a);
        root.style.setProperty('--palette-neutral', n);

        root.style.setProperty('--color-primary', hexToRgb(p, '33 17 112'));
        root.style.setProperty('--color-secondary', hexToRgb(s, '26 10 83'));
        root.style.setProperty('--color-accent', hexToRgb(a, '247 147 30'));
        root.style.setProperty('--color-neutral', hexToRgb(n, '100 116 139'));

        const logoH = Math.min(120, Math.max(24, Number(branding?.sidebar_logo_height) || 44));
        root.style.setProperty('--sidebar-logo-height', `${logoH}px`);
    }, [branding]);

    const handleTabChange = (val) => {
        setActiveTab(val);
        const item = contextualNav.tabs.find(t => t.value === val);
        if (item?.action) {
            item.action();
        }
    };

    const [userDropdownOpen, setUserDropdownOpen] = useState(false);
    const [systemNotifications, setSystemNotifications] = useState(serverNotifications);

    useEffect(() => {
        if (serverNotifications) {
            setSystemNotifications(serverNotifications);
        }
    }, [serverNotifications]);

    const handleNotificationReadChange = (item, nextRead) => {
        setSystemNotifications(prev => prev.map(n => n.id === item.id ? { ...n, read: nextRead } : n));
        const url = `/notifications/${item.id}/read`;
        if (window.axios) {
            window.axios.post(url, { read: nextRead }).catch(err => {
                console.error('Error al actualizar notificación:', err);
            });
        } else {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ read: nextRead })
            }).catch(err => console.error('Error al actualizar notificación:', err));
        }
    };

    const handleNotificationDismiss = (item) => {
        setSystemNotifications(prev => prev.filter(n => n.id !== item.id));
        const url = `/notifications/${item.id}`;
        if (window.axios) {
            window.axios.delete(url).catch(err => {
                console.error('Error al eliminar notificación:', err);
            });
        } else {
            fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                }
            }).catch(err => console.error('Error al eliminar notificación:', err));
        }
    };

    const [darkMode, setDarkMode] = useState(false);
    const [searchModalOpen, setSearchModalOpen] = useState(false);
    const [academicOpen, setAcademicOpen] = useState(() => {
        return url.startsWith('/admin/courses') || url.startsWith('/admin/careers');
    });
    const [diplomasOpen, setDiplomasOpen] = useState(() => {
        return url.startsWith('/admin/certificates') || url.startsWith('/admin/certificate-templates');
    });

    const [isNavigating, setIsNavigating] = useState(false);

    // Dynamic Inertia Navigation transition tracker (like Bills SPA)
    useEffect(() => {
        const unbindStart = router.on('start', () => setIsNavigating(true));
        const unbindFinish = router.on('finish', () => setIsNavigating(false));
        const unbindError = router.on('error', () => setIsNavigating(false));
        return () => {
            unbindStart();
            unbindFinish();
            unbindError();
        };
    }, []);

    useEffect(() => {
        const isDark = localStorage.getItem('darkMode') === 'true' || 
            (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches);
        setDarkMode(isDark);
        if (isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    }, []);

    // Atajo de teclado Ctrl+K / Ctrl+K para Búsqueda Global
    useEffect(() => {
        const handleKeyDown = (e) => {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                setSearchModalOpen(prev => !prev);
            }
        };
        window.addEventListener('keydown', handleKeyDown);
        return () => window.removeEventListener('keydown', handleKeyDown);
    }, []);

    const toggleDarkMode = () => {
        const next = !darkMode;
        setDarkMode(next);
        localStorage.setItem('darkMode', String(next));
        if (next) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    };

    const handleLogout = (e) => {
        e.preventDefault();
        router.post('/logout', {}, {
            onFinish: () => {
                window.location.href = '/login';
            },
        });
    };

    const primaryColor = branding?.primary_color || '#211170';
    const navbarType = branding?.navbar_type || 'gradient';
    const navGradientStart = (branding?.navbar_gradient_start && branding?.navbar_gradient_start !== '#211170')
        ? branding.navbar_gradient_start
        : primaryColor;
    const navGradientEnd = branding?.navbar_gradient_end || '#1a0a53';
    const navGradientDir = branding?.navbar_gradient_direction || 'to bottom right';

    let navBg;
    if (darkMode) {
        navBg = '#171717';
    } else if (navbarType === 'solid') {
        navBg = primaryColor;
    } else {
        navBg = `linear-gradient(${navGradientDir}, ${navGradientStart}, ${navGradientEnd})`;
    }
    const logoUrl = branding?.logo || '/branding/academic-logo.png';
    const sidebarLogoHeight = Math.min(120, Math.max(24, Number(branding?.sidebar_logo_height) || 44));
    const faviconUrl = branding?.favicon || '/branding/academic-favicon.png';
    const institutionName = branding?.name || 'Academic+';
    const userName = auth?.user?.name || 'Admin General';
    const userEmail = auth?.user?.email || 'admin@admin.com';
    const userRoles = auth?.user?.roles || ['Admin'];
    const hasRole = (role) => userRoles.includes(role);
    const hasAnyRole = (roles) => roles.some(r => userRoles.includes(r));
    const isStudent = hasRole('Estudiante') || url.startsWith('/student');
    const isTeacher = hasRole('Docente') || url.startsWith('/teacher');

    const isActive = (href) => {
        if (href === '/admin/dashboard' || href === '/dashboard') {
            return url === '/admin/dashboard' || url === '/dashboard';
        }
        if (href === '/student/dashboard') {
            return url === '/student/dashboard' || url === '/student';
        }
        return url === href || url.startsWith(href + '/');
    };

    const homeUrl = isStudent ? '/student/dashboard' : isTeacher ? '/teacher/dashboard' : '/admin/dashboard';

    return (
        <div className="h-screen flex w-full overflow-hidden bg-gray-50 dark:bg-[#0A0A0A] font-sans antialiased text-slate-600 dark:text-neutral-300">
            {/* Modal de Búsqueda Global Ctrl+K */}
            <GlobalSearchModal isOpen={searchModalOpen} onClose={() => setSearchModalOpen(false)} isStudent={isStudent} />

            {/* Notificaciones Flotantes Flash */}
            <ToastContainer />
            <ModalStandardizer />

            {/* Sidebar Institucional SGA con Shadcn / Radix / Animate UI */}
            <Sidebar 
                collapsible="icon" 
                style={{ background: navBg, color: 'white' }}
                className={darkMode ? "border-r border-[#262626]" : "border-r border-white/10"}
            >
                {/* Header / Logo Sidebar adaptable a modo Icono */}
                <SidebarHeader className={`px-4 pb-3 pt-3.5 border-b shrink-0 group-data-[collapsible=icon]:p-2.5 ${darkMode ? 'border-[#262626]' : 'border-white/10'}`}>
                    <div className="flex items-center justify-between w-full group-data-[collapsible=icon]:justify-center">
                        <Link href={homeUrl} className="flex items-center justify-center transition-transform hover:scale-[1.02]">
                            <img 
                                src={logoUrl} 
                                alt={institutionName} 
                                style={{
                                    height: `var(--sidebar-logo-height, ${sidebarLogoHeight}px)`,
                                    maxHeight: `var(--sidebar-logo-height, ${sidebarLogoHeight}px)`,
                                }}
                                className="w-auto max-w-[215px] object-contain drop-shadow transition-all duration-200 group-data-[collapsible=icon]:hidden" 
                            />
                            <img 
                                src={faviconUrl} 
                                alt={institutionName} 
                                className="hidden h-9 w-9 rounded-lg object-contain drop-shadow transition-all duration-300 group-data-[collapsible=icon]:block" 
                            />
                        </Link>
                        {isMobile && (
                            <button 
                                onClick={() => setOpenMobile(false)}
                                className="p-1.5 rounded-lg text-white/60 hover:text-white hover:bg-white/10"
                                aria-label="Cerrar menú"
                            >
                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        )}
                    </div>
                </SidebarHeader>

                {/* Contenido / Enlaces del Sidebar */}
                <SidebarContent>
                    {isStudent ? (
                        <>
                            {/* Dashboard Estudiante */}
                            <SidebarMenu>
                                <SidebarMenuItem>
                                    <SidebarMenuButton 
                                        href="/student/dashboard" 
                                        isActive={isActive('/student/dashboard')}
                                        tooltip="Mi Portal"
                                    >
                                        <LayoutDashboard className="h-[18px] w-[18px] shrink-0 text-white/80" />
                                        <span>Mi Portal</span>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            </SidebarMenu>

                            {/* SECCIÓN GESTIÓN ACADÉMICA ESTUDIANTE */}
                            <SidebarGroup>
                                <SidebarGroupLabel>
                                    Académico
                                </SidebarGroupLabel>
                                <SidebarMenu>
                                    <SidebarMenuItem>
                                        <SidebarMenuButton 
                                            href="/student/dashboard" 
                                            isActive={isActive('/student/dashboard')}
                                            tooltip="Mis Asignaturas y Horario"
                                        >
                                            <BookOpen className="h-[18px] w-[18px] shrink-0 text-white/80" />
                                            <span>Mis Asignaturas</span>
                                        </SidebarMenuButton>
                                    </SidebarMenuItem>

                                    <SidebarMenuItem>
                                        <SidebarMenuButton 
                                            href="/student/selection" 
                                            isActive={isActive('/student/selection')}
                                            tooltip="Selección de Materias"
                                        >
                                            <Calendar className="h-[18px] w-[18px] shrink-0 text-white/80" />
                                            <span>Selección de Materias</span>
                                        </SidebarMenuButton>
                                    </SidebarMenuItem>

                                    <SidebarMenuItem>
                                        <SidebarMenuButton 
                                            href="/student/projection" 
                                            isActive={isActive('/student/projection')}
                                            tooltip="Pensum & Avance"
                                        >
                                            <GraduationCap className="h-[18px] w-[18px] shrink-0 text-white/80" />
                                            <span>Pensum & Avance</span>
                                        </SidebarMenuButton>
                                    </SidebarMenuItem>

                                    <SidebarMenuItem>
                                        <SidebarMenuButton 
                                            href="/student/moodle-auth" 
                                            target="_blank"
                                            tooltip="Aula Virtual (Moodle)"
                                        >
                                            <Award className="h-[18px] w-[18px] shrink-0 text-white/80" />
                                            <span>Aula Virtual (Moodle)</span>
                                        </SidebarMenuButton>
                                    </SidebarMenuItem>
                                </SidebarMenu>
                            </SidebarGroup>

                            {/* SECCIÓN FINANZAS ESTUDIANTE */}
                            <SidebarGroup>
                                <SidebarGroupLabel>
                                    Finanzas
                                </SidebarGroupLabel>
                                <SidebarMenu>
                                    <SidebarMenuItem>
                                        <SidebarMenuButton 
                                            href="/student/payments" 
                                            isActive={isActive('/student/payments')}
                                            tooltip="Mis Pagos & Facturas"
                                        >
                                            <CreditCard className="h-[18px] w-[18px] shrink-0 text-white/80" />
                                            <span>Mis Pagos & Facturas</span>
                                        </SidebarMenuButton>
                                    </SidebarMenuItem>
                                </SidebarMenu>
                            </SidebarGroup>

                            {/* SECCIÓN TRÁMITES Y SERVICIOS ESTUDIANTE */}
                            <SidebarGroup>
                                <SidebarGroupLabel>
                                    Trámites & Servicios
                                </SidebarGroupLabel>
                                <SidebarMenu>
                                    <SidebarMenuItem>
                                        <SidebarMenuButton 
                                            href="/student/requests" 
                                            isActive={isActive('/student/requests')}
                                            tooltip="Solicitudes y Récords"
                                        >
                                            <FileText className="h-[18px] w-[18px] shrink-0 text-white/80" />
                                            <span>Solicitudes & Récords</span>
                                        </SidebarMenuButton>
                                    </SidebarMenuItem>
                                </SidebarMenu>
                            </SidebarGroup>

                            {/* SECCIÓN MI CUENTA */}
                            <SidebarGroup>
                                <SidebarGroupLabel>
                                    Mi Cuenta
                                </SidebarGroupLabel>
                                <SidebarMenu>
                                    <SidebarMenuItem>
                                        <SidebarMenuButton 
                                            href="/student/profile" 
                                            isActive={isActive('/student/profile')}
                                            tooltip="Mi Perfil"
                                        >
                                            <UserCheck className="h-[18px] w-[18px] shrink-0 text-white/80" />
                                            <span>Mi Perfil</span>
                                        </SidebarMenuButton>
                                    </SidebarMenuItem>
                                </SidebarMenu>
                            </SidebarGroup>
                        </>
                    ) : (
                        <>
                            {/* Dashboard */}
                            <SidebarMenu>
                        <SidebarMenuItem>
                            <SidebarMenuButton 
                                href="/admin/dashboard" 
                                isActive={isActive('/admin/dashboard')}
                                tooltip="Dashboard"
                            >
                                <svg className="h-[18px] w-[18px] shrink-0 text-white/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M11.47 3.84a.75.75 0 0 1 1.06 0l8.635 8.635a.75.75 0 1 1-1.06 1.06l-.312-.31V21a.75.75 0 0 1-.75.75H2.75a.75.75 0 0 1-.75-.75v-7.775l-.31.31a.75.75 0 1 1-1.06-1.06l8.635-8.635ZM18.75 12.016v-2.072l-6.75-6.75-6.75 6.75v2.072h13.5Z" />
                                </svg>
                                <span>Dashboard</span>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </SidebarMenu>

                    {/* SECCIÓN GESTIÓN ACADÉMICA (Admin | Registro) */}
                    {(hasAnyRole(['Admin', 'Registro'])) && (
                        <SidebarGroup>
                            <SidebarGroupLabel>
                                Gestión Académica
                            </SidebarGroupLabel>
                            <SidebarMenu>
                                <SidebarMenuItem>
                                    <SidebarMenuButton 
                                        href="/admin/students" 
                                        isActive={isActive('/admin/students')}
                                        tooltip="Estudiantes"
                                    >
                                        <svg className="h-[18px] w-[18px] shrink-0 text-white/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path fillRule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clipRule="evenodd" />
                                        </svg>
                                        <span>Estudiantes</span>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>

                                <SidebarMenuItem>
                                    <SidebarMenuButton 
                                        href="/admin/teachers" 
                                        isActive={isActive('/admin/teachers')}
                                        tooltip="Docentes"
                                    >
                                        <svg className="h-[18px] w-[18px] shrink-0 text-white/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M11.7 2.805a.75.75 0 0 1 .6 0A60.65 60.65 0 0 1 22.83 8.72a.75.75 0 0 1-.231 1.337 49.949 49.949 0 0 0-9.902 3.912l-.003.002c-.114.06-.227.119-.343.18a.75.75 0 0 1-.707 0c-.116-.061-.23-.12-.343-.18a49.949 49.949 0 0 0-9.902-3.912.75.75 0 0 1-.231-1.337A60.653 60.653 0 0 1 11.7 2.805Z" />
                                            <path d="M13.06 15.473a48.45 48.45 0 0 1 7.666-3.282c.134 1.414.22 2.843.255 4.285a.75.75 0 0 1-.46.71 47.878 47.878 0 0 0-8.105 4.342.75.75 0 0 1-.832 0 47.877 47.877 0 0 0-8.104-4.342.75.75 0 0 1-.461-.71c.035-1.442.121-2.87.255-4.286A48.4 48.4 0 0 1 6 13.18v2.296c0 2.267.18 4.505.54 6.713a.75.75 0 0 1-1.485.276c-.5-3.07-.75-6.195-.75-9.315a.75.75 0 0 1 .631-.74A47.98 47.98 0 0 0 11.963 2.37a.75.75 0 0 1 1.074 0c.055.056.11.11.166.166.056.056.11.11.166.166a.75.75 0 1 1-1.074 1.074 46.471 46.471 0 0 1-5.118-4.048c.07-.157.14-.312.212-.465A48.369 48.369 0 0 1 13.06 15.473Z" />
                                        </svg>
                                        <span>Docentes</span>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>

                                {/* Desplegable Académico */}
                                <SidebarMenuItem>
                                    <SidebarMenuButton 
                                        onClick={() => setAcademicOpen(!academicOpen)}
                                        isActive={isActive('/admin/courses') || isActive('/admin/careers')}
                                        tooltip="Académico"
                                    >
                                        <svg className="h-[18px] w-[18px] shrink-0 text-white/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M11.25 4.533A9.707 9.707 0 0 0 6 3a9.735 9.735 0 0 0-3.25.555.75.75 0 0 0-.5.707v14.25a.75.75 0 0 0 1 .707A8.237 8.237 0 0 1 6 18.75c1.995 0 3.823.707 5.25 1.886V4.533ZM12.75 20.636A8.214 8.214 0 0 1 18 18.75c.966 0 1.89.166 2.75.47a.75.75 0 0 0 1-.708V4.262a.75.75 0 0 0-.5-.707A9.735 9.735 0 0 0 18 3a9.707 9.707 0 0 0-5.25 1.533v16.103Z" />
                                        </svg>
                                        <span className="flex-1 text-left">Académico</span>
                                        <svg className={`h-3.5 w-3.5 transform transition-transform duration-200 opacity-60 ${academicOpen ? 'rotate-180' : ''}`} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path fillRule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clipRule="evenodd" />
                                        </svg>
                                    </SidebarMenuButton>
                                    <CollapsibleSidebarMenu open={academicOpen}>
                                        <SidebarMenuSub>
                                            <SidebarMenuSubItem>
                                                <SidebarMenuSubButton 
                                                    href="/admin/courses" 
                                                    isActive={isActive('/admin/courses')}
                                                >
                                                    Cursos
                                                </SidebarMenuSubButton>
                                            </SidebarMenuSubItem>
                                            <SidebarMenuSubItem>
                                                <SidebarMenuSubButton 
                                                    href="/admin/careers" 
                                                    isActive={isActive('/admin/careers')}
                                                >
                                                    Carreras
                                                </SidebarMenuSubButton>
                                            </SidebarMenuSubItem>
                                        </SidebarMenuSub>
                                    </CollapsibleSidebarMenu>
                                </SidebarMenuItem>

                                <SidebarMenuItem>
                                    <SidebarMenuButton 
                                        href="/admin/calendar" 
                                        isActive={isActive('/admin/calendar')}
                                        tooltip="Calendario"
                                    >
                                        <svg className="h-[18px] w-[18px] shrink-0 text-white/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12.75 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM7.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM8.25 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM9.75 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM10.5 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM12 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM12.75 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM14.25 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 17.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 15.75a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5ZM15 12.75a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0ZM16.5 13.5a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Z" />
                                            <path fillRule="evenodd" d="M6.75 2.25A.75.75 0 0 1 7.5 3v1.5h9V3A.75.75 0 0 1 18 3v1.5h.75a3 3 0 0 1 3 3v11.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V7.5a3 3 0 0 1 3-3H6V3a.75.75 0 0 1 .75-.75Zm13.5 9a1.5 1.5 0 0 0-1.5-1.5H5.25a1.5 1.5 0 0 0-1.5 1.5v7.5a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5v-7.5Z" clipRule="evenodd" />
                                        </svg>
                                        <span>Calendario</span>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>

                                <SidebarMenuItem>
                                    <SidebarMenuButton 
                                        href="/admin/requests" 
                                        isActive={isActive('/admin/requests')}
                                        tooltip="Trámites"
                                    >
                                        <svg className="h-[18px] w-[18px] shrink-0 text-white/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path fillRule="evenodd" d="M10.125 2.25h-4.5c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125v-9M10.125 2.25h.375c.621 0 1.125.504 1.125 1.125v.375M10.125 2.25v3.375c0 .621.504 1.125 1.125 1.125h3.375M9 15l2.25 2.25L15 15m-6 6h6" />
                                        </svg>
                                        <span>Trámites</span>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>

                                <SidebarMenuItem>
                                    <SidebarMenuButton 
                                        href="/admin/admissions" 
                                        isActive={isActive('/admin/admissions')}
                                        tooltip="Admisiones"
                                    >
                                        <svg className="h-[18px] w-[18px] shrink-0 text-white/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M4.5 3.75a3 3 0 0 0-3 3v.75h21v-.75a3 3 0 0 0-3-3h-15Z" />
                                            <path fillRule="evenodd" d="M22.5 9.75h-21v7.5a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3v-7.5Zm-18 3.75a.75.75 0 0 1 .75-.75h6a.75.75 0 0 1 0 1.5h-6a.75.75 0 0 1-.75-.75Zm.75 2.25a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" clipRule="evenodd" />
                                        </svg>
                                        <span>Admisiones</span>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>

                                <SidebarMenuItem>
                                    <SidebarMenuButton 
                                        href="/admin/inventory" 
                                        isActive={isActive('/admin/inventory')}
                                        tooltip="Inventario"
                                    >
                                        <svg className="h-[18px] w-[18px] shrink-0 text-white/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path fillRule="evenodd" d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0 0 16.5 9h-1.875a1.875 1.875 0 0 1-1.875-1.875V5.25A3.75 3.75 0 0 0 9 1.5H5.625ZM7.5 15a.75.75 0 0 1 .75-.75h7.5a.75.75 0 0 1 0 1.5h-7.5A.75.75 0 0 1 7.5 15Zm.75 2.25a.75.75 0 0 0 0 1.5h7.5a.75.75 0 0 0 0-1.5h-7.5Z" clipRule="evenodd" />
                                        </svg>
                                        <span>Inventario</span>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            </SidebarMenu>
                        </SidebarGroup>
                    )}

                    {/* SECCIÓN FINANZAS (Admin | Contabilidad | Caja) */}
                    {(hasAnyRole(['Admin', 'Contabilidad', 'Caja'])) && (
                        <SidebarGroup>
                            <SidebarGroupLabel>
                                Finanzas
                            </SidebarGroupLabel>
                            <SidebarMenu>
                                <SidebarMenuItem>
                                    <SidebarMenuButton 
                                        href="/admin/pos" 
                                        isActive={isActive('/admin/pos')}
                                        tooltip="Terminal de Caja"
                                    >
                                        <svg className="h-[18px] w-[18px] shrink-0 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M4.5 3.75a3 3 0 0 0-3 3v.75h21v-.75a3 3 0 0 0-3-3h-15Z" />
                                            <path fillRule="evenodd" d="M22.5 9.75h-21v7.5a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3v-7.5Zm-18 3.75a.75.75 0 0 1 .75-.75h6a.75.75 0 0 1 0 1.5h-6a.75.75 0 0 1-.75-.75Zm.75 2.25a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" clipRule="evenodd" />
                                        </svg>
                                        <span className="font-bold">Terminal de Caja</span>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>

                                <SidebarMenuItem>
                                    <SidebarMenuButton 
                                        href="/admin/finance/dashboard" 
                                        isActive={isActive('/admin/finance/dashboard')}
                                        tooltip="Panel Financiero"
                                    >
                                        <svg className="h-[18px] w-[18px] shrink-0 text-white/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M12 7.5a2.25 2.25 0 1 0 0 4.5 2.25 2.25 0 0 0 0-4.5Z" />
                                            <path fillRule="evenodd" d="M1.5 4.875C1.5 3.839 2.34 3 3.375 3h17.25c1.035 0 1.875.84 1.875 1.875v9.75c0 1.036-.84 1.875-1.875 1.875H3.375A1.875 1.875 0 0 1 1.5 14.625v-9.75ZM8.25 9.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM18.75 9a.75.75 0 0 0-.75.75v.008c0 .414.336.75.75.75h.008a.75.75 0 0 0 .75-.75V9.75a.75.75 0 0 0-.75-.75h-.008ZM4.5 9.75A.75.75 0 0 1 5.25 9h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H5.25a.75.75 0 0 1-.75-.75V9.75Z" clipRule="evenodd" />
                                            <path d="M2.25 18a.75.75 0 0 0 0 1.5c5.4 0 10.63.722 15.6 2.075 1.19.324 2.4-.558 2.4-1.82V18.75a.75.75 0 0 0-.75-.75H2.25Z" />
                                        </svg>
                                        <span>Panel Financiero</span>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>

                                {hasAnyRole(['Admin', 'Contabilidad']) && (
                                    <>
                                        <SidebarMenuItem>
                                            <SidebarMenuButton 
                                                href="/admin/finance/payment-concepts" 
                                                isActive={isActive('/admin/finance/payment-concepts')}
                                                tooltip="Conceptos Pago"
                                            >
                                                <svg className="h-[18px] w-[18px] shrink-0 text-white/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                    <path fillRule="evenodd" d="M11.078 2.25c-.917 0-1.699.663-1.85 1.567L9.05 4.889c-.02.12-.115.26-.297.348a7.493 7.493 0 0 0-.986.57c-.166.115-.334.126-.45.083L6.3 5.508a1.875 1.875 0 0 0-2.282.819l-.922 1.597a1.875 1.875 0 0 0 .432 2.385l.84.692c.095.078.17.229.154.43a7.598 7.598 0 0 0 0 1.139c.015.2-.059.352-.153.43l-.841.692a1.875 1.875 0 0 0-.432 2.385l.922 1.597a1.875 1.875 0 0 0 2.282.818l1.019-.382c.115-.043.283-.031.45.082.312.214.641.405.985.57.182.088.277.228.297.35l.178 1.071c.151.904.933 1.567 1.85 1.567h1.844c.916 0 1.699-.663 1.85-1.567l.178-1.072c.02-.12.114-.26.297-.349.344-.165.673-.356.985-.57.167-.114.335-.125.45-.082l1.02.382a1.875 1.875 0 0 0 2.28-.819l.923-1.597a1.875 1.875 0 0 0-.432-2.385l-.84-.692c-.114.043-.282.031-.449-.083a7.49 7.49 0 0 0-.985-.57c-.183-.087-.277-.227-.297-.348l-.179-1.072a1.875 1.875 0 0 0-1.85-1.567h-1.843ZM12 15.75a3.75 3.75 0 1 0 0-7.5 3.75 3.75 0 0 0 0 7.5Z" clipRule="evenodd" />
                                                </svg>
                                                <span>Conceptos Pago</span>
                                            </SidebarMenuButton>
                                        </SidebarMenuItem>

                                        <SidebarMenuItem>
                                            <SidebarMenuButton 
                                                href="/admin/finance/chart-of-accounts" 
                                                isActive={isActive('/admin/finance/chart-of-accounts')}
                                                tooltip="Catálogo CC"
                                            >
                                                <svg className="h-[18px] w-[18px] shrink-0 text-white/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                    <path fillRule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm13.36-1.814a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clipRule="evenodd" />
                                                </svg>
                                                <span>Catálogo CC</span>
                                            </SidebarMenuButton>
                                        </SidebarMenuItem>

                                        <SidebarMenuItem>
                                            <SidebarMenuButton 
                                                href="/admin/finance/manual-entry" 
                                                isActive={isActive('/admin/finance/manual-entry')}
                                                tooltip="Asiento Manual"
                                            >
                                                <svg className="h-[18px] w-[18px] shrink-0 text-white/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                    <path d="M21.731 2.269a2.625 2.625 0 00-3.712 0l-1.157 1.158 3.71 3.71 1.159-1.157a2.625 2.625 0 000-3.711z" />
                                                    <path d="M10.75 13a4.156 4.156 0 011.66-3.329l5.122-5.123-3.71-3.71-5.123 5.122A4.156 4.156 0 015.371 7.62l-1.766 1.767c-.22.22-.36.505-.407.817L2.03 15.65a.75.75 0 00.865.865l5.446-1.168a1.5 1.5 0 00.817-.407l1.767-1.766z" />
                                                </svg>
                                                <span>Asiento Manual</span>
                                            </SidebarMenuButton>
                                        </SidebarMenuItem>

                                        <SidebarMenuItem>
                                            <SidebarMenuButton 
                                                href="/admin/finance/expenses" 
                                                isActive={isActive('/admin/finance/expenses')}
                                                tooltip="Cuentas por Pagar"
                                            >
                                                <svg className="h-[18px] w-[18px] shrink-0 text-white/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                    <path fillRule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM9 7.5A1.5 1.5 0 0 0 7.5 9v1.5h9V9A1.5 1.5 0 0 0 15 7.5H9Zm6.5 4.5h-7A1.5 1.5 0 0 0 7 13.5V15h10v-1.5A1.5 1.5 0 0 0 15.5 12Z" clipRule="evenodd" />
                                                </svg>
                                                <span>Cuentas por Pagar</span>
                                            </SidebarMenuButton>
                                        </SidebarMenuItem>

                                        <SidebarMenuItem>
                                            <SidebarMenuButton 
                                                href="/admin/finance/dgii-reports" 
                                                isActive={isActive('/admin/finance/dgii-reports')}
                                                tooltip="Reportes DGII"
                                            >
                                                <svg className="h-[18px] w-[18px] shrink-0 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24" strokeWidth="2">
                                                    <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                                </svg>
                                                <span>Reportes DGII</span>
                                            </SidebarMenuButton>
                                        </SidebarMenuItem>
                                    </>
                                )}
                            </SidebarMenu>
                        </SidebarGroup>
                    )}

                    {/* SECCIÓN RECURSOS HUMANOS (Admin | Contabilidad) */}
                    {(hasAnyRole(['Admin', 'Contabilidad'])) && (
                        <SidebarGroup>
                            <SidebarGroupLabel>
                                Recursos Humanos
                            </SidebarGroupLabel>
                            <SidebarMenu>
                                <SidebarMenuItem>
                                    <SidebarMenuButton 
                                        href="/admin/hr/employees" 
                                        isActive={isActive('/admin/hr/employees')}
                                        tooltip="Empleados"
                                    >
                                        <svg className="h-[18px] w-[18px] shrink-0 text-white/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path fillRule="evenodd" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z" clipRule="evenodd" />
                                        </svg>
                                        <span>Empleados</span>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>

                                <SidebarMenuItem>
                                    <SidebarMenuButton 
                                        href="/admin/hr/attendances" 
                                        isActive={isActive('/admin/hr/attendances')}
                                        tooltip="Asistencia (Reloj)"
                                    >
                                        <svg className="h-[18px] w-[18px] shrink-0 text-white/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path fillRule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v6c0 .414.336.75.75.75h4.5a.75.75 0 0 0 0-1.5h-3.75V6Z" clipRule="evenodd" />
                                        </svg>
                                        <span>Asistencia (Reloj)</span>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>

                                <SidebarMenuItem>
                                    <SidebarMenuButton 
                                        href="/admin/hr/payroll" 
                                        isActive={isActive('/admin/hr/payroll')}
                                        tooltip="Nómina de Pago"
                                    >
                                        <svg className="h-[18px] w-[18px] shrink-0 text-white/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path fillRule="evenodd" d="M12 2.25a.75.75 0 0 1 .75.75v11.69l3.22-3.22a.75.75 0 1 1 1.06 1.06l-4.5 4.5a.75.75 0 0 1-1.06 0l-4.5-4.5a.75.75 0 1 1 1.06-1.06l3.22 3.22V3a.75.75 0 0 1 .75-.75Zm-9 13.5a.75.75 0 0 1 .75.75v2.25a1.5 1.5 0 0 0 1.5 1.5h13.5a1.5 1.5 0 0 0 1.5-1.5V16.5a.75.75 0 0 1 1.5 0v2.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V16.5a.75.75 0 0 1 1.5 0v2.25a3 3 0 0 1-3 3H5.25a3 3 0 0 1-3-3V16.5a.75.75 0 0 1 .75-.75Z" clipRule="evenodd" />
                                        </svg>
                                        <span>Nómina de Pago</span>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            </SidebarMenu>
                        </SidebarGroup>
                    )}

                    {/* SECCIÓN REPORTES */}
                    {(hasAnyRole(['Admin', 'Registro', 'Contabilidad'])) && (
                        <SidebarGroup>
                            <SidebarGroupLabel>
                                Reportes
                            </SidebarGroupLabel>
                            <SidebarMenu>
                                <SidebarMenuItem>
                                    <SidebarMenuButton 
                                        href="/admin/reports" 
                                        isActive={isActive('/admin/reports')}
                                        tooltip="Centro de Reportes"
                                    >
                                        <svg className="h-[18px] w-[18px] shrink-0 text-white/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path fillRule="evenodd" d="M2.25 2.25a.75.75 0 0 0 0 1.5H3v10.5a3 3 0 0 0 3 3h1.21l-1.172 3.513a.75.75 0 0 0 1.424.474l.329-.987h8.418l.33.987a.75.75 0 0 0 1.422-.474l-1.17-3.513H18a3 3 0 0 0 3-3V3.75h.75a.75.75 0 0 0 0-1.5H2.25Zm6.04 16.5.5-1.5h6.42l.5 1.5H8.29Zm7.46-12a.75.75 0 0 0-1.5 0v6a.75.75 0 0 0 1.5 0v-6Zm-3 2.25a.75.75 0 0 0-1.5 0v3.75a.75.75 0 0 0 1.5 0V9Zm-3 2.25a.75.75 0 0 0-1.5 0v1.5a.75.75 0 0 1 1.5 0v-1.5Z" clipRule="evenodd" />
                                        </svg>
                                        <span>Centro de Reportes</span>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            </SidebarMenu>
                        </SidebarGroup>
                    )}

                    {/* SECCIÓN SISTEMA (Solo Admin) */}
                    {hasRole('Admin') && (
                        <SidebarGroup>
                            <SidebarGroupLabel>
                                Configuración
                            </SidebarGroupLabel>
                            <SidebarMenu>
                                <SidebarMenuItem>
                                    <SidebarMenuButton 
                                        href="/admin/users" 
                                        isActive={isActive('/admin/users')}
                                        tooltip="Usuarios"
                                    >
                                        <svg className="h-[18px] w-[18px] shrink-0 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24" strokeWidth="2">
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                        </svg>
                                        <span>Usuarios</span>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>

                                <SidebarMenuItem>
                                    <SidebarMenuButton 
                                        href="/admin/classrooms" 
                                        isActive={isActive('/admin/classrooms')}
                                        tooltip="Aulas"
                                    >
                                        <svg className="h-[18px] w-[18px] shrink-0 text-white/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M18 1.5c2.9 0 5.25 2.35 5.25 5.25v3.75a.75.75 0 0 1-1.5 0V6.75a3.75 3.75 0 1 0-7.5 0v3a3 3 0 1 0-6 0v-3a3.75 3.75 0 1 0-7.5 0v3.75a.75.75 0 0 1-1.5 0V6.75a5.25 5.25 0 0 1 5.25-5.25H18Z" />
                                        </svg>
                                        <span>Aulas</span>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>

                                {/* Desplegable Diplomas */}
                                <SidebarMenuItem>
                                    <SidebarMenuButton 
                                        onClick={() => setDiplomasOpen(!diplomasOpen)}
                                        isActive={isActive('/admin/certificates') || isActive('/admin/certificate-templates')}
                                        tooltip="Diplomas"
                                    >
                                        <svg className="h-[18px] w-[18px] shrink-0 text-white/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path fillRule="evenodd" d="M10.5 3.75a6.75 6.75 0 1 0 0 13.5 6.75 6.75 0 0 0 0-13.5ZM2.25 10.5a8.25 8.25 0 1 1 14.59 5.28l4.69 4.69a.75.75 0 1 1-1.06 1.06l-4.69-4.69A8.25 8.25 0 0 1 2.25 10.5Z" clipRule="evenodd" />
                                        </svg>
                                        <span className="flex-1 text-left">Diplomas</span>
                                        <svg className={`h-3.5 w-3.5 transform transition-transform duration-200 opacity-60 ${diplomasOpen ? 'rotate-180' : ''}`} xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path fillRule="evenodd" d="M12.53 16.28a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 0 1 1.06-1.06L12 14.69l6.97-6.97a.75.75 0 1 1 1.06 1.06l-7.5 7.5Z" clipRule="evenodd" />
                                        </svg>
                                    </SidebarMenuButton>
                                    <CollapsibleSidebarMenu open={diplomasOpen}>
                                        <SidebarMenuSub>
                                            <SidebarMenuSubItem>
                                                <SidebarMenuSubButton 
                                                    href="/admin/certificates" 
                                                    isActive={isActive('/admin/certificates')}
                                                >
                                                    Emitidos
                                                </SidebarMenuSubButton>
                                            </SidebarMenuSubItem>
                                            <SidebarMenuSubItem>
                                                <SidebarMenuSubButton 
                                                    href="/admin/certificate-templates" 
                                                    isActive={isActive('/admin/certificate-templates')}
                                                >
                                                    Plantillas
                                                </SidebarMenuSubButton>
                                            </SidebarMenuSubItem>
                                        </SidebarMenuSub>
                                    </CollapsibleSidebarMenu>
                                </SidebarMenuItem>

                                <SidebarMenuItem>
                                    <SidebarMenuButton 
                                        href="/admin/import" 
                                        isActive={isActive('/admin/import')}
                                        tooltip="Importar Datos"
                                    >
                                        <svg className="h-[18px] w-[18px] shrink-0 text-white/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path d="M11.644 1.59a.75.75 0 0 1 .712 0l9.75 5.25a.75.75 0 0 1 0 1.32l-9.75 5.25a.75.75 0 0 1-.712 0l-9.75-5.25a.75.75 0 0 1 0-1.32l9.75-5.25Z" />
                                            <path d="M3.265 10.602l7.668 4.129a2.25 2.25 0 0 0 2.134 0l7.668-4.13 1.37.739a.75.75 0 0 1 0 1.32l-9.75 5.25a.75.75 0 0 1-.71 0l-9.75-5.25a.75.75 0 0 1 0-1.32l1.37-.738Z" />
                                            <path d="M10.933 19.231l-7.668-4.13-1.37.739a.75.75 0 0 0 0 1.32l9.75 5.25c.221.12.489.12.71 0l9.75-5.25a.75.75 0 0 0 0-1.32l-1.37-.738-7.668 4.13a2.25 2.25 0 0 1-2.134 0Z" />
                                        </svg>
                                        <span>Importar Datos</span>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>

                                <SidebarMenuItem>
                                    <SidebarMenuButton 
                                        href="/admin/activity-logs" 
                                        isActive={isActive('/admin/activity-logs')}
                                        tooltip="Auditoría & Logs"
                                    >
                                        <svg className="h-[18px] w-[18px] shrink-0 text-white/80" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                            <path fillRule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12Zm11.378-3.917c-.89-.777-2.366-.777-3.255 0a.75.75 0 0 1-.988-1.129c1.454-1.272 3.776-1.272 5.23 0 1.539 1.345 1.539 3.535 0 4.881L10.3 16.912a.75.75 0 0 1-1.133-.976l4.312-5.042c.835-.976.835-2.564 0-3.54Z" clipRule="evenodd" />
                                        </svg>
                                        <span>Auditoría & Logs</span>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>

                                <SidebarMenuItem>
                                    <SidebarMenuButton 
                                        href="/admin/settings" 
                                        isActive={isActive('/admin/settings')}
                                        tooltip="Ajustes Globales"
                                    >
                                        <svg className="h-[18px] w-[18px] shrink-0 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24" strokeWidth="2">
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path strokeLinecap="round" strokeLinejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>Ajustes Globales</span>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            </SidebarMenu>
                        </SidebarGroup>
                    )}
                        </>
                    )}
                </SidebarContent>

                {/* Footer del Sidebar con Perfil Resumido */}
                <SidebarFooter className={`p-3 border-t ${darkMode ? 'border-[#262626]' : 'border-white/10'} shrink-0 mt-auto group-data-[collapsible=icon]:p-2`}>
                    <div className={`flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl ${darkMode ? 'bg-[#212121] border-[#262626]' : 'bg-white/[0.07] border-white/10'} border group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:bg-transparent group-data-[collapsible=icon]:border-transparent group-data-[collapsible=icon]:p-0`}>
                        <div className="h-8 w-8 rounded-full bg-white/20 text-white flex items-center justify-center font-bold text-xs ring-1 ring-white/30 shrink-0 shadow-xs">
                            {userName.charAt(0)}
                        </div>
                        <div className="min-w-0 flex-1 group-data-[collapsible=icon]:hidden">
                            <p className="text-xs font-semibold text-white truncate leading-tight">{userName}</p>
                            <p className="text-[10px] text-white/60 truncate capitalize">{userRoles[0] || 'Admin'}</p>
                        </div>
                    </div>
                </SidebarFooter>

                {/* Riel interactivo de alternancia */}
                <SidebarRail />
            </Sidebar>

            {/* Inset Principal para Cabecera, Contenido y Pie */}
            <SidebarInset className="bg-gray-50 dark:bg-[#0A0A0A] flex flex-col h-full overflow-hidden">
                {/* Header Superior con Trigger de Sidebar */}
                <header className="sticky top-0 z-20 bg-white/80 dark:bg-[#0A0A0A]/85 backdrop-blur-xl border-b border-gray-200/50 dark:border-[#262626] flex-shrink-0" style={{ paddingTop: 'env(safe-area-inset-top, 0px)' }}>
                    <div className="flex items-center justify-between h-14 lg:h-16 px-3 sm:px-5 lg:px-8">
                        {/* Izquierda: Trigger Shadcn con PanelLeft & Título */}
                        <div className="flex items-center gap-2.5 sm:gap-3 min-w-0">
                            <SidebarTrigger className="text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200" />
                            <div className="min-w-0">
                                <h1 className="text-base lg:text-lg font-bold text-gray-800 dark:text-gray-100 truncate tracking-tight leading-tight">
                                    {resolveSectionTitle(title, url, institutionName)}
                                </h1>
                            </div>
                        </div>

                        {/* Centro: Buscador Institucional con Trigger a Modal */}
                        <div className="hidden md:flex flex-1 max-w-md mx-6 justify-center">
                            <button
                                type="button"
                                onClick={() => setSearchModalOpen(true)}
                                className="w-full flex items-center justify-between pl-3.5 pr-3 py-2 text-xs rounded-xl bg-gray-100/80 dark:bg-[#171717] hover:bg-gray-200/70 dark:hover:bg-[#262626] text-gray-400 border border-transparent dark:border-[#262626] hover:border-gray-300 dark:hover:border-[#404040] transition-all text-left shadow-sm group"
                            >
                                <div className="flex items-center gap-2.5">
                                    <svg className="h-4 w-4 text-gray-400 group-hover:text-gray-600 dark:group-hover:text-gray-300" viewBox="0 0 20 20" fill="currentColor">
                                        <path fillRule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clipRule="evenodd" />
                                    </svg>
                                    <span className="truncate">Buscar módulos, estudiantes, matrículas...</span>
                                </div>
                                <kbd className="hidden lg:inline-flex items-center gap-0.5 px-2 py-0.5 text-[10px] font-bold text-gray-400 bg-white dark:bg-[#121212] rounded border border-gray-200 dark:border-[#262626] shadow-xs">
                                    Ctrl+K
                                </kbd>
                            </button>
                        </div>

                        {/* Derecha: Acciones, Modo Oscuro & Usuario */}
                        <div className="flex items-center gap-1.5 sm:gap-2 lg:gap-3">
                            {/* Status Caja Operativa / Estudiante */}
                            <span className="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-200/80 dark:border-emerald-800/80">
                                <span className="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                {isStudent ? 'Estudiante Activo' : 'Caja Operativa'}
                            </span>

                            {/* Notification Center (UI Arc / Motion System) */}
                            <NotificationCenter
                                notifications={systemNotifications}
                                label="Notificaciones"
                                onReadChange={handleNotificationReadChange}
                                onDismiss={handleNotificationDismiss}
                            />

                            {/* Theme Switch Component (Arc / Motion) */}
                            <ThemeSwitch
                                theme={darkMode ? "dark" : "light"}
                                variant="reveal"
                                iconOnly={true}
                                onThemeChange={(next, variant, trigger) => {
                                    const isAppearanceTransition = document.startViewTransition
                                        && !window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                                    if (!isAppearanceTransition) {
                                        toggleDarkMode();
                                        return;
                                    }

                                    let x = window.innerWidth / 2;
                                    let y = window.innerHeight / 2;
                                    if (trigger && typeof trigger.getBoundingClientRect === 'function') {
                                        const rect = trigger.getBoundingClientRect();
                                        x = rect.left + rect.width / 2;
                                        y = rect.top + rect.height / 2;
                                    }
                                    const endRadius = Math.hypot(
                                        Math.max(x, window.innerWidth - x),
                                        Math.max(y, window.innerHeight - y)
                                    );

                                    document.documentElement.classList.add('theme-transitioning');
                                    const transition = document.startViewTransition(() => {
                                        toggleDarkMode();
                                    });

                                    transition.ready.then(() => {
                                        document.documentElement.animate(
                                            {
                                                clipPath: [
                                                    `circle(0px at ${x}px ${y}px)`,
                                                    `circle(${endRadius}px at ${x}px ${y}px)`
                                                ]
                                            },
                                            {
                                                duration: 450,
                                                easing: 'cubic-bezier(0.16, 1, 0.3, 1)',
                                                pseudoElement: '::view-transition-new(root)'
                                            }
                                        );
                                    });

                                    transition.finished.finally(() => {
                                        document.documentElement.classList.remove('theme-transitioning');
                                    });
                                }}
                            />

                            <div className="hidden lg:block h-5 w-px bg-gray-200 dark:bg-[#262626] mx-1" />

                            {/* Dropdown de Usuario */}
                            <div className="relative">
                                <button
                                    type="button"
                                    onClick={() => setUserDropdownOpen(!userDropdownOpen)}
                                    className="flex items-center gap-2.5 p-1 lg:py-1.5 lg:pl-3 lg:pr-2 rounded-full lg:rounded-xl hover:bg-gray-50 dark:hover:bg-[#262626] transition-all duration-150 focus:outline-none active:scale-[0.97]"
                                >
                                    <div className="hidden lg:block text-right">
                                        <p className="text-sm font-semibold text-gray-700 dark:text-gray-200 leading-none">
                                            {userName}
                                        </p>
                                        <p className="text-[11px] text-gray-400 dark:text-gray-500 mt-0.5 truncate max-w-[140px]">
                                            {userEmail}
                                        </p>
                                    </div>
                                    <div className="relative">
                                        <div className="h-8 w-8 lg:h-9 lg:w-9 rounded-full bg-brand text-white flex items-center justify-center font-bold text-xs ring-2 ring-white dark:ring-[#262626] shadow-sm">
                                            {userName.charAt(0)}
                                        </div>
                                        <span className="absolute -bottom-0.5 -right-0.5 block h-2.5 w-2.5 rounded-full bg-emerald-400 ring-2 ring-white dark:ring-[#0A0A0A]"></span>
                                    </div>
                                    <svg className="h-4 w-4 text-gray-400 dark:text-gray-500 hidden sm:block" fill="none" viewBox="0 0 24 24" strokeWidth="2" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                    </svg>
                                </button>

                                {userDropdownOpen && (
                                    <div 
                                        className="absolute right-0 mt-2 w-48 rounded-xl bg-white dark:bg-[#171717] py-1 shadow-xl ring-1 ring-black/5 dark:ring-white/10 border border-gray-100 dark:border-[#262626] z-50 animate-in fade-in zoom-in-95 duration-100"
                                        onMouseLeave={() => setUserDropdownOpen(false)}
                                    >
                                        <div className="px-4 py-3 border-b border-gray-100 dark:border-[#262626]">
                                            <p className="text-[10px] text-gray-400 dark:text-gray-500 uppercase font-bold tracking-widest">Mi Cuenta</p>
                                            <p className="text-xs font-semibold text-gray-800 dark:text-gray-200 truncate mt-0.5">{userName}</p>
                                        </div>
                                        <Link href={isStudent ? "/student/profile" : "/admin/profile"} className="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-[#262626]">
                                            <svg className="w-4 h-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fillRule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clipRule="evenodd" />
                                            </svg>
                                            Mi Perfil
                                        </Link>
                                        <div className="border-t border-gray-100 dark:border-[#262626] my-1"></div>
                                        <button 
                                            type="button" 
                                            onClick={handleLogout}
                                            className="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 hover:text-red-600 flex items-center gap-2.5"
                                        >
                                            <svg className="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" strokeWidth="2">
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                                            </svg>
                                            Cerrar Sesión
                                        </button>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                </header>

                {/* Contenido Principal con Scroll + Footer */}
                <main className="flex-1 flex flex-col overflow-y-auto focus:outline-none scroll-smooth bg-gray-50 dark:bg-[#0A0A0A] pb-16 lg:pb-0 relative">
                    {/* Indicador de Carga Estilo Bills (Línea Hairline Superior Fluida) */}
                    <div className={`absolute top-0 left-0 right-0 h-[2.5px] z-50 transition-opacity duration-200 pointer-events-none ${isNavigating ? 'opacity-100' : 'opacity-0'}`}>
                        <div 
                            className="h-full w-full animate-bills-loading-line" 
                            style={{ 
                                background: primaryColor, 
                                boxShadow: `0 0 10px ${primaryColor}, 0 0 5px ${primaryColor}` 
                            }} 
                        />
                    </div>

                    <div className="w-full flex-1 p-4 sm:p-6 lg:px-8 lg:py-6">
                        <div key={url} className={`w-full max-w-[1720px] mx-auto bills-view-transition transition-opacity duration-150 ${isNavigating ? 'opacity-70 pointer-events-none' : 'opacity-100'}`}>
                            {children}
                        </div>
                    </div>

                    {/* Footer Institucional Dinámico */}
                    <footer className="mt-auto border-t border-gray-200 dark:border-[#262626] py-6 w-full">
                        <div className="w-full max-w-[1720px] mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4">
                            <div className="text-left w-full md:w-auto order-2 md:order-1">
                                <p className="text-xs text-gray-400 leading-relaxed">
                                    &copy; {new Date().getFullYear()} <span className="font-medium text-gray-600 dark:text-gray-400">{institutionName}</span>. Todos los derechos reservados.
                                </p>
                            </div>
                            <div className="flex items-center justify-center md:justify-end gap-6 w-full md:w-auto order-1 md:order-2">
                                <a href="#" className="group flex items-center gap-1.5 text-xs font-medium text-gray-500 hover:text-sga-primary transition-colors duration-200">
                                    <svg className="w-3.5 h-3.5 text-gray-400 group-hover:text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    Soporte
                                </a>
                            </div>
                        </div>
                    </footer>
                </main>

                {/* Bottom Navigation Bar para Móvil (<lg) */}
                <nav className="fixed bottom-0 inset-x-0 z-40 lg:hidden bg-white/80 dark:bg-[#0A0A0A]/85 backdrop-blur-xl border-t border-gray-200/60 dark:border-[#262626] shadow-[0_-4px_30px_rgba(0,0,0,0.06)] px-4 py-2 flex items-center justify-around" style={{ paddingBottom: 'env(safe-area-inset-bottom, 0px)' }}>
                    {isStudent ? (
                        <>
                            <Link href="/student/dashboard" className={`flex flex-col items-center gap-1 p-1 text-center ${isActive('/student/dashboard') ? 'text-brand font-bold' : 'text-gray-500 dark:text-gray-400'}`}>
                                <LayoutDashboard className="h-5 w-5" />
                                <span className="text-[10px]">Inicio</span>
                            </Link>
                            <Link href="/student/selection" className={`flex flex-col items-center gap-1 p-1 text-center ${isActive('/student/selection') ? 'text-brand font-bold' : 'text-gray-500 dark:text-gray-400'}`}>
                                <BookOpen className="h-5 w-5" />
                                <span className="text-[10px]">Selección</span>
                            </Link>
                            <Link href="/student/payments" className={`flex flex-col items-center gap-1 p-1 text-center ${isActive('/student/payments') ? 'text-brand font-bold' : 'text-gray-500 dark:text-gray-400'}`}>
                                <CreditCard className="h-5 w-5" />
                                <span className="text-[10px]">Pagos</span>
                            </Link>
                            <Link href="/student/requests" className={`flex flex-col items-center gap-1 p-1 text-center ${isActive('/student/requests') ? 'text-brand font-bold' : 'text-gray-500 dark:text-gray-400'}`}>
                                <FileText className="h-5 w-5" />
                                <span className="text-[10px]">Trámites</span>
                            </Link>
                            <button 
                                type="button" 
                                onClick={() => setOpenMobile(true)}
                                className="flex flex-col items-center gap-1 p-1 text-gray-500 dark:text-gray-400 text-center"
                            >
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                                <span className="text-[10px] font-medium">Menú</span>
                            </button>
                        </>
                    ) : (
                        <>
                            <Link href="/admin/dashboard" className={`flex flex-col items-center gap-1 p-1 text-center ${isActive('/admin/dashboard') ? 'text-brand font-bold' : 'text-gray-500 dark:text-gray-400'}`}>
                                <svg className="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M11.47 3.84a.75.75 0 0 1 1.06 0l8.635 8.635a.75.75 0 1 1-1.06 1.06l-.312-.31V21a.75.75 0 0 1-.75.75H2.75a.75.75 0 0 1-.75-.75v-7.775l-.31.31a.75.75 0 1 1-1.06-1.06l8.635-8.635ZM18.75 12.016v-2.072l-6.75-6.75-6.75 6.75v2.072h13.5Z" />
                                </svg>
                                <span className="text-[10px]">Inicio</span>
                            </Link>
                            <Link href="/admin/students" className={`flex flex-col items-center gap-1 p-1 text-center ${isActive('/admin/students') ? 'text-brand font-bold' : 'text-gray-500 dark:text-gray-400'}`}>
                                <svg className="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                    <path fillRule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clipRule="evenodd" />
                                </svg>
                                <span className="text-[10px]">Alumnos</span>
                            </Link>
                            <Link href="/admin/pos" className={`flex flex-col items-center gap-1 p-1 text-center relative ${isActive('/admin/pos') ? 'text-brand font-bold' : 'text-gray-500 dark:text-gray-400'}`}>
                                <svg className="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M4.5 3.75a3 3 0 0 0-3 3v.75h21v-.75a3 3 0 0 0-3-3h-15Z" />
                                    <path fillRule="evenodd" d="M22.5 9.75h-21v7.5a3 3 0 0 0 3 3h15a3 3 0 0 0 3-3v-7.5Zm-18 3.75a.75.75 0 0 1 .75-.75h6a.75.75 0 0 1 0 1.5h-6a.75.75 0 0 1-.75-.75Zm.75 2.25a.75.75 0 0 0 0 1.5h3a.75.75 0 0 0 0-1.5h-3Z" clipRule="evenodd" />
                                </svg>
                                <span className="text-[10px]">Caja</span>
                                {isActive('/admin/pos') && <span className="w-1 h-1 rounded-full bg-brand"></span>}
                            </Link>
                            <button 
                                type="button" 
                                onClick={() => setOpenMobile(true)}
                                className="flex flex-col items-center gap-1 p-1 text-gray-500 dark:text-gray-400 text-center"
                            >
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16"/>
                                </svg>
                                <span className="text-[10px] font-medium">Menú</span>
                            </button>
                        </>
                    )}
                </nav>

                {/* ── MENÚ CONTEXTUAL UNIVERSAL DINÁMICO (GodUI TabBar) (oculto en dashboard principal para evitar sobrecarga y obstrucción) ── */}
                {!url.startsWith('/admin/dashboard') && !url.startsWith('/admin/pos') && (
                    <aside 
                        aria-label="Acciones contextuales dinámicas"
                        className="fixed bottom-20 right-4 lg:bottom-6 lg:right-6 z-30 pointer-events-auto"
                    >
                        <TabBar
                            value={activeTab}
                            onChange={handleTabChange}
                            labelsOnActiveOnly={isMobile}
                            tabs={contextualNav.tabs.map(t => ({
                                value: t.value,
                                label: t.label,
                                icon: t.icon,
                                badge: t.badge
                            }))}
                            className="shadow-2xl shadow-blue-950/20 border-white/20 dark:border-[#262626]"
                        />
                    </aside>
                )}
            </SidebarInset>
        </div>
    );
}

export default function AuthenticatedLayout(props) {
    return (
        <SidebarProvider defaultOpen={true}>
            <AuthenticatedContent {...props} />
        </SidebarProvider>
    );
}