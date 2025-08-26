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
        Schema::create('registrations', function (Blueprint $t) {
        $t->id();
        $t->foreignId('user_id')->constrained()->cascadeOnDelete();
        $t->foreignId('event_id')->constrained()->cascadeOnDelete();
        $t->string('token_hash');       // hash 10 digit
        $t->timestamp('token_sent_at')->nullable();
        $t->enum('status',['registered','cancelled'])->default('registered');
        $t->timestamps();
        $t->unique(['user_id','event_id']); // 1 user 1 kali daftar
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
