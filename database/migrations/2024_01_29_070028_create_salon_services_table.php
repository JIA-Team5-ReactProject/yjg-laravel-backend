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
        Schema::create('salon_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_category_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('service',15);
            $table->string('price', 10);
            $table->string('gender', 10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salon_services', function (Blueprint $table) {
            $table->dropForeign(['salon_category_id']);
            $table->dropIfExists();
        });
    }
};
