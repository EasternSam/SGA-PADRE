<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_attendances', function (Blueprint $table) {
            $table->id();
            $table->integer('biometric_id')->index()->comment('emp_code from ZKTeco');
            $table->dateTime('punch_time');
            $table->integer('punch_type')->default(0)->comment('0=Check-in, 1=Check-out, 4=Overtime-in, 5=Overtime-out');
            $table->string('device_serial')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_attendances');
    }
};
