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
        Schema::create('salon_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_service_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->char('gender', 1);
            $table->string('price', 10);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salon_prices', function (Blueprint $table) {
            $table->dropForeign(['salon_service_id']);
            $table->dropIfExists();
        });
    }
};
