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
            $table->String('name');
            $table->String('email')->unique();
            $table->String('password');
            $table->String('phone_number');
            $table->enum('role', ['farmer', 'worker', 'driver', 'admin', 'cs'])->nullable();
            $table->text('profile_picture')->nullable();
            $table->boolean('is_active')->nullable();
            $table->boolean('email_verrified')->nullable();
            $table->boolean('phone_verrified')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
