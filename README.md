# 🎓 SGA Academic+ (SGA-PADRE)

Sistema de Gestión Académica integral para centros educativos en República Dominicana. Gestiona estudiantes, finanzas, asistencia, calificaciones, certificados, y más.

## Stack Tecnológico

| Componente | Tecnología |
|------------|-----------|
| **Backend** | Laravel 12, PHP 8.2+ |
| **Frontend** | Livewire 3, Blade, Vite |
| **Auth** | Jetstream + Fortify |
| **Permisos** | Spatie Permission |
| **Base de datos** | MySQL 8+ / SQLite |
| **Pagos** | Cardnet (Redirección POST) |
| **PDF** | DomPDF |
| **Auditoría** | Spatie Activity Log |
| **LMS** | Integración Moodle |

## Módulos

- **📚 Gestión Académica** — Estudiantes, profesores, cursos, carreras, matrícula, asistencia, calificaciones
- **💰 Finanzas** — Pagos, cuentas contables, NCF/DGII, gastos, proveedores, cierre de período
- **🖥️ Kiosco** — Terminal de autoservicio con login por PIN
- **🎓 Certificados** — Editor de plantillas, generación y verificación pública QR
- **📊 Reportes** — PDFs de asistencia, calificaciones, estados financieros
- **🔌 Integraciones** — Cardnet, DGII, Moodle, WordPress
- **👥 Portales** — Estudiante, Profesor, Administrador, Aspirante

## Requisitos

- PHP 8.2+
- MySQL 8+ o SQLite
- Composer 2+
- Node.js 18+ y npm
- Extensiones PHP: `mbstring`, `xml`, `curl`, `gd`, `zip`

## Instalación Rápida

```bash
# 1. Clonar
git clone https://github.com/EasternSam/SGA-PADRE.git
cd SGA-PADRE

# 2. Dependencias
composer install
npm install && npm run build

# 3. Configurar
cp .env.example .env
php artisan key:generate

# 4. Base de datos
php artisan migrate:fresh --seed

# 5. Arrancar
php artisan serve
```

O usa el instalador automatizado: `bash install-client.sh`

## Estructura de Rutas

```
routes/
├── web.php        → Rutas públicas, dashboard, diagnóstico
├── auth.php       → Autenticación (login, registro, email)
├── api.php        → API REST (WordPress, inscripciones)
├── admin.php      → Panel administrativo
├── student.php    → Portal del estudiante
├── teacher.php    → Portal del profesor
├── kiosk.php      → Kiosco de autoservicio
├── cardnet.php    → Callbacks de pago Cardnet
└── reports.php    → Generación de PDFs y reportes
```

## Roles del Sistema

| Rol | Acceso |
|-----|--------|
| **Admin** | Acceso total al sistema |
| **Registro** | Gestión de estudiantes y matrícula |
| **Contabilidad** | Finanzas, reportes contables, DGII |
| **Caja** | Recepción de pagos |
| **Profesor** | Portal de calificaciones y asistencia |
| **Estudiante** | Portal de consultas y pagos |
| **Solicitante** | Formulario de admisión |

## Despliegue en Producción (cPanel)

```bash
# Configurar .env con credenciales de producción
APP_ENV=production
APP_DEBUG=false

# Optimizar
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Licencia

Software propietario — © AplusMaster / 90s.agency
