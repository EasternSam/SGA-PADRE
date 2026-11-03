import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

function IllustrationSelfCheckout({ primary = '#181956', secondary = '#064e3b', accent = '#10b981' }) {
    return (
        <svg className="w-24 h-24 sm:w-28 sm:h-28 mx-auto" viewBox="0 0 160 160" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M40 66L58 34H116L130 66H40Z" fill={secondary} stroke="#0F172A" strokeWidth="2.5" strokeLinejoin="round" />
            <path d="M58 34L44 66H118L106 34H58Z" fill={primary} fillOpacity="0.16" stroke="#0F172A" strokeWidth="2.5" strokeLinejoin="round" />
            <rect x="63" y="40" width="38" height="20" rx="2" fill={accent} stroke="#0F172A" strokeWidth="2" />
            <path d="M82 44L84.5 47.5L89 46.5L87 50.5L90 54L85.5 54.5L84 58L81 55L76.5 56L78 51.5L75 48L79.5 47.5L82 44Z" fill="#FFFFFF" stroke="#0F172A" strokeWidth="1.5" />
            <path
                d="M54 66C54 84 68 96 66 112C64 124 54 128 56 136H114C112 126 120 120 118 106C116 92 104 82 104 66H54Z"
                fill="#FFFFFF"
                stroke="#0F172A"
                strokeWidth="2.5"
                strokeLinejoin="round"
            />
            <circle cx="70" cy="84" r="4.5" fill={secondary} stroke="#0F172A" strokeWidth="1.8" />
            <line x1="82" y1="84" x2="104" y2="84" stroke="#0F172A" strokeWidth="2.5" strokeLinecap="round" />
            <circle cx="74" cy="100" r="4.5" fill={primary} stroke="#0F172A" strokeWidth="1.8" />
            <line x1="86" y1="100" x2="108" y2="100" stroke="#0F172A" strokeWidth="2.5" strokeLinecap="round" />
            <line x1="60" y1="118" x2="114" y2="118" stroke="#0F172A" strokeWidth="2" />
            <circle cx="68" cy="127" r="5.5" fill={accent} stroke="#0F172A" strokeWidth="2" />
            <path d="M65.5 127L67.5 129L71 125" stroke="#0F172A" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
            <line x1="80" y1="127" x2="106" y2="127" stroke="#0F172A" strokeWidth="2.5" strokeLinecap="round" />
        </svg>
    );
}

function IllustrationGradesCard({ primary = '#181956', secondary = '#064e3b', accent = '#10b981' }) {
    return (
        <svg className="w-24 h-24 sm:w-28 sm:h-28 mx-auto" viewBox="0 0 160 160" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M40 44L43 52L51 55L43 58L40 66L37 58L29 55L37 52L40 44Z" fill={accent} stroke="#0F172A" strokeWidth="2" strokeLinejoin="round" />
            <path d="M126 66L128.5 72.5L135 75L128.5 77.5L126 84L123.5 77.5L117 75L123.5 72.5L126 66Z" fill={secondary} stroke="#0F172A" strokeWidth="2" strokeLinejoin="round" />
            <rect x="64" y="32" width="44" height="68" rx="7" fill={primary} stroke="#0F172A" strokeWidth="2.5" />
            <rect x="72" y="40" width="8" height="10" rx="2" fill={accent} stroke="#0F172A" strokeWidth="2" />
            <line x1="94" y1="42" x2="94" y2="86" stroke="#FFFFFF" strokeWidth="2" strokeLinecap="round" />
            <line x1="100" y1="48" x2="100" y2="76" stroke="#FFFFFF" strokeWidth="2" strokeLinecap="round" />
            <path
                d="M54 78C54 71 61 68 64 74V96C64 96 72 80 78 76C83 72 88 77 84 84L75 102H94C99 102 99 109 94 109H78C83 109 83 116 78 116H64C54 116 50 102 54 78Z"
                fill="#FFFFFF"
                stroke="#0F172A"
                strokeWidth="2.5"
                strokeLinejoin="round"
            />
            <rect x="98" y="80" width="14" height="7" rx="3.5" fill="#FFFFFF" stroke="#0F172A" strokeWidth="2" />
            <rect x="98" y="88" width="14" height="7" rx="3.5" fill="#FFFFFF" stroke="#0F172A" strokeWidth="2" />
            <rect x="98" y="96" width="12" height="7" rx="3.5" fill="#FFFFFF" stroke="#0F172A" strokeWidth="2" />
        </svg>
    );
}

function IllustrationScheduleAvailability({ primary = '#181956', secondary = '#064e3b', accent = '#10b981' }) {
    return (
        <svg className="w-24 h-24 sm:w-28 sm:h-28 mx-auto" viewBox="0 0 160 160" fill="none" xmlns="http://www.w3.org/2000/svg">
            <line x1="76" y1="24" x2="76" y2="31" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" />
            <line x1="100" y1="30" x2="96" y2="36" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" />
            <line x1="116" y1="48" x2="110" y2="52" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" />
            <line x1="120" y1="72" x2="113" y2="72" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" />
            <line x1="52" y1="30" x2="56" y2="36" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" />
            <line x1="36" y1="48" x2="42" y2="52" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" />
            <line x1="32" y1="72" x2="39" y2="72" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" />
            <line x1="40" y1="96" x2="46" y2="92" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" />
            <line x1="58" y1="112" x2="62" y2="105" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" />
            <path d="M92 94L116 130C118 133 123 130 121 126L98 90" fill={secondary} stroke="#0F172A" strokeWidth="2.5" strokeLinejoin="round" />
            <rect x="105" y="114" width="10" height="6" transform="rotate(-33 105 114)" fill={accent} stroke="#0F172A" strokeWidth="2" />
            <circle cx="76" cy="70" r="32" fill={primary} stroke="#0F172A" strokeWidth="2.5" />
            <circle cx="76" cy="70" r="22" fill="#FFFFFF" stroke="#0F172A" strokeWidth="2.5" />
            <path d="M56 70C62 59 90 59 96 70C90 81 62 81 56 70Z" fill={accent} stroke="#0F172A" strokeWidth="2.2" />
            <path d="M74 61C82 61 88 65 92 70C88 75 82 79 74 79C70 74 70 66 74 61Z" fill={primary} />
        </svg>
    );
}

function IllustrationAcademicOffer({ primary = '#181956', secondary = '#064e3b', accent = '#10b981' }) {
    return (
        <svg className="w-24 h-24 sm:w-28 sm:h-28 mx-auto" viewBox="0 0 160 160" fill="none" xmlns="http://www.w3.org/2000/svg">
            <ellipse cx="80" cy="128" rx="28" ry="8" fill={primary} />
            <path d="M58 112H102L108 126H52L58 112Z" fill={primary} stroke="#0F172A" strokeWidth="2" strokeLinejoin="round" />
            <circle cx="80" cy="76" r="42" fill={secondary} stroke="#0F172A" strokeWidth="2.5" />
            <g transform="rotate(-28 80 76)">
                <rect x="60" y="58" width="38" height="28" rx="2" fill="#FFFFFF" stroke="#0F172A" strokeWidth="2.3" />
                <rect x="65" y="62" width="26" height="18" rx="1" fill={accent} stroke="#0F172A" strokeWidth="2" />
                <path d="M60 86H98L102 92H64L60 86Z" fill="#FFFFFF" stroke="#0F172A" strokeWidth="2" strokeLinejoin="round" />
            </g>
            <circle cx="54" cy="64" r="5" fill="#FFFFFF" stroke="#0F172A" strokeWidth="2" />
            <circle cx="88" cy="46" r="3" fill="#FFFFFF" stroke="#0F172A" strokeWidth="2" />
            <circle cx="106" cy="76" r="3.5" fill="#FFFFFF" stroke="#0F172A" strokeWidth="2" />
            <circle cx="68" cy="102" r="3" fill="#FFFFFF" stroke="#0F172A" strokeWidth="2" />
            <path d="M84 104C94 102 102 95 105 86" stroke="#FFFFFF" strokeWidth="4" strokeLinecap="round" />
        </svg>
    );
}

export default function TouchTerminal({ student }) {
    const { branding = {} } = usePage().props;
    const institutionName = branding?.name || branding?.institution_name || 'Academic+';
    const primaryColor = branding?.primary_color || '#181956';
    const secondaryColor = branding?.secondary_color || '#064e3b';
    const accentColor = branding?.accent_color || '#10b981';

    const modules = [
        {
            title: 'Autopago',
            href: '/kiosk/finances',
            illustration: (
                <IllustrationSelfCheckout
                    primary={primaryColor}
                    secondary={secondaryColor}
                    accent={accentColor}
                />
            ),
        },
        {
            title: 'Calificaciones',
            href: '/kiosk/grades',
            illustration: (
                <IllustrationGradesCard
                    primary={primaryColor}
                    secondary={secondaryColor}
                    accent={accentColor}
                />
            ),
        },
        {
            title: 'Mi Horario',
            href: '/kiosk/schedule',
            illustration: (
                <IllustrationScheduleAvailability
                    primary={primaryColor}
                    secondary={secondaryColor}
                    accent={accentColor}
                />
            ),
        },
        {
            title: 'Oferta Académica',
            href: '/kiosk/academic-offer',
            illustration: (
                <IllustrationAcademicOffer
                    primary={primaryColor}
                    secondary={secondaryColor}
                    accent={accentColor}
                />
            ),
        },
    ];

    return (
        <KioskLayout student={student} showBack={false}>
            <Head title={`Kiosco - ${institutionName}`} />

            <div className="w-full flex flex-col items-center justify-center">
                <h1 className="text-center text-2xl sm:text-3xl md:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight leading-[1.18] mb-6 sm:mb-8">
                    ¡Bienvenido!
                    <br />
                    ¿En qué podemos ayudarte hoy?
                </h1>

                {/* Responsive 2x2 Minimalist Card Grid */}
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-5 w-full">
                    {modules.map((item, idx) => (
                        <Link
                            key={idx}
                            href={item.href}
                            className="group rounded-3xl kiosk-card-interactive p-6 sm:p-7 flex flex-col items-center justify-center text-center hover:-translate-y-1 active:scale-[0.98]"
                        >
                            <div className="mb-3 transform group-hover:scale-105 transition-transform duration-200">
                                {item.illustration}
                            </div>

                            <span className="text-lg sm:text-xl font-bold text-slate-900 dark:text-white tracking-tight">
                                {item.title}
                            </span>
                        </Link>
                    ))}
                </div>

                {/* Minimalist Quick Links */}
                <div className="mt-6 flex flex-wrap items-center justify-center gap-6 text-xs sm:text-sm font-bold">
                    <Link
                        href="/kiosk/hr-punch"
                        style={{ color: primaryColor }}
                        className="dark:!text-emerald-400 hover:underline transition-all flex items-center gap-1.5"
                    >
                        <span>Check-in Personal</span>
                        <span>→</span>
                    </Link>
                    <Link
                        href="/kiosk/signup"
                        style={{ color: accentColor }}
                        className="hover:underline transition-all flex items-center gap-1.5"
                    >
                        <span>Solicitar Admisión</span>
                        <span>→</span>
                    </Link>
                </div>
            </div>
        </KioskLayout>
    );
}
