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
        DB::unprepared(
            'CREATE EVENT clean_up_reset_codes
            ON SCHEDULE EVERY 6 HOUR
            DO
            DELETE FROM yjg_db.password_reset_codes WHERE `expires_at` < NOW()'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared(
            'DROP EVENT IF EXISTS clean_up_reset_codes'
        );
    }
};
