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
            $table->foreignId('salon_price_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamp('reservation_date');
            $table->char('status')->default('S');
            $table->softDeletes();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salon_reservations', function (Blueprint $table) {
            $table->dropForeign(['user_id', 'salon_menu_id']);
            $table->dropIfExists();
        });
    }
};
