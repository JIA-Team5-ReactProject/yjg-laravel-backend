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
        Schema::create('salon_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salon_service_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->date('reservation_date');
            $table->time('reservation_time');
            $table->string('status', 10)->default('submit');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salon_reservations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['salon_service_id']);
            $table->dropIfExists();
        });
    }
};
