import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import KioskLayout from '@/Layouts/KioskLayout';

/* Custom Expressive Vector Illustrations (Inspired by Reference Image 1 & 2) */
function IllustrationSelfCheckout() {
    return (
        <svg className="w-36 h-36 mx-auto" viewBox="0 0 160 160" fill="none" xmlns="http://www.w3.org/2000/svg">
            {/* Terminal Stand Base */}
            <path d="M40 66L58 34H116L130 66H40Z" fill="#FB923C" stroke="#111827" strokeWidth="2.5" strokeLinejoin="round" />
            <path d="M58 34L44 66H118L106 34H58Z" fill="#BFDBFE" stroke="#111827" strokeWidth="2.5" strokeLinejoin="round" />
            <rect x="63" y="40" width="38" height="20" rx="2" fill="#FEF08A" stroke="#111827" strokeWidth="2" />
            {/* Starburst on screen */}
            <path d="M82 44L84.5 47.5L89 46.5L87 50.5L90 54L85.5 54.5L84 58L81 55L76.5 56L78 51.5L75 48L79.5 47.5L82 44Z" fill="#F97316" stroke="#111827" strokeWidth="1.5" />
            {/* Flowing Receipt Paper */}
            <path
                d="M54 66C54 84 68 96 66 112C64 124 54 128 56 136H114C112 126 120 120 118 106C116 92 104 82 104 66H54Z"
                fill="#FEFCE8"
                stroke="#111827"
                strokeWidth="2.5"
                strokeLinejoin="round"
            />
            {/* Receipt Hearts & Lines */}
            <circle cx="70" cy="84" r="4.5" fill="#FB7185" stroke="#111827" strokeWidth="1.8" />
            <line x1="82" y1="84" x2="104" y2="84" stroke="#111827" strokeWidth="2.5" strokeLinecap="round" />
            <circle cx="74" cy="100" r="4.5" fill="#FB7185" stroke="#111827" strokeWidth="1.8" />
            <line x1="86" y1="100" x2="108" y2="100" stroke="#111827" strokeWidth="2.5" strokeLinecap="round" />
            {/* Bottom Checkmark Row */}
            <line x1="60" y1="118" x2="114" y2="118" stroke="#111827" strokeWidth="2" />
            <circle cx="68" cy="127" r="5.5" fill="#FEF08A" stroke="#111827" strokeWidth="2" />
            <path d="M65.5 127L67.5 129L71 125" stroke="#111827" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
            <line x1="80" y1="127" x2="106" y2="127" stroke="#111827" strokeWidth="2.5" strokeLinecap="round" />
        </svg>
    );
}

function IllustrationGradesCard() {
    return (
        <svg className="w-36 h-36 mx-auto" viewBox="0 0 160 160" fill="none" xmlns="http://www.w3.org/2000/svg">
            {/* Sparkles */}
            <path d="M40 44L43 52L51 55L43 58L40 66L37 58L29 55L37 52L40 44Z" fill="#FEF08A" stroke="#111827" strokeWidth="2" strokeLinejoin="round" />
            <path d="M126 66L128.5 72.5L135 75L128.5 77.5L126 84L123.5 77.5L117 75L123.5 72.5L126 66Z" fill="#FEF08A" stroke="#111827" strokeWidth="2" strokeLinejoin="round" />
            {/* Vertical Academic Smart Card */}
            <rect x="64" y="32" width="44" height="68" rx="7" fill="#93C5FD" stroke="#111827" strokeWidth="2.5" />
            <rect x="72" y="40" width="8" height="10" rx="2" fill="#FB923C" stroke="#111827" strokeWidth="2" />
            <line x1="94" y1="42" x2="94" y2="86" stroke="#111827" strokeWidth="2" strokeLinecap="round" />
            <line x1="100" y1="48" x2="100" y2="76" stroke="#111827" strokeWidth="2" strokeLinecap="round" />
            {/* Stylized Hand Holding Card */}
            <path
                d="M54 78C54 71 61 68 64 74V96C64 96 72 80 78 76C83 72 88 77 84 84L75 102H94C99 102 99 109 94 109H78C83 109 83 116 78 116H64C54 116 50 102 54 78Z"
                fill="#FEFCE8"
                stroke="#111827"
                strokeWidth="2.5"
                strokeLinejoin="round"
            />
            {/* Finger pills on right side of card */}
            <rect x="98" y="80" width="14" height="7" rx="3.5" fill="#FEFCE8" stroke="#111827" strokeWidth="2" />
            <rect x="98" y="88" width="14" height="7" rx="3.5" fill="#FEFCE8" stroke="#111827" strokeWidth="2" />
            <rect x="98" y="96" width="12" height="7" rx="3.5" fill="#FEFCE8" stroke="#111827" strokeWidth="2" />
        </svg>
    );
}

function IllustrationScheduleAvailability() {
    return (
        <svg className="w-36 h-36 mx-auto" viewBox="0 0 160 160" fill="none" xmlns="http://www.w3.org/2000/svg">
            {/* Rays around lens */}
            <line x1="76" y1="24" x2="76" y2="31" stroke="#111827" strokeWidth="2.2" strokeLinecap="round" />
            <line x1="100" y1="30" x2="96" y2="36" stroke="#111827" strokeWidth="2.2" strokeLinecap="round" />
            <line x1="116" y1="48" x2="110" y2="52" stroke="#111827" strokeWidth="2.2" strokeLinecap="round" />
            <line x1="120" y1="72" x2="113" y2="72" stroke="#111827" strokeWidth="2.2" strokeLinecap="round" />
            <line x1="52" y1="30" x2="56" y2="36" stroke="#111827" strokeWidth="2.2" strokeLinecap="round" />
            <line x1="36" y1="48" x2="42" y2="52" stroke="#111827" strokeWidth="2.2" strokeLinecap="round" />
            <line x1="32" y1="72" x2="39" y2="72" stroke="#111827" strokeWidth="2.2" strokeLinecap="round" />
            <line x1="40" y1="96" x2="46" y2="92" stroke="#111827" strokeWidth="2.2" strokeLinecap="round" />
            <line x1="58" y1="112" x2="62" y2="105" stroke="#111827" strokeWidth="2.2" strokeLinecap="round" />
            {/* Handle */}
            <path d="M92 94L116 130C118 133 123 130 121 126L98 90" fill="#FB923C" stroke="#111827" strokeWidth="2.5" strokeLinejoin="round" />
            <rect x="105" y="114" width="10" height="6" transform="rotate(-33 105 114)" fill="#FEF08A" stroke="#111827" strokeWidth="2" />
            {/* Outer Orange Ring */}
            <circle cx="76" cy="70" r="32" fill="#FB923C" stroke="#111827" strokeWidth="2.5" />
            {/* Inner Lens */}
            <circle cx="76" cy="70" r="22" fill="#E0E7FF" stroke="#111827" strokeWidth="2.5" />
            {/* Eye / Clock Focus Inside Lens */}
            <path d="M56 70C62 59 90 59 96 70C90 81 62 81 56 70Z" fill="#BFDBFE" stroke="#111827" strokeWidth="2.2" />
            <path d="M74 61C82 61 88 65 92 70C88 75 82 79 74 79C70 74 70 66 74 61Z" fill="#1E293B" />
        </svg>
    );
}

function IllustrationAcademicOffer() {
    return (
        <svg className="w-36 h-36 mx-auto" viewBox="0 0 160 160" fill="none" xmlns="http://www.w3.org/2000/svg">
            {/* Pedestal Base */}
            <ellipse cx="80" cy="128" rx="28" ry="8" fill="#111827" />
            <path d="M58 112H102L108 126H52L58 112Z" fill="#111827" stroke="#111827" strokeWidth="2" strokeLinejoin="round" />
            {/* Crystal Globe */}
            <circle cx="80" cy="76" r="42" fill="#93C5FD" stroke="#111827" strokeWidth="2.5" />
            {/* Floating Book Inside Globe */}
            <g transform="rotate(-28 80 76)">
                <rect x="60" y="58" width="38" height="28" rx="2" fill="#FEF08A" stroke="#111827" strokeWidth="2.3" />
                <rect x="65" y="62" width="26" height="18" rx="1" fill="#FB923C" stroke="#111827" strokeWidth="2" />
                <path d="M60 86H98L102 92H64L60 86Z" fill="#FEFCE8" stroke="#111827" strokeWidth="2" strokeLinejoin="round" />
            </g>
            {/* Floating Orbs & Shine */}
            <circle cx="54" cy="64" r="5" fill="#FEFCE8" stroke="#111827" strokeWidth="2" />
            <circle cx="88" cy="46" r="3" fill="#FEFCE8" stroke="#111827" strokeWidth="2" />
            <circle cx="106" cy="76" r="3.5" fill="#FEFCE8" stroke="#111827" strokeWidth="2" />
            <circle cx="68" cy="102" r="3" fill="#FEFCE8" stroke="#111827" strokeWidth="2" />
            <path d="M84 104C94 102 102 95 105 86" stroke="#FEFCE8" strokeWidth="4" strokeLinecap="round" />
        </svg>
    );
}

export default function TouchTerminal({ student }) {
    const { branding = {} } = usePage().props;
    const institutionName = branding?.institution_name || 'ACADEMIC+';

    const modules = [
        {
            title: 'Autopago y Estado de Cuenta',
            subtitle: 'Consulta tu balance, paga con QR o tarjeta',
            href: '/kiosk/finances',
            illustration: <IllustrationSelfCheckout />,
        },
        {
            title: 'Récord de Calificaciones',
            subtitle: 'Consulta tu índice académico y notas',
            href: '/kiosk/grades',
            illustration: <IllustrationGradesCard />,
        },
        {
            title: 'Mi Horario y Aulas',
            subtitle: 'Verifica tus secciones, aulas y docentes',
            href: '/kiosk/schedule',
            illustration: <IllustrationScheduleAvailability />,
        },
        {
            title: 'Explorar Oferta Académica',
            subtitle: 'Descubre carreras, cursos y diplomados',
            href: '/kiosk/academic-offer',
            illustration: <IllustrationAcademicOffer />,
        },
    ];

    return (
        <KioskLayout title="" student={student} showBack={false}>
            <Head title={`Kiosco de Autoservicio - ${institutionName}`} />

            <div className="max-w-4xl mx-auto w-full flex flex-col items-center justify-center py-2 md:py-6">
                {/* Centered Greeting Header (Reference Images 1 & 2) */}
                <div className="text-center max-w-2xl mx-auto mb-8 md:mb-12">
                    {student ? (
                        <p className="text-sm md:text-base font-semibold text-slate-500 dark:text-slate-400 mb-2">
                            Hola, <span className="text-[#166534] dark:text-emerald-400 font-bold">{student.name}</span> • Matrícula {student.student_code}
                        </p>
                    ) : (
                        <p className="text-sm md:text-base font-semibold text-slate-500 dark:text-slate-400 mb-2">
                            Hola, bienvenido a <span className="text-[#166534] dark:text-emerald-400 font-bold">{institutionName}</span>
                        </p>
                    )}

                    <h1 className="text-3xl sm:text-4xl md:text-[42px] font-bold tracking-tight leading-[1.18] text-[#111827] dark:text-white">
                        ¡Bienvenido al Kiosco!
                        <br />
                        ¿En qué podemos ayudarte hoy?
                    </h1>
                </div>

                {/* 2x2 Minimalist Rounded Card Grid (Reference Image 1) */}
                <div className="grid grid-cols-1 sm:grid-cols-2 gap-5 md:gap-7 w-full max-w-3xl">
                    {modules.map((item, idx) => (
                        <Link
                            key={idx}
                            href={item.href}
                            className="group rounded-[28px] bg-[#F3F4F6] hover:bg-[#EAECEF] dark:bg-slate-800/90 dark:hover:bg-slate-800 p-7 md:p-9 flex flex-col items-center justify-center text-center transition-all duration-200 hover:-translate-y-1 active:scale-[0.98] border-2 border-transparent hover:border-[#166534]/30 shadow-xs hover:shadow-md"
                        >
                            <div className="mb-5 transform group-hover:scale-105 transition-transform duration-200">
                                {item.illustration}
                            </div>

                            <h2 className="text-xl md:text-2xl font-bold text-[#111827] dark:text-white tracking-tight">
                                {item.title}
                            </h2>
                            <p className="text-xs md:text-sm text-slate-500 dark:text-slate-400 mt-1.5 max-w-xs">
                                {item.subtitle}
                            </p>
                        </Link>
                    ))}
                </div>

                {/* Bottom Divider + Quick Actions (Reference Image 2 "Or / Need help?") */}
                <div className="mt-8 md:mt-10 flex flex-col items-center gap-3">
                    <span className="text-xs font-semibold uppercase tracking-widest text-slate-400">
                        O también puedes
                    </span>
                    <div className="flex flex-wrap items-center justify-center gap-3">
                        <Link
                            href="/kiosk/hr-punch"
                            className="px-6 py-3 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:border-[#166534] text-sm font-bold text-[#111827] dark:text-white shadow-xs transition-all active:scale-95 flex items-center gap-2.5"
                        >
                            <span className="w-6 h-6 rounded-md bg-[#166534] text-white flex items-center justify-center text-xs">
                                →
                            </span>
                            <span>Check-in / Check-out Colaboradores</span>
                        </Link>
                    </div>
                </div>
            </div>
        </KioskLayout>
    );
}
