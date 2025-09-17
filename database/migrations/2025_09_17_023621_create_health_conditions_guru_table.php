<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('health_condition_gurus', function (Blueprint $table) {
            $table->id();

            // Relasi ke tabel guru
            $table->foreignId('guru_id')->constrained('gurus')->onDelete('cascade');

            // Relasi ke admin (user yang input data)
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');

            // Unik kombinasi guru_id + id_condition_guru
            $table->unsignedInteger('id_guru_condition')->nullable();

            // Data kesehatan
            $table->integer('tension'); // tekanan darah
            $table->integer('temperature'); // suhu tubuh
            $table->decimal('height', 5, 2); // tinggi
            $table->decimal('weight', 5, 2); // berat
            $table->integer('spo2'); // saturasi oksigen
            $table->integer('pulse'); // nadi
            $table->text('therapy'); // terapi
            $table->text('anamnesis'); // keluhan / anamnesis

            // Soft delete manual
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            // Unique constraint
            $table->unique(['guru_id', 'id_guru_condition']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('health_condition_gurus');
    }
};
