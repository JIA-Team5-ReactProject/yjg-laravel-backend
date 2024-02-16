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
        Schema::create('used_qr_codes', function (Blueprint $table) {
            $table->id();
            $table->string('used_code')->unique();// 사용된 QR 코드를 저장할 컬럼 (고유성 유지)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('used_qr_codes');
    }
};
