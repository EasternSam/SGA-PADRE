<?php

namespace Tests\Unit\UI;

use PHPUnit\Framework\TestCase;

class ViewSubtitlesRemovedTest extends TestCase
{
    private function projectPath(string $relative = ''): string
    {
        $base = dirname(__DIR__, 3);

        return $relative === '' ? $base : $base . '/' . ltrim($relative, '/');
    }

    public function test_views_do_not_contain_redundant_explanatory_subtitles(): void
    {
        $checks = [
            'resources/js/Pages/Admin/Settings/Index.jsx' => [
                'Administra la conectividad con WordPress (Academic+ Catalog), Aula Virtual Moodle, Facturación Bills, Correo y Marca.',
                'Sincroniza la oferta formativa, estudiantes y reservas online con el plugin de WordPress.',
                'Genera un código temporal único para vincular este sistema con tu plugin de WordPress automáticamente sin configurar tokens a mano.',
                'Matriculación automatizada de alumnos en aulas virtuales y Single Sign-On (SSO) directo para estudiantes.',
                'Emisión de facturas fiscales y sincronización con el sistema externo Bills.',
                'Define una paleta armónica de 4 colores que sustituye los colores genéricos del dashboard, gráficos y módulos para cualquier institución.',
                'Esta composición cromática sustituye los colores de plantilla genéricos en todo el panel.',
                'Ajusta la altura visual del logotipo en la cabecera del menú lateral izquierdo (entre 24px y 120px).',
            ],
            'resources/js/Pages/Pos/Index.jsx' => [
                'Cobro de servicios, emisión de recibos y facturación electrónica institucional',
            ],
            'resources/js/Pages/Finance/Dashboard.jsx' => [
                'Comparativa mensual de ingresos cobrados vs montos en espera',
            ],
            'resources/js/Pages/Finance/DgiiReports/Index.jsx' => [
                'Facturas de gastos con proveedores locales, desglose de ITBIS facturado, ITBIS retenido y retención de ISR.',
                'Recibos de estudiantes, matrículas, aranceles y facturas con comprobantes fiscales',
            ],
            'resources/js/Pages/Grades/MatrixEntry.jsx' => [
                'Promedios acumulados por asignatura para todos los estudiantes matriculados.',
            ],
            'resources/js/Pages/Accounting/PeriodClosing.jsx' => [
                'Selecciona la fecha límite de corte contable que deseas proteger.',
            ],
            'resources/js/Pages/Profile/Edit.jsx' => [
                'Actualice su nombre legal, correo electrónico institucional y foto de perfil.',
                'Asegúrese de que su cuenta utilice una contraseña robusta para mantener la integridad de sus accesos.',
                'Una vez eliminada su cuenta, todos los recursos y datos asociados serán permanentemente destruidos.',
            ],
            'resources/js/Pages/StudentPortal/Dashboard.jsx' => [
                'Organización de clases de Lunes a Sábado',
            ],
            'resources/js/Pages/Applicant/Portal.jsx' => [
                'Completa todos los campos con exactitud de acuerdo con tus documentos de identidad oficial.',
            ],
            'resources/js/Pages/Kiosk/TouchTerminal.jsx' => [
                'Toca cualquiera de las opciones para acceder a tus servicios académicos y financieros.',
            ],
            'resources/js/components/pos/ShiftDrawer.jsx' => [
                'Cobros registrados en la fecha de hoy',
            ],
        ];

        foreach ($checks as $file => $forbiddenSubtitles) {
            $content = file_get_contents($this->projectPath($file));
            foreach ($forbiddenSubtitles as $subtitle) {
                $this->assertStringNotContainsString(
                    $subtitle,
                    $content,
                    "El subtítulo redundante '{$subtitle}' debe ser removido de {$file}"
                );
            }
        }
    }
}
