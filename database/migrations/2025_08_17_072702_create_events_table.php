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
        Schema::create('events', function (Blueprint $t) {
        $t->id();
        $t->string('title');
        $t->text('description')->nullable();
        $t->date('event_date');
        $t->time('start_time');
        $t->time('end_time')->nullable();
        $t->string('location');
        $t->string('flyer_path')->nullable();
        $t->string('certificate_template_path')->nullable(); // template sertifikat
        $t->boolean('is_published')->default(false);
        $t->timestamp('registration_closes_at')->nullable(); // opsional, bisa dihitung = datetime mulai
        $t->foreignId('created_by')->constrained('users');   // admin pembuat
        $t->timestamps();
        $t->index(['event_date','start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
