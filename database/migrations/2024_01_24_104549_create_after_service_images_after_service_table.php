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
        Schema::create('after_service_image_after_service', function (Blueprint $table) {
            $table->id();
            $table->foreignId('after_service_image_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('after_service_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('after_service_image_after_service', function(Blueprint $table) {
            $table->dropForeign(['after_service_image_id', 'after_service_id']);
            $table->dropIfExists();
        });
    }
};
