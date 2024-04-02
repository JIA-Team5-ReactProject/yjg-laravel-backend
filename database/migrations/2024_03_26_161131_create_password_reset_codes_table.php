<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('password_reset_codes', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('code');
            $table->timestamps();
            $table->timestamp('expires_at')->nullable();
        });
        DB::unprepared(
            'CREATE TRIGGER set_expires_at_insert BEFORE INSERT ON password_reset_codes
                   FOR EACH ROW
                   BEGIN
                       SET NEW.expires_at = NOW() + INTERVAL 3 MINUTE;
                   END;'
        );
        DB::unprepared(
            'CREATE TRIGGER set_expires_at_update BEFORE UPDATE ON password_reset_codes
                   FOR EACH ROW
                   BEGIN
                       SET NEW.expires_at = NOW() + INTERVAL 3 MINUTE;
                   END;'
        );

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS set_expires_at_insert');
        DB::unprepared('DROP TRIGGER IF EXISTS set_expires_at_update');
        Schema::dropIfExists('reset_password_codes');
    }
};
