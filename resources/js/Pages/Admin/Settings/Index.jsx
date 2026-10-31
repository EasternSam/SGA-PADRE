import React, { useState, useEffect } from 'react';
import { Head, useForm, router, usePage } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { 
    Building2, 
    Palette, 
    FileText, 
    Share2, 
    Mail, 
    CreditCard, 
    Save, 
    Loader2, 
    CheckCircle2, 
    XCircle, 
    RefreshCw, 
    Copy, 
    Check, 
    Eye, 
    EyeOff, 
    ExternalLink, 
    GraduationCap, 
    Globe, 
    ShieldCheck, 
    Server, 
    Sparkles, 
    Smartphone 
} from 'lucide-react';

export default function Index({ 
    settings = {}, 
    pairingCode = null, 
    pairingCodeExpiresAt = null, 
    apiBaseUrl = '' 
}) {
    const { flash } = usePage().props;
    const [activeTab, setActiveTab] = useState('apis'); // Default to integrations/apis to highlight WP & Moodle
    const [copiedCode, setCopiedCode] = useState(false);
    const [showWpSecret, setShowWpSecret] = useState(false);
    const [showMoodleToken, setShowMoodleToken] = useState(false);
    const [showBillsToken, setShowBillsToken] = useState(false);
    const [showSmtpPassword, setShowSmtpPassword] = useState(false);

    const [isGeneratingCode, setIsGeneratingCode] = useState(false);
    const [isSyncingWp, setIsSyncingWp] = useState(false);
    const [isTestingMoodle, setIsTestingMoodle] = useState(false);
    const [isTestingBills, setIsTestingBills] = useState(false);

    // Dynamic timer for pairing code expiration
    const [timeLeftMinutes, setTimeLeftMinutes] = useState(0);

    useEffect(() => {
        if (!pairingCodeExpiresAt) {
            setTimeLeftMinutes(0);
            return;
        }
        const calcTime = () => {
            const now = Math.floor(Date.now() / 1000);
            const diff = pairingCodeExpiresAt - now;
            setTimeLeftMinutes(diff > 0 ? Math.ceil(diff / 60) : 0);
        };
        calcTime();
        const interval = setInterval(calcTime, 15000);
        return () => clearInterval(interval);
    }, [pairingCodeExpiresAt]);

    const { data, setData, post, processing, errors } = useForm({
        // Identidad & Contacto
        institution_name: settings.institution_name || '',
        support_email: settings.support_email || '',
        contact_phone: settings.contact_phone || '',
        contact_address: settings.contact_address || '',
        website_url: settings.website_url || '',
        social_facebook: settings.social_facebook || '',
        social_instagram: settings.social_instagram || '',
        enable_careers: settings.enable_careers ?? true,

        // Marca & Apariencia (Paleta Institucional de 4 Colores)
        brand_primary_color: settings.brand_primary_color || '#211170',
        brand_secondary_color: settings.brand_secondary_color || '#1a0a53',
        brand_accent_color: settings.brand_accent_color || '#f7931e',
        brand_neutral_color: settings.brand_neutral_color || '#64748b',
        institution_logo: settings.institution_logo || '/branding/academic-logo.png',
        sidebar_logo_height: Number(settings.sidebar_logo_height) || 44,
        favicon: settings.favicon || '/branding/academic-favicon.png',
        app_icon: settings.app_icon || '/branding/academic-favicon.png',
        login_banner: settings.login_banner || '',
        navbar_type: settings.navbar_type || 'gradient',
        navbar_gradient_start: settings.navbar_gradient_start || '#211170',
        navbar_gradient_end: settings.navbar_gradient_end || '#1a0a53',
        navbar_gradient_direction: settings.navbar_gradient_direction || 'to bottom right',

        // WordPress / Academic+ Catalog
        wp_api_url: settings.wp_api_url || '',
        wp_api_secret: settings.wp_api_secret || '',

        // Moodle
        moodle_url: settings.moodle_url || '',
        moodle_token: settings.moodle_token || '',

        // Bills
        enable_bills_invoicing: settings.enable_bills_invoicing ?? false,
        bills_api_url: settings.bills_api_url || 'https://btest.gridbase.com.do/api/v1',
        bills_api_token: settings.bills_api_token || '',
        bills_default_tax_rate: settings.bills_default_tax_rate || '18',

        // Cardnet & DGII
        cardnet_merchant_id: settings.cardnet_merchant_id || '',
        cardnet_terminal_id: settings.cardnet_terminal_id || '',
        enable_electronic_billing: settings.enable_electronic_billing ?? false,
        ecf_rnc_emisor: settings.ecf_rnc_emisor || '101000000',

        // SMTP
        smtp_host: settings.smtp_host || '',
        smtp_port: settings.smtp_port || '587',
        smtp_username: settings.smtp_username || '',
        smtp_password: settings.smtp_password || '',
        smtp_encryption: settings.smtp_encryption || 'tls',
        smtp_from_address: settings.smtp_from_address || '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/admin/settings', {
            preserveScroll: true,
        });
    };

    const handleGeneratePairingCode = () => {
        setIsGeneratingCode(true);
        router.post('/admin/settings/pairing-code', {}, {
            preserveScroll: true,
            onFinish: () => setIsGeneratingCode(false),
        });
    };

    const handleSyncWpStudents = () => {
        setIsSyncingWp(true);
        router.post('/admin/settings/sync-wp-students', {}, {
            preserveScroll: true,
            onFinish: () => setIsSyncingWp(false),
        });
    };

    const handleTestMoodle = () => {
        setIsTestingMoodle(true);
        router.post('/admin/settings/test-moodle', {}, {
            preserveScroll: true,
            onFinish: () => setIsTestingMoodle(false),
        });
    };

    const handleTestBills = () => {
        setIsTestingBills(true);
        router.post('/admin/settings/test-bills', {}, {
            preserveScroll: true,
            onFinish: () => setIsTestingBills(false),
        });
    };

    const copyToClipboard = (text) => {
        if (!text) return;
        navigator.clipboard.writeText(text);
        setCopiedCode(true);
        setTimeout(() => setCopiedCode(false), 2500);
    };

    const isWpConnected = Boolean(data.wp_api_url && data.wp_api_secret);
    const isMoodleConnected = Boolean(data.moodle_url && data.moodle_token);

    return (
        <AuthenticatedLayout title="Configuración Global">
            <Head title="Configuración del Sistema - SGA" />

            <div className="max-w-6xl mx-auto space-y-6 pb-12">
                {/* Header Banner */}
                <div className="bg-white dark:bg-[#161622] rounded-2xl p-6 border border-slate-200 dark:border-white/5 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div className="flex items-center gap-2 text-xs font-bold text-sga-primary dark:text-blue-400 uppercase tracking-wider">
                            <Server className="w-4 h-4" /> Centro de Ajustes & Integraciones
                        </div>
                        <h1 className="text-2xl font-bold text-slate-800 dark:text-white mt-1">
                            Configuración General del SGA
                        </h1>
                        <p className="text-slate-500 dark:text-slate-400 text-sm mt-0.5">
                            Administra la conectividad con WordPress (Academic+ Catalog), Aula Virtual Moodle, Facturación Bills, Correo y Marca.
                        </p>
                    </div>

                    <div className="flex items-center gap-3">
                        <button
                            type="button"
                            onClick={handleSubmit}
                            disabled={processing}
                            className="px-6 py-2.5 rounded-xl bg-sga-primary hover:bg-blue-700 text-white font-bold text-sm shadow-md transition-all flex items-center gap-2 shrink-0 cursor-pointer disabled:opacity-50"
                        >
                            {processing ? (
                                <>
                                    <Loader2 className="w-4 h-4 animate-spin" />
                                    <span>Guardando...</span>
                                </>
                            ) : (
                                <>
                                    <Save className="w-4 h-4" />
                                    <span>Guardar Ajustes</span>
                                </>
                            )}
                        </button>
                    </div>
                </div>

                {/* Notifications & Flash Messages */}
                {flash?.success && (
                    <div className="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 flex items-center gap-3 text-emerald-800 dark:text-emerald-300 text-sm font-medium">
                        <CheckCircle2 className="w-5 h-5 text-emerald-600 shrink-0" />
                        <span>{flash.success}</span>
                    </div>
                )}
                {flash?.error && (
                    <div className="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 flex items-center gap-3 text-rose-800 dark:text-rose-300 text-sm font-medium">
                        <XCircle className="w-5 h-5 text-rose-600 shrink-0" />
                        <span>{flash.error}</span>
                    </div>
                )}

                {/* Tab Navigation */}
                <div className="flex items-center gap-2 border-b border-slate-200 dark:border-white/10 pb-2 overflow-x-auto">
                    <button
                        type="button"
                        onClick={() => setActiveTab('apis')}
                        className={`flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition-all whitespace-nowrap cursor-pointer ${
                            activeTab === 'apis'
                                ? 'bg-sga-primary text-white shadow-sm'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-100/60 dark:bg-white/5'
                        }`}
                    >
                        <Share2 className="w-4 h-4" />
                        <span>Integraciones & APIs</span>
                        {isWpConnected && isMoodleConnected && (
                            <span className="w-2 h-2 rounded-full bg-emerald-400"></span>
                        )}
                    </button>

                    <button
                        type="button"
                        onClick={() => setActiveTab('identity')}
                        className={`flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition-all whitespace-nowrap cursor-pointer ${
                            activeTab === 'identity'
                                ? 'bg-sga-primary text-white shadow-sm'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-100/60 dark:bg-white/5'
                        }`}
                    >
                        <Building2 className="w-4 h-4" />
                        <span>Identidad & Contacto</span>
                    </button>

                    <button
                        type="button"
                        onClick={() => setActiveTab('branding')}
                        className={`flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition-all whitespace-nowrap cursor-pointer ${
                            activeTab === 'branding'
                                ? 'bg-sga-primary text-white shadow-sm'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-100/60 dark:bg-white/5'
                        }`}
                    >
                        <Palette className="w-4 h-4" />
                        <span>Marca & Colores</span>
                    </button>

                    <button
                        type="button"
                        onClick={() => setActiveTab('finance')}
                        className={`flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition-all whitespace-nowrap cursor-pointer ${
                            activeTab === 'finance'
                                ? 'bg-sga-primary text-white shadow-sm'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-100/60 dark:bg-white/5'
                        }`}
                    >
                        <CreditCard className="w-4 h-4" />
                        <span>Pasarelas & DGII</span>
                    </button>

                    <button
                        type="button"
                        onClick={() => setActiveTab('smtp')}
                        className={`flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition-all whitespace-nowrap cursor-pointer ${
                            activeTab === 'smtp'
                                ? 'bg-sga-primary text-white shadow-sm'
                                : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white bg-slate-100/60 dark:bg-white/5'
                        }`}
                    >
                        <Mail className="w-4 h-4" />
                        <span>Correo / SMTP</span>
                    </button>
                </div>

                {/* Form Panels Container */}
                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* ══════════════════════════════════════════════════════════════
                        TAB 1: INTEGRACIONES & APIS (WORDPRESS, MOODLE, BILLS)
                    ══════════════════════════════════════════════════════════════ */}
                    {activeTab === 'apis' && (
                        <div className="space-y-6">
                            {/* --- SECCIÓN 1: WORDPRESS / ACADEMIC+ CATALOG --- */}
                            <div className="bg-white dark:bg-[#161622] rounded-2xl p-6 sm:p-8 border border-slate-200 dark:border-white/5 shadow-xs space-y-6">
                                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-white/5">
                                    <div className="flex items-center gap-3">
                                        <div className="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 border border-blue-200 dark:border-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-400 font-black text-lg">
                                            W
                                        </div>
                                        <div>
                                            <h2 className="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                                Conexión WordPress (Academic+ Catalog)
                                            </h2>
                                            <p className="text-xs text-slate-500 dark:text-slate-400">
                                                Sincroniza la oferta formativa, estudiantes y reservas online con el plugin de WordPress.
                                            </p>
                                        </div>
                                    </div>

                                    <div>
                                        {isWpConnected ? (
                                            <span className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                <span className="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                                Academic+ Catalog conectado
                                            </span>
                                        ) : (
                                            <span className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                                <span className="h-2 w-2 rounded-full bg-slate-400"></span>
                                                WordPress desconectado
                                            </span>
                                        )}
                                    </div>
                                </div>

                                {/* Enlace Rápido por Código de Emparejamiento */}
                                <div className="p-5 rounded-2xl bg-indigo-50/80 dark:bg-indigo-950/30 border border-indigo-100 dark:border-indigo-900/50 flex flex-col md:flex-row items-center justify-between gap-5">
                                    <div className="flex-1 space-y-1.5">
                                        <h3 className="font-bold text-indigo-950 dark:text-indigo-200 text-sm flex items-center gap-2">
                                            <Sparkles className="w-4 h-4 text-indigo-600" />
                                            Enlace Rápido Automático con Academic+ Catalog
                                        </h3>
                                        <p className="text-xs text-indigo-800/80 dark:text-indigo-300 leading-relaxed">
                                            Genera un código temporal único para vincular este sistema con tu plugin de WordPress automáticamente sin configurar tokens a mano.
                                        </p>
                                        <div className="text-xs text-indigo-900 dark:text-indigo-200 font-mono pt-1">
                                            URL de API: <span className="bg-indigo-100 dark:bg-indigo-900/60 px-2 py-0.5 rounded text-indigo-950 dark:text-indigo-200 font-bold">{apiBaseUrl || '/api'}</span>
                                        </div>
                                    </div>

                                    <div className="shrink-0 flex flex-col items-center gap-2">
                                        {pairingCode && timeLeftMinutes > 0 ? (
                                            <div className="flex flex-col items-center p-3 bg-white dark:bg-slate-900 rounded-xl border border-indigo-200 dark:border-indigo-800 shadow-xs">
                                                <div className="flex items-center gap-2">
                                                    <span className="text-2xl font-black tracking-widest text-indigo-900 dark:text-indigo-100 font-mono select-all">
                                                        {pairingCode}
                                                    </span>
                                                    <button
                                                        type="button"
                                                        onClick={() => copyToClipboard(pairingCode)}
                                                        className="p-1.5 rounded-lg hover:bg-indigo-50 dark:hover:bg-slate-800 text-indigo-600 dark:text-indigo-400 transition-colors"
                                                        title="Copiar código"
                                                    >
                                                        {copiedCode ? <Check className="w-4 h-4 text-emerald-500" /> : <Copy className="w-4 h-4" />}
                                                    </button>
                                                </div>
                                                <span className="text-[11px] text-indigo-600 dark:text-indigo-400 font-semibold mt-1">
                                                    Expira en: {timeLeftMinutes} min
                                                </span>
                                                <button
                                                    type="button"
                                                    onClick={handleGeneratePairingCode}
                                                    disabled={isGeneratingCode}
                                                    className="mt-2 text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-semibold flex items-center gap-1 cursor-pointer"
                                                >
                                                    <RefreshCw className={`w-3 h-3 ${isGeneratingCode ? 'animate-spin' : ''}`} />
                                                    Renovar Código
                                                </button>
                                            </div>
                                        ) : (
                                            <button
                                                type="button"
                                                onClick={handleGeneratePairingCode}
                                                disabled={isGeneratingCode}
                                                className="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-sm transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50"
                                            >
                                                {isGeneratingCode ? (
                                                    <>
                                                        <Loader2 className="w-3.5 h-3.5 animate-spin" />
                                                        <span>Generando...</span>
                                                    </>
                                                ) : (
                                                    <>
                                                        <Sparkles className="w-3.5 h-3.5" />
                                                        <span>Generar Código de Enlace</span>
                                                    </>
                                                )}
                                            </button>
                                        )}
                                    </div>
                                </div>

                                {/* Formulario Manual de WordPress */}
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                            URL Base de la API de WordPress
                                        </label>
                                        <input
                                            type="url"
                                            value={data.wp_api_url}
                                            onChange={e => setData('wp_api_url', e.target.value)}
                                            placeholder="https://institucion.edu.do/wp-json/"
                                            className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary font-mono text-xs"
                                        />
                                        <p className="text-[11px] text-slate-500 mt-1">Endpoint REST base del sitio web institucional.</p>
                                    </div>

                                    <div>
                                        <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                            Token Secreto de Firma (X-SGA-Signature)
                                        </label>
                                        <div className="relative">
                                            <input
                                                type={showWpSecret ? 'text' : 'password'}
                                                value={data.wp_api_secret}
                                                onChange={e => setData('wp_api_secret', e.target.value)}
                                                placeholder="Token de autenticación secreto"
                                                className="w-full px-4 py-2.5 pr-10 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary font-mono text-xs"
                                            />
                                            <button
                                                type="button"
                                                onClick={() => setShowWpSecret(!showWpSecret)}
                                                className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                                            >
                                                {showWpSecret ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                                            </button>
                                        </div>
                                        <p className="text-[11px] text-slate-500 mt-1">Clave criptográfica para validar solicitudes salientes y entrantes.</p>
                                    </div>
                                </div>

                                {/* Sincronización Manual de Estudiantes */}
                                <div className="p-5 rounded-2xl bg-slate-50 dark:bg-white/[0.02] border border-slate-200 dark:border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div className="space-y-1">
                                        <h4 className="font-bold text-slate-800 dark:text-white text-sm flex items-center gap-2">
                                            <RefreshCw className="w-4 h-4 text-indigo-600 dark:text-indigo-400" />
                                            Sincronización Manual de Estudiantes desde WordPress
                                        </h4>
                                        <p className="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                            Importa todos los registros de estudiantes inscritos en el catálogo de WordPress hacia la base de datos local.
                                            <br /><span className="text-indigo-600 dark:text-indigo-400 font-semibold">• Garantía sin Notificaciones:</span> Este proceso <strong className="text-slate-700 dark:text-slate-200">NO enviará correos de bienvenida</strong> a los estudiantes importados.
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        onClick={handleSyncWpStudents}
                                        disabled={isSyncingWp || !isWpConnected}
                                        className="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition-all flex items-center gap-2 shrink-0 cursor-pointer disabled:opacity-50"
                                    >
                                        {isSyncingWp ? (
                                            <>
                                                <Loader2 className="w-4 h-4 animate-spin" />
                                                <span>Sincronizando...</span>
                                            </>
                                        ) : (
                                            <>
                                                <RefreshCw className="w-4 h-4" />
                                                <span>Sincronizar Estudiantes de WP</span>
                                            </>
                                        )}
                                    </button>
                                </div>
                            </div>

                            {/* --- SECCIÓN 2: AULA VIRTUAL MOODLE --- */}
                            <div className="bg-white dark:bg-[#161622] rounded-2xl p-6 sm:p-8 border border-slate-200 dark:border-white/5 shadow-xs space-y-6">
                                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100 dark:border-white/5">
                                    <div className="flex items-center gap-3">
                                        <div className="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 border border-amber-200 dark:border-amber-900 flex items-center justify-center text-amber-600 dark:text-amber-400 font-black text-lg">
                                            M
                                        </div>
                                        <div>
                                            <h2 className="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                                                Conexión Aula Virtual Moodle
                                            </h2>
                                            <p className="text-xs text-slate-500 dark:text-slate-400">
                                                Matriculación automatizada de alumnos en aulas virtuales y Single Sign-On (SSO) directo para estudiantes.
                                            </p>
                                        </div>
                                    </div>

                                    <div>
                                        {isMoodleConnected ? (
                                            <span className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                <span className="h-2 w-2 rounded-full bg-amber-500"></span>
                                                Moodle configurado
                                            </span>
                                        ) : (
                                            <span className="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                                <span className="h-2 w-2 rounded-full bg-slate-400"></span>
                                                Moodle desconectado
                                            </span>
                                        )}
                                    </div>
                                </div>

                                <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div>
                                        <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                            URL de la Plataforma Moodle
                                        </label>
                                        <input
                                            type="url"
                                            value={data.moodle_url}
                                            onChange={e => setData('moodle_url', e.target.value)}
                                            placeholder="https://moodle.institucion.edu.do"
                                            className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary font-mono text-xs"
                                        />
                                        <p className="text-[11px] text-slate-500 mt-1">Dirección HTTPS del campus virtual institucional.</p>
                                    </div>

                                    <div>
                                        <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                            Token de Servicio Web (REST Webservice Token)
                                        </label>
                                        <div className="relative">
                                            <input
                                                type={showMoodleToken ? 'text' : 'password'}
                                                value={data.moodle_token}
                                                onChange={e => setData('moodle_token', e.target.value)}
                                                placeholder="Token generado en Moodle Web Services"
                                                className="w-full px-4 py-2.5 pr-10 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary font-mono text-xs"
                                            />
                                            <button
                                                type="button"
                                                onClick={() => setShowMoodleToken(!showMoodleToken)}
                                                className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                                            >
                                                {showMoodleToken ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                                            </button>
                                        </div>
                                        <p className="text-[11px] text-slate-500 mt-1">Generado con permisos para core_course_get_courses y auth_userkey.</p>
                                    </div>
                                </div>

                                <div className="flex flex-wrap items-center justify-between gap-3 pt-2">
                                    <div className="text-xs text-slate-500 dark:text-slate-400">
                                        Ruta de SSO para Estudiantes: <code className="bg-slate-100 dark:bg-slate-800 px-2 py-0.5 rounded font-mono text-slate-700 dark:text-slate-300">/student/moodle-auth</code>
                                    </div>

                                    <div className="flex items-center gap-3">
                                        <button
                                            type="button"
                                            onClick={handleTestMoodle}
                                            disabled={isTestingMoodle || !data.moodle_url}
                                            className="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-xs transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50"
                                        >
                                            {isTestingMoodle ? (
                                                <>
                                                    <Loader2 className="w-4 h-4 animate-spin" />
                                                    <span>Probando conexión...</span>
                                                </>
                                            ) : (
                                                <>
                                                    <ShieldCheck className="w-4 h-4" />
                                                    <span>Probar Conexión Moodle</span>
                                                </>
                                            )}
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {/* --- SECCIÓN 3: FACTURACIÓN EXTERNA GRIDBASE BILLS --- */}
                            <div className="bg-white dark:bg-[#161622] rounded-2xl p-6 sm:p-8 border border-slate-200 dark:border-white/5 shadow-xs space-y-6">
                                <div className="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-white/5">
                                    <div className="flex items-center gap-3">
                                        <div className="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-200 dark:border-indigo-900 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-bold">
                                            <FileText className="w-5 h-5" />
                                        </div>
                                        <div>
                                            <h2 className="text-lg font-bold text-slate-800 dark:text-white">
                                                Facturación Externa Gridbase Bills API
                                            </h2>
                                            <p className="text-xs text-slate-500 dark:text-slate-400">
                                                Emisión de facturas fiscales y sincronización con el sistema externo Bills.
                                            </p>
                                        </div>
                                    </div>

                                    <label className="relative inline-flex items-center cursor-pointer">
                                        <input
                                            type="checkbox"
                                            checked={data.enable_bills_invoicing}
                                            onChange={e => setData('enable_bills_invoicing', e.target.checked)}
                                            className="sr-only peer"
                                        />
                                        <div className="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-sga-primary"></div>
                                    </label>
                                </div>

                                {data.enable_bills_invoicing && (
                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-5 pt-2">
                                        <div className="md:col-span-2">
                                            <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                                URL Base de Gridbase Bills API (incluyendo /api/v1)
                                            </label>
                                            <input
                                                type="url"
                                                value={data.bills_api_url}
                                                onChange={e => setData('bills_api_url', e.target.value)}
                                                className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary font-mono text-xs"
                                            />
                                        </div>

                                        <div>
                                            <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                                Tasa ITBIS por Defecto (%)
                                            </label>
                                            <input
                                                type="number"
                                                min="0"
                                                max="100"
                                                value={data.bills_default_tax_rate}
                                                onChange={e => setData('bills_default_tax_rate', e.target.value)}
                                                className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary font-mono text-xs"
                                            />
                                        </div>

                                        <div className="md:col-span-3">
                                            <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                                Bearer Token de API (empieza con gb_)
                                            </label>
                                            <div className="relative">
                                                <input
                                                    type={showBillsToken ? 'text' : 'password'}
                                                    value={data.bills_api_token}
                                                    onChange={e => setData('bills_api_token', e.target.value)}
                                                    placeholder="gb_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx"
                                                    className="w-full px-4 py-2.5 pr-10 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary font-mono text-xs"
                                                />
                                                <button
                                                    type="button"
                                                    onClick={() => setShowBillsToken(!showBillsToken)}
                                                    className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                                >
                                                    {showBillsToken ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                                                </button>
                                            </div>
                                        </div>

                                        <div className="md:col-span-3 flex flex-wrap items-center justify-between gap-3 pt-3 border-t border-slate-100 dark:border-white/5">
                                            <div className="text-xs text-slate-500 dark:text-slate-400">
                                                Estado de Integración: <span className="font-semibold text-slate-700 dark:text-slate-200">{data.bills_api_token ? 'Token Configurado' : 'Sin Token Configurado'}</span>
                                            </div>

                                            <div className="flex items-center gap-3">
                                                <button
                                                    type="button"
                                                    onClick={handleTestBills}
                                                    disabled={isTestingBills || !data.bills_api_url || !data.bills_api_token}
                                                    className="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-xs transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50"
                                                >
                                                    {isTestingBills ? (
                                                        <>
                                                            <Loader2 className="w-4 h-4 animate-spin" />
                                                            <span>Probando conexión con Bills...</span>
                                                        </>
                                                    ) : (
                                                        <>
                                                            <ShieldCheck className="w-4 h-4" />
                                                            <span>Probar Conexión con Bills</span>
                                                        </>
                                                    )}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}

                    {/* ══════════════════════════════════════════════════════════════
                        TAB 2: IDENTIDAD & CONTACTO
                    ══════════════════════════════════════════════════════════════ */}
                    {activeTab === 'identity' && (
                        <div className="bg-white dark:bg-[#161622] rounded-2xl p-6 sm:p-8 border border-slate-200 dark:border-white/5 shadow-xs space-y-6">
                            <h2 className="text-lg font-bold text-slate-800 dark:text-white pb-3 border-b border-slate-100 dark:border-white/5">
                                Información Institucional
                            </h2>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div className="md:col-span-2">
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                        Nombre Oficial de la Institución
                                    </label>
                                    <input
                                        type="text"
                                        value={data.institution_name}
                                        onChange={e => setData('institution_name', e.target.value)}
                                        className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary"
                                        required
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                        Correo de Soporte Institucional
                                    </label>
                                    <input
                                        type="email"
                                        value={data.support_email}
                                        onChange={e => setData('support_email', e.target.value)}
                                        className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary"
                                        required
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                        Teléfono de Contacto
                                    </label>
                                    <input
                                        type="text"
                                        value={data.contact_phone}
                                        onChange={e => setData('contact_phone', e.target.value)}
                                        placeholder="809-555-0100"
                                        className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary"
                                    />
                                </div>

                                <div className="md:col-span-2">
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                        Dirección Física
                                    </label>
                                    <input
                                        type="text"
                                        value={data.contact_address}
                                        onChange={e => setData('contact_address', e.target.value)}
                                        placeholder="Autopista Las Américas, Km. 27, La Caleta, Boca Chica"
                                        className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                        Sitio Web Oficial
                                    </label>
                                    <input
                                        type="url"
                                        value={data.website_url}
                                        onChange={e => setData('website_url', e.target.value)}
                                        placeholder="https://itla.edu.do"
                                        className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                        Página de Facebook
                                    </label>
                                    <input
                                        type="url"
                                        value={data.social_facebook}
                                        onChange={e => setData('social_facebook', e.target.value)}
                                        placeholder="https://facebook.com/itla"
                                        className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary"
                                    />
                                </div>
                            </div>
                        </div>
                    )}

                    {/* ══════════════════════════════════════════════════════════════
                        TAB 3: MARCA & APARIENCIA
                    ══════════════════════════════════════════════════════════════ */}
                    {activeTab === 'branding' && (
                        <div className="bg-white dark:bg-[#161622] rounded-2xl p-6 sm:p-8 border border-slate-200 dark:border-white/5 shadow-xs space-y-6">
                            <h2 className="text-lg font-bold text-slate-800 dark:text-white pb-3 border-b border-slate-100 dark:border-white/5">
                                Identidad Gráfica Institucional
                            </h2>

                            {/* --- SECCIÓN: PALETA DE IDENTIDAD INSTITUCIONAL (4 COLORES) --- */}
                            <div className="p-6 rounded-2xl bg-slate-50/70 dark:bg-white/[0.02] border border-slate-200/80 dark:border-white/5 space-y-5">
                                <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200/60 dark:border-white/5">
                                    <div>
                                        <h3 className="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                            <Palette className="w-4 h-4 text-sga-primary dark:text-blue-400" />
                                            Paleta de Identidad Institucional (Armonía Global)
                                        </h3>
                                        <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                            Define una paleta armónica de 4 colores que sustituye los colores genéricos del dashboard, gráficos y módulos para cualquier institución.
                                        </p>
                                    </div>
                                    <span className="text-[11px] font-bold text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-white/10 px-2.5 py-1 rounded-lg self-start sm:self-auto">
                                        4 Colores Coordinados
                                    </span>
                                </div>

                                {/* Presets Rápidos */}
                                <div>
                                    <span className="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2">
                                        Plantillas de Marca Preconfiguradas (1 Clic)
                                    </span>
                                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2.5">
                                        {[
                                            {
                                                name: 'Academic+ (Default)',
                                                tag: 'Índigo / Naranja Oficial',
                                                p: '#211170',
                                                s: '#1a0a53',
                                                a: '#f7931e',
                                                n: '#64748b',
                                                isDefault: true
                                            },
                                            {
                                                name: 'Universidad Azul',
                                                tag: 'Académico / Cobalto',
                                                p: '#0b2c79',
                                                s: '#1e3a8a',
                                                a: '#0284c7',
                                                n: '#64748b'
                                            },
                                            {
                                                name: 'Instituto Tech',
                                                tag: 'Esmeralda / Bosque',
                                                p: '#0f766e',
                                                s: '#064e3b',
                                                a: '#10b981',
                                                n: '#6b7280'
                                            },
                                            {
                                                name: 'Prestigio & Artes',
                                                tag: 'Borgoña / Vino',
                                                p: '#881337',
                                                s: '#4c0519',
                                                a: '#d97706',
                                                n: '#71717a'
                                            },
                                            {
                                                name: 'Minimal Grafito',
                                                tag: 'Neutro Monocromo',
                                                p: '#18181b',
                                                s: '#27272a',
                                                a: '#52525b',
                                                n: '#a1a1aa'
                                            }
                                        ].map((preset, idx) => (
                                            <button
                                                key={idx}
                                                type="button"
                                                onClick={() => {
                                                    setData(prev => ({
                                                        ...prev,
                                                        brand_primary_color: preset.p,
                                                        brand_secondary_color: preset.s,
                                                        brand_accent_color: preset.a,
                                                        brand_neutral_color: preset.n,
                                                        ...(preset.isDefault ? {
                                                            institution_name: 'Academic+',
                                                            institution_logo: '/branding/academic-logo.png',
                                                            sidebar_logo_height: 44,
                                                            favicon: '/branding/academic-favicon.png',
                                                            app_icon: '/branding/academic-favicon.png',
                                                            login_banner: '',
                                                            navbar_type: 'gradient',
                                                            navbar_gradient_start: '#211170',
                                                            navbar_gradient_end: '#1a0a53',
                                                            navbar_gradient_direction: 'to bottom right',
                                                        } : {}),
                                                        ...(!preset.isDefault && (prev.navbar_type === 'solid' || prev.navbar_gradient_start === prev.brand_primary_color) ? { navbar_gradient_start: preset.p } : {})
                                                    }));
                                                }}
                                                className="p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 hover:border-sga-primary dark:hover:border-blue-400 text-left transition-all group cursor-pointer shadow-2xs"
                                            >
                                                <div className="flex items-center gap-1 mb-1.5">
                                                    <span style={{ backgroundColor: preset.p }} className="size-3 rounded-full" />
                                                    <span style={{ backgroundColor: preset.s }} className="size-3 rounded-full" />
                                                    <span style={{ backgroundColor: preset.a }} className="size-3 rounded-full" />
                                                    <span style={{ backgroundColor: preset.n }} className="size-3 rounded-full" />
                                                </div>
                                                <div className="text-xs font-bold text-slate-800 dark:text-white leading-tight">
                                                    {preset.name}
                                                </div>
                                                <div className="text-[10px] text-slate-400 mt-0.5 leading-tight">
                                                    {preset.tag}
                                                </div>
                                            </button>
                                        ))}
                                    </div>
                                </div>

                                {/* 4 Color Pickers Grid */}
                                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-1">
                                    {/* Color 1: Primario */}
                                    <div className="bg-white dark:bg-slate-900 p-3.5 rounded-xl border border-slate-200 dark:border-white/10 space-y-2">
                                        <div className="flex items-center justify-between">
                                            <span className="text-xs font-bold text-slate-700 dark:text-slate-300">1. Color Primario</span>
                                            <span className="text-[9px] font-bold text-sga-primary dark:text-blue-400 uppercase bg-blue-50 dark:bg-blue-950/60 px-1.5 py-0.5 rounded">Marca</span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <input
                                                type="color"
                                                value={data.brand_primary_color}
                                                onChange={e => {
                                                    const val = e.target.value;
                                                    setData(prev => ({
                                                        ...prev,
                                                        brand_primary_color: val,
                                                        ...(prev.navbar_type === 'solid' || prev.navbar_gradient_start === prev.brand_primary_color ? { navbar_gradient_start: val } : {})
                                                    }));
                                                }}
                                                className="size-9 rounded-lg border border-slate-200 dark:border-white/10 cursor-pointer p-0.5 bg-white dark:bg-slate-900 shrink-0"
                                            />
                                            <input
                                                type="text"
                                                value={data.brand_primary_color}
                                                onChange={e => {
                                                    const val = e.target.value;
                                                    setData(prev => ({
                                                        ...prev,
                                                        brand_primary_color: val,
                                                        ...(prev.navbar_type === 'solid' || prev.navbar_gradient_start === prev.brand_primary_color ? { navbar_gradient_start: val } : {})
                                                    }));
                                                }}
                                                className="w-full px-2.5 py-1.5 rounded-lg bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-xs font-mono text-slate-800 dark:text-white"
                                            />
                                        </div>
                                        <p className="text-[10px] text-slate-400">Sidebar, logos, botones clave y curvas maestras.</p>
                                    </div>

                                    {/* Color 2: Secundario */}
                                    <div className="bg-white dark:bg-slate-900 p-3.5 rounded-xl border border-slate-200 dark:border-white/10 space-y-2">
                                        <div className="flex items-center justify-between">
                                            <span className="text-xs font-bold text-slate-700 dark:text-slate-300">2. Color Secundario</span>
                                            <span className="text-[9px] font-bold text-slate-600 dark:text-slate-400 uppercase bg-slate-100 dark:bg-white/10 px-1.5 py-0.5 rounded">Soporte</span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <input
                                                type="color"
                                                value={data.brand_secondary_color}
                                                onChange={e => setData('brand_secondary_color', e.target.value)}
                                                className="size-9 rounded-lg border border-slate-200 dark:border-white/10 cursor-pointer p-0.5 bg-white dark:bg-slate-900 shrink-0"
                                            />
                                            <input
                                                type="text"
                                                value={data.brand_secondary_color}
                                                onChange={e => setData('brand_secondary_color', e.target.value)}
                                                className="w-full px-2.5 py-1.5 rounded-lg bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-xs font-mono text-slate-800 dark:text-white"
                                            />
                                        </div>
                                        <p className="text-[10px] text-slate-400">Fondos de alto contraste, turnos y pilares secundarios.</p>
                                    </div>

                                    {/* Color 3: Acento */}
                                    <div className="bg-white dark:bg-slate-900 p-3.5 rounded-xl border border-slate-200 dark:border-white/10 space-y-2">
                                        <div className="flex items-center justify-between">
                                            <span className="text-xs font-bold text-slate-700 dark:text-slate-300">3. Color Acento</span>
                                            <span className="text-[9px] font-bold text-amber-600 dark:text-amber-400 uppercase bg-amber-50 dark:bg-amber-950/60 px-1.5 py-0.5 rounded">Realce</span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <input
                                                type="color"
                                                value={data.brand_accent_color}
                                                onChange={e => setData('brand_accent_color', e.target.value)}
                                                className="size-9 rounded-lg border border-slate-200 dark:border-white/10 cursor-pointer p-0.5 bg-white dark:bg-slate-900 shrink-0"
                                            />
                                            <input
                                                type="text"
                                                value={data.brand_accent_color}
                                                onChange={e => setData('brand_accent_color', e.target.value)}
                                                className="w-full px-2.5 py-1.5 rounded-lg bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-xs font-mono text-slate-800 dark:text-white"
                                            />
                                        </div>
                                        <p className="text-[10px] text-slate-400">Llamadas a la acción, acentos y turnos vespertinos.</p>
                                    </div>

                                    {/* Color 4: Neutro */}
                                    <div className="bg-white dark:bg-slate-900 p-3.5 rounded-xl border border-slate-200 dark:border-white/10 space-y-2">
                                        <div className="flex items-center justify-between">
                                            <span className="text-xs font-bold text-slate-700 dark:text-slate-300">4. Color Neutro</span>
                                            <span className="text-[9px] font-bold text-slate-500 uppercase bg-slate-100 dark:bg-white/10 px-1.5 py-0.5 rounded">Base</span>
                                        </div>
                                        <div className="flex items-center gap-2">
                                            <input
                                                type="color"
                                                value={data.brand_neutral_color}
                                                onChange={e => setData('brand_neutral_color', e.target.value)}
                                                className="size-9 rounded-lg border border-slate-200 dark:border-white/10 cursor-pointer p-0.5 bg-white dark:bg-slate-900 shrink-0"
                                            />
                                            <input
                                                type="text"
                                                value={data.brand_neutral_color}
                                                onChange={e => setData('brand_neutral_color', e.target.value)}
                                                className="w-full px-2.5 py-1.5 rounded-lg bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-xs font-mono text-slate-800 dark:text-white"
                                            />
                                        </div>
                                        <p className="text-[10px] text-slate-400">Gráficos de fondo, turnos nocturnos e indicadores base.</p>
                                    </div>
                                </div>

                                {/* Vista Previa Dinámica de la Paleta */}
                                <div className="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div>
                                        <span className="text-xs font-bold text-slate-800 dark:text-white block">
                                            Vista Previa de la Paleta Institucional
                                        </span>
                                        <span className="text-[11px] text-slate-500">
                                            Esta composición cromática sustituye los colores de plantilla genéricos en todo el panel.
                                        </span>
                                    </div>
                                    <div className="flex flex-wrap items-center gap-2">
                                        <div 
                                            style={{ backgroundColor: `${data.brand_primary_color}18`, color: data.brand_primary_color, borderColor: `${data.brand_primary_color}40` }}
                                            className="px-2.5 py-1 rounded-lg text-xs font-bold border flex items-center gap-1.5"
                                        >
                                            <span style={{ backgroundColor: data.brand_primary_color }} className="size-2 rounded-full" />
                                            Primario
                                        </div>
                                        <div 
                                            style={{ backgroundColor: `${data.brand_secondary_color}18`, color: data.brand_secondary_color, borderColor: `${data.brand_secondary_color}40` }}
                                            className="px-2.5 py-1 rounded-lg text-xs font-bold border flex items-center gap-1.5"
                                        >
                                            <span style={{ backgroundColor: data.brand_secondary_color }} className="size-2 rounded-full" />
                                            Secundario
                                        </div>
                                        <div 
                                            style={{ backgroundColor: `${data.brand_accent_color}18`, color: data.brand_accent_color, borderColor: `${data.brand_accent_color}40` }}
                                            className="px-2.5 py-1 rounded-lg text-xs font-bold border flex items-center gap-1.5"
                                        >
                                            <span style={{ backgroundColor: data.brand_accent_color }} className="size-2 rounded-full" />
                                            Acento
                                        </div>
                                        <div 
                                            style={{ backgroundColor: `${data.brand_neutral_color}18`, color: data.brand_neutral_color, borderColor: `${data.brand_neutral_color}40` }}
                                            className="px-2.5 py-1 rounded-lg text-xs font-bold border flex items-center gap-1.5"
                                        >
                                            <span style={{ backgroundColor: data.brand_neutral_color }} className="size-2 rounded-full" />
                                            Neutro
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-5 pt-2">
                                <div>
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                        Estilo de Barra de Navegación
                                    </label>
                                    <select
                                        value={data.navbar_type}
                                        onChange={e => setData('navbar_type', e.target.value)}
                                        className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary"
                                    >
                                        <option value="solid">Color Sólido (Usa el color primario)</option>
                                        <option value="gradient">Degradado Lineal Personalizado</option>
                                    </select>
                                </div>

                                {data.navbar_type === 'gradient' && (
                                    <>
                                        <div>
                                            <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                                Inicio del Degradado
                                            </label>
                                            <div className="flex items-center gap-3">
                                                <input
                                                    type="color"
                                                    value={data.navbar_gradient_start}
                                                    onChange={e => setData('navbar_gradient_start', e.target.value)}
                                                    className="h-10 w-16 rounded-xl border border-slate-200 dark:border-white/10 cursor-pointer p-0.5 bg-white dark:bg-slate-900"
                                                />
                                                <input
                                                    type="text"
                                                    value={data.navbar_gradient_start}
                                                    onChange={e => setData('navbar_gradient_start', e.target.value)}
                                                    className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm font-mono"
                                                />
                                            </div>
                                        </div>

                                        <div>
                                            <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                                Fin del Degradado
                                            </label>
                                            <div className="flex items-center gap-3">
                                                <input
                                                    type="color"
                                                    value={data.navbar_gradient_end}
                                                    onChange={e => setData('navbar_gradient_end', e.target.value)}
                                                    className="h-10 w-16 rounded-xl border border-slate-200 dark:border-white/10 cursor-pointer p-0.5 bg-white dark:bg-slate-900"
                                                />
                                                <input
                                                    type="text"
                                                    value={data.navbar_gradient_end}
                                                    onChange={e => setData('navbar_gradient_end', e.target.value)}
                                                    className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm font-mono"
                                                />
                                            </div>
                                        </div>

                                        <div className="md:col-span-2">
                                            <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                                Dirección del Degradado
                                            </label>
                                            <select
                                                value={data.navbar_gradient_direction}
                                                onChange={e => setData('navbar_gradient_direction', e.target.value)}
                                                className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm focus:outline-none focus:border-sga-primary"
                                            >
                                                <option value="to bottom right">Diagonal (Hacia abajo y derecha)</option>
                                                <option value="to bottom">Vertical (De arriba hacia abajo)</option>
                                                <option value="to right">Horizontal (De izquierda a derecha)</option>
                                                <option value="to bottom left">Diagonal Inversa (Hacia abajo e izquierda)</option>
                                            </select>
                                        </div>
                                    </>
                                )}

                                <div className="md:col-span-2">
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                        Ruta del Logotipo Institucional
                                    </label>
                                    <input
                                        type="text"
                                        value={data.institution_logo}
                                        onChange={e => setData('institution_logo', e.target.value)}
                                        className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm font-mono text-xs"
                                    />
                                </div>

                                {/* Control de Tamaño del Logo en el Sidebar */}
                                <div className="md:col-span-2 p-5 rounded-2xl bg-slate-50/80 dark:bg-white/[0.02] border border-slate-200/80 dark:border-white/10 space-y-4">
                                    <div className="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                        <div>
                                            <label className="block text-xs font-bold text-slate-800 dark:text-white uppercase tracking-wide">
                                                Tamaño del Logo en el Sidebar (Menú Lateral)
                                            </label>
                                            <p className="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                                Ajusta la altura visual del logotipo en la cabecera del menú lateral izquierdo (entre 24px y 120px).
                                            </p>
                                        </div>
                                        <div className="flex items-center gap-2 self-start sm:self-auto">
                                            <input
                                                type="number"
                                                min={24}
                                                max={120}
                                                value={data.sidebar_logo_height}
                                                onChange={e => {
                                                    const val = Math.min(120, Math.max(24, Number(e.target.value) || 44));
                                                    setData('sidebar_logo_height', val);
                                                    document.documentElement.style.setProperty('--sidebar-logo-height', `${val}px`);
                                                }}
                                                className="w-20 px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-white/10 text-xs font-mono font-bold text-center text-slate-800 dark:text-white"
                                            />
                                            <span className="text-xs font-bold text-slate-500 dark:text-slate-400">px</span>
                                        </div>
                                    </div>

                                    <div className="grid grid-cols-1 lg:grid-cols-12 gap-5 items-center">
                                        <div className="lg:col-span-7 space-y-3">
                                            <div className="flex items-center gap-3">
                                                <span className="text-[11px] font-mono text-slate-400">24px</span>
                                                <input
                                                    type="range"
                                                    min={24}
                                                    max={120}
                                                    step={2}
                                                    value={data.sidebar_logo_height}
                                                    onChange={e => {
                                                        const val = Number(e.target.value);
                                                        setData('sidebar_logo_height', val);
                                                        document.documentElement.style.setProperty('--sidebar-logo-height', `${val}px`);
                                                    }}
                                                    className="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-sga-primary"
                                                />
                                                <span className="text-[11px] font-mono text-slate-400">120px</span>
                                            </div>

                                            <div className="flex flex-wrap items-center gap-2 pt-1">
                                                {[
                                                    { label: 'Compacto (36px)', value: 36 },
                                                    { label: 'Estándar (44px)', value: 44 },
                                                    { label: 'Mediano (56px)', value: 56 },
                                                    { label: 'Grande (72px)', value: 72 },
                                                    { label: 'Extra Grande (88px)', value: 88 },
                                                ].map(sizePreset => (
                                                    <button
                                                        key={sizePreset.value}
                                                        type="button"
                                                        onClick={() => {
                                                            setData('sidebar_logo_height', sizePreset.value);
                                                            document.documentElement.style.setProperty('--sidebar-logo-height', `${sizePreset.value}px`);
                                                        }}
                                                        className={`px-3 py-1.5 rounded-lg text-xs font-semibold transition-all cursor-pointer border ${
                                                            Number(data.sidebar_logo_height) === sizePreset.value
                                                                ? 'bg-sga-primary text-white border-sga-primary shadow-2xs'
                                                                : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-white/10 hover:border-sga-primary'
                                                        }`}
                                                    >
                                                        {sizePreset.label}
                                                    </button>
                                                ))}
                                            </div>
                                        </div>

                                        {/* Mini Vista Previa en Tiempo Real de la Cabecera del Sidebar */}
                                        <div className="lg:col-span-5">
                                            <div
                                                className="rounded-xl p-4 border border-white/15 flex flex-col items-start justify-center min-h-[110px] shadow-inner overflow-hidden"
                                                style={{
                                                    background: data.navbar_type === 'gradient'
                                                        ? `linear-gradient(${data.navbar_gradient_direction || 'to bottom right'}, ${data.navbar_gradient_start || data.brand_primary_color}, ${data.navbar_gradient_end || '#1a0a53'})`
                                                        : (data.brand_primary_color || '#211170')
                                                }}
                                            >
                                                <span className="text-[10px] uppercase tracking-wider font-bold text-white/60 mb-2">
                                                    Vista previa en Sidebar ({data.sidebar_logo_height}px)
                                                </span>
                                                <img
                                                    src={data.institution_logo || '/branding/academic-logo.png'}
                                                    alt="Vista previa logo sidebar"
                                                    style={{
                                                        height: `${data.sidebar_logo_height}px`,
                                                        maxHeight: `${data.sidebar_logo_height}px`,
                                                    }}
                                                    className="w-auto max-w-[215px] object-contain drop-shadow transition-all duration-150"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                        Favicon URL
                                    </label>
                                    <input
                                        type="text"
                                        value={data.favicon}
                                        onChange={e => setData('favicon', e.target.value)}
                                        className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm font-mono text-xs"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                        Banner Lateral de Inicio de Sesión
                                    </label>
                                    <input
                                        type="text"
                                        value={data.login_banner}
                                        onChange={e => setData('login_banner', e.target.value)}
                                        className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm font-mono text-xs"
                                    />
                                </div>
                            </div>
                        </div>
                    )}

                    {/* ══════════════════════════════════════════════════════════════
                        TAB 4: PASARELAS & DGII
                    ══════════════════════════════════════════════════════════════ */}
                    {activeTab === 'finance' && (
                        <div className="bg-white dark:bg-[#161622] rounded-2xl p-6 sm:p-8 border border-slate-200 dark:border-white/5 shadow-xs space-y-6">
                            <h2 className="text-lg font-bold text-slate-800 dark:text-white pb-3 border-b border-slate-100 dark:border-white/5">
                                Pasarelas de Pago & Fiscalidad Dominicana
                            </h2>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                        Cardnet Merchant ID
                                    </label>
                                    <input
                                        type="text"
                                        value={data.cardnet_merchant_id}
                                        onChange={e => setData('cardnet_merchant_id', e.target.value)}
                                        placeholder="349000000"
                                        className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm font-mono"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                        Cardnet Terminal ID
                                    </label>
                                    <input
                                        type="text"
                                        value={data.cardnet_terminal_id}
                                        onChange={e => setData('cardnet_terminal_id', e.target.value)}
                                        placeholder="58585858"
                                        className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm font-mono"
                                    />
                                </div>

                                <div className="md:col-span-2 pt-2 border-t border-slate-100 dark:border-white/5">
                                    <div className="flex items-center justify-between mb-4">
                                        <div>
                                            <span className="text-sm font-bold text-slate-800 dark:text-white">Facturación Electrónica DGII (e-CF)</span>
                                            <p className="text-xs text-slate-500">Emisión de NCF tipo B01, B02, B14 y reportes de formato 606 y 607.</p>
                                        </div>
                                        <label className="relative inline-flex items-center cursor-pointer">
                                            <input
                                                type="checkbox"
                                                checked={data.enable_electronic_billing}
                                                onChange={e => setData('enable_electronic_billing', e.target.checked)}
                                                className="sr-only peer"
                                            />
                                            <div className="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-sga-primary"></div>
                                        </label>
                                    </div>

                                    {data.enable_electronic_billing && (
                                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                                    RNC Emisor Institucional
                                                </label>
                                                <input
                                                    type="text"
                                                    value={data.ecf_rnc_emisor}
                                                    onChange={e => setData('ecf_rnc_emisor', e.target.value)}
                                                    placeholder="101000000"
                                                    className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm font-mono"
                                                />
                                            </div>
                                        </div>
                                    )}
                                </div>
                            </div>
                        </div>
                    )}

                    {/* ══════════════════════════════════════════════════════════════
                        TAB 5: CORREO / SMTP
                    ══════════════════════════════════════════════════════════════ */}
                    {activeTab === 'smtp' && (
                        <div className="bg-white dark:bg-[#161622] rounded-2xl p-6 sm:p-8 border border-slate-200 dark:border-white/5 shadow-xs space-y-6">
                            <h2 className="text-lg font-bold text-slate-800 dark:text-white pb-3 border-b border-slate-100 dark:border-white/5">
                                Configuración de Servidor de Correo (SMTP)
                            </h2>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                        Servidor SMTP (Host)
                                    </label>
                                    <input
                                        type="text"
                                        value={data.smtp_host}
                                        onChange={e => setData('smtp_host', e.target.value)}
                                        placeholder="mail.institucion.edu.do"
                                        className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm font-mono"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                        Puerto SMTP
                                    </label>
                                    <input
                                        type="text"
                                        value={data.smtp_port}
                                        onChange={e => setData('smtp_port', e.target.value)}
                                        placeholder="587 o 465"
                                        className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm font-mono"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                        Usuario SMTP
                                    </label>
                                    <input
                                        type="text"
                                        value={data.smtp_username}
                                        onChange={e => setData('smtp_username', e.target.value)}
                                        className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm font-mono"
                                    />
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                        Contraseña SMTP
                                    </label>
                                    <div className="relative">
                                        <input
                                            type={showSmtpPassword ? 'text' : 'password'}
                                            value={data.smtp_password}
                                            onChange={e => setData('smtp_password', e.target.value)}
                                            className="w-full px-4 py-2.5 pr-10 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm font-mono"
                                        />
                                        <button
                                            type="button"
                                            onClick={() => setShowSmtpPassword(!showSmtpPassword)}
                                            className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
                                        >
                                            {showSmtpPassword ? <EyeOff className="w-4 h-4" /> : <Eye className="w-4 h-4" />}
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                        Cifrado de Conexión
                                    </label>
                                    <select
                                        value={data.smtp_encryption}
                                        onChange={e => setData('smtp_encryption', e.target.value)}
                                        className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm"
                                    >
                                        <option value="tls">TLS</option>
                                        <option value="ssl">SSL</option>
                                        <option value="">Ninguno</option>
                                    </select>
                                </div>

                                <div>
                                    <label className="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase mb-1.5">
                                        Dirección de Remitente (De)
                                    </label>
                                    <input
                                        type="email"
                                        value={data.smtp_from_address}
                                        onChange={e => setData('smtp_from_address', e.target.value)}
                                        placeholder="no-reply@institucion.edu"
                                        className="w-full px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-200 dark:border-white/10 text-slate-800 dark:text-white text-sm font-mono"
                                    />
                                </div>
                            </div>
                        </div>
                    )}

                    {/* ══════════════════════════════════════════════════════════════
                        FOOTER CON STATUS BAR
                    ══════════════════════════════════════════════════════════════ */}
                    <div className="bg-white dark:bg-[#161622] rounded-2xl p-5 border border-slate-200 dark:border-white/5 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div className="flex flex-wrap items-center gap-3">
                            {isWpConnected ? (
                                <span className="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                    <span className="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Academic+ Catalog conectado
                                </span>
                            ) : (
                                <span className="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                    <span className="h-2 w-2 rounded-full bg-slate-400"></span>
                                    WordPress desconectado
                                </span>
                            )}

                            {isMoodleConnected ? (
                                <span className="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/50 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                    <span className="h-2 w-2 rounded-full bg-amber-500"></span>
                                    Moodle conectado
                                </span>
                            ) : (
                                <span className="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                    <span className="h-2 w-2 rounded-full bg-slate-400"></span>
                                    Moodle desconectado
                                </span>
                            )}
                        </div>

                        <div className="flex items-center gap-3">
                            <button
                                type="submit"
                                disabled={processing}
                                className="px-6 py-2.5 rounded-xl bg-sga-primary hover:bg-blue-700 text-white font-bold text-sm shadow-md transition-all flex items-center gap-2 cursor-pointer disabled:opacity-50"
                            >
                                {processing ? (
                                    <>
                                        <Loader2 className="w-4 h-4 animate-spin" />
                                        <span>Guardando...</span>
                                    </>
                                ) : (
                                    <>
                                        <Save className="w-4 h-4" />
                                        <span>Guardar Configuraciones</span>
                                    </>
                                )}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
