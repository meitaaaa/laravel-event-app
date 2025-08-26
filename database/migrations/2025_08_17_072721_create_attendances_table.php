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
        Schema::create('attendances', function (Blueprint $t) {
        $t->id();
        $t->foreignId('registration_id')->constrained()->cascadeOnDelete();
        $t->foreignId('event_id')->constrained()->cascadeOnDelete();
        $t->foreignId('user_id')->constrained()->cascadeOnDelete();
        $t->string('token_entered');     // simpan terakhir (opsional, bisa dihapus)
        $t->enum('status',['present','invalid'])->default('present');
        $t->timestamp('attendance_time');
        $t->timestamps();
        $t->unique(['registration_id']); // 1 kali hadir
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
