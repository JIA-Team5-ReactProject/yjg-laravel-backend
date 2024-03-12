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
        Schema::create('bus_schedules', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('bus_round_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('station');
            $table->string('bus_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
        Schema::table('bus_schedules', function (Blueprint $table) {
            // bus_round_id 외래 키 제약 조건 삭제
            $table->dropForeign(['bus_round_id']); // 추가된 외래 키 제약 조건 삭제
        });
        Schema::dropIfExists('bus_schedules'); // 테이블 삭제
    }
};
