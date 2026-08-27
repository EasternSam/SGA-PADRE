<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use App\Models\Module;
use App\Models\DegreePlan;
use App\Models\PlannedModule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Livewire\Livewire;

class AcademicProjectionTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $student;
    protected $course;
    protected $module1;
    protected $module2;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('Estudiante', 'web');

        $this->course = Course::create([
            'name' => 'Ingeniería de Software',
            'code' => 'IDS',
            'program_type' => 'degree',
            'total_credits' => 120,
            'duration_periods' => 8,
        ]);

        // Crear materias
        $this->module1 = Module::create([
            'course_id' => $this->course->id,
            'name' => 'Introducción a la Programación',
            'code' => 'IDS-101',
            'credits' => 4,
            'period_number' => 1,
        ]);

        $this->module2 = Module::create([
            'course_id' => $this->course->id,
            'name' => 'Programación Orientada a Objetos',
            'code' => 'IDS-102',
            'credits' => 4,
            'period_number' => 2,
        ]);

        // Registrar prerrequisito: module2 requiere module1
        $this->module2->prerequisites()->attach($this->module1->id);

        // Crear usuario y estudiante
        $this->user = User::factory()->create();
        $this->user->assignRole('Estudiante');

        $this->student = Student::create([
            'user_id' => $this->user->id,
            'course_id' => $this->course->id,
            'student_code' => '20260001',
            'first_name' => 'Juan',
            'last_name' => 'Perez',
            'cedula' => '001-0000000-0',
            'email' => $this->user->email,
            'status' => 'Activo',
        ]);
    }

    public function test_student_can_render_academic_projection(): void
    {
        $response = $this->actingAs($this->user)->get('/student/projection');
        $response->assertStatus(200);
    }

    public function test_degree_plan_is_created_automatically(): void
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\StudentPortal\AcademicProjection::class)
            ->assertSet('pace', 5);

        $this->assertDatabaseHas('degree_plans', [
            'student_id' => $this->student->id,
            'pace' => '5',
        ]);
    }

    public function test_auto_generate_projections_respects_prerequisites(): void
    {
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\StudentPortal\AcademicProjection::class)
            ->call('autoGeneratePlan')
            ->assertHasNoErrors();

        // module1 debe planificarse antes que module2 debido al prerrequisito
        $plan = DegreePlan::where('student_id', $this->student->id)->first();
        
        $planned1 = PlannedModule::where('degree_plan_id', $plan->id)
            ->where('module_id', $this->module1->id)
            ->first();

        $planned2 = PlannedModule::where('degree_plan_id', $plan->id)
            ->where('module_id', $this->module2->id)
            ->first();

        $this->assertNotNull($planned1);
        $this->assertNotNull($planned2);
        
        // El periodo sugerido de module1 debe ser menor al periodo de module2
        $this->assertTrue($planned1->target_period < $planned2->target_period);
    }

    public function test_manual_planning_validates_prerequisites(): void
    {
        // Si intentamos planificar module2 en el periodo 1 sin haber aprobado/planificado module1 antes, debe fallar
        Livewire::actingAs($this->user)
            ->test(\App\Livewire\StudentPortal\AcademicProjection::class)
            ->call('planModuleManually', $this->module2->id, 1)
            ->assertSet('errorMessage', 'No puedes planificar Programación Orientada a Objetos en el Período 1 porque requiere Introducción a la Programación, la cual no está aprobada ni planificada para un período anterior.');
        
        $this->assertDatabaseMissing('planned_modules', [
            'module_id' => $this->module2->id,
        ]);
    }
}
