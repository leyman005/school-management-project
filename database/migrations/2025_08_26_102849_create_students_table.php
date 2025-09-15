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
    Schema::create('students', function (Blueprint $table) {
      $table->id();
      $table->string('first_name');
      $table->string('last_name');
      $table->string('middle_name')->nullable();
      $table->string('email')->unique();
      $table->string('student_pin')->unique();
      $table->rememberToken();
      $table->string('student_number')->unique();
      $table->string('phone')->nullable();
      $table->date('date_of_birth')->nullable();
      $table->string('gender')->nullable();
      $table->string('address')->nullable();
      $table->string('profile_picture')->nullable();
      $table->string('status')->default('active');
      $table->string('google2fa_secret')->nullable();
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('students');
  }
};
