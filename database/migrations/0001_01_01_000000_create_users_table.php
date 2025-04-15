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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'user'])->default('user');
            $table->unsignedBigInteger('phone_number')->nullable(); 
            $table->enum('gender', ['male', 'female'])->nullable(z);
            $table->enum('name_grades', ['RPL', 'Animasi', 'DKV'])->nullable();
            $table->enum('class', ['10', '11', '12'])->nullable();
            $table->unsignedBigInteger('no_hp_parent')->nullable(); 
            $table->string('name_parent')->nullable();
            $table->string('name_walikelas')->nullable();
            $table->unsignedTinyInteger('absent')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    


        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
