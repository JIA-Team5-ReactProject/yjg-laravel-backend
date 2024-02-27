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
        Schema::create('bus_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_route_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->time('bokhyun');
            $table->time('woobang');
            $table->time('city');
            $table->time('sk');
            $table->time('dc');
            $table->time('bukgu');
            $table->time('bank');
            $table->time('taejeon');
            $table->time('g_campus');
            $table->time('en');
            $table->time('munyang');
            //$table->boolean('start_end'); // 출발, 도착
            //$table->boolean('week_end'); // 주말, 평일
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bus_times', function (Blueprint $table) {
            $table->dropForeign(['bus_route_id']); // 외래 키 제약 조건 삭제
        });
        Schema::dropIfExists('bus_times'); // 테이블 삭제
    }
};
