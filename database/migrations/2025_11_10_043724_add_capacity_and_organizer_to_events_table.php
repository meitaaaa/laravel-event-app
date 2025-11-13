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
        Schema::table('events', function (Blueprint $table) {
            // Kapasitas peserta: null = tidak terbatas, angka = terbatas
            $table->integer('max_participants')->nullable()->after('price');
            
            // Penyelenggara event (nama organisasi/instansi)
            $table->string('organizer')->nullable()->after('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['max_participants', 'organizer']);
        });
    }
};
