
<h1 align="center">Academic+ by GridBase Digital Solutions</h1>

<p align="center">
  <strong>Sistema de Gestión Académica integral para centros educativos</strong><br>
  Matrícula · Finanzas · Asistencia · Calificaciones · Certificados · Kiosco · Pagos Online<br>
  <em>Creado por Samuel Díaz Pilier</em>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Livewire-3-FB70A9?style=flat-square&logo=livewire&logoColor=white" alt="Livewire 3">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/MySQL-8+-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL 8+">
</p>

---

## 📋 Tabla de Contenidos

- [Descripción](#-descripción)
- [Stack Tecnológico](#-stack-tecnológico)
- [Módulos del Sistema](#-módulos-del-sistema)
- [Arquitectura](#-arquitectura)
- [Requisitos](#-requisitos)
- [Instalación](#-instalación)
- [Configuración](#-configuración)
- [Roles y Permisos](#-roles-y-permisos)
- [Estructura de Rutas](#-estructura-de-rutas)
- [Integraciones Externas](#-integraciones-externas)
- [Tests](#-tests)
- [Despliegue en Producción](#-despliegue-en-producción)
- [Licencia](#-licencia)

---

## 🏫 Descripción

**Academic+ by GridBase Digital Solutions** es un sistema de gestión académica diseñado para centros educativos en **República Dominicana**. Permite administrar todo el ciclo de vida académico y financiero de los estudiantes: desde la admisión hasta la emisión de certificados, pasando por matrícula, pagos, asistencia, calificaciones y facturación electrónica (e-CF DGII).

### Cifras del sistema

| Métrica | Valor |
|---------|-------|
| Modelos (Eloquent) | 31 |
| Componentes Livewire | 54+ |
| Servicios | 14 |
| Rutas web | 137+ |
| Tests automatizados | 39+ |

---

## 🛠 Stack Tecnológico

### Backend
| Componente | Tecnología | Versión |
|------------|------------|---------|
| Framework | [Laravel](https://laravel.com) | 12.x |
| PHP | PHP | 8.2+ |
| Auth | [Jetstream](https://jetstream.laravel.com) + [Fortify](https://github.com/laravel/fortify) | 5.x / 1.x |
| Permisos | [Spatie Permission](https://spatie.be/docs/laravel-permission) | 6.x |
| API Auth | [Sanctum](https://laravel.com/docs/sanctum) | 4.x |
| Auditoría | [Spatie Activity Log](https://spatie.be/docs/laravel-activitylog) | 4.x |
| PDF | [DomPDF](https://github.com/barryvdh/laravel-dompdf) | 3.x |
| QR | [chillerlan/php-qrcode](https://github.com/chillerlan/php-qrcode) | 5.x |

### Frontend
| Componente | Tecnología |
|------------|------------|
| Reactivity | [Livewire 3](https://livewire.laravel.com) |
| Templates | Blade |
| Build Tool | [Vite](https://vitejs.dev) |
| CSS | Tailwind CSS |

### Base de datos
| Componente | Tecnología |
|------------|------------|
| RDBMS | MySQL 8+ |
| Migraciones | Laravel Migrations (101 archivos) |
| Seeders | Demo + Masivo (100 estudiantes, 70 cursos) |

---

## 📦 Módulos del Sistema

### 📚 Gestión Académica
- **Estudiantes** — Registro completo, cédula, datos de contacto, tutor (menores)
- **Cursos y Carreras** — Creación de cursos, módulos, malla curricular
- **Secciones/Horarios** — Programación por módulo con aula, profesor, horario
- **Matrícula** — Generación automática de código de matrícula al pagar inscripción
- **Inscripción** — Individual (un curso) o agrupada (cuatrimestre completo)
- **Asistencia** — Registro y reportes por sección, exportable a PDF
- **Calificaciones** — Entrada por profesor, consulta por estudiante
- **Calendario Académico** — Eventos institucionales

### 💰 Finanzas y Contabilidad
- **Pagos** — Efectivo, tarjeta Cardnet, con generación automática de NCF (DGII)
- **Plan de cuentas** — Catálogo contable con tipos: Activo, Pasivo, Capital, Ingreso, Gasto, Costo
- **Asientos contables** — Generación automática al registrar pagos
- **Conceptos de pago** — Inscripción, mensualidad, certificado, etc.
- **Gastos y proveedores** — Registro de egresos con asientos contables
- **Cierre de período** — Consolidación contable por mes/trimestre
- **Reportes DGII** — Exportación para formatos 606, 607
- **Estados financieros** — Balance general, estado de resultados, mayor general

### 🖥️ Kiosco de Autoservicio
- Login por PIN de 4 dígitos
- Consulta de estado financiero
- Pago online con Cardnet
- Consulta de calificaciones y horario
- Oferta académica
- Registro de nuevos aspirantes

### 🎓 Certificados
- Editor de plantillas con variables dinámicas
- Generación individual o masiva
- Código QR de verificación pública
- Verificación online: `/certificates/verify/{code}`

### 📊 Reportes PDF
- Recibo de pago (formato térmico)
- Reporte de asistencia
- Calificaciones por sección
- Estados financieros
- Exportación DGII

### 👥 Portales
| Portal | Funcionalidades |
|--------|----------------|
| **Administrador** | Acceso completo, dashboard con métricas |
| **Registro** | Gestión de estudiantes, matrícula, inscripciones |
| **Contabilidad** | Finanzas, DGII, cierre de período |
| **Caja** | Recepción de pagos |
| **Profesor** | Asistencia, calificaciones, horarios |
| **Estudiante** | Consultas, pagos, solicitudes, selección de materias |
| **Aspirante** | Formulario de admisión |

---

## 🏗 Arquitectura

```
app/
├── Http/Controllers/     # Controladores (CardnetController, etc.)
├── Livewire/             # 54+ componentes reactivos
│   ├── Admin/            # Panel administrativo
│   ├── Kiosk/            # Kiosco autoservicio
│   ├── StudentPortal/    # Portal estudiantil
│   ├── TeacherPortal/    # Portal docente
│   └── Finance/          # Módulo financiero
├── Models/               # 31 modelos Eloquent
├── Services/             # 14 servicios de negocio
│   ├── AccountingEngine  # Motor de contabilidad
│   ├── MatriculaService  # Matrícula + sync Moodle
│   ├── EcfService        # Facturación electrónica DGII
│   ├── CardnetService    # Pasarela de pagos
│   └── DgiiExportService # Reportes fiscales
├── Mail/                 # Emails transaccionales
└── Traits/               # RecordsActivity (auditoría)

routes/
├── web.php       # Rutas públicas, dashboard, diagnóstico
├── auth.php      # Login, registro, verificación email
├── api.php       # API REST (WordPress, inscripciones)
├── admin.php     # Panel administrativo (~120 rutas)
├── student.php   # Portal del estudiante
├── teacher.php   # Portal del profesor
├── kiosk.php     # Kiosco de autoservicio
├── cardnet.php   # Callbacks de pago (sin CSRF)
└── reports.php   # Generación de PDFs
```

---

## 📋 Requisitos

| Requisito | Versión mínima |
|-----------|---------------|
| PHP | 8.2+ |
| MySQL | 8.0+ |
| Composer | 2.x |
| Node.js | 18+ |
| npm | 9+ |

### Extensiones PHP requeridas
`mbstring`, `xml`, `curl`, `gd`, `zip`, `pdo_mysql`, `openssl`, `bcmath`

---

## 🚀 Instalación

### Instalación rápida (recomendada)

```bash
# 1. Clonar el repositorio
git clone https://github.com/EasternSam/SGA-PADRE.git
cd SGA-PADRE

# 2. Instalar dependencias
composer install
npm install && npm run build

# 3. Configurar entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos en .env
# DB_DATABASE=sga_centu
# DB_USERNAME=root
# DB_PASSWORD=

# 5. Migrar y poblar datos demo
php artisan migrate:fresh --seed

# 6. Arrancar servidor
php artisan serve
```

### Instalación automatizada (Linux/cPanel)

```bash
bash install-client.sh
```

### Entorno de desarrollo recomendado (Windows)

Se recomienda usar **[Laragon](https://laragon.org)** en lugar de XAMPP:
- MySQL 8.4+ incluido
- Terminal con PATH preconfigurado
- Auto virtual hosts

---

## ⚙️ Configuración

### Variables de entorno principales

```env
# --- Aplicación ---
APP_NAME="Academic+ by GridBase Digital Solutions"
APP_URL=http://127.0.0.1:8000

# --- Base de datos ---
DB_CONNECTION=mysql
DB_DATABASE=sga_centu
DB_USERNAME=root
DB_PASSWORD=

# --- Sesión (importante para Cardnet) ---
SESSION_DRIVER=database

# --- Email (SMTP Gmail) ---
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu-email@gmail.com
MAIL_PASSWORD=tu-app-password

# --- Cardnet (Pasarela de pagos) ---
CARDNET_ENV=sandbox
CARDNET_MERCHANT_ID=349000000
CARDNET_TERMINAL_ID=58585858

# --- Moodle (Aula Virtual) ---
MOODLE_URL=https://tu-moodle.com
MOODLE_TOKEN=tu-token

# --- SaaS / Licencia ---
SAAS_MODE_ENABLED=false
APP_LICENSE_KEY=TU-LICENCIA
```

---

## 🔐 Roles y Permisos

El sistema usa [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) con los siguientes roles:

| Rol | Acceso | Dashboard |
|-----|--------|-----------|
| **Admin** | Acceso total | `/admin/dashboard` |
| **Registro** | Estudiantes, matrícula, inscripciones | `/admin/dashboard` |
| **Contabilidad** | Finanzas, reportes, DGII, cierre | `/admin/dashboard` |
| **Caja** | Solo recepción de pagos | `/admin/dashboard` |
| **Profesor** | Asistencia, calificaciones | `/teacher/dashboard` |
| **Estudiante** | Portal de consultas y pagos | `/student/dashboard` |
| **Solicitante** | Formulario de admisión | `/applicant/dashboard` |

### Credenciales demo (después de seed)

| Rol | Email | Contraseña |
|-----|-------|------------|
| Admin | `admin@admin.com` | `password` |
| Estudiante | `estudiante@estudiante.com` | `password` |
| Profesor | `profesor@profesor.com` | `password` |

---

## 🗂 Estructura de Rutas

| Archivo | Prefijo | Middleware | Descripción |
|---------|---------|------------|-------------|
| `web.php` | `/` | `web` | Públicas, dashboard redirect, diagnóstico |
| `auth.php` | `/` | `web` | Login, registro, password reset |
| `api.php` | `/api` | `api` | REST API (WordPress, inscripciones) |
| `admin.php` | `/admin` | `web, auth, role` | Panel completo de administración |
| `student.php` | `/student` | `web, auth, role:Estudiante` | Portal del estudiante |
| `teacher.php` | `/teacher` | `web, auth, role:Profesor` | Portal del profesor |
| `kiosk.php` | `/kiosk` | `web` | Terminal de autoservicio |
| `cardnet.php` | `/cardnet` | `web` (sin CSRF) | Callbacks de pago |
| `reports.php` | `/reports` | `web, auth` | PDFs y reportes |

---

## 🔌 Integraciones Externas

### Cardnet (Pagos con tarjeta)
- Método: **Redirección POST** (el usuario paga en la página de Cardnet)
- Callbacks: `/cardnet/response` (éxito/fallo), `/cardnet/cancel` (cancelación)
- Restauración automática de sesión al volver
- Soporte para pagos desde el Kiosco

### DGII (Facturación electrónica RD)
- Generación automática de NCF (E31: Crédito Fiscal, E32: Consumo)
- Código de seguridad para verificación QR
- Secuencias atómicas thread-safe
- Exportación de reportes 606/607

### Moodle (Aula Virtual)
- Sincronización automática de inscripciones
- Creación de usuarios en Moodle al matricular
- Prioridad de enlace: Sección → Módulo → Curso
- Envío de credenciales por email

### WordPress
- API REST para formulario de inscripción externo
- Sincronización de cursos y secciones disponibles

---

## 🧪 Tests

```bash
# Ejecutar todos los tests
php artisan test

# Ejecutar por suite
php artisan test --filter=CardnetControllerTest
php artisan test --filter=PaymentModelTest
php artisan test --filter=EnrollmentTest
php artisan test --filter=DashboardRedirectTest
```

### Suites disponibles

| Suite | Tests | Cubre |
|-------|:-----:|-------|
| `CardnetControllerTest` | 13 | Pagos exitosos/fallidos, cancelación, kiosco, session restore |
| `PaymentModelTest` | 10 | Relaciones, status, casting, DGII QR URL |
| `EnrollmentTest` | 9 | CRUD, cascade delete, `is_paid`, transiciones |
| `DashboardRedirectTest` | 6 | Redirect por rol, guest, 403 |
| Auth Tests | 9 | Login, registro, password, verificación email |

> **Nota:** Los tests usan una base de datos MySQL dedicada (`sga_centu_test`). Créala antes de ejecutar los tests.

---

## 🚢 Despliegue en Producción

### cPanel (con `.cpanel.yml`)

```bash
# 1. Subir código (git push al remote de producción)
git push production main

# 2. En el servidor
composer install --no-dev --optimize-autoloader
npm install && npm run build

# 3. Optimizar Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 4. Migrar (con cuidado en producción)
php artisan migrate --force
```

### Variables de producción

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tu-dominio.com

CARDNET_ENV=production
CARDNET_MERCHANT_ID=tu-merchant-real
CARDNET_TERMINAL_ID=tu-terminal-real

SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none
```

---

## 📁 Archivos importantes

| Archivo | Propósito |
|---------|-----------|
| `bootstrap/app.php` | Registro de rutas modulares y middleware |
| `config/permission.php` | Configuración de Spatie Permission |
| `database/seeders/DemoDataSeeder.php` | Datos para desarrollo |
| `database/seeders/MassiveDataSeeder.php` | 100 estudiantes + transacciones |
| `app/Services/AccountingEngine.php` | Motor de contabilidad automática |
| `app/Services/MatriculaService.php` | Matrícula + integración Moodle |
| `app/Services/EcfService.php` | Facturación electrónica DGII |

---

## 📄 Licencia

Software propietario — © [GridBase](https://90s.agency) / Samuel Díaz Pilier

Todos los derechos reservados. Este software requiere una licencia válida para su uso.
