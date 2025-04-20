<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
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
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->enum('name_department', ['RPL', 'Animasi 2D','Animasi 3D','DKV DG', 'DKV TG'])->nullable();
            $table->enum('class', ['10', '11', '12'])->nullable();

            // Tambahkan langsung di tabel user
            $table->enum('name_grades', [
                'Animasi 3D 1',
                'Animasi 3D 2',
                'Animasi 3D 3',
                'Animasi 2D 4',
                'Animasi 2D 5',
                'RPL 1',
                'RPL 2',
                'DKV DG 1',
                'DKV DG 2',
                'DKV DG 3',
                'DKV TG 4',
                'DKV TG 5',
            ])->nullable();
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

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
        Schema::dropIfExists('grades');
    }
};
