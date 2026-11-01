<?php

namespace Tests\Unit\Admissions;

use PHPUnit\Framework\TestCase;

class AdmissionDocumentSpanishLabelsTest extends TestCase
{
    public function test_admissions_pipeline_and_controllers_translate_document_keys_to_spanish(): void
    {
        $basePath = dirname(__DIR__, 3);
        $pipelineJsx = file_get_contents($basePath . '/resources/js/Pages/Admissions/Pipeline.jsx');
        $admissionController = file_get_contents($basePath . '/app/Http/Controllers/AdmissionController.php');

        $this->assertStringContainsString("birth_certificate: 'Acta de Nacimiento'", $pipelineJsx);
        $this->assertStringContainsString("id_card: 'Cédula / Identidad'", $pipelineJsx);
        $this->assertStringContainsString("high_school_record: 'Récord de Bachillerato'", $pipelineJsx);
        $this->assertStringContainsString("photo: 'Fotografía 2x2'", $pipelineJsx);
        $this->assertStringContainsString('getDocumentLabel(key)', $pipelineJsx);

        $this->assertStringContainsString("'birth_certificate' => 'Acta de Nacimiento'", $admissionController);
        $this->assertStringContainsString("'id_card' => 'Cédula / Identidad'", $admissionController);
        $this->assertStringContainsString("'high_school_record' => 'Récord de Bachillerato'", $admissionController);
        $this->assertStringContainsString("'photo' => 'Fotografía 2x2'", $admissionController);
    }
}
