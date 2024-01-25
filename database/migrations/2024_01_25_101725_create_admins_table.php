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
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name', 20);
            $table->string('phone_number', 15);
            $table->string('email', 50)->unique();
            $table->string('password');
            $table->boolean('salon_privilege')->default(false);
            $table->boolean('restaurant_privilege')->default(false);
            $table->boolean('admin_privilege')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
