<?php

namespace Tests\Unit\Admissions;

use PHPUnit\Framework\TestCase;

class SaveReviewDoesNotAutoApproveTest extends TestCase
{
    public function test_save_review_does_not_call_approve_automatically(): void
    {
        $basePath = dirname(__DIR__, 3);
        $controllerFile = file_get_contents($basePath . '/app/Http/Controllers/AdmissionController.php');
        $livewireFile = file_get_contents($basePath . '/app/Livewire/Admissions/Index.php');

        // Extract saveReview method body from AdmissionController
        $start = strpos($controllerFile, 'public function saveReview');
        $end = strpos($controllerFile, 'public function approve', $start);
        $saveReviewBody = substr($controllerFile, $start, $end - $start);

        $this->assertStringNotContainsString('$this->approve(', $saveReviewBody);

        // Extract saveReview method body from Livewire Index
        $lwStart = strpos($livewireFile, 'public function saveReview');
        $lwEnd = strpos($livewireFile, 'public function render', $lwStart);
        $lwSaveReviewBody = substr($livewireFile, $lwStart, $lwEnd - $lwStart);

        $this->assertStringNotContainsString('$this->approveAdmission(', $lwSaveReviewBody);
    }
}
