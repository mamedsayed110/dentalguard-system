<?php

use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {

            $table->id();

            $table->string('name');
            $table->string('email')->unique();

            $table->string('password');
            $table->string('phone')->nullable();

            // نوع الحساب
            $table->enum('role', UserRole::values())
                  ->default(UserRole::USER->value);

            // مفعل / موقوف
            $table->boolean('is_active')->default(true);

            // المستخدم ممكن يتبع Super Admin واحد
            $table->unsignedBigInteger('admin_id')->nullable();

            $table->rememberToken();
            $table->timestamps();

            $table->foreign('admin_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
