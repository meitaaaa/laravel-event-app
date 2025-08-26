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
    {Schema::create('certificates', function (Blueprint $table) {
        $table->id();
        $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
        $table->string('serial_number')->unique();
        $table->string('file_path');    // pdf
        $table->timestamp('issued_at');
    });
    
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
