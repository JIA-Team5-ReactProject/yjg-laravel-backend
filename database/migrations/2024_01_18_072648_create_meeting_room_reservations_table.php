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
            $table->string('meeting_room_number', 10);
            $table->foreign('meeting_room_number')->references('room_number')->on('meeting_rooms')->cascadeOnUpdate()->cascadeOnDelete();
            $table->boolean('status')->default(true);
            $table->date('reservation_date');
            $table->time('reservation_s_time');
            $table->time('reservation_e_time');
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
            $table->dropForeign(['meeting_room_number']);
            
        });
        //Schema::dropIfExists('meeting_room_reservations');
    }
};
