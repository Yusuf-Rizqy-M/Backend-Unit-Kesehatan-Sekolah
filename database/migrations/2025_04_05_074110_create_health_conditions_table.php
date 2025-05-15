<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHealthConditionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('health_conditions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
        
            // Kolom ini harus didefinisikan dulu sebelum digunakan dalam unique
            $table->unsignedInteger('id_user_condition')->nullable();

        
            $table->integer('tension');
            $table->integer('temperature');
            $table->decimal('height', 5, 2);
            $table->decimal('weight', 5, 2);
            $table->integer('spo2');
            $table->integer('pulse');
            $table->text('therapy');
            $table->text('anamnesis');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        
            // Pindahkan ke bawah setelah kolom didefinisikan
            $table->unique(['user_id', 'id_user_condition']);
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('health_conditions');
    }
}
