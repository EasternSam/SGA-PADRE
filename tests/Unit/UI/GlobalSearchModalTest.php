<?php

namespace Tests\Unit\UI;

use Tests\TestCase;

class GlobalSearchModalTest extends TestCase
{
    public function test_global_search_modal_does_not_contain_sga_search_engine_label(): void
    {
        $basePath = dirname(__DIR__, 3);
        $content = file_get_contents($basePath . '/resources/js/components/layout/GlobalSearchModal.jsx');

        $this->assertStringNotContainsString(
            'SGA Search Engine',
            $content,
            'GlobalSearchModal should not display "SGA Search Engine".'
        );
    }

    public function test_global_search_modal_normalizes_and_tokenizes_queries_with_spaces(): void
    {
        $basePath = dirname(__DIR__, 3);
        $content = file_get_contents($basePath . '/resources/js/components/layout/GlobalSearchModal.jsx');

        $this->assertStringContainsString(
            "normalize('NFD')",
            $content,
            'GlobalSearchModal should normalize accents for search matching.'
        );
        $this->assertStringContainsString(
            '.split(/\\s+/)',
            $content,
            'GlobalSearchModal should tokenize queries by whitespace so trailing spaces and multi-word queries return results.'
        );
    }

    public function test_student_search_controllers_handle_multi_word_queries_with_spaces(): void
    {
        $basePath = dirname(__DIR__, 3);
        $posController = file_get_contents($basePath . '/app/Http/Controllers/PosController.php');
        $studentController = file_get_contents($basePath . '/app/Http/Controllers/StudentController.php');

        $this->assertStringContainsString(
            "preg_split('/\\s+/u'",
            $posController,
            'PosController::searchStudents should split multi-word queries by whitespace.'
        );
        $this->assertStringContainsString(
            "preg_split('/\\s+/u'",
            $studentController,
            'StudentController::index should split multi-word queries by whitespace.'
        );
    }
}
