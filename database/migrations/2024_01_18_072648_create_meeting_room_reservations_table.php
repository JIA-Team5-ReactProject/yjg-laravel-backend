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
        Schema::create('meeting_room_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('meeting_room_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->char('status');
            $table->timestamp('reservation_date');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meeting_room_reservations', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['meeting_room_id']);
            $table->dropIfExists();
        });
    }
};
