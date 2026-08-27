<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use App\Models\Module;
use App\Models\CourseSchedule;
use App\Models\Enrollment;
use App\Models\AcademicDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Livewire\Livewire;

class AcademicDocumentTest extends TestCase
{
    use RefreshDatabase;

    protected $teacher;
    protected $student1;
    protected $student2;
    protected $schedule;
    protected $module;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('Admin', 'web');
        Role::findOrCreate('Profesor', 'web');
        Role::findOrCreate('Estudiante', 'web');

        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('Profesor');

        $course = Course::create([
            'name' => 'Ingeniería de Software',
            'code' => 'IDS',
            'program_type' => 'degree',
        ]);

        $this->module = Module::create([
            'course_id' => $course->id,
            'name' => 'Matemática Discreta',
            'code' => 'MAT-101',
            'credits' => 4,
        ]);

        $this->schedule = CourseSchedule::create([
            'module_id' => $this->module->id,
            'teacher_id' => $this->teacher->id,
            'section_name' => '01',
            'status' => 'Activo',
            'capacity' => 30,
            'start_date' => now(),
            'end_date' => now()->addMonths(4),
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'days_of_week' => ['Lunes'],
        ]);

        // Crear estudiantes
        $u1 = User::factory()->create();
        $u1->assignRole('Estudiante');
        $this->student1 = Student::create([
            'user_id' => $u1->id,
            'course_id' => $course->id,
            'student_code' => '20261001',
            'first_name' => 'Maria',
            'last_name' => 'Gomez',
            'cedula' => '001-0000000-1',
            'email' => $u1->email,
            'status' => 'Activo',
        ]);

        $u2 = User::factory()->create();
        $u2->assignRole('Estudiante');
        $this->student2 = Student::create([
            'user_id' => $u2->id,
            'course_id' => $course->id,
            'student_code' => '20261002',
            'first_name' => 'Pedro',
            'last_name' => 'Martinez',
            'cedula' => '001-0000000-2',
            'email' => $u2->email,
            'status' => 'Activo',
        ]);

        // Inscribir a student1 en la sección
        Enrollment::create([
            'student_id' => $this->student1->id,
            'course_schedule_id' => $this->schedule->id,
            'status' => 'Cursando',
            'enrollment_date' => now(),
        ]);
    }

    public function test_teacher_can_upload_document(): void
    {
        Storage::fake('public');

        $fakeFile = UploadedFile::fake()->create('silabario.pdf', 500); // 500 KB

        Livewire::actingAs($this->teacher)
            ->test(\App\Livewire\TeacherPortal\TeacherDocuments::class)
            ->set('name', 'Silabario Oficial')
            ->set('selectedScheduleId', $this->schedule->id)
            ->set('file', $fakeFile)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('academic_documents', [
            'name' => 'Silabario Oficial',
            'module_id' => $this->module->id,
            'course_schedule_id' => $this->schedule->id,
            'uploaded_by' => $this->teacher->id,
        ]);
    }

    public function test_students_can_only_access_their_own_section_documents(): void
    {
        // 1. Crear documento asociado a la sección de student1
        $document = AcademicDocument::create([
            'name' => 'Guía de Prácticas',
            'file_path' => '/storage/academic_documents/guia.pdf',
            'file_size' => 1024,
            'file_type' => 'pdf',
            'module_id' => $this->module->id,
            'course_schedule_id' => $this->schedule->id,
            'uploaded_by' => $this->teacher->id,
        ]);

        // 2. Probar student1 (Tiene acceso porque está inscrita en la sección)
        Livewire::actingAs($this->student1->user)
            ->test(\App\Livewire\StudentPortal\DocumentRepository::class)
            ->assertSee('Guía de Prácticas');

        // 3. Probar student2 (No tiene acceso porque no está inscrito)
        Livewire::actingAs($this->student2->user)
            ->test(\App\Livewire\StudentPortal\DocumentRepository::class)
            ->assertDontSee('Guía de Prácticas');
    }
}
