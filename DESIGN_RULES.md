# REGLAS DE DISEÑO UNIVERSAL Y SISTEMA DE COMPONENTES ITLA / SGA
> **ESTRICTO CUMPLIMIENTO**: Esta guía define el estándar de integridad visual y arquitectura de interfaz para todos los módulos, vistas y componentes del sistema. Cualquier desarrollo debe seguir estas reglas para garantizar cero discrepancias de diseño.

---

## 1. REGLA UNIVERSAL DE BOTONES: `ProgressFoldButton`
Todos los botones de acción, envío, modales y procesos interactivos en la plataforma deben utilizar **`ProgressFoldButton`** (GodUI Design System).

### Especificación de Uso
- **Importación**:
  ```jsx
  import { ProgressFoldButton } from '@/components/godui';
  ```
- **Variantes Obligatorias**:
  - `variant="primary"`: Acciones principales, confirmaciones y llamadas prioritarias (Color Azul Institucional `#0b2c79` / Azul 600 Dark).
  - `variant="secondary"`: Acciones secundarias, cancelaciones, filtros secundarios y herramientas de soporte.
  - `variant="destructive"`: Acciones peligrosas (eliminar registros, desvincular, revocar).
  - `variant="success"`: Acciones de aprobación, cobro completado o certificación.
  - `variant="outline"`: Botones limpios de navegación complementaria.
- **Tamaños Estandarizados**:
  - `size="sm"`: Para tablas de datos, tarjetas métricas compactas y barras de herramientas densas (`px-3 py-1.5 text-xs`).
  - `size="md"`: Tamaño predeterminado para formularios, tarjetas principales y modales (`px-4 py-2.5 text-sm`).
  - `size="lg"`: Botones destacados de portada, terminales táctiles (Kioskos) y confirmaciones mayores (`px-6 py-3.5 text-base`).
- **Estados de Carga y Progreso**:
  - `status="idle"`: Estado normal con animación 3D de inclinación en hover (`rotateX(32deg)`).
  - `status="loading"`: Despliega la bandeja interior automáticamente con barra de progreso reactiva.
  - `progress={porcentaje}`: Progreso determinado (0–100%) o indeterminado (animación continua) si se omite.

---

## 2. REGLA UNIVERSAL DE SELECTORES SEGMENTADOS: `SegmentedControl`
Toda selección entre 2 y 6 opciones (cambios de vista, filtros de tiempo, estados de matrícula, turnos) DEBE implementarse mediante **`SegmentedControl`**.

### Especificación de Uso
- **Importación**:
  ```jsx
  import { SegmentedControl } from '@/components/godui';
  ```
- **Prohibición**: Queda terminantemente prohibido crear grupos manuales de botones con clases condicionales ternarias (`academicView === 'x' ? 'bg-...' : 'text-...'`).
- **Comportamiento**:
  - Deslizador elástico con física de resortes (`framer-motion`).
  - Soporte integrado de iconos SVG y badges de conteo numérico.
  - Tamaños: `size="sm"` (vistas dentro de tarjetas), `size="md"` (filtros de página), `size="lg"` (pantallas completas).

---

## 3. REGLA UNIVERSAL DE MENÚ CONTEXTUAL Y DOCK: `TabBar`
En cada vista principal, panel operativo o dashboard analítico, DEBE existir un **mini menú contextual persistente (`TabBar`)** para agilizar la navegación entre subsecciones y funciones críticas.

### Especificación de Uso
- **Importación**:
  ```jsx
  import { TabBar } from '@/components/godui';
  ```
- **Ubicación y Estilo**:
  - Flotante centrado (dock) en la parte inferior o anclado contextualmente en la cabecera del módulo.
  - Borde sutil con cristal translúcido (`backdrop-blur-xl bg-white/95 dark:bg-[#161D30]/95`).
  - Desplazamiento fluido del indicador de pestaña activa y efecto de rebote ("pop" háptico) en los iconos.
  - Insignias de notificación en tiempo real (`badge`).

---

## 4. PALETA DE COLORES Y MODO OSCURO
- **Color Primario Institucional**: `#0b2c79` (Azul Zafiro ITLA) en modo claro / `oklch(0.55 0.19 266)` o `#3b82f6` en modo oscuro.
- **Superficies**:
  - Modo Claro: Fondo general `#F4F5F7`, Tarjetas `#FFFFFF`, Bordes `#E5E7EB`.
  - Modo Oscuro: Fondo general `#0B0F19`, Tarjetas `#161D30`, Bordes `rgba(255, 255, 255, 0.08)`.
- **Tipografía**: Fuente del sistema Inter / Outfit con tracking ajustado (`tracking-tight` en encabezados).

---

## 5. POLÍTICA DE CERO ERRORES
1. **Consistencia de Botones**: Ningún botón puede tener esquinas cuadradas arbitrarias ni sombras planas desalineadas.
2. **Interactividad Reactiva**: Todos los estados interactivos deben contar con transiciones suaves (`transition-all duration-200`).
3. **Persistencia Accesible**: Todo componente debe ser accesible vía teclado (`focus-visible:ring-2`) y compatible con lectores de pantalla (`aria-selected`, `aria-busy`, `role="tab"`).
