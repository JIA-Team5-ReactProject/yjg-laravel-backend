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
        Schema::create('restaurant_apply_manuals', function (Blueprint $table) {
            $table->id();
            $table->string('division');
            $table->boolean('open')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('restaurant_apply_manuals', function (Blueprint $table) {
            $table->dropForeign(['division_id']); // 추가된 외래 키 제약 조건 삭제
        });
        Schema::dropIfExists('restaurant_apply_manuals'); // 테이블 삭제
    }
};
